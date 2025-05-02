<?php
/**
 * Razorpay Payment Page
 * Handles Razorpay payment initialization and callback
 */

// Include initialization file
require_once __DIR__ . '/../includes/init.php';

// Include email functionality
require_once ROOT_PATH . '/app/config/email.php';

// Include Razorpay configuration
require_once ROOT_PATH . '/app/config/razorpay.php';

// Check if order ID is provided
if (!isset($_GET['order_id']) && !isset($_SESSION['pending_order_id'])) {
    header('Location: shop.php');
    exit;
}

// Get order ID from the query string or session
$orderId = isset($_GET['order_id']) ? $_GET['order_id'] : $_SESSION['pending_order_id'];

// Get order details
$order = getOrderById($orderId);
if (!$order) {
    $_SESSION['error'] = 'Order not found';
    header('Location: cart.php');
    exit;
}

// Get order items and billing details
$orderItems = getOrderItems($orderId);
$billingDetails = getBillingDetailsByOrderId($orderId);

// Check if this is a callback from Razorpay after payment
if (isset($_POST['razorpay_payment_id']) && isset($_POST['razorpay_order_id']) && isset($_POST['razorpay_signature'])) {
    $razorpayPaymentId = $_POST['razorpay_payment_id'];
    $razorpayOrderId = $_POST['razorpay_order_id'];
    $signature = $_POST['razorpay_signature'];
    
    // Verify the payment signature
    $isValidSignature = verifyRazorpaySignature($razorpayOrderId, $razorpayPaymentId, $signature);
    
    if ($isValidSignature) {
        // Get payment details from Razorpay API
        $paymentDetails = fetchRazorpayPayment($razorpayPaymentId);
        
        // Make sure we received payment details and payment is authorized or captured
        if ($paymentDetails && isset($paymentDetails['status']) && in_array($paymentDetails['status'], ['authorized', 'captured'])) {
            // Record payment transaction with completed status
            $paymentResult = recordPaymentTransaction(
                $orderId,
                $razorpayPaymentId,
                'razorpay',
                $order['total'],
                'completed',
                json_encode($paymentDetails)
            );
            
            if ($paymentResult['success']) {
                // Get updated order details with new payment status
                $updatedOrder = getOrderById($orderId);
                
                // Get transaction details for email
                $transactionDetails = [
                    'transaction_id' => $razorpayPaymentId,
                    'status' => 'completed'
                ];
                
                // Send order notification emails with transaction details
                sendOrderNotificationEmails($orderId, $updatedOrder, $orderItems, $billingDetails, $transactionDetails);
                
                // Clear cart only after successful payment confirmation
                clearCart();
                
                // Store order ID in session for confirmation page
                $_SESSION['last_order_id'] = $orderId;
                
                // Redirect to order received page
                header('Location: order-received.php?order_id=' . $orderId);
                exit;
            } else {
                $_SESSION['error'] = 'Payment processing error: ' . $paymentResult['message'];
                header('Location: checkout.php');
                exit;
            }
        } else {
            // Payment was not authorized or captured
            $_SESSION['error'] = 'Payment was not completed. Please try again or contact support.';
            header('Location: checkout.php');
            exit;
        }
    } else {
        $_SESSION['error'] = 'Payment verification failed. Please contact support.';
        header('Location: checkout.php');
        exit;
    }
}

// Create Razorpay order if not already created
if (!isset($_SESSION['razorpay_order_id_' . $orderId])) {
    // Create a Razorpay order
    try {
        // Sleep for a second to avoid rate limiting
        usleep(500000); // 500ms delay
        
        $razorpayOrder = createRazorpayOrder(
            $order['order_number'],
            $order['total'],
            [
                'order_id' => $orderId,
                'customer_name' => $billingDetails['first_name'] . ' ' . $billingDetails['last_name'],
                'customer_email' => $billingDetails['email'],
                'customer_phone' => $billingDetails['phone']
            ]
        );
        
        if (!$razorpayOrder) {
            throw new Exception('Failed to create Razorpay order - API returned null');
        }
        
        // Store Razorpay order ID in session
        $_SESSION['razorpay_order_id_' . $orderId] = $razorpayOrder['id'];
        
    } catch (Exception $e) {
        // Display error and log it
        echo '<div style="color:red; padding:20px; margin:20px; border:1px solid red;">';
        echo 'Error creating Razorpay order: ' . $e->getMessage() . '<br>';
        echo 'Debug info: orderID=' . $orderId . ', amount=' . $order['total'] . '<br>';
        echo 'Please try again or contact support.';
        echo '</div>';
        
        // Log error
        error_log('Razorpay Error: ' . $e->getMessage());
        $_SESSION['error'] = 'Failed to create payment order: ' . $e->getMessage();
    }
} else {
    // Get existing Razorpay order ID from session
    $razorpayOrderId = $_SESSION['razorpay_order_id_' . $orderId];
    
    // For simplicity, we'll create a new order object with the existing ID
    $razorpayOrder = ['id' => $razorpayOrderId];
}

// Set page title and load header
$pageTitle = 'Complete Your Payment - STR Works';
$custom_css = 'checkout.css';
require_once ROOT_PATH . '/app/views/partials/header.php';
?>

<div class="container checkout-container">
    <h1 class="page-title">Complete Your Payment</h1>
    
    <!-- Progress Steps -->
    <div class="progress-steps">
        <div class="step">
            <div class="step-number">1</div>
            <span class="step-text">Shopping Cart</span>
            <div class="arrow">→</div>
        </div>
        <div class="step active">
            <div class="step-number pink">2</div>
            <span class="step-text">Payment & Delivery Options</span>
            <div class="arrow">→</div>
        </div>
        <div class="step">
            <div class="step-number">3</div>
            <span class="step-text">Order Received</span>
        </div>
    </div>
    
    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($_SESSION['error']); ?></div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>
    
    <div class="order-summary">
        <h2>Order Summary</h2>
        
        <div class="order-details">
            <div class="order-info">
                <p><strong>Order Number:</strong> <?php echo htmlspecialchars($order['order_number']); ?></p>
                <p><strong>Total Amount:</strong> ₹<?php echo number_format($order['total'], 2); ?></p>
            </div>
            
            <div class="order-items">
                <?php foreach ($orderItems as $item): ?>
                    <div class="order-item">
                        <span class="item-name"><?php echo htmlspecialchars($item['title']); ?> × <?php echo $item['quantity']; ?></span>
                        <span class="item-price">₹<?php echo number_format($item['subtotal'], 2); ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        
        <div class="payment-container">
            <h3>Complete Your Payment</h3>
            <p>You have chosen to pay via Razorpay. Please click the button below to complete your payment.</p>
            <p class="payment-note"><strong>Note:</strong> Your order will not be processed until payment is confirmed.</p>
            
            <div id="razorpay-button-container">
                <button id="razorpay-payment-button" class="payment-button">
                    <img src="assets/img/razorpay-logo.svg" alt="Razorpay" class="razorpay-logo">
                    Pay ₹<?php echo number_format($order['total'], 2); ?>
                </button>
            </div>
            
            <p class="payment-note">You will be redirected to Razorpay's secure payment page to complete your payment.</p>
        </div>
    </div>
</div>

<style>
    .payment-container {
        background-color: #f9f9f9;
        padding: 20px;
        border-radius: 8px;
        margin-top: 20px;
    }
    
    .order-item {
        display: flex;
        justify-content: space-between;
        padding: 10px 0;
        border-bottom: 1px solid #eee;
    }
    
    .payment-button {
        background-color: #2d84fb;
        color: white;
        border: none;
        padding: 12px 24px;
        border-radius: 4px;
        font-weight: 600;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 20px auto;
        cursor: pointer;
        transition: background-color 0.3s;
    }
    
    .payment-button:hover {
        background-color: #1a6ad2;
    }
    
    .razorpay-logo {
        height: 20px;
        margin-right: 10px;
    }
    
    .payment-note {
        font-size: 0.9rem;
        color: #666;
        text-align: center;
        margin-top: 15px;
    }
</style>

<!-- Include Razorpay JavaScript SDK -->
<script src="https://checkout.razorpay.com/v1/checkout.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Log key information
    console.log('Razorpay payment setup initializing');
    console.log('Order amount:', <?php echo $order['total']; ?>);
    
    const options = {
        key: '<?php echo RAZORPAY_KEY_ID; ?>',
        amount: <?php echo round($order['total'] * 100); ?>,
        currency: 'INR',
        name: 'STR Works',
        description: 'Order #<?php echo $order['order_number']; ?>',
        order_id: '<?php echo isset($razorpayOrder['id']) ? $razorpayOrder['id'] : ''; ?>',
        image: '<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]"; ?>/assets/img/STR-logo.webp',
        prefill: {
            name: '<?php echo htmlspecialchars($billingDetails['first_name'] . ' ' . $billingDetails['last_name']); ?>',
            email: '<?php echo htmlspecialchars($billingDetails['email']); ?>',
            contact: '<?php echo htmlspecialchars($billingDetails['phone']); ?>'
        },
        notes: {
            address: '<?php echo htmlspecialchars($billingDetails['street_address_1'] . ', ' . $billingDetails['city']); ?>',
            order_id: '<?php echo $orderId; ?>'
        },
        theme: {
            color: '#2d84fb'
        },
        handler: function(response) {
            // Show loading indicator
            document.getElementById('razorpay-button-container').innerHTML = '<div class="processing-payment">Processing payment, please wait...</div>';
            
            // When payment is successful, submit form with payment details
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = 'razorpay-payment.php?order_id=<?php echo $orderId; ?>';
            
            const inputPaymentId = document.createElement('input');
            inputPaymentId.type = 'hidden';
            inputPaymentId.name = 'razorpay_payment_id';
            inputPaymentId.value = response.razorpay_payment_id;
            form.appendChild(inputPaymentId);
            
            const inputOrderId = document.createElement('input');
            inputOrderId.type = 'hidden';
            inputOrderId.name = 'razorpay_order_id';
            inputOrderId.value = response.razorpay_order_id;
            form.appendChild(inputOrderId);
            
            const inputSignature = document.createElement('input');
            inputSignature.type = 'hidden';
            inputSignature.name = 'razorpay_signature';
            inputSignature.value = response.razorpay_signature;
            form.appendChild(inputSignature);
            
            document.body.appendChild(form);
            form.submit();
        },
        modal: {
            ondismiss: function() {
                console.log('Payment modal dismissed');
            },
            escape: true,
            backdropclose: false
        }
    };
    
    const rzp = new Razorpay(options);
    
    // Error handling for Razorpay
    rzp.on('payment.failed', function(response) {
        console.error('Payment failed:', response.error);
        alert('Payment failed: ' + response.error.description);
    });
    
    // Auto-open Razorpay checkout if debug parameter is provided
    <?php if (isset($_GET['auto_open']) && $_GET['auto_open'] === '1'): ?>
    setTimeout(function() {
        try {
            rzp.open();
        } catch (e) {
            console.error('Error opening Razorpay:', e);
            alert('Error opening payment form: ' + e.message);
        }
    }, 1000);
    <?php endif; ?>
    
    document.getElementById('razorpay-payment-button').onclick = function(e) {
        console.log('Payment button clicked, opening Razorpay');
        try {
            rzp.open();
        } catch (e) {
            console.error('Error opening Razorpay:', e);
            alert('Error opening payment form: ' + e.message);
        }
        e.preventDefault();
    };
});
</script>

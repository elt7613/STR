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
    <div class="payment-wrapper">
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
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle"></i>
                <?php echo htmlspecialchars($_SESSION['error']); ?>
            </div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>
        
        <div class="payment-card">
            <div class="order-summary">
                <h2>Order Summary</h2>
                
                <div class="order-details">
                    <div class="order-info">
                        <div class="info-row">
                            <span class="info-label">Order Number:</span>
                            <span class="info-value"><?php echo htmlspecialchars($order['order_number']); ?></span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Total Amount:</span>
                            <span class="info-value amount">₹<?php echo number_format($order['total'], 2); ?></span>
                        </div>
                    </div>
                    
                    <div class="order-items">
                        <h3>Items in Order</h3>
                        <?php foreach ($orderItems as $item): ?>
                            <div class="order-item">
                                <span class="item-name"><?php echo htmlspecialchars($item['title']); ?> × <?php echo $item['quantity']; ?></span>
                                <span class="item-price">₹<?php echo number_format($item['subtotal'], 2); ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            
            <div class="payment-container">
                <div class="payment-header">
                    <h3>Complete Your Payment</h3>
                    <div class="payment-method-logo">
                        <img src="assets/img/razorpay-logo.svg" alt="Razorpay" class="razorpay-logo">
                    </div>
                </div>
                
                <div class="payment-info">
                    <p>You have chosen to pay via Razorpay. Please click the button below to complete your payment.</p>
                    <p class="payment-note"><i class="fas fa-info-circle"></i> Your order will not be processed until payment is confirmed.</p>
                </div>
                
                <div id="razorpay-button-container">
                    <button id="razorpay-payment-button" class="payment-button">
                        <span class="button-content">
                            <img src="assets/img/razorpay-logo.svg" alt="Razorpay" class="razorpay-logo">
                            <span>Pay ₹<?php echo number_format($order['total'], 2); ?></span>
                        </span>
                        <span class="button-loading">
                            <i class="fas fa-spinner fa-spin"></i>
                            Processing...
                        </span>
                    </button>
                </div>
                
                <div class="payment-security">
                    <i class="fas fa-lock"></i>
                    <span>Secure Payment</span>
                </div>
                
                <p class="payment-note redirect-note">
                    <i class="fas fa-external-link-alt"></i>
                    You will be redirected to Razorpay's secure payment page to complete your payment.
                </p>
            </div>
        </div>
    </div>
</div>

<style>
    .payment-wrapper {
        max-width: 800px;
        margin: 0 auto;
        padding: 2rem;
    }

    .payment-card {
        background: #fff;
        border-radius: 12px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        overflow: hidden;
        margin-top: 2rem;
    }

    .order-summary {
        padding: 2rem;
        border-bottom: 1px solid #eee;
    }

    .order-summary h2 {
        color: #333;
        margin-bottom: 1.5rem;
        font-size: 1.5rem;
    }

    .order-details {
        display: grid;
        gap: 2rem;
    }

    .info-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 0.5rem 0;
    }

    .info-label {
        color: #666;
    }

    .info-value {
        font-weight: 500;
    }

    .info-value.amount {
        color: #2d84fb;
        font-size: 1.2rem;
    }

    .order-items {
        background: #f8f9fa;
        padding: 1.5rem;
        border-radius: 8px;
    }

    .order-items h3 {
        margin-bottom: 1rem;
        font-size: 1.1rem;
        color: #444;
    }

    .order-item {
        display: flex;
        justify-content: space-between;
        padding: 0.75rem 0;
        border-bottom: 1px solid #eee;
    }

    .order-item:last-child {
        border-bottom: none;
    }

    .payment-container {
        padding: 2rem;
    }

    .payment-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1.5rem;
    }

    .payment-header h3 {
        margin: 0;
        font-size: 1.3rem;
        color: #333;
    }

    .payment-method-logo {
        height: 30px;
    }

    .payment-method-logo img {
        height: 100%;
    }

    .payment-info {
        margin-bottom: 2rem;
    }

    .payment-info p {
        color: #666;
        margin-bottom: 1rem;
    }

    .payment-button {
        background: #2d84fb;
        color: white;
        border: none;
        padding: 1rem 2rem;
        border-radius: 8px;
        font-weight: 600;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto;
        cursor: pointer;
        transition: all 0.3s ease;
        width: 100%;
        max-width: 300px;
        position: relative;
        overflow: hidden;
    }

    .payment-button:hover {
        background: #1a6ad2;
        transform: translateY(-2px);
    }

    .payment-button .button-content {
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .payment-button .button-loading {
        display: none;
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
    }

    .payment-button.loading .button-content {
        display: none;
    }

    .payment-button.loading .button-loading {
        display: flex;
    }

    .payment-security {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        color: #666;
        margin: 1.5rem 0;
    }

    .payment-note {
        font-size: 0.9rem;
        color: #666;
        text-align: center;
        margin-top: 1rem;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
    }

    .payment-note i {
        color: #2d84fb;
    }

    .redirect-note {
        margin-top: 2rem;
    }

    .alert-danger {
        background: #fff3f3;
        border: 1px solid #ffcdd2;
        color: #d32f2f;
        padding: 1rem;
        border-radius: 8px;
        margin-bottom: 1.5rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .alert-danger i {
        color: #d32f2f;
    }

    @media (max-width: 768px) {
        .payment-wrapper {
            padding: 1rem;
        }

        .payment-card {
            margin-top: 1rem;
        }

        .order-summary,
        .payment-container {
            padding: 1.5rem;
        }

        .payment-button {
            padding: 0.875rem 1.5rem;
        }
    }
</style>

<!-- Include Razorpay JavaScript SDK -->
<script src="https://checkout.razorpay.com/v1/checkout.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const paymentButton = document.getElementById('razorpay-payment-button');
    const buttonContainer = document.getElementById('razorpay-button-container');
    
    // Log key information for debugging
    console.log('Razorpay payment setup initializing');
    console.log('Order amount:', <?php echo $order['total']; ?>);
    console.log('Razorpay Order ID:', '<?php echo isset($razorpayOrder['id']) ? $razorpayOrder['id'] : ''; ?>');
    
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
            // Show loading state
            paymentButton.classList.add('loading');
            
            // Create and submit form
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
                paymentButton.classList.remove('loading');
            },
            escape: true,
            backdropclose: false
        }
    };
    
    const rzp = new Razorpay(options);
    
    rzp.on('payment.failed', function(response) {
        console.error('Payment failed:', response.error);
        paymentButton.classList.remove('loading');
        buttonContainer.innerHTML = `
            <div class="payment-error">
                <i class="fas fa-exclamation-circle"></i>
                <p>Payment failed: ${response.error.description}</p>
                <button onclick="window.location.reload()" class="retry-button">Try Again</button>
            </div>
        `;
    });
    
    paymentButton.onclick = function(e) {
        e.preventDefault();
        try {
            rzp.open();
        } catch (error) {
            console.error('Error opening Razorpay:', error);
            alert('Error opening payment form. Please try again.');
        }
    };

    // Auto-open Razorpay checkout if debug parameter is provided
    <?php if (isset($_GET['auto_open']) && $_GET['auto_open'] === '1'): ?>
    setTimeout(function() {
        try {
            rzp.open();
        } catch (error) {
            console.error('Error auto-opening Razorpay:', error);
        }
    }, 1000);
    <?php endif; ?>
});
</script>

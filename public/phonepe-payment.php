<?php
/**
 * PhonePe Payment Page
 * Handles PhonePe payment initialization and callback
 */

// Include initialization file
require_once __DIR__ . '/../includes/init.php';

// Include email functionality
require_once ROOT_PATH . '/app/config/email.php';

// Include PhonePe configuration
require_once ROOT_PATH . '/app/config/phonepe.php';

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

// Create PhonePe payment if not already created
if (!isset($_SESSION['phonepe_order_id_' . $orderId])) {
    // Build redirect URL (absolute URL with current domain)
    $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? "https" : "http";
    $host = $_SERVER['HTTP_HOST'];
    $redirectUrl = "$protocol://$host/phonepe-callback.php?order_id=$orderId";
    
    // Create a PhonePe payment
    try {
        $paymentResponse = createPhonePePayment(
            $order['order_number'],
            $order['total'],
            $redirectUrl,
            "Payment for Order #{$order['order_number']}"
        );
        
        if ($paymentResponse['success']) {
            // Store PhonePe order ID in session
            $_SESSION['phonepe_order_id_' . $orderId] = $paymentResponse['order_id'];
            
            // Redirect to PhonePe payment page
            header('Location: ' . $paymentResponse['redirect_url']);
            exit;
        } else {
            // Display error
            echo '<div class="payment-error">';
            echo '<h3>Payment Error</h3>';
            echo '<p>' . htmlspecialchars($paymentResponse['error']) . '</p>';
            echo '<p>Please try again or contact support.</p>';
            echo '</div>';
            
            // Log error
            error_log('PhonePe Error: ' . $paymentResponse['error']);
            $_SESSION['error'] = 'Failed to create payment order: ' . $paymentResponse['error'];
            
            // Redirect back to checkout after 5 seconds
            echo '<script>setTimeout(function() { window.location.href = "checkout.php"; }, 5000);</script>';
            exit;
        }
    } catch (\Exception $e) {
        // Display error
        echo '<div class="payment-error">';
        echo '<h3>Payment Error</h3>';
        echo '<p>' . htmlspecialchars($e->getMessage()) . '</p>';
        echo '<p>Please try again or contact support.</p>';
        echo '</div>';
        
        // Log error
        error_log('PhonePe Error: ' . $e->getMessage());
        $_SESSION['error'] = 'Failed to create payment order: ' . $e->getMessage();
        
        // Redirect back to checkout after 5 seconds
        echo '<script>setTimeout(function() { window.location.href = "checkout.php"; }, 5000);</script>';
        exit;
    }
} else {
    // Payment already initiated, redirect to checkout
    $_SESSION['error'] = 'Payment already initiated. Please check your email for payment status.';
    header('Location: checkout.php');
    exit;
}

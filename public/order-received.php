<?php
/**
 * Order Received page controller
 * Displays the order confirmation after successful checkout
 */

// Include initialization file
require_once __DIR__ . '/../includes/init.php';

// Check if order ID is provided
if (!isset($_GET['order_id']) && !isset($_SESSION['last_order_id'])) {
    header('Location: index.php');
    exit;
}

// Get order ID from query string or session
$orderId = isset($_GET['order_id']) ? $_GET['order_id'] : $_SESSION['last_order_id'];

// Get order details
$order = getOrderById($orderId);

// If order not found or doesn't belong to the current user/session, redirect
if (!$order) {
    header('Location: index.php');
    exit;
}

// For Razorpay payments, verify payment status is completed
if ($order['payment_method'] === 'razorpay' && $order['payment_status'] !== 'completed') {
    // If payment is still pending, redirect to the payment page to complete payment
    $_SESSION['pending_order_id'] = $orderId;
    $_SESSION['error'] = 'Please complete your payment to view your order confirmation.';
    header('Location: razorpay-payment.php?order_id=' . $orderId);
    exit;
}

// Get order items
$orderItems = getOrderItems($orderId);

// Get billing details
$billingDetails = getBillingDetailsByOrderId($orderId);

// Get payment transaction details (for display purposes)
$paymentTransaction = null;
if ($order['payment_method'] === 'razorpay') {
    // Function to get latest payment transaction
    function getLatestPaymentTransaction($orderId) {
        /** @var \PDO $pdo */
        global $pdo;
        
        if (!$pdo) {
            return null;
        }
        
        try {
            $stmt = $pdo->prepare("
                SELECT * FROM payment_transactions
                WHERE order_id = :order_id
                ORDER BY created_at DESC
                LIMIT 1
            ");
            
            $stmt->execute([':order_id' => $orderId]);
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log('Error fetching payment transaction: ' . $e->getMessage());
            return null;
        }
    }
    
    $paymentTransaction = getLatestPaymentTransaction($orderId);
}

// Load the order received view
require_once ROOT_PATH . '/app/views/order-received.php';
?> 
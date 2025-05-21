<?php
/**
 * API Endpoint: Get Order Details
 * 
 * Returns order details, items, and billing information for a given order ID.
 * Only returns data if the order belongs to the current logged-in user.
 */

// Include initialization script
require_once __DIR__ . '/../../includes/init.php';

// Set content type to JSON
header('Content-Type: application/json');

// Check if user is logged in
if (!isLoggedIn()) {
    echo json_encode([
        'success' => false,
        'message' => 'Authentication required'
    ]);
    exit;
}

// Get order ID from query string
$orderId = isset($_GET['order_id']) ? (int)$_GET['order_id'] : 0;

if ($orderId <= 0) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid order ID'
    ]);
    exit;
}

// Get current user ID
$userId = $_SESSION['user_id'];

try {
    // Check if order belongs to the current user
    $stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ? AND user_id = ?");
    $stmt->execute([$orderId, $userId]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$order) {
        echo json_encode([
            'success' => false,
            'message' => 'Order not found or access denied'
        ]);
        exit;
    }
    
    // Get order items
    $orderItems = getOrderItems($orderId);
    
    // Get billing details
    $billingDetails = getBillingDetailsByOrderId($orderId);
    
    // Return success with order data
    echo json_encode([
        'success' => true,
        'order' => $order,
        'items' => $orderItems,
        'billing' => $billingDetails
    ]);
    
} catch (PDOException $e) {
    // Log error and return error response
    error_log('API Error - get_order_details.php: ' . $e->getMessage());
    
    echo json_encode([
        'success' => false,
        'message' => 'Error retrieving order details: ' . $e->getMessage()
    ]);
}
?> 
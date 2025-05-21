<?php
/**
 * Admin Orders Management Page
 */

// Include initialization script
require_once __DIR__ . '/../../includes/init.php';

// Enable error reporting for debugging
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Check if user is admin
if (!isAdmin()) {
    // Redirect to home page or show access denied
    header('Location: ../index.php');
    exit;
}

// Get the action from the query parameter
$action = isset($_GET['action']) ? $_GET['action'] : 'list';
$orderId = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Handle messages from redirect
$success = isset($_GET['success']) ? $_GET['success'] : '';
$error = isset($_GET['error']) ? $_GET['error'] : '';

// Process status updates
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $orderId = isset($_POST['order_id']) ? intval($_POST['order_id']) : 0;
    $newStatus = isset($_POST['status']) ? $_POST['status'] : '';
    
    if ($orderId && $newStatus) {
        $result = updateOrderStatus($orderId, $newStatus);
        
        if ($result['success']) {
            // Send cancellation emails if order is being cancelled
            if ($newStatus === 'cancelled') {
                // Send emails to both admin and customer about cancellation
                sendOrderCancellationEmails($orderId);
                header('Location: manage_orders.php?action=view&id=' . $orderId . '&success=Order has been cancelled and notification emails have been sent.');
                exit;
            }
            
            // Add specific message for COD orders marked as delivered
            if ($newStatus === 'delivered') {
                $stmt = $pdo->prepare("SELECT payment_method FROM orders WHERE id = ?");
                $stmt->execute([$orderId]);
                $orderData = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($orderData && (strtolower($orderData['payment_method']) === 'cod' || 
                                 strtolower($orderData['payment_method']) === 'cash on delivery')) {
                    header('Location: manage_orders.php?action=view&id=' . $orderId . '&success=Order status updated to delivered. Payment status has been automatically updated to "completed".');
                    exit;
                }
            }
            
            header('Location: manage_orders.php?success=Order status updated successfully');
            exit;
        } else {
            $error = $result['message'] ?? 'Failed to update order status';
        }
    } else {
        $error = 'Invalid order ID or status';
    }
}

// Get all orders for list view
$orders = [];
if ($action === 'list') {
    // Get filter parameters
    $filters = [
        'status' => isset($_GET['status']) ? $_GET['status'] : null,
        'payment_status' => isset($_GET['payment_status']) ? $_GET['payment_status'] : null,
        'start_date' => isset($_GET['start_date']) ? $_GET['start_date'] : null,
        'end_date' => isset($_GET['end_date']) ? $_GET['end_date'] : null,
        'order_number' => isset($_GET['order_number']) ? trim($_GET['order_number']) : null
    ];

    // Use getAllOrders function with filters
    $orders = getAllOrders($filters);

    // If no orders returned, check for errors
    if (empty($orders) && !isset($_GET['status']) && !isset($_GET['payment_status']) 
        && !isset($_GET['start_date']) && !isset($_GET['end_date']) && !isset($_GET['order_number'])) {
        // Direct query as a fallback in case the function is having issues
        try {
            $stmt = $pdo->query("SELECT * FROM orders ORDER BY created_at DESC");
            $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $error = "Database error: " . $e->getMessage();
        }
    }
}

// Get single order details for the view action
$orderDetails = [];
$orderItems = [];
$billingDetails = [];
$paymentDetails = [];

if ($action === 'view' && $orderId > 0) {
    $orderDetails = getOrderById($orderId);
    
    if ($orderDetails) {
        $orderItems = getOrderItems($orderId);
        $billingDetails = getOrderBillingDetails($orderId);
        $paymentDetails = getOrderPaymentDetails($orderId);
    } else {
        $error = 'Order not found';
        $action = 'list';
    }
}

// Set page title
$pageTitle = 'Manage Orders';

// Include admin orders view
require_once ROOT_PATH . '/app/views/admin/manage_orders.php';
?> 
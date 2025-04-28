<?php
/**
 * Cart Status API Endpoint
 * For debugging cart issues
 */

// Include initialization file
require_once __DIR__ . '/../../includes/init.php';

// Set the content type to JSON
header('Content-Type: application/json');

// Get session info
$sessionId = session_id();
$userId = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;

// Get PDO connection
global $pdo;

// Initialize response
$response = [
    'success' => true,
    'timestamp' => date('Y-m-d H:i:s'),
    'session' => [
        'id' => $sessionId,
        'status' => session_status(),
        'is_empty' => empty($sessionId),
        'variables' => $_SESSION
    ],
    'user' => [
        'id' => $userId,
        'is_logged_in' => !empty($userId)
    ],
    'cart' => [
        'count' => getCartItemCount(),
        'items' => []
    ],
    'database' => [
        'connection' => !empty($pdo),
        'tables' => []
    ]
];

// Get cart items from database directly
if ($pdo) {
    try {
        // Check shopping_cart_items table exists
        $result = $pdo->query("SHOW TABLES LIKE 'shopping_cart_items'");
        $response['database']['tables']['shopping_cart_items_exists'] = $result->rowCount() > 0;
        
        if ($result->rowCount() > 0) {
            // Get direct count of items
            if ($userId) {
                $stmt = $pdo->prepare("SELECT COUNT(*) FROM shopping_cart_items WHERE user_id = ? OR session_id = ?");
                $stmt->execute([$userId, $sessionId]);
            } else {
                $stmt = $pdo->prepare("SELECT COUNT(*) FROM shopping_cart_items WHERE session_id = ?");
                $stmt->execute([$sessionId]);
            }
            $response['database']['tables']['direct_item_count'] = $stmt->fetchColumn();
            
            // Get items directly from the database
            if ($userId) {
                $stmt = $pdo->prepare("
                    SELECT * FROM shopping_cart_items 
                    WHERE user_id = ? OR session_id = ?
                    ORDER BY created_at DESC
                ");
                $stmt->execute([$userId, $sessionId]);
            } else {
                $stmt = $pdo->prepare("
                    SELECT * FROM shopping_cart_items 
                    WHERE session_id = ?
                    ORDER BY created_at DESC
                ");
                $stmt->execute([$sessionId]);
            }
            $rawItems = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $response['cart']['raw_items'] = $rawItems;
            
            // Try fetching with the getCartItems function
            $cartItems = getCartItems();
            $response['cart']['items'] = $cartItems;
            $response['cart']['function_item_count'] = count($cartItems);
            
            // Check for discrepancy
            $response['cart']['has_discrepancy'] = count($rawItems) !== count($cartItems);
        }
        
        // Check if products table exists
        $result = $pdo->query("SHOW TABLES LIKE 'products'");
        $response['database']['tables']['products_exists'] = $result->rowCount() > 0;
        
        // Check if product_images table exists
        $result = $pdo->query("SHOW TABLES LIKE 'product_images'");
        $response['database']['tables']['product_images_exists'] = $result->rowCount() > 0;
        
    } catch (PDOException $e) {
        $response['database']['error'] = $e->getMessage();
    }
}

echo json_encode($response, JSON_PRETTY_PRINT);
?> 
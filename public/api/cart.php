<?php
/**
 * Cart API Endpoint
 * 
 * Handles cart operations like adding, updating, removing items, and fetching the cart
 */

// Include initialization file
require_once __DIR__ . '/../../includes/init.php';

// Set the content type to JSON
header('Content-Type: application/json');

// Get the request method
$method = $_SERVER['REQUEST_METHOD'];

// Handle different request methods
switch ($method) {
    case 'GET':
        // Get cart items
        $cartItems = getCartItems();
        $cartCount = getCartItemCount();
        
        echo json_encode([
            'success' => true,
            'items' => $cartItems,
            'count' => $cartCount
        ]);
        break;
        
    case 'POST':
        // Add item to cart
        // Get JSON data
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!$data) {
            // Try to get from POST
            $productId = isset($_POST['product_id']) ? $_POST['product_id'] : null;
            $quantity = isset($_POST['quantity']) ? $_POST['quantity'] : 1;
        } else {
            $productId = isset($data['product_id']) ? $data['product_id'] : null;
            $quantity = isset($data['quantity']) ? $data['quantity'] : 1;
        }
        
        if (!$productId) {
            echo json_encode(['success' => false, 'message' => 'Product ID is required']);
            exit;
        }
        
        $result = addToCart($productId, $quantity);
        $cartCount = getCartItemCount();
        
        echo json_encode(array_merge($result, ['count' => $cartCount]));
        break;
        
    case 'PUT':
        // Update cart item quantity
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!$data) {
            echo json_encode(['success' => false, 'message' => 'Invalid data']);
            exit;
        }
        
        $cartItemId = isset($data['cart_item_id']) ? $data['cart_item_id'] : null;
        $quantity = isset($data['quantity']) ? $data['quantity'] : null;
        
        if (!$cartItemId || !$quantity) {
            echo json_encode(['success' => false, 'message' => 'Cart item ID and quantity are required']);
            exit;
        }
        
        $result = updateCartItemQuantity($cartItemId, $quantity);
        $cartCount = getCartItemCount();
        
        echo json_encode(array_merge($result, ['count' => $cartCount]));
        break;
        
    case 'DELETE':
        // Remove item from cart
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!$data) {
            // Try to get from URL parameters
            parse_str($_SERVER['QUERY_STRING'], $params);
            $cartItemId = isset($params['cart_item_id']) ? $params['cart_item_id'] : null;
        } else {
            $cartItemId = isset($data['cart_item_id']) ? $data['cart_item_id'] : null;
        }
        
        if (!$cartItemId) {
            echo json_encode(['success' => false, 'message' => 'Cart item ID is required']);
            exit;
        }
        
        $result = removeFromCart($cartItemId);
        $cartCount = getCartItemCount();
        
        echo json_encode(array_merge($result, ['count' => $cartCount]));
        break;
        
    default:
        echo json_encode(['success' => false, 'message' => 'Method not allowed']);
        break;
} 
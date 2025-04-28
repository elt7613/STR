<?php
/**
 * Cart page controller
 * Displays and manages the shopping cart
 */

// Include initialization file
require_once __DIR__ . '/../includes/init.php';

// Get cart items
$cartItems = getCartItems();

// Calculate cart subtotal
$cartSubtotal = 0;
foreach ($cartItems as $item) {
    $cartSubtotal += $item['amount'] * $item['quantity'];
}

// Load the cart view
require_once ROOT_PATH . '/app/views/cart.php';
?> 
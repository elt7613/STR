<?php
/**
 * Product Detail Page
 */

// Include initialization script
require_once __DIR__ . '/../includes/init.php';

// Include shop functions
require_once ROOT_PATH . '/app/core/shop.php';

// Get product ID from URL
$productId = isset($_GET['id']) ? intval($_GET['id']) : 0;

// If no product ID provided, redirect to shop page
if ($productId <= 0) {
    header('Location: shop.php');
    exit;
}

// Get product details
$product = getProductById($productId);
if (!$product) {
    header('Location: /');
    exit;
}

// Get device information if available
if (!empty($product['device_id'])) {
    $device = getDeviceById($product['device_id']);
    if ($device) {
        $product['device_name'] = $device['name'];
    }
}

// Get product images
$productImages = getProductImages($productId);

// Get product categories
$productCategories = getProductCategories($productId);

// Get related products (products from the same brand, excluding current product)
$relatedProducts = [];
if (isset($product['brand_id'])) {
    $stmt = $pdo->prepare("
        SELECT p.*, 
               (SELECT image_path FROM product_images WHERE product_id = p.id AND is_primary = 1 LIMIT 1) as primary_image
        FROM products p
        WHERE p.brand_id = ? AND p.id != ?
        ORDER BY p.created_at DESC
        LIMIT 4
    ");
    $stmt->execute([$product['brand_id'], $productId]);
    $relatedProducts = $stmt->fetchAll();
}

// Include product detail view
require_once ROOT_PATH . '/app/views/product.php';
?> 
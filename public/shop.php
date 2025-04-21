<?php
/**
 * Shop Page - Brands and Products
 */

// Include initialization script
require_once __DIR__ . '/../includes/init.php';

// Get filter parameters
$brandId = isset($_GET['brand']) ? intval($_GET['brand']) : null;
$categoryId = isset($_GET['category']) ? intval($_GET['category']) : null;

// Get all brands for filter dropdown
$brands = getAllBrands();

// Get all categories for filter dropdown
$categories = getAllCategories();

// Get filtered products
if ($categoryId) {
    // Filter by category
    $products = getProductsByCategory($categoryId);
} else {
    // Filter by brand or get all
    $products = getAllProducts($brandId);
}

// Include shop view
require_once ROOT_PATH . '/app/views/shop.php';
?> 
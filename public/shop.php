<?php
/**
 * Shop Page - Brands and Categories
 */

// Include initialization script
require_once __DIR__ . '/../includes/init.php';

// Get all brands for display
$brands = getAllBrands();

// Get all categories grouped by brand for display
$brandCategories = [];
foreach ($brands as $brand) {
    $brandCategories[$brand['id']] = getCategoriesByBrandId($brand['id']);
}

// Include shop view
require_once ROOT_PATH . '/app/views/shop.php';
?> 
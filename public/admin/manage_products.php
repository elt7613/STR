<?php
/**
 * Admin - Manage Products
 */

// Include initialization script
require_once __DIR__ . '/../../includes/init.php';

// Check if user is admin
if (!isAdmin()) {
    // Redirect to home page or show access denied
    header('Location: /index.php');
    exit;
}

// Process form submissions
$error = '';
$success = '';
$currentProduct = null;
$productImages = [];
$productCategories = [];

// Get all vehicle makes, models and series for dropdowns
$makes = getAllVehicleMakes();
$models = [];
$series = [];

// Get all categories for dropdown
$categories = getAllCategories();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Add or update product
    if (isset($_POST['action']) && ($_POST['action'] === 'add' || $_POST['action'] === 'update')) {
        $brandId = isset($_POST['brand_id']) ? intval($_POST['brand_id']) : 0;
        $productId = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;
        $title = $_POST['title'] ?? '';
        $amount = isset($_POST['amount']) ? floatval($_POST['amount']) : 0;
        $description = $_POST['description'] ?? '';
        
        // Get selected categories (if any)
        $categoryIds = isset($_POST['categories']) && is_array($_POST['categories']) ? $_POST['categories'] : [];
        
        // New optional fields
        $makeId = !empty($_POST['make_id']) ? intval($_POST['make_id']) : null;
        $modelId = !empty($_POST['model_id']) ? intval($_POST['model_id']) : null;
        $seriesId = !empty($_POST['series_id']) ? intval($_POST['series_id']) : null;
        
        if (empty($brandId) || empty($title) || $amount <= 0 || empty($description)) {
            $error = 'All fields are required and price must be greater than zero.';
        } else {
            if ($_POST['action'] === 'add') {
                // Adding new product
                $result = addProduct($brandId, $title, $amount, $description, $makeId, $modelId, $seriesId);
                if ($result['success']) {
                    $productId = $result['id'];
                    $success = $result['message'];
                    
                    // Update product categories (if any)
                    if (!empty($categoryIds)) {
                        updateProductCategories($productId, $categoryIds);
                    }
                    
                    // Handle image uploads for new product
                    if (isset($_FILES['images']) && is_array($_FILES['images']['name'])) {
                        $targetDir = ROOT_PATH . '/public/assets/img/products/';
                        
                        // Create directory if it doesn't exist
                        if (!is_dir($targetDir)) {
                            mkdir($targetDir, 0755, true);
                        }
                        
                        // Process each uploaded image
                        $uploadCount = count($_FILES['images']['name']);
                        for ($i = 0; $i < $uploadCount; $i++) {
                            if ($_FILES['images']['error'][$i] === UPLOAD_ERR_OK) {
                                $extension = pathinfo($_FILES['images']['name'][$i], PATHINFO_EXTENSION);
                                $fileName = uniqid('product_') . '.' . $extension;
                                $targetFile = $targetDir . $fileName;
                                
                                if (move_uploaded_file($_FILES['images']['tmp_name'][$i], $targetFile)) {
                                    $imagePath = 'assets/img/products/' . $fileName;
                                    // Set first image as primary
                                    $isPrimary = ($i === 0);
                                    addProductImage($productId, $imagePath, $isPrimary);
                                }
                            }
                        }
                    }
                } else {
                    $error = $result['message'];
                }
            } else {
                // Updating existing product
                $result = updateProduct($productId, $brandId, $title, $amount, $description, $makeId, $modelId, $seriesId);
                if ($result['success']) {
                    $success = $result['message'];
                    
                    // Update product categories
                    updateProductCategories($productId, $categoryIds);
                    
                    // Handle image uploads for existing product
                    if (isset($_FILES['images']) && is_array($_FILES['images']['name'])) {
                        $targetDir = ROOT_PATH . '/public/assets/img/products/';
                        
                        // Create directory if it doesn't exist
                        if (!is_dir($targetDir)) {
                            mkdir($targetDir, 0755, true);
                        }
                        
                        // Process each uploaded image
                        $uploadCount = count($_FILES['images']['name']);
                        for ($i = 0; $i < $uploadCount; $i++) {
                            if ($_FILES['images']['error'][$i] === UPLOAD_ERR_OK) {
                                $extension = pathinfo($_FILES['images']['name'][$i], PATHINFO_EXTENSION);
                                $fileName = uniqid('product_') . '.' . $extension;
                                $targetFile = $targetDir . $fileName;
                                
                                if (move_uploaded_file($_FILES['images']['tmp_name'][$i], $targetFile)) {
                                    $imagePath = 'assets/img/products/' . $fileName;
                                    // If no images exist, set as primary, otherwise not
                                    $existingImages = getProductImages($productId);
                                    $isPrimary = empty($existingImages);
                                    addProductImage($productId, $imagePath, $isPrimary);
                                }
                            }
                        }
                    }
                } else {
                    $error = $result['message'];
                }
            }
        }
    }
    
    // Delete product
    else if (isset($_POST['action']) && $_POST['action'] === 'delete') {
        $productId = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;
        
        if (empty($productId)) {
            $error = 'Product ID is required.';
        } else {
            $result = deleteProduct($productId);
            if ($result['success']) {
                $success = $result['message'];
            } else {
                $error = $result['message'];
            }
        }
    }
    
    // Delete product image
    else if (isset($_POST['action']) && $_POST['action'] === 'delete_image') {
        $imageId = isset($_POST['image_id']) ? intval($_POST['image_id']) : 0;
        $productId = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;
        
        if (empty($imageId)) {
            $error = 'Image ID is required.';
        } else {
            $result = deleteProductImage($imageId);
            if ($result['success']) {
                $success = $result['message'];
                
                // If product ID is provided, get its details for display
                if (!empty($productId)) {
                    $currentProduct = getProductById($productId);
                    $productImages = getProductImages($productId);
                    $productCategories = getProductCategoryIds($productId);
                }
            } else {
                $error = $result['message'];
            }
        }
    }
    
    // Set primary image
    else if (isset($_POST['action']) && $_POST['action'] === 'set_primary') {
        $imageId = isset($_POST['image_id']) ? intval($_POST['image_id']) : 0;
        $productId = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;
        
        if (empty($imageId) || empty($productId)) {
            $error = 'Image ID and Product ID are required.';
        } else {
            $result = setImageAsPrimary($imageId, $productId);
            if ($result['success']) {
                $success = $result['message'];
                
                // Get product details for display
                $currentProduct = getProductById($productId);
                $productImages = getProductImages($productId);
                $productCategories = getProductCategoryIds($productId);
            } else {
                $error = $result['message'];
            }
        }
    }
    
    // Edit specific product
    else if (isset($_POST['action']) && $_POST['action'] === 'edit') {
        $productId = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;
        
        if (!empty($productId)) {
            $currentProduct = getProductById($productId);
            $productImages = getProductImages($productId);
            $productCategories = getProductCategoryIds($productId);
            
            // Load models and series based on current selection
            if (!empty($currentProduct['make_id'])) {
                $models = getVehicleModelsByMake($currentProduct['make_id']);
                
                if (!empty($currentProduct['model_id'])) {
                    $series = getVehicleSeriesByModel($currentProduct['model_id']);
                }
            }
        }
    }
}

// Get edit product ID from URL (if provided)
if (isset($_GET['edit']) && !$currentProduct) {
    $editProductId = intval($_GET['edit']);
    if ($editProductId > 0) {
        $currentProduct = getProductById($editProductId);
        $productImages = getProductImages($editProductId);
        $productCategories = getProductCategoryIds($editProductId);
        
        // Load models and series based on current selection
        if (!empty($currentProduct['make_id'])) {
            $models = getVehicleModelsByMake($currentProduct['make_id']);
            
            if (!empty($currentProduct['model_id'])) {
                $series = getVehicleSeriesByModel($currentProduct['model_id']);
            }
        }
    }
}

// Get all brands and products for display
$brands = getAllBrands();
$products = getAllProducts();

// Include admin products view
require_once ROOT_PATH . '/app/views/admin/manage_products.php';
?> 
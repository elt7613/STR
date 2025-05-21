<?php
/**
 * Admin - Manage Products
 */

// Ensure no output is sent before we decide what to send
ob_start();

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

// Get all brands for dropdown
$brands = getAllBrands();

// Get all categories for initial dropdown (will be filtered via AJAX)
$categories = getAllCategories();

// Get filter parameters from URL
$makeId = isset($_POST['make_id']) ? intval($_POST['make_id']) : 0;
$modelId = isset($_POST['model_id']) ? intval($_POST['model_id']) : 0;
$seriesId = isset($_POST['series_id']) ? intval($_POST['series_id']) : 0;
$deviceId = isset($_POST['device_id']) ? intval($_POST['device_id']) : 0;
$categoryId = isset($_POST['category_id']) ? intval($_POST['category_id']) : 0;

// If product is being edited, get the vehicle-related data
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $productId = (int)$_GET['id'];
    $currentProduct = getProductById($productId);
    
    if ($currentProduct) {
        $productImages = getProductImages($productId);
        $productCategories = getProductCategoryIds($productId);
        
        // Get vehicle data to pre-populate dropdowns
        if (!empty($currentProduct['make_id'])) {
            $makeId = $currentProduct['make_id'];
            $models = getVehicleModelsByMake($makeId);
            
            if (!empty($currentProduct['model_id'])) {
                $modelId = $currentProduct['model_id'];
                $series = getVehicleSeriesByModel($modelId);
                
                if (!empty($currentProduct['series_id'])) {
                    $seriesId = $currentProduct['series_id'];
                    $devices = getVehicleDevicesBySeries($seriesId);
                    
                    if (!empty($currentProduct['device_id'])) {
                        $deviceId = $currentProduct['device_id'];
                    }
                }
            }
        }
    }
}

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
        $deviceId = !empty($_POST['device_id']) ? intval($_POST['device_id']) : null;
        
        // Get direct_buying value (checkbox)
        $directBuying = isset($_POST['direct_buying']) ? 1 : 0;
        
        if (empty($brandId) || empty($title) || $amount <= 0 || empty($description)) {
            $error = 'All fields are required and price must be greater than zero.';
        } else {
            if ($_POST['action'] === 'add') {
                // Adding new product
                $result = addProduct($brandId, $title, $amount, $description, $makeId, $modelId, $seriesId, $deviceId, $directBuying);
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
                $result = updateProduct($productId, $brandId, $title, $amount, $description, $makeId, $modelId, $seriesId, $deviceId, $directBuying);
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
        
        // Return JSON data if format=json is specified
        if (isset($_GET['format']) && $_GET['format'] === 'json') {
            header('Content-Type: application/json');
            
            // Debug: Log what we're sending
            error_log('Sending JSON response for product ID: ' . $editProductId);
            error_log('Categories: ' . json_encode($productCategories));
            error_log('Images: ' . json_encode($productImages));
            
            // Ensure categories are properly formatted as an array of integers
            $formattedCategories = array_map('intval', $productCategories);
            
            $response = [
                'success' => true,
                'product' => $currentProduct,
                'images' => $productImages,
                'categories' => $formattedCategories
            ];
            
            echo json_encode($response);
            exit;
        }
        
        // Load models and series based on current selection
        if (!empty($currentProduct['make_id'])) {
            $models = getVehicleModelsByMake($currentProduct['make_id']);
            
            if (!empty($currentProduct['model_id'])) {
                $series = getVehicleSeriesByModel($currentProduct['model_id']);
            }
        }
    }
}

// Add JSON response for actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $jsonResponse = false;
    $responseData = [
        'success' => false,
        'message' => 'Unknown error'
    ];
    
    // Check if this is an AJAX request
    $acceptHeader = $_SERVER['HTTP_ACCEPT'] ?? '';
    if (strpos($acceptHeader, 'application/json') !== false) {
        $jsonResponse = true;
    }
    
    // Handle image actions
    if ($_POST['action'] === 'set_primary') {
        $imageId = isset($_POST['image_id']) ? intval($_POST['image_id']) : 0;
        $productId = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;
        
        if (empty($imageId) || empty($productId)) {
            $responseData['message'] = 'Image ID and Product ID are required.';
        } else {
            $result = setImageAsPrimary($imageId, $productId);
            $responseData['success'] = $result['success'];
            $responseData['message'] = $result['message'];
            
            if ($result['success']) {
                $responseData['product'] = getProductById($productId);
                $responseData['images'] = getProductImages($productId);
                $responseData['categories'] = getProductCategoryIds($productId);
            }
        }
        
        if ($jsonResponse) {
            // Clean any output before sending JSON
            ob_clean();
            header('Content-Type: application/json');
            echo json_encode($responseData);
            exit;
        }
    }
    
    else if ($_POST['action'] === 'delete_image') {
        $imageId = isset($_POST['image_id']) ? intval($_POST['image_id']) : 0;
        $productId = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;
        
        if (empty($imageId)) {
            $responseData['message'] = 'Image ID is required.';
        } else {
            $result = deleteProductImage($imageId);
            $responseData['success'] = $result['success'];
            $responseData['message'] = $result['message'];
            
            if ($result['success'] && !empty($productId)) {
                $responseData['product'] = getProductById($productId);
                $responseData['images'] = getProductImages($productId);
                $responseData['categories'] = getProductCategoryIds($productId);
            }
        }
        
        if ($jsonResponse) {
            // Clean any output before sending JSON
            ob_clean();
            header('Content-Type: application/json');
            echo json_encode($responseData);
            exit;
        }
    }
}

// AJAX: Get categories for specific brand
if (isset($_GET['action']) && $_GET['action'] === 'get_brand_categories') {
    header('Content-Type: application/json');
    $brandId = isset($_GET['brand_id']) ? intval($_GET['brand_id']) : 0;
    
    if ($brandId <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid brand ID']);
        exit;
    }
    
    $categories = getCategoriesByBrandId($brandId);
    echo json_encode(['success' => true, 'categories' => $categories]);
    exit;
}

// Get all brands and products for display
$brands = getAllBrands();
$products = getAllProducts();

// Include admin products view
require_once ROOT_PATH . '/app/views/admin/manage_products.php';
?> 
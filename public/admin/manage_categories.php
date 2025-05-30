<?php
/**
 * Admin - Manage Categories
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
$currentCategory = null;

// Function to handle image upload
function handleCategoryImageUpload() {
    if (isset($_FILES['category_image']) && $_FILES['category_image']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['category_image'];
        
        // Validate file type
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        if (!in_array($file['type'], $allowedTypes)) {
            return ['success' => false, 'message' => 'Invalid file type. Only JPEG, PNG, GIF, and WEBP are allowed.'];
        }
        
        // Validate file size (max 2MB)
        $maxFileSize = 2 * 1024 * 1024; // 2MB
        if ($file['size'] > $maxFileSize) {
            return ['success' => false, 'message' => 'File is too large. Maximum size is 2MB.'];
        }
        
        // Generate unique filename
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = 'category_' . uniqid() . '.' . $extension;
        
        // Create upload directory if it doesn't exist
        $uploadDir = ROOT_PATH . '/public/uploads/categories/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        $uploadPath = $uploadDir . $filename;
        
        // Check if we're on localhost or a production server
        $isLocalhost = ($_SERVER['HTTP_HOST'] == 'localhost' || strpos($_SERVER['HTTP_HOST'], '127.0.0.1') !== false);
        
        // Set the appropriate path based on the environment
        if ($isLocalhost) {
            $relativePath = '/uploads/categories/' . $filename;  // For local environment
        } else {
            $relativePath = '../uploads/categories/' . $filename;  // For Hostinger
        }
        
        // Move uploaded file
        if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
            return [
                'success' => true,
                'path' => $relativePath,
                'message' => 'Image uploaded successfully'
            ];
        } else {
            return ['success' => false, 'message' => 'Failed to upload image.'];
        }
    }
    
    return ['success' => true, 'path' => null];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Add or update category
    if (isset($_POST['action']) && ($_POST['action'] === 'add' || $_POST['action'] === 'update')) {
        $categoryId = isset($_POST['category_id']) ? intval($_POST['category_id']) : 0;
        $name = $_POST['name'] ?? '';
        $brandId = isset($_POST['brand_id']) ? intval($_POST['brand_id']) : 0;
        $description = $_POST['description'] ?? '';
        
        if (empty($name)) {
            $error = 'Category name is required.';
        } else if (empty($brandId)) {
            $error = 'Brand is required.';
        } else {
            // Handle image upload if file is selected
            $uploadResult = handleCategoryImageUpload();
            
            if (!$uploadResult['success']) {
                $error = $uploadResult['message'];
            } else {
                $imagePath = $uploadResult['path'];
                
                if ($_POST['action'] === 'add') {
                    // Adding new category
                    $result = addCategory($name, $brandId, $description, $imagePath);
                    if ($result['success']) {
                        $success = $result['message'];
                    } else {
                        $error = $result['message'];
                    }
                } else {
                    // Updating existing category
                    $result = updateCategory($categoryId, $name, $brandId, $description, $imagePath);
                    if ($result['success']) {
                        $success = $result['message'];
                    } else {
                        $error = $result['message'];
                    }
                }
            }
        }
    }
    
    // Delete category
    else if (isset($_POST['action']) && $_POST['action'] === 'delete') {
        $categoryId = isset($_POST['category_id']) ? intval($_POST['category_id']) : 0;
        
        if (empty($categoryId)) {
            $error = 'Category ID is required.';
        } else {
            $result = deleteCategory($categoryId);
            if ($result['success']) {
                $success = $result['message'];
            } else {
                $error = $result['message'];
            }
        }
    }
    
    // Edit specific category
    else if (isset($_POST['action']) && $_POST['action'] === 'edit') {
        $categoryId = isset($_POST['category_id']) ? intval($_POST['category_id']) : 0;
        
        if (!empty($categoryId)) {
            $currentCategory = getCategoryById($categoryId);
        }
    }
}

// Get edit category ID from URL (if provided)
if (isset($_GET['edit']) && !$currentCategory) {
    $editCategoryId = intval($_GET['edit']);
    if ($editCategoryId > 0) {
        $currentCategory = getCategoryById($editCategoryId);
    }
}

// Filter by brand if specified
$filterBrandId = isset($_GET['brand_id']) ? intval($_GET['brand_id']) : 0;

// Get all brands for dropdown
$brands = getAllBrands();

// Get all categories for display (filtered by brand if specified)
$categories = $filterBrandId > 0 ? getAllCategories($filterBrandId) : getAllCategories();

// Include admin categories view
require_once ROOT_PATH . '/app/views/admin/manage_categories.php';
?> 
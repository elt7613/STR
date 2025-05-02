<?php
/**
 * Admin - Manage Brands
 */

// Include initialization script
require_once __DIR__ . '/../../includes/init.php';

// Check if user is admin
if (!isAdmin()) {
    // Redirect to home page or show access denied
    header('Location: ../index.php');
    exit;
}

// Process form submissions
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Add or update brand
    if (isset($_POST['action']) && ($_POST['action'] === 'add' || $_POST['action'] === 'update')) {
        $name = $_POST['name'] ?? '';
        $brandId = isset($_POST['brand_id']) ? intval($_POST['brand_id']) : 0;
        $sequence = isset($_POST['sequence']) ? intval($_POST['sequence']) : 999;
        
        // Check if an image was uploaded
        $image = '';
        $imageUploaded = false;
        
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $targetDir = ROOT_PATH . '/public/assets/img/brands/';
            
            // Create directory if it doesn't exist
            if (!is_dir($targetDir)) {
                mkdir($targetDir, 0755, true);
            }
            
            // Generate unique filename
            $extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            $fileName = uniqid('brand_') . '.' . $extension;
            $targetFile = $targetDir . $fileName;
            
            // Move uploaded file
            if (move_uploaded_file($_FILES['image']['tmp_name'], $targetFile)) {
                $image = 'assets/img/brands/' . $fileName;
                $imageUploaded = true;
            } else {
                $error = 'Failed to upload image.';
            }
        }
        
        if ($_POST['action'] === 'add') {
            // Adding new brand
            if (empty($name)) {
                $error = 'Brand name is required.';
            } else if (empty($image) && !$imageUploaded) {
                $error = 'Brand image is required.';
            } else {
                $result = addBrand($name, $image, $sequence);
                if ($result['success']) {
                    $success = $result['message'];
                } else {
                    $error = $result['message'];
                }
            }
        } else {
            // Updating existing brand
            if (empty($name) || empty($brandId)) {
                $error = 'Brand name and ID are required.';
            } else {
                if ($imageUploaded) {
                    $result = updateBrand($brandId, $name, $image, $sequence);
                } else {
                    $result = updateBrand($brandId, $name, null, $sequence);
                }
                
                if ($result['success']) {
                    $success = $result['message'];
                } else {
                    $error = $result['message'];
                }
            }
        }
    }
    
    // Delete brand
    else if (isset($_POST['action']) && $_POST['action'] === 'delete') {
        $brandId = isset($_POST['brand_id']) ? intval($_POST['brand_id']) : 0;
        
        if (empty($brandId)) {
            $error = 'Brand ID is required.';
        } else {
            $result = deleteBrand($brandId);
            if ($result['success']) {
                $success = $result['message'];
            } else {
                $error = $result['message'];
            }
        }
    }
}

// Get all brands for display
$brands = getAllBrands();

// Include admin brands view
require_once ROOT_PATH . '/app/views/admin/manage_brands.php';
?> 
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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Add or update category
    if (isset($_POST['action']) && ($_POST['action'] === 'add' || $_POST['action'] === 'update')) {
        $categoryId = isset($_POST['category_id']) ? intval($_POST['category_id']) : 0;
        $name = $_POST['name'] ?? '';
        $description = $_POST['description'] ?? '';
        
        if (empty($name)) {
            $error = 'Category name is required.';
        } else {
            if ($_POST['action'] === 'add') {
                // Adding new category
                $result = addCategory($name, $description);
                if ($result['success']) {
                    $success = $result['message'];
                } else {
                    $error = $result['message'];
                }
            } else {
                // Updating existing category
                $result = updateCategory($categoryId, $name, $description);
                if ($result['success']) {
                    $success = $result['message'];
                } else {
                    $error = $result['message'];
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

// Get all categories for display
$categories = getAllCategories();

// Include admin categories view
require_once ROOT_PATH . '/app/views/admin/manage_categories.php';
?> 
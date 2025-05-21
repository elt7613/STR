<?php
/**
 * User Profile Page Controller
 * 
 * This page allows users to:
 * - View and update their profile information
 * - Upload and update their profile image
 * - View their order history
 * - Manage their orders
 */

// Include initialization file
require_once __DIR__ . '/../includes/init.php';

// Include vehicle functions
require_once __DIR__ . '/../app/core/vehicle.php';

// Redirect to login if not logged in
if (!isLoggedIn()) {
    // Use a safe fallback if REQUEST_URI is not available
    $referrer = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : 'profile.php';
    $_SESSION['referring_page'] = $referrer;
    
    // Only attempt to redirect if we're in a web context, not CLI
    if (php_sapi_name() !== 'cli') {
        header('Location: index.php');
        exit;
    }
}

// Initialize variables
$userId = $_SESSION['user_id'] ?? 0;

// If no user ID, show error and exit
if ($userId === 0) {
    $error = 'Invalid user session. Please log in again.';
    require_once __DIR__ . '/../app/views/user_profile.php';
    exit;
}

$error = '';
$success = '';
$currentTab = isset($_GET['tab']) ? $_GET['tab'] : 'profile';

// Validate tab parameter
$validTabs = ['profile', 'orders', 'vehicles'];
if (!in_array($currentTab, $validTabs)) {
    $currentTab = 'profile';
}

// Get user data
$user = getUserById($userId);
if (!$user) {
    $error = 'Unable to retrieve user data';
}

// Get user orders if on orders tab
$orders = [];
if ($currentTab === 'orders') {
    $orders = getUserOrders($userId);
}

// Get user vehicle info if on vehicles tab
$vehicles = [];
if ($currentTab === 'vehicles') {
    $vehicles = getUserVehicleInfo($userId);
}

// Process profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_profile'])) {
        // Handle profile update
        $data = [
            'username' => $_POST['username'] ?? '',
            'email' => $_POST['email'] ?? '',
            'phone' => $_POST['phone'] ?? ''
        ];
        
        // Check if password is being updated
        $newPassword = null;
        if (!empty($_POST['new_password'])) {
            // Verify current password first
            if (empty($_POST['current_password'])) {
                $error = 'Current password is required to set a new password';
            } else {
                // Verify the current password
                $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
                $stmt->execute([$userId]);
                $result = $stmt->fetch();
                
                if ($result && password_verify($_POST['current_password'], $result['password'])) {
                    // Current password is correct, set the new password
                    $newPassword = $_POST['new_password'];
                } else {
                    $error = 'Current password is incorrect';
                }
            }
        }
        
        // Handle profile image upload
        $profileImage = isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] !== UPLOAD_ERR_NO_FILE ? $_FILES['profile_image'] : null;
        
        if (empty($error)) {
            // Update profile
            $result = updateUserProfile($userId, $data, $newPassword, $profileImage);
            
            if ($result['success']) {
                $success = $result['message'];
                // Refresh user data
                $user = getUserById($userId);
            } else {
                $error = $result['message'];
            }
        }
    }
    
    // Handle profile image removal
    if (isset($_POST['remove_profile_image'])) {
        // Check if user has a profile image
        if (!empty($user['profile_image'])) {
            // Delete the image file
            // Use DOCUMENT_ROOT to ensure path works in both local and hosting environments
            $imagePath = $_SERVER['DOCUMENT_ROOT'] . '/uploads/profile_images/' . $user['profile_image'];
            
            // For debugging purposes
            error_log('Attempting to delete profile image at: ' . $imagePath);
            
            if (file_exists($imagePath)) {
                unlink($imagePath);
                error_log('Profile image deleted successfully');
            } else {
                error_log('Profile image file not found at: ' . $imagePath);
            }
            
            // Update the database
            $stmt = $pdo->prepare("UPDATE users SET profile_image = NULL WHERE id = ?");
            $stmt->execute([$userId]);
            
            $success = 'Profile image removed successfully';
            
            // Refresh user data
            $user = getUserById($userId);
        }
    }
}

// Process vehicle information submission
if (isset($_POST['submit_vehicle_info'])) {
    // Get submitted data
    $brand = $_POST['vehicle_brand'] ?? '';
    $model = $_POST['vehicle_model'] ?? '';
    $series = $_POST['vehicle_series'] ?? '';
    
    // Check if there are images uploaded
    $vehicleImages = [];
    if (isset($_FILES['vehicle_images']) && is_array($_FILES['vehicle_images']['name'])) {
        $fileCount = count($_FILES['vehicle_images']['name']);
        
        for ($i = 0; $i < $fileCount; $i++) {
            if ($_FILES['vehicle_images']['error'][$i] === UPLOAD_ERR_OK) {
                $vehicleImages[] = [
                    'name' => $_FILES['vehicle_images']['name'][$i],
                    'type' => $_FILES['vehicle_images']['type'][$i],
                    'tmp_name' => $_FILES['vehicle_images']['tmp_name'][$i],
                    'error' => $_FILES['vehicle_images']['error'][$i],
                    'size' => $_FILES['vehicle_images']['size'][$i]
                ];
            }
        }
    }
    
    // Validate required fields
    if (empty($brand) || empty($model)) {
        $error = 'Vehicle brand and model are required';
    } else {
        // Submit vehicle information
        $result = saveUserVehicleInfo($userId, $brand, $model, $series, $vehicleImages);
        
        if ($result['success']) {
            $success = $result['message'];
            // Refresh vehicle data
            $vehicles = getUserVehicleInfo($userId);
            $currentTab = 'vehicles'; // Switch to vehicles tab after submission
        } else {
            $error = $result['message'];
        }
    }
}

// Process vehicle deletion
if (isset($_POST['delete_vehicle']) && isset($_POST['vehicle_id'])) {
    $vehicleId = $_POST['vehicle_id'];
    $result = deleteVehicleInfo($vehicleId, $userId);
    
    if ($result['success']) {
        $success = $result['message'];
        // Refresh vehicle data
        $vehicles = getUserVehicleInfo($userId);
    } else {
        $error = $result['message'];
    }
}

// Process order cancellation request
if (isset($_POST['cancel_order']) && isset($_POST['order_id'])) {
    $orderId = $_POST['order_id'];
    
    // Check if order belongs to user
    $stmt = $pdo->prepare("SELECT id FROM orders WHERE id = ? AND user_id = ?");
    $stmt->execute([$orderId, $userId]);
    
    if ($stmt->rowCount() > 0) {
        // Update order status to cancelled
        $result = updateOrderStatus($orderId, 'cancelled');
        
        if ($result['success']) {
            // Send cancellation notification emails
            sendOrderCancellationEmails($orderId);
            $success = 'Order has been cancelled successfully and confirmation emails have been sent';
        } else {
            $error = $result['message'] ?? 'Failed to cancel order';
        }
        
        // Refresh orders list
        $orders = getUserOrders($userId);
    } else {
        $error = 'Invalid order or you do not have permission to cancel this order';
    }
}

// For debugging, log some information
error_log("Profile page loaded for user ID: $userId, Tab: $currentTab");
if (!empty($error)) {
    error_log("Profile page error: $error");
}

// Include the view file - use the correct path
require_once __DIR__ . '/../app/views/user_profile.php';
?> 
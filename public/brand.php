<?php
/**
 * Brand Page - Products from a specific brand
 */

// Include initialization script
require_once __DIR__ . '/../includes/init.php';
// Include email functionality
require_once ROOT_PATH . '/app/config/email.php';

// Get brand ID from URL
$brandId = isset($_GET['id']) ? intval($_GET['id']) : 0;

// If no brand ID provided, redirect to shop page
if ($brandId <= 0) {
    header('Location: shop.php');
    exit;
}

// Get brand details
$brand = getBrandById($brandId);

// If brand not found, redirect to shop page
if (!$brand) {
    header('Location: shop.php');
    exit;
}

// Define constants with brand data that can't be overwritten
if (!defined('SELECTED_BRAND_NAME')) {
    define('SELECTED_BRAND_NAME', $brand['name']);
}
if (!defined('SELECTED_BRAND_ID')) {
    define('SELECTED_BRAND_ID', $brand['id']);
}

// Store brand name securely to prevent it from being overwritten
$brandName = SELECTED_BRAND_NAME;
$brandId = SELECTED_BRAND_ID;

// Get all vehicle makes for the filter dropdown
$makes = getAllVehicleMakes();
$models = [];
$series = [];
$devices = [];

// Get categories for the brand (instead of all categories)
$categories = getCategoriesByBrandId($brandId);

// Get filter parameters from URL
$makeId = isset($_GET['make_id']) ? intval($_GET['make_id']) : 0;
$modelId = isset($_GET['model_id']) ? intval($_GET['model_id']) : 0;
$seriesId = isset($_GET['series_id']) ? intval($_GET['series_id']) : 0;
$deviceId = isset($_GET['device_id']) ? intval($_GET['device_id']) : 0;
$categoryId = isset($_GET['category_id']) ? intval($_GET['category_id']) : 0;

// If make ID is provided, get models for that make
if ($makeId > 0) {
    $models = getVehicleModelsByMake($makeId);
    
    // If model ID is provided, get series for that model
    if ($modelId > 0) {
        $series = getVehicleSeriesByModel($modelId);
        
        // If series ID is provided, get devices for that series
        if ($seriesId > 0) {
            $devices = getVehicleDevicesBySeries($seriesId);
        }
    }
}

// Get all products for this brand, with optional filters
if ($categoryId > 0) {
    // If category filter is applied, we need to filter products by both brand and category
    $allCategoryProducts = getProductsByCategory($categoryId);
    $products = array_filter($allCategoryProducts, function($product) use ($brandId, $makeId, $modelId, $seriesId, $deviceId) {
        // Match brand ID always
        if ($product['brand_id'] != $brandId) {
            return false;
        }
        
        // Match make ID if specified
        if ($makeId > 0 && (!isset($product['make_id']) || $product['make_id'] != $makeId)) {
            return false;
        }
        
        // Match model ID if specified
        if ($modelId > 0 && (!isset($product['model_id']) || $product['model_id'] != $modelId)) {
            return false;
        }
        
        // Match series ID if specified
        if ($seriesId > 0 && (!isset($product['series_id']) || $product['series_id'] != $seriesId)) {
            return false;
        }
        
        // Match device ID if specified
        if ($deviceId > 0 && (!isset($product['device_id']) || $product['device_id'] != $deviceId)) {
            return false;
        }
        
        return true;
    });
} else {
    // If no category filter, use the standard function
    $products = getAllProductsWithFilters($brandId, $makeId, $modelId, $seriesId, $deviceId);
}

// Check if vehicle filters were applied but no products found
$vehicleFilterApplied = ($makeId > 0 || $modelId > 0 || $seriesId > 0 || $deviceId > 0);
$noProductsFound = empty($products);

// Send email to admin if vehicle filters applied but no products found
if ($vehicleFilterApplied && $noProductsFound) {
    // Get filter details for the email
    $filterDetails = [];
    
    if ($makeId > 0) {
        foreach ($makes as $make) {
            if ($make['id'] == $makeId) {
                $filterDetails['make'] = $make['name'];
                break;
            }
        }
    }
    
    if ($modelId > 0 && !empty($models)) {
        foreach ($models as $model) {
            if ($model['id'] == $modelId) {
                $filterDetails['model'] = $model['name'];
                break;
            }
        }
    }
    
    if ($seriesId > 0 && !empty($series)) {
        foreach ($series as $seriesItem) {
            if ($seriesItem['id'] == $seriesId) {
                $filterDetails['series'] = $seriesItem['name'];
                break;
            }
        }
    }
    
    if ($deviceId > 0 && !empty($devices)) {
        foreach ($devices as $device) {
            if ($device['id'] == $deviceId) {
                $filterDetails['device'] = $device['name'];
                break;
            }
        }
    }
    
    // Get user information for the email
    $userInfo = [
        'is_logged_in' => isLoggedIn(),
        'username' => isLoggedIn() ? $_SESSION['username'] : 'Guest',
        'email' => isLoggedIn() ? $_SESSION['email'] : 'Not provided',
        'phone' => isLoggedIn() ? (isset($_SESSION['phone']) ? $_SESSION['phone'] : 'Not provided') : 'Not provided',
        'ip_address' => $_SERVER['REMOTE_ADDR']
    ];
    
    // Prepare email subject and body
    $subject = 'Vehicle Product Request - No Results Found';
    
    $htmlBody = '<h2>Vehicle Product Request - No Results Found</h2>';
    $htmlBody .= '<p>A user has searched for products with the following vehicle filters but found no results:</p>';
    $htmlBody .= '<h3>Filter Details:</h3>';
    $htmlBody .= '<ul>';
    $htmlBody .= '<li><strong>Brand:</strong> ' . htmlspecialchars($brandName) . '</li>';
    
    if (isset($filterDetails['make'])) {
        $htmlBody .= '<li><strong>Make:</strong> ' . htmlspecialchars($filterDetails['make']) . '</li>';
    }
    
    if (isset($filterDetails['model'])) {
        $htmlBody .= '<li><strong>Model:</strong> ' . htmlspecialchars($filterDetails['model']) . '</li>';
    }
    
    if (isset($filterDetails['series'])) {
        $htmlBody .= '<li><strong>Series:</strong> ' . htmlspecialchars($filterDetails['series']) . '</li>';
    }
    
    if (isset($filterDetails['device'])) {
        $htmlBody .= '<li><strong>Device:</strong> ' . htmlspecialchars($filterDetails['device']) . '</li>';
    }
    
    if ($categoryId > 0) {
        foreach ($categories as $category) {
            if ($category['id'] == $categoryId) {
                $htmlBody .= '<li><strong>Category:</strong> ' . htmlspecialchars($category['name']) . '</li>';
                break;
            }
        }
    }
    
    $htmlBody .= '</ul>';
    
    $htmlBody .= '<h3>User Information:</h3>';
    $htmlBody .= '<ul>';
    $htmlBody .= '<li><strong>User Status:</strong> ' . ($userInfo['is_logged_in'] ? 'Logged In' : 'Guest') . '</li>';
    $htmlBody .= '<li><strong>Username:</strong> ' . htmlspecialchars($userInfo['username']) . '</li>';
    $htmlBody .= '<li><strong>Email:</strong> ' . htmlspecialchars($userInfo['email']) . '</li>';
    $htmlBody .= '<li><strong>Phone:</strong> ' . htmlspecialchars($userInfo['phone']) . '</li>';
    $htmlBody .= '<li><strong>IP Address:</strong> ' . htmlspecialchars($userInfo['ip_address']) . '</li>';
    $htmlBody .= '<li><strong>Date/Time:</strong> ' . date('Y-m-d H:i:s') . '</li>';
    $htmlBody .= '</ul>';
    
    // Send the email notification to admin
    sendEmail($subject, $htmlBody);
    
    // Also send notification to the user if they're logged in
    if ($userInfo['is_logged_in'] && !empty($userInfo['email'])) {
        // Use the centralized function to send notification to user
        sendVehicleSearchNotification(
            ['id' => $brandId, 'name' => $brandName], // Use our saved brand information
            $filterDetails, 
            $userInfo['email'], 
            $userInfo['username'], 
            $categoryId, 
            $categories
        );
    }
}

/**
 * Helper function to get the base URL
 */
function getBaseUrl() {
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://';
    $host = $_SERVER['HTTP_HOST'];
    $path = dirname($_SERVER['PHP_SELF']);
    return $protocol . $host . $path . '/';
}

// Right before including the view, make sure the $brand variable is correct
$brand = [
    'id' => SELECTED_BRAND_ID,
    'name' => SELECTED_BRAND_NAME
];

// Include brand view
require_once ROOT_PATH . '/app/views/brand.php';
?> 
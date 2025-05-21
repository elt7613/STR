<?php
/**
 * Category Products Page - Display products in a specific category
 */

// Include initialization script
require_once __DIR__ . '/../includes/init.php';
// Include email functionality
require_once ROOT_PATH . '/app/config/email.php';

// Get category ID from URL
$categoryId = isset($_GET['id']) ? intval($_GET['id']) : 0;

// If no category ID provided, redirect to shop page
if ($categoryId <= 0) {
    header('Location: shop.php');
    exit;
}

// Get category details
$category = getCategoryById($categoryId);

// If category not found, redirect to shop page
if (!$category) {
    header('Location: shop.php');
    exit;
}

// Get brand details
$brand = getBrandById($category['brand_id']);

// Define constants with category data that can't be overwritten
if (!defined('SELECTED_CATEGORY_NAME')) {
    define('SELECTED_CATEGORY_NAME', $category['name']);
}
if (!defined('SELECTED_CATEGORY_ID')) {
    define('SELECTED_CATEGORY_ID', $category['id']);
}
if (!defined('SELECTED_BRAND_NAME')) {
    define('SELECTED_BRAND_NAME', $brand['name']);
}
if (!defined('SELECTED_BRAND_ID')) {
    define('SELECTED_BRAND_ID', $brand['id']);
}

// Get all vehicle makes for the filter dropdown
$makes = getAllVehicleMakes();
$models = [];
$series = [];
$devices = [];

// Get filter parameters from URL
$makeId = isset($_GET['make_id']) ? intval($_GET['make_id']) : 0;
$modelId = isset($_GET['model_id']) ? intval($_GET['model_id']) : 0;
$seriesId = isset($_GET['series_id']) ? intval($_GET['series_id']) : 0;
$deviceId = isset($_GET['device_id']) ? intval($_GET['device_id']) : 0;

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

// Get products for this category
$allCategoryProducts = getProductsByCategory($categoryId);

// Filter products based on vehicle filters
if ($makeId > 0 || $modelId > 0 || $seriesId > 0 || $deviceId > 0) {
    $products = array_filter($allCategoryProducts, function($product) use ($makeId, $modelId, $seriesId, $deviceId) {
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
    $products = $allCategoryProducts;
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
    $subject = 'Category Product Request - No Results Found';
    
    $htmlBody = '<h2>Category Product Request - No Results Found</h2>';
    $htmlBody .= '<p>A user has searched for products with the following vehicle filters but found no results:</p>';
    $htmlBody .= '<h3>Filter Details:</h3>';
    $htmlBody .= '<ul>';
    $htmlBody .= '<li><strong>Category:</strong> ' . htmlspecialchars(SELECTED_CATEGORY_NAME) . '</li>';
    $htmlBody .= '<li><strong>Brand:</strong> ' . htmlspecialchars(SELECTED_BRAND_NAME) . '</li>';
    
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
            ['id' => $brand['id'], 'name' => SELECTED_BRAND_NAME], // Use our saved brand information
            $filterDetails, 
            $userInfo['email'], 
            $userInfo['username'], 
            $categoryId, 
            [$category]  // Pass the current category as an array for consistency
        );
    }
}

/**
 * Helper function to remove a query parameter from a URL
 * 
 * @param string $url The URL
 * @param string $param The parameter to remove
 * @return string The modified URL
 */
function removeQueryParam($url, $param) {
    $urlParts = parse_url($url);
    if (isset($urlParts['query'])) {
        parse_str($urlParts['query'], $params);
        unset($params[$param]);
        $urlParts['query'] = http_build_query($params);
    }
    
    $newUrl = $urlParts['path'];
    if (isset($urlParts['query']) && !empty($urlParts['query'])) {
        $newUrl .= '?' . $urlParts['query'];
    }
    
    return $newUrl;
}

// Include category products view
require_once ROOT_PATH . '/app/views/category_products.php';
?> 
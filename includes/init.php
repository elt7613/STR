<?php
/**
 * Application initialization script
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Set default timezone to Indian Standard Time (IST)
date_default_timezone_set('Asia/Kolkata');

// Define root path
if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', realpath(__DIR__ . '/..'));
}

// Include database schema
require_once ROOT_PATH . '/app/config/schema.php';

// Include vehicle schema
require_once ROOT_PATH . '/app/config/vehicle_schema.php';

// Include shop schema
require_once ROOT_PATH . '/app/config/shop_schema.php';

// Include authentication functions
require_once ROOT_PATH . '/app/core/auth.php';

// Include vehicle functions
require_once ROOT_PATH . '/app/core/vehicle.php';

// Include shop functions
require_once ROOT_PATH . '/app/core/shop.php';
?> 
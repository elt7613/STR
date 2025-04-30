<?php
/**
 * Application initialization script
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    // Set session cookie parameters for better security
    session_set_cookie_params([
        'lifetime' => 86400, // 24 hours
        'path' => '/',
        'domain' => '',
        'secure' => false, // Set to true if using HTTPS
        'httponly' => true,
        'samesite' => 'Lax'
    ]);
    
    session_start();
    
    // Regenerate session ID periodically to prevent session fixation
    if (!isset($_SESSION['last_regeneration']) || (time() - $_SESSION['last_regeneration']) > 1800) {
        session_regenerate_id(true);
        $_SESSION['last_regeneration'] = time();
    }
}

// Ensure we have a session ID
if (empty(session_id())) {
    error_log("WARNING: Empty session ID after session start in init.php");
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

// Include shop functions
require_once ROOT_PATH . '/app/core/shop.php';

require_once ROOT_PATH . '/app/core/vehicle.php';
?> 
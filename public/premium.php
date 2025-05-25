<?php
/**
 * Premium Membership Page Controller
 * 
 * This page allows users to:
 * - View premium membership plans
 * - Subscribe to premium membership
 */

// Include initialization file
require_once __DIR__ . '/../includes/init.php';

// Include premium functions
require_once __DIR__ . '/../app/config/premium_schema.php';

// Redirect to login if not logged in
if (!isLoggedIn()) {
    $_SESSION['referring_page'] = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : 'premium.php';
    header('Location: index.php');
    exit;
}

// Check if user is already a premium member
$isPremium = isPremiumMember();

// Get user information
$userId = $_SESSION['user_id'];
$userInfo = getUserById($userId);

// Set custom CSS for premium page
$custom_css = 'premium.css';
$page_title = 'Premium Membership';

// Include header
include_once ROOT_PATH . '/app/views/partials/header.php';

// Include premium view
include_once ROOT_PATH . '/app/views/premium_view.php';

// Include footer
include_once ROOT_PATH . '/app/views/partials/footer.php';
?>

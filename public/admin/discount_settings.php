<?php
/**
 * Admin discount settings controller
 */

// Include initialization file
require_once __DIR__ . '/../../includes/init.php';

// Include discount functionality
require_once ROOT_PATH . '/app/config/discount.php';

// Include auth functionality
require_once ROOT_PATH . '/app/core/auth.php';

// Debug session information (temporary)
error_log("User ID: " . (isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 'not set'));
error_log("Is Admin: " . (isset($_SESSION['is_admin']) ? ($_SESSION['is_admin'] ? 'yes' : 'no') : 'not set'));

// Check if the user is logged in and is an admin using the isAdmin function
if (!isLoggedIn() || !isAdmin()) {
    // Redirect to home page if not an admin
    header('Location: /');
    exit;
}

// Load the admin layout with the discount settings view
$pageTitle = 'Premium Discount Settings - Admin';
$currentPage = 'discount_settings';

require_once ROOT_PATH . '/app/views/admin/partials/header.php';
require_once ROOT_PATH . '/app/views/admin/discount_settings.php';
require_once ROOT_PATH . '/app/views/admin/partials/footer.php';
?> 
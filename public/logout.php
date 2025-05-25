<?php
/**
 * Logout Page
 */

// Include initialization script
require_once __DIR__ . '/../includes/init.php';

// Log out the user
logoutUser();

// Redirect to login page
header('Location: index.php');
exit;
?>
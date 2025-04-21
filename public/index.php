<?php
/**
 * Main entry point - Login Page
 */

// Include initialization script
require_once __DIR__ . '/../includes/init.php';

// Process login form submission
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['login'])) {
        // Login form was submitted
        $username = $_POST['username'] ?? '';
        $password = $_POST['password'] ?? '';
        
        $result = loginUser($username, $password);
        
        if ($result['success']) {
            // Redirect to vehicle page on successful login instead of dashboard
            header('Location: vehicle.php');
            exit;
        } else {
            $error = $result['message'];
        }
    }
}

// If user is already logged in, redirect to vehicle page instead of dashboard
if (isLoggedIn()) {
    header('Location: vehicle.php');
    exit;
}

// Include login view
require_once ROOT_PATH . '/app/views/login.php';
?> 
<?php
/**
 * Registration Page
 */

// Include initialization script
require_once __DIR__ . '/../includes/init.php';

// Process registration form submission
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['register'])) {
        // Registration form was submitted
        $username = $_POST['username'] ?? '';
        $email = $_POST['email'] ?? '';
        $phone = $_POST['phone'] ?? '';
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        
        // Additional validation
        if ($password !== $confirmPassword) {
            $error = 'Passwords do not match';
        } else {
            $result = registerUser($username, $email, $password, $phone);
            
            if ($result['success']) {
                $success = $result['message'] . ' <a href="index.php">Login here</a>';
            } else {
                $error = $result['message'];
            }
        }
    }
}

// If user is already logged in, redirect to vehicle page
if (isLoggedIn()) {
    header('Location: vehicle.php');
    exit;
}

// Include register view
require_once ROOT_PATH . '/app/views/register.php';
?> 
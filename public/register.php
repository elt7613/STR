<?php
/**
 * Registration Page
 */

// Include initialization script
require_once __DIR__ . '/../includes/init.php';

// Process registration form submission
$error = '';
$success = '';

// Store the referring page in session if it's not already set
if (isset($_GET['redirect']) && !empty($_GET['redirect'])) {
    // Use the explicit redirect parameter if provided
    $_SESSION['referring_page'] = urldecode($_GET['redirect']);
} elseif (!isset($_SESSION['referring_page']) && isset($_SERVER['HTTP_REFERER'])) {
    $referer = $_SERVER['HTTP_REFERER'];
    $host = $_SERVER['HTTP_HOST'];
    
    // Make sure it's from our own site and not the login or register page
    if (strpos($referer, $host) !== false && 
        strpos($referer, 'index.php') === false && 
        strpos($referer, 'register.php') === false && 
        strpos($referer, 'logout.php') === false) {
        $_SESSION['referring_page'] = $referer;
    }
}

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
                // If successful, either automatically log them in and redirect to the previous page,
                // or prompt them to login with a link to the login page
                $success = $result['message'] . ' <a href="index.php">Login here</a>';
            } else {
                $error = $result['message'];
            }
        }
    }
}

// If user is already logged in, redirect to referring page or shop page
if (isLoggedIn()) {
    $redirect_to = isset($_SESSION['referring_page']) && !empty($_SESSION['referring_page']) 
        ? $_SESSION['referring_page'] 
        : 'shop.php';
    unset($_SESSION['referring_page']); // Clear the referring page after use
    header('Location: ' . $redirect_to);
    exit;
}

// Include register view
require_once ROOT_PATH . '/app/views/register.php';
?> 
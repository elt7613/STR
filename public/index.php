<?php
/**
 * Main entry point - Login Page
 */

// Include initialization script
require_once __DIR__ . '/../includes/init.php';

// Process login form submission
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
    if (isset($_POST['login'])) {
        // Login form was submitted
        $username = $_POST['username'] ?? '';
        $password = $_POST['password'] ?? '';
        
        $result = loginUser($username, $password);
        
        if ($result['success']) {
            // Redirect to referring page if set, or shop page as fallback
            $redirect_to = isset($_SESSION['referring_page']) && !empty($_SESSION['referring_page']) 
                ? $_SESSION['referring_page'] 
                : 'shop.php';
            unset($_SESSION['referring_page']); // Clear the referring page after use
            header('Location: ' . $redirect_to);
            exit;
        } else {
            $error = $result['message'];
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

// Include login view
require_once ROOT_PATH . '/app/views/login.php';
?> 
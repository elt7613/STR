<?php
/**
 * Admin Login Page
 */

// Include initialization script
require_once __DIR__ . '/../../includes/init.php';

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
            // Check if the user is an admin
            if (isAdmin()) {
                // Redirect to admin dashboard
                header('Location: dashboard.php');
                exit;
            } else {
                // Not an admin
                $error = 'Access denied. You do not have admin privileges.';
                
                // Log them out since they're not an admin
                logoutUser();
            }
        } else {
            $error = $result['message'];
        }
    }
}

// If user is already logged in and is admin, redirect to dashboard
if (isLoggedIn() && isAdmin()) {
    header('Location: dashboard.php');
    exit;
}

// Set page title
$pageTitle = 'Admin Login';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?></title>
    <link rel="stylesheet" href="/assets/css/style.css">
    <link rel="stylesheet" href="/assets/css/admin.css">
    <style>
        body {
            background-color: #f8f9fa;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .admin-login-container {
            width: 100%;
            max-width: 400px;
            padding: 30px;
            background-color: white;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
        }
        .admin-login-logo {
            text-align: center;
            margin-bottom: 20px;
            font-size: 24px;
            font-weight: bold;
            color: #2c3e50;
        }
    </style>
</head>
<body>
    <div class="admin-login-container">
        <div class="admin-login-logo">Admin Panel</div>
        
        <?php if (!empty($error)): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <?php if (!empty($success)): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>
        
        <h1>Admin Login</h1>
        
        <form method="POST" action="">
            <div class="form-group">
                <label for="username">Username or Email</label>
                <input type="text" id="username" name="username" required>
            </div>
            
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>
            
            <div class="form-group">
                <button type="submit" name="login" class="btn btn-primary" style="width: 100%;">Login as Admin</button>
            </div>
            
            <p class="text-center"><a href="/">Back to Main Site</a></p>
        </form>
    </div>
</body>
</html> 
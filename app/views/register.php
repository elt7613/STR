<?php 
// Set page title
$pageTitle = 'Register - STR Works';
$custom_css = 'auth.css';

// Include header
require_once __DIR__ . '/partials/header.php'; 
?>

<div class="container">
    <div class="auth-container">
        <div class="auth-header">
            <h1>Register</h1>
            <p>Create an account to get access to all features</p>
        </div>
        
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($success)): ?>
            <div class="alert alert-success">
                <?php echo $success; ?>
            </div>
        <?php endif; ?>
        
        <form method="POST" action="" class="auth-form">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" class="form-control" required>
            </div>
            
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" class="form-control" required>
            </div>
            
            <div class="form-group">
                <label for="phone">Phone Number</label>
                <input type="tel" id="phone" name="phone" class="form-control" required>
                <small>Please include country code if outside of India</small>
            </div>
            
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" class="form-control" required>
                <small>Password must be at least 8 characters</small>
            </div>
            
            <div class="form-group">
                <label for="confirm_password">Confirm Password</label>
                <input type="password" id="confirm_password" name="confirm_password" class="form-control" required>
            </div>
            
            <div class="form-group btn-container">
                <button type="submit" name="register" class="btn-primary">Create Account</button>
            </div>
            
            <p class="text-center">Already have an account? <a href="/index.php">Login here</a></p>
        </form>
    </div>
</div>

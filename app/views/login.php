<?php 
// Set page title
$pageTitle = 'Login - STR Works';
$custom_css = 'auth.css';

// Include header
require_once __DIR__ . '/partials/header.php'; 
?>

<div class="container">
    <div class="auth-container">
        <div class="auth-header">
            <h1>Login</h1>
            <p>Sign in to your account to access all features</p>
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
                <label for="username">Username or Email</label>
                <input type="text" id="username" name="username" class="form-control" required>
            </div>
            
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" class="form-control" required>
            </div>
            
            <div class="auth-options">
                <label class="remember-me">
                    <input type="checkbox" name="remember" id="remember">
                    Remember me
                </label>
                <a href="#" class="forgot-password">Forgot password?</a>
            </div>
            
            <div class="form-group btn-container">
                <button type="submit" name="login" class="btn-primary">Sign In</button>
            </div>
            
            <p class="text-center">Don't have an account? <a href="register.php">Register here</a></p>
        </form>
        
        <!-- Optional Social Login - Can be enabled later -->
        <!--
        <div class="social-login">
            <p>Or sign in with</p>
            <div class="social-btns">
                <a href="#" class="social-btn google">
                    <i class="fab fa-google"></i> Google
                </a>
                <a href="#" class="social-btn facebook">
                    <i class="fab fa-facebook-f"></i> Facebook
                </a>
            </div>
        </div>
        -->
    </div>
</div>

<?php require_once __DIR__ . '/partials/footer.php'; ?>
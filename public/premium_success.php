<?php
/**
 * Premium Membership Success Page
 */

// Include initialization file
require_once __DIR__ . '/../includes/init.php';

// Redirect to login if not logged in
if (!isLoggedIn()) {
    header('Location: index.php');
    exit;
}

// Check if user is a premium member
if (!isPremiumMember()) {
    $_SESSION['alert_type'] = 'warning';
    $_SESSION['alert_message'] = 'You are not a premium member yet. Please subscribe to continue.';
    header('Location: premium.php');
    exit;
}

// Page title
$pageTitle = 'Premium Membership Activated';
$activePage = 'premium';

// Set custom CSS for premium page
$custom_css = 'premium.css';
$page_title = 'Premium Membership Activated';

// Include header
include_once ROOT_PATH . '/app/views/partials/header.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8 text-center">
            <div class="card shadow-sm">
                <div class="card-body py-5">
                    <div class="mb-4">
                        <i class="fas fa-check-circle text-success" style="font-size: 5rem;"></i>
                    </div>
                    
                    <h1 class="h2 mb-4">Welcome to Premium Membership!</h1>
                    
                    <p class="lead mb-4">Your premium membership has been successfully activated.</p>
                    
                    <div class="alert alert-info mb-4">
                        <p class="mb-0">You now have access to all premium features and benefits.</p>
                    </div>
                    
                    <div class="premium-benefits mb-4">
                        <h5 class="mb-3">Your Premium Benefits Include:</h5>
                        <ul class="list-unstyled">
                            <li><i class="fas fa-check text-success mr-2"></i> Special discounts on all services</li>
                            <li><i class="fas fa-check text-success mr-2"></i> Priority customer support</li>
                            <li><i class="fas fa-check text-success mr-2"></i> Exclusive access to premium content</li>
                            <li><i class="fas fa-check text-success mr-2"></i> Advanced features and tools</li>
                        </ul>
                    </div>
                    
                    <div class="mt-4">
                        <a href="profile.php" class="btn btn-primary mr-2">
                            <i class="fas fa-user mr-1"></i> View Your Profile
                        </a>
                        <a href="index.php" class="btn btn-outline-primary">
                            <i class="fas fa-home mr-1"></i> Return to Homepage
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// Include footer
include_once ROOT_PATH . '/app/views/partials/footer.php';
?>

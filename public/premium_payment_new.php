<?php
/**
 * Premium Membership Payment Page
 * 
 * Handles Razorpay payment integration for premium membership
 */

// Include initialization file
require_once __DIR__ . '/../includes/init.php';

// Include premium functions
require_once __DIR__ . '/../app/config/premium_schema.php';

// Include Razorpay configuration
require_once ROOT_PATH . '/app/config/razorpay.php';

// Set page title
$pageTitle = 'Premium Membership Payment';

// Add custom CSS
$customCSS = '<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap">
              <link rel="stylesheet" href="assets/css/premium.css">';

// Add custom JS
$customJS = '<script src="https://checkout.razorpay.com/v1/checkout.js"></script>';

// Redirect to login if not logged in
if (!isLoggedIn()) {
    $_SESSION['referring_page'] = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : 'premium_payment.php';
    header('Location: index.php');
    exit;
}

// Get user information
$userId = $_SESSION['user_id'];
$userInfo = getUserById($userId);

// Check if user is already a premium member
if (isPremiumMember()) {
    $_SESSION['alert_type'] = 'info';
    $_SESSION['alert_message'] = 'You are already a premium member.';
    header('Location: profile.php');
    exit;
}

// Initialize variables
$planId = isset($_GET['plan_id']) ? (int)$_GET['plan_id'] : (isset($_POST['plan_id']) ? (int)$_POST['plan_id'] : 0);
$plan = null;
$razorpayOrder = null;
$setupError = null;
$paymentError = null;

// Validate plan ID
if ($planId <= 0) {
    $_SESSION['alert_type'] = 'danger';
    $_SESSION['alert_message'] = 'Invalid plan selected. Please try again.';
    header('Location: premium.php');
    exit;
}

// Get plan details
$plan = getPremiumPricingPlan($planId);
if (!$plan) {
    $_SESSION['alert_type'] = 'danger';
    $_SESSION['alert_message'] = 'Selected plan not found. Please try again.';
    header('Location: premium.php');
    exit;
}

// Calculate expiry date
$durationMonths = $plan['duration_months'];
$expiryDate = date('Y-m-d H:i:s', strtotime("+{$durationMonths} months"));

// Handle payment verification
if (isset($_POST['razorpay_payment_id']) && isset($_POST['razorpay_order_id']) && isset($_POST['razorpay_signature'])) {
    try {
        $attributes = array(
            'razorpay_payment_id' => $_POST['razorpay_payment_id'],
            'razorpay_order_id' => $_POST['razorpay_order_id'],
            'razorpay_signature' => $_POST['razorpay_signature']
        );
        
        // Verify payment signature
        $api = new Razorpay\Api\Api(RAZORPAY_KEY_ID, RAZORPAY_KEY_SECRET);
        $api->utility->verifyPaymentSignature($attributes);
        
        // Update user's premium status
        $success = updateUserPremiumStatus($userId, 1, $expiryDate, $planId, $_POST['razorpay_payment_id']);
        
        if ($success) {
            // Redirect to success page
            $_SESSION['alert_type'] = 'success';
            $_SESSION['alert_message'] = 'Congratulations! Your premium membership has been activated.';
            header('Location: dashboard.php');
            exit;
        } else {
            $paymentError = "Failed to update premium status. Please contact support.";
        }
    } catch (Exception $e) {
        $paymentError = $e->getMessage();
    }
}

// Create Razorpay order
try {
    $api = new Razorpay\Api\Api(RAZORPAY_KEY_ID, RAZORPAY_KEY_SECRET);
    
    $orderData = [
        'receipt' => 'order_' . time(),
        'amount' => $plan['price'] * 100, // Amount in paise
        'currency' => 'INR',
        'payment_capture' => 1 // Auto-capture
    ];
    
    $razorpayOrder = $api->order->create($orderData);
} catch (Exception $e) {
    $setupError = $e->getMessage();
}

// Include header
include_once ROOT_PATH . '/app/views/partials/header.php';
?>

<div class="payment-page-container">
    <?php if (isset($setupError)): ?>
        <div class="payment-error">
            <i class="fas fa-exclamation-circle"></i>
            <h3>Payment Setup Failed</h3>
            <p><?php echo $setupError; ?></p>
            <a href="premium.php" class="back-button">Return to Premium Plans</a>
        </div>
    <?php elseif (isset($paymentError)): ?>
        <div class="payment-error-alert">
            <strong><i class="fas fa-exclamation-triangle"></i> Payment Error:</strong> <?php echo $paymentError; ?>
        </div>
    <?php else: ?>
        <div class="payment-header">
            <h1>Complete Your Purchase</h1>
            <p>You're just one step away from unlocking premium features</p>
        </div>
        
        <div class="payment-content-wrapper">
            <!-- Plan Summary Section -->
            <div class="payment-plan-summary">
                <div class="plan-header">
                    <h2><?php echo htmlspecialchars($plan['name']); ?></h2>
                    <?php if (isset($plan['is_recommended']) && $plan['is_recommended']): ?>
                        <span class="payment-recommended-badge">Recommended</span>
                    <?php endif; ?>
                </div>
                
                <div class="plan-price">
                    <span class="price-amount">₹<?php echo number_format($plan['price'], 2); ?></span>
                    <span class="price-duration">for <?php echo $plan['duration_months']; ?> months</span>
                </div>
                
                <div class="plan-description">
                    <?php echo htmlspecialchars($plan['description']); ?>
                </div>
                
                <div class="plan-features">
                    <ul>
                        <li><i class="fas fa-check-circle"></i> Unlimited Premium Content</li>
                        <li><i class="fas fa-check-circle"></i> Priority Customer Support</li>
                        <li><i class="fas fa-check-circle"></i> No Advertisements</li>
                        <li><i class="fas fa-check-circle"></i> Exclusive Premium Features</li>
                    </ul>
                </div>
                
                <div class="plan-details">
                    <div class="detail-item">
                        <span class="detail-label">Plan ID:</span>
                        <span class="detail-value">#<?php echo $planId; ?></span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Valid Until:</span>
                        <span class="detail-value"><?php echo date('d M Y', strtotime("+{$plan['duration_months']} months")); ?></span>
                    </div>
                </div>
            </div>
            
            <!-- Payment Processor Section -->
            <div class="payment-processor">
                <div class="payment-processor-header">
                    <h3>Payment Details</h3>
                </div>
                
                <div class="payment-secure-badge">
                    <i class="fas fa-shield-alt"></i> Secured by Razorpay Payment Gateway
                </div>
                
                <div class="payment-provider">
                    <img src="https://razorpay.com/assets/razorpay-logo.svg" alt="Razorpay">
                </div>
                
                <button id="razorpay-payment-button" class="payment-button">
                    <i class="fas fa-lock"></i> Pay ₹<?php echo number_format($plan['price'], 2); ?> Securely
                </button>
                
                <div class="payment-info">
                    <div class="info-item">
                        <i class="fas fa-check-circle"></i>
                        <span>100% Secure Payments</span>
                    </div>
                    <div class="info-item">
                        <i class="fas fa-check-circle"></i>
                        <span>Instant Activation</span>
                    </div>
                    <div class="info-item">
                        <i class="fas fa-check-circle"></i>
                        <span>24/7 Customer Support</span>
                    </div>
                </div>
                
                <div class="text-center mt-4">
                    <a href="premium.php" style="color: #666; text-decoration: none;">
                        <i class="fas fa-arrow-left"></i> Back to Premium Plans
                    </a>
                </div>
            </div>
        </div>
        
        <div class="payment-help">
            <h3>Need Help?</h3>
            <p>If you have any questions about your payment or membership, please contact our support team.</p>
        </div>
    <?php endif; ?>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const paymentButton = document.getElementById('razorpay-payment-button');
        
        if (paymentButton) {
            paymentButton.addEventListener('click', function() {
                // Show loading state
                paymentButton.innerHTML = '<i class="fas fa-circle-notch fa-spin"></i> Processing...';
                paymentButton.disabled = true;
                
                const options = {
                    key: '<?php echo RAZORPAY_KEY_ID; ?>',
                    amount: '<?php echo $plan['price'] * 100; ?>',
                    currency: 'INR',
                    name: '<?php echo htmlspecialchars($_SERVER['HTTP_HOST']); ?>',
                    description: '<?php echo htmlspecialchars($plan['name']); ?>',
                    order_id: '<?php echo isset($razorpayOrder['id']) ? $razorpayOrder['id'] : ''; ?>',
                    handler: function(response) {
                        // Show success state
                        paymentButton.innerHTML = '<i class="fas fa-check-circle"></i> Payment Successful! Redirecting...';
                        
                        // Create a form to submit the payment details
                        const form = document.createElement('form');
                        form.method = 'POST';
                        form.action = 'premium_payment.php';
                        
                        const inputPaymentId = document.createElement('input');
                        inputPaymentId.type = 'hidden';
                        inputPaymentId.name = 'razorpay_payment_id';
                        inputPaymentId.value = response.razorpay_payment_id;
                        form.appendChild(inputPaymentId);
                        
                        const inputOrderId = document.createElement('input');
                        inputOrderId.type = 'hidden';
                        inputOrderId.name = 'razorpay_order_id';
                        inputOrderId.value = response.razorpay_order_id;
                        form.appendChild(inputOrderId);
                        
                        const inputSignature = document.createElement('input');
                        inputSignature.type = 'hidden';
                        inputSignature.name = 'razorpay_signature';
                        inputSignature.value = response.razorpay_signature;
                        form.appendChild(inputSignature);
                        
                        const inputPlanId = document.createElement('input');
                        inputPlanId.type = 'hidden';
                        inputPlanId.name = 'plan_id';
                        inputPlanId.value = '<?php echo $planId; ?>';
                        form.appendChild(inputPlanId);
                        
                        document.body.appendChild(form);
                        
                        // Brief delay for user to see success message
                        setTimeout(function() {
                            form.submit();
                        }, 1000);
                    },
                    modal: {
                        ondismiss: function() {
                            // Reset button if payment window is closed
                            paymentButton.innerHTML = '<i class="fas fa-lock"></i> Pay ₹<?php echo number_format($plan["price"], 2); ?> Securely';
                            paymentButton.disabled = false;
                        }
                    },
                    prefill: {
                        name: '<?php echo htmlspecialchars($userInfo["username"]); ?>',
                        email: '<?php echo htmlspecialchars($userInfo["email"]); ?>',
                        contact: '<?php echo htmlspecialchars($userInfo["phone"] ?? ""); ?>'
                    },
                    theme: {
                        color: '#ff5bae' // Match the pink color theme
                    }
                };
                
                const rzp = new Razorpay(options);
                rzp.open();
            });
        }
    });
</script>

<?php
// Include footer
include_once ROOT_PATH . '/app/views/partials/footer.php';
?>

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

// Generate unique order reference
$orderReference = 'PREMIUM_' . $userId . '_' . time();

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
            // Redirect to profile page
            $_SESSION['alert_type'] = 'success';
            $_SESSION['alert_message'] = 'Congratulations! Your premium membership has been activated.';
            header('Location: profile.php');
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

// Create premium_payments table if it doesn't exist
try {
    $sql = "CREATE TABLE IF NOT EXISTS premium_payments (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        plan_id INT NOT NULL,
        payment_id VARCHAR(100) NOT NULL,
        payment_amount DECIMAL(10,2) NOT NULL,
        payment_status VARCHAR(50) NOT NULL,
        order_reference VARCHAR(100) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        FOREIGN KEY (plan_id) REFERENCES premium_pricing(id)
    )";
    $pdo->exec($sql);
} catch (PDOException $e) {
    error_log('Error creating premium_payments table: ' . $e->getMessage());
}

// Create Razorpay order if not already created
if (!isset($_SESSION['razorpay_premium_order_id_' . $userId])) {
    // Create a Razorpay order
    try {
        $razorpayOrder = createRazorpayOrder(
            $orderReference,
            $plan['price'],
            [
                'user_id' => $userId,
                'plan_id' => $planId,
                'plan_name' => $plan['name']
            ]
        );
        
        if (!$razorpayOrder) {
            throw new Exception('Failed to create Razorpay order - API returned null');
        }
        
        // Store Razorpay order ID in session
        $_SESSION['razorpay_premium_order_id_' . $userId] = $razorpayOrder['id'];
    } catch (Exception $e) {
        error_log('Razorpay Error: ' . $e->getMessage());
        $error = 'Payment initialization failed. Please try again or contact support.';
    }
} else {
    // Get existing Razorpay order ID from session
    $razorpayOrderId = $_SESSION['razorpay_premium_order_id_' . $userId];
    
    // Create a simplified order array for the view
    $razorpayOrder = ['id' => $razorpayOrderId];
}

// Page title
$pageTitle = 'Premium Membership Payment';
$activePage = 'premium';

// Set custom CSS for premium page
$custom_css = 'premium.css';
$page_title = 'Premium Membership Payment';

// Set variables for admin header
$activeMenu = 'premium';
$currentPage = 'premium_payment';

// Combine error messages for display
$error = !empty($setupError) ? $setupError : (!empty($paymentError) ? $paymentError : null);

// Include header
include_once ROOT_PATH . '/app/views/partials/header.php';
?>

<div class="payment-page-container">
    <div class="payment-header">
        <h1>Complete Your Premium Membership</h1>
        <p>You're just one step away from unlocking exclusive benefits</p>
    </div>

    <?php if ($error): ?>
        <div class="payment-error-alert">
            <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>
    
    <div class="payment-content-wrapper">
        <div class="payment-plan-summary">
            <div class="plan-header">
                <h2><?php echo htmlspecialchars($plan['name']); ?></h2>
                <?php if (isset($plan['is_recommended']) && $plan['is_recommended'] == 1): ?>
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
                    <li><i class="fas fa-check"></i> Special discounts on all services</li>
                    <li><i class="fas fa-check"></i> Priority customer support</li>
                    <li><i class="fas fa-check"></i> Exclusive access to premium content</li>
                    <li><i class="fas fa-check"></i> Advanced features and tools</li>
                </ul>
            </div>
            
            <div class="plan-details">
                <div class="detail-item">
                    <span class="detail-label">Order Reference:</span>
                    <span class="detail-value"><?php echo $orderReference; ?></span>
                </div>
            </div>
        </div>

        <div class="payment-processor">
            <?php if (isset($razorpayOrder) && isset($razorpayOrder['id'])): ?>
                <div class="payment-processor-header">
                    <h3>Payment Method</h3>
                </div>

                <div class="payment-secure-badge">
                    <i class="fas fa-shield-alt"></i> Secure Payment
                </div>

                <div class="payment-provider">
                    <img src="assets/img/razorpay-logo.svg" alt="Razorpay" class="razorpay-logo" 
                         onerror="this.src='assets/img/payment-secure.png'; this.onerror=null;">
                </div>
                
                <div id="razorpay-button-container">
                    <button id="razorpay-payment-button" class="payment-button">
                        <i class="fas fa-lock"></i> Pay ₹<?php echo number_format($plan['price'], 2); ?> Securely
                    </button>
                </div>
                
                <div class="payment-info">
                    <div class="info-item">
                        <i class="fas fa-check-circle"></i>
                        <span>Instant activation after payment</span>
                    </div>
                    <div class="info-item">
                        <i class="fas fa-shield-alt"></i>
                        <span>Secure payment processing</span>
                    </div>
                    <div class="info-item">
                        <i class="fas fa-undo"></i>
                        <span>Support available for any issues</span>
                    </div>
                </div>
            <?php else: ?>
                <div class="payment-error">
                    <i class="fas fa-exclamation-triangle"></i>
                    <h3>Payment Gateway Not Available</h3>
                    <p>We're unable to initialize the payment gateway at this time. Please try again later or contact support.</p>
                    <a href="premium.php" class="back-button">Back to Premium Plans</a>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="payment-help">
        <h3>Need Help?</h3>
        <p>If you have any questions or encounter any issues during the payment process, please <a href="contact.php">contact our support team</a>.</p>
    </div>
</div>

<!-- Include Razorpay JavaScript SDK -->
<script src="https://checkout.razorpay.com/v1/checkout.js"></script>
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

<?php
/**
 * Premium Membership Payment Page
 * 
 * Handles payment integration for premium membership
 */

// Include initialization file
require_once __DIR__ . '/../includes/init.php';

// Include premium functions
require_once __DIR__ . '/../app/config/premium_schema.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Get user info
$userId = $_SESSION['user_id'];
$userInfo = getUserById($userId);

// Check if plan_id is set
if (!isset($_GET['plan_id']) && !isset($_POST['plan_id'])) {
    header("Location: premium.php");
    exit;
}

$planId = isset($_GET['plan_id']) ? $_GET['plan_id'] : $_POST['plan_id'];
$plan = getPremiumPricingPlan($planId);

if (!$plan) {
    header("Location: premium.php");
    exit;
}

// Handle payment verification
if (isset($_POST['razorpay_payment_id']) && isset($_POST['razorpay_order_id']) && isset($_POST['razorpay_signature'])) {
    $success = false;
    $error = "Payment Failed";
    
    $attributes = array(
        'razorpay_payment_id' => $_POST['razorpay_payment_id'],
        'razorpay_order_id' => $_POST['razorpay_order_id'],
        'razorpay_signature' => $_POST['razorpay_signature']
    );
    
    try {
        $api = new Razorpay\Api\Api(RAZORPAY_KEY_ID, RAZORPAY_KEY_SECRET);
        $api->utility->verifyPaymentSignature($attributes);
        
        // Payment successful, update user's premium status
        $durationMonths = $plan['duration_months'];
        $currentDate = date('Y-m-d H:i:s');
        $expiryDate = date('Y-m-d H:i:s', strtotime("+{$durationMonths} months"));
        
        $success = updateUserPremiumStatus($userId, 1, $expiryDate, $planId, $_POST['razorpay_payment_id']);
        
        if ($success) {
            // Redirect to profile page
            header("Location: profile.php?premium_success=1");
            exit;
        } else {
            $error = "Failed to update premium status";
        }
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
    
    // If we're here, something went wrong
    $paymentError = $error;
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
    // Show error and provide a link back to premium page
    $setupError = $e->getMessage();
}
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
                    <?php if ($plan['is_recommended']): ?>
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

</body>
</html>

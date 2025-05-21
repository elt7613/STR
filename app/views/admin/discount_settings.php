<?php
// Set page title
$pageTitle = 'Premium User Discount Settings';

// Include header
require_once ROOT_PATH . '/app/views/admin/partials/header.php';

// Get the current discount percentage
$currentDiscountPercentage = getPremiumDiscountPercentage();

// Handle form submission to update discount percentage
$successMessage = '';
$errorMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_discount'])) {
    // Get submitted percentage
    $newPercentage = isset($_POST['discount_percentage']) ? floatval($_POST['discount_percentage']) : 0;
    
    // Update the discount percentage
    $result = updatePremiumDiscountPercentage($newPercentage);
    
    if ($result['success']) {
        $successMessage = $result['message'];
        $currentDiscountPercentage = $newPercentage; // Update the displayed value
    } else {
        $errorMessage = $result['message'];
    }
}
?>

<!-- Main Content -->
<div class="content">
    <h1>Premium User Discount Settings</h1>
    
    <?php if (!empty($successMessage)): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($successMessage); ?></div>
    <?php endif; ?>
    
    <?php if (!empty($errorMessage)): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($errorMessage); ?></div>
    <?php endif; ?>
    
    <div class="card">
        <div class="card-header">
            <h2>Manage Premium Discount</h2>
        </div>
        <div class="card-body">
            <form method="post" action="">
                <div class="form-group">
                    <label for="discount_percentage">Premium User Discount Percentage (%)</label>
                    <input type="number" class="form-control" id="discount_percentage" name="discount_percentage" 
                           value="<?php echo htmlspecialchars($currentDiscountPercentage); ?>"
                           step="0.01" min="0" max="100" required>
                    <small class="form-text text-muted">Enter a percentage between 0 and 100. This discount will be applied to premium users' purchases during checkout.</small>
                </div>
                
                <button type="submit" name="update_discount" class="btn btn-primary">Update Discount</button>
            </form>
        </div>
    </div>
    
    <div class="card mt-4">
        <div class="card-header">
            <h2>About Premium Discounts</h2>
        </div>
        <div class="card-body">
            <p>Premium users automatically receive a discount on their purchases. The current discount percentage is <strong><?php echo htmlspecialchars($currentDiscountPercentage); ?>%</strong>.</p>
            
            <h3>How it works:</h3>
            <ul>
                <li>When a premium user checks out, the discount is automatically applied to their subtotal</li>
                <li>The GST is calculated after the discount has been applied</li>
                <li>The discount is clearly shown in the order summary</li>
                <li>Non-premium users are shown a message encouraging them to upgrade to premium</li>
            </ul>
            
            <h3>Premium Member Statistics:</h3>
            <?php 
            // Get premium member statistics
            $premiumStats = getPremiumMemberStatistics();
            ?>
            
            <div class="row statistics">
                <div class="col-md-3">
                    <div class="stat-box">
                        <span class="stat-value"><?php echo $premiumStats['total_premium_users']; ?></span>
                        <span class="stat-label">Total Premium Users</span>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-box">
                        <span class="stat-value"><?php echo $premiumStats['premium_percentage']; ?>%</span>
                        <span class="stat-label">% of All Users</span>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-box">
                        <span class="stat-value">₹<?php echo number_format($premiumStats['total_discount_amount'], 2); ?></span>
                        <span class="stat-label">Total Discounts Given</span>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-box">
                        <span class="stat-value"><?php echo $premiumStats['premium_orders']; ?></span>
                        <span class="stat-label">Premium Orders</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.statistics {
    margin-top: 20px;
}

.stat-box {
    background-color: #f5f5f5;
    border-radius: 5px;
    padding: 15px;
    text-align: center;
    margin-bottom: 15px;
}

.stat-value {
    display: block;
    font-size: 24px;
    font-weight: bold;
    color: #333;
    margin-bottom: 5px;
}

.stat-label {
    display: block;
    font-size: 14px;
    color: #666;
}

.form-group {
    margin-bottom: 20px;
}

.btn-primary {
    background-color: #007bff;
    border-color: #007bff;
    padding: 8px 16px;
}

.btn-primary:hover {
    background-color: #0069d9;
    border-color: #0062cc;
}

.alert {
    padding: 15px;
    margin-bottom: 20px;
    border-radius: 4px;
}

.alert-success {
    background-color: #d4edda;
    border-color: #c3e6cb;
    color: #155724;
}

.alert-danger {
    background-color: #f8d7da;
    border-color: #f5c6cb;
    color: #721c24;
}
</style> 
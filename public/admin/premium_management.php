<?php
/**
 * Admin Premium Membership Management Controller
 * 
 * Allows admins to:
 * - View all premium plans
 * - Add new premium plans
 * - Edit existing premium plans
 * - Delete premium plans
 * - View statistics about premium members
 */

// Ensure no output is sent before we decide what to send
ob_start();

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Log the current file being executed
error_log('Executing file: ' . __FILE__);

// Include initialization file
require_once __DIR__ . '/../../includes/init.php';

// Define constants for consistent use
define('PREMIUM_SCHEMA_PATH', ROOT_PATH . '/app/config/premium_schema.php');
define('PREMIUM_VIEW_PATH', ROOT_PATH . '/app/views/admin/premium_management.php');

// Include premium schema with path verification
if (file_exists(PREMIUM_SCHEMA_PATH)) {
    require_once PREMIUM_SCHEMA_PATH;
    error_log('Successfully included premium schema from: ' . PREMIUM_SCHEMA_PATH);
} else {
    error_log('ERROR: Premium schema file not found at: ' . PREMIUM_SCHEMA_PATH);
    die('Configuration error: Premium schema file not found. Please contact the administrator.');
}

// Redirect if not admin
if (!isAdmin()) {
    error_log('User not admin, redirecting to index');
    header('Location: /index.php');
    exit;
}

// Initialize variables
$successMessage = null;
$errorMessage = null;

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'add':
            // Add new premium plan
            $name = $_POST['name'] ?? '';
            $description = $_POST['description'] ?? '';
            $price = isset($_POST['price']) ? (float)$_POST['price'] : 0;
            $durationMonths = isset($_POST['duration_months']) ? (int)$_POST['duration_months'] : 12;
            $isActive = isset($_POST['is_active']) ? 1 : 0;
            $isRecommended = isset($_POST['is_recommended']) ? 1 : 0;
            
            if (empty($name) || empty($description) || $price <= 0 || $durationMonths <= 0) {
                $errorMessage = 'Please fill in all required fields with valid values.';
            } else {
                $planId = addPremiumPricingPlan($name, $description, $price, $durationMonths, $isActive, $isRecommended);
                
                if ($planId) {
                    $successMessage = 'Premium plan added successfully.';
                } else {
                    $errorMessage = 'Failed to add premium plan. Please try again.';
                }
            }
            break;
            
        case 'edit':
            // Edit existing premium plan
            $planId = isset($_POST['plan_id']) ? (int)$_POST['plan_id'] : 0;
            $name = $_POST['name'] ?? '';
            $description = $_POST['description'] ?? '';
            $price = isset($_POST['price']) ? (float)$_POST['price'] : 0;
            $durationMonths = isset($_POST['duration_months']) ? (int)$_POST['duration_months'] : 12;
            $isActive = isset($_POST['is_active']) ? 1 : 0;
            $isRecommended = isset($_POST['is_recommended']) ? 1 : 0;
            
            if ($planId <= 0 || empty($name) || empty($description) || $price <= 0 || $durationMonths <= 0) {
                $errorMessage = 'Please fill in all required fields with valid values.';
            } else {
                $success = updatePremiumPricingPlan($planId, $name, $description, $price, $durationMonths, $isActive, $isRecommended);
                
                if ($success) {
                    $successMessage = 'Premium plan updated successfully.';
                } else {
                    $errorMessage = 'Failed to update premium plan. Please try again.';
                }
            }
            break;
            
        case 'delete':
            // Delete premium plan
            $planId = isset($_POST['plan_id']) ? (int)$_POST['plan_id'] : 0;
            
            if ($planId <= 0) {
                $errorMessage = 'Invalid plan ID.';
            } else {
                try {
                    // First, check if there are any active subscriptions for this plan
                    $stmt = $pdo->prepare("SELECT COUNT(*) as subscription_count FROM premium_payments WHERE plan_id = ?");
                    $stmt->execute([$planId]);
                    $result = $stmt->fetch();
                    
                    // If there are subscriptions, just mark as inactive instead of deleting
                    if ($result && $result['subscription_count'] > 0) {
                        $stmt = $pdo->prepare("UPDATE premium_pricing SET is_active = 0 WHERE id = ?");
                        $success = $stmt->execute([$planId]);
                        
                        if ($success) {
                            $successMessage = 'Premium plan marked as inactive because there are existing subscriptions.';
                        } else {
                            $errorMessage = 'Failed to update premium plan status.';
                        }
                    } else {
                        // No subscriptions, safe to delete
                        $stmt = $pdo->prepare("DELETE FROM premium_pricing WHERE id = ?");
                        $success = $stmt->execute([$planId]);
                        
                        if ($success) {
                            $successMessage = 'Premium plan deleted successfully.';
                        } else {
                            $errorMessage = 'Failed to delete premium plan.';
                        }
                    }
                } catch (PDOException $e) {
                    error_log('Error deleting premium plan: ' . $e->getMessage());
                    $errorMessage = 'Database error occurred. Please try again.';
                }
            }
            break;
            
        default:
            $errorMessage = 'Invalid action.';
            break;
    }
}

// Get all premium plans
$stmt = $pdo->query("SELECT * FROM premium_pricing ORDER BY price ASC");
$premiumPlans = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get premium membership statistics
try {
    // Total premium members
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM users WHERE is_premium_member = 1");
    $result = $stmt->fetch();
    $totalPremiumMembers = $result ? $result['count'] : 0;
    
    // Total revenue from premium subscriptions
    $stmt = $pdo->query("SELECT SUM(payment_amount) as total FROM premium_payments WHERE payment_status = 'completed'");
    $result = $stmt->fetch();
    $totalRevenue = $result ? $result['total'] : 0;
    
    // Recent subscriptions (last 30 days)
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM premium_payments 
                        WHERE payment_status = 'completed' 
                        AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)");
    $result = $stmt->fetch();
    $recentSubscriptions = $result ? $result['count'] : 0;
    
    // Active plans count
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM premium_pricing WHERE is_active = 1");
    $result = $stmt->fetch();
    $activePlans = $result ? $result['count'] : 0;
} catch (PDOException $e) {
    error_log('Error fetching premium statistics: ' . $e->getMessage());
    $totalPremiumMembers = $totalRevenue = $recentSubscriptions = $activePlans = 0;
}

// Page title and active menu
$pageTitle = 'Premium Membership Management';
$activeMenu = 'premium';
$currentPage = ''; // Define currentPage variable to avoid undefined warnings

// Include admin header
include_once ROOT_PATH . '/app/views/admin/partials/header.php';

// Check if view file exists before attempting to include it
if (file_exists(PREMIUM_VIEW_PATH)) {
    error_log('Including view file from: ' . PREMIUM_VIEW_PATH);
    include_once PREMIUM_VIEW_PATH;
} else {
    error_log('ERROR: Premium view file not found at: ' . PREMIUM_VIEW_PATH);
    echo '<div class="alert alert-danger">Error: Could not find premium management view file.</div>';
    echo '<div class="alert alert-info">Debug info: Looking for file at ' . htmlspecialchars(PREMIUM_VIEW_PATH) . '</div>';
    // List directory contents to see what's actually there
    $dir = dirname(PREMIUM_VIEW_PATH);
    if (is_dir($dir)) {
        echo '<div class="alert alert-info">Files in directory ' . htmlspecialchars($dir) . ':</div>';
        echo '<ul>';
        foreach (scandir($dir) as $file) {
            echo '<li>' . htmlspecialchars($file) . '</li>';
        }
        echo '</ul>';
    }
}

// Include admin footer
include_once ROOT_PATH . '/app/views/admin/partials/footer.php';
?>

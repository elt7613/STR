<?php
/**
 * Premium Subscriptions Management
 * 
 * Allows admins to view and manage premium subscribers
 */

// Include initialization file
require_once __DIR__ . '/../../includes/init.php';

// Include premium functions
require_once ROOT_PATH . '/app/config/premium_schema.php';

// Verify admin privileges
if (!isLoggedIn() || !isAdmin()) {
    header('Location: ../index.php');
    exit;
}

// Page variables
$pageTitle = 'Premium Subscriptions';
$activeMenu = 'premium';
$currentPage = 'premium_subscriptions';

// Get filter parameters
$planId = isset($_GET['plan_id']) ? (int)$_GET['plan_id'] : 0;
$isRecent = isset($_GET['recent']) && $_GET['recent'] == '1';
$searchQuery = isset($_GET['search']) ? trim($_GET['search']) : '';

// Initialize variables
$subscribers = [];
$plans = [];
$error = null;
$filterDescription = 'All Subscribers';
$currentPlan = null;

// Get all premium plans for the filter dropdown
try {
    $plans = getPremiumPricingPlans();
} catch (PDOException $e) {
    error_log('Error retrieving premium plans: ' . $e->getMessage());
}

// Build query based on filters
$query = "
    SELECT 
        u.id,
        u.username,
        u.email,
        u.profile_image,
        u.is_premium_member,
        u.premium_expiry,
        pp.id AS plan_id,
        pp.name AS plan_name,
        pp.price,
        pp.duration_months,
        ppm.payment_id,
        ppm.payment_amount,
        ppm.created_at AS subscription_date
    FROM 
        users u
    LEFT JOIN 
        premium_payments ppm ON u.id = ppm.user_id
    LEFT JOIN 
        premium_pricing pp ON ppm.plan_id = pp.id
    WHERE 
        u.is_premium_member = 1
";

// Add plan filter if specified
if ($planId > 0) {
    $query .= " AND pp.id = :plan_id";
    
    // Get the current plan details
    foreach ($plans as $plan) {
        if ($plan['id'] == $planId) {
            $currentPlan = $plan;
            $filterDescription = 'Subscribers to "' . htmlspecialchars($plan['name']) . '"';
            break;
        }
    }
}

// Add recent filter (last 30 days)
if ($isRecent) {
    $query .= " AND ppm.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
    $filterDescription = 'Recent Subscribers (Last 30 Days)';
}

// Add search filter
if (!empty($searchQuery)) {
    $query .= " AND (u.username LIKE :search OR u.email LIKE :search)";
    $filterDescription = 'Search Results for "' . htmlspecialchars($searchQuery) . '"';
}

$query .= " ORDER BY ppm.created_at DESC";

try {
    $stmt = $pdo->prepare($query);
    
    // Bind parameters
    if ($planId > 0) {
        $stmt->bindParam(':plan_id', $planId, PDO::PARAM_INT);
    }
    
    if (!empty($searchQuery)) {
        $searchParam = '%' . $searchQuery . '%';
        $stmt->bindParam(':search', $searchParam, PDO::PARAM_STR);
    }
    
    $stmt->execute();
    $subscribers = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log('Error retrieving premium subscribers: ' . $e->getMessage());
    $error = 'An error occurred while retrieving subscriber data.';
}

// Include header
include_once ROOT_PATH . '/app/views/admin/partials/header.php';
?>

<!-- Main Content -->
<div class="container-fluid px-4">
    <h1 class="mt-4">Premium Subscriptions</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="premium_management.php">Premium Management</a></li>
        <li class="breadcrumb-item active">Subscriptions</li>
    </ol>
    
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div>
                <i class="fas fa-users me-1"></i>
                <?php echo $filterDescription; ?>
            </div>
            <div>
                <a href="premium_export.php" class="btn btn-sm btn-success">
                    <i class="fas fa-file-export"></i> Export Data
                </a>
            </div>
        </div>
        <div class="card-body">
            <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <!-- Filters -->
            <div class="row mb-4">
                <div class="col-md-8">
                    <form method="GET" action="premium_subscriptions.php" class="d-flex">
                        <select name="plan_id" class="form-select me-2" style="max-width: 250px;">
                            <option value="0">All Plans</option>
                            <?php foreach ($plans as $plan): ?>
                                <option value="<?php echo $plan['id']; ?>" <?php echo ($planId == $plan['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($plan['name']); ?> (₹<?php echo number_format($plan['price'], 2); ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <button type="submit" class="btn btn-primary me-2">Filter</button>
                        <a href="premium_subscriptions.php" class="btn btn-outline-secondary me-2">Reset</a>
                        <a href="premium_subscriptions.php?recent=1" class="btn <?php echo $isRecent ? 'btn-info' : 'btn-outline-info'; ?>">
                            Recent Only
                        </a>
                    </form>
                </div>
                <div class="col-md-4">
                    <form method="GET" action="premium_subscriptions.php" class="d-flex">
                        <input type="text" name="search" class="form-control me-2" placeholder="Search by username or email" value="<?php echo htmlspecialchars($searchQuery); ?>">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search"></i>
                        </button>
                    </form>
                </div>
            </div>
            
            <?php if (empty($subscribers)): ?>
                <div class="alert alert-info">No subscribers found matching your criteria.</div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>User</th>
                                <th>Email</th>
                                <th>Plan</th>
                                <th>Price</th>
                                <th>Subscription Date</th>
                                <th>Expiry Date</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($subscribers as $subscriber): ?>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <?php if (!empty($subscriber['profile_image'])): ?>
                                                <img src="../uploads/profile/<?php echo htmlspecialchars($subscriber['profile_image']); ?>" 
                                                     class="rounded-circle me-2" width="40" height="40" alt="Profile">
                                            <?php else: ?>
                                                <div class="bg-secondary rounded-circle me-2 d-flex align-items-center justify-content-center" 
                                                     style="width: 40px; height: 40px; color: white;">
                                                    <i class="fas fa-user"></i>
                                                </div>
                                            <?php endif; ?>
                                            <div>
                                                <?php echo htmlspecialchars($subscriber['username']); ?>
                                                <div class="small text-muted">ID: <?php echo $subscriber['id']; ?></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td><?php echo htmlspecialchars($subscriber['email']); ?></td>
                                    <td><?php echo htmlspecialchars($subscriber['plan_name']); ?></td>
                                    <td>₹<?php echo number_format($subscriber['price'], 2); ?></td>
                                    <td><?php echo !empty($subscriber['subscription_date']) ? date('M d, Y', strtotime($subscriber['subscription_date'])) : 'N/A'; ?></td>
                                    <td>
                                        <?php
                                            if (empty($subscriber['premium_expiry'])) {
                                                echo '<span class="text-muted">Not set</span>';
                                                $daysLeft = 0;
                                            } else {
                                                $expiryDate = strtotime($subscriber['premium_expiry']);
                                                $now = time();
                                                $daysLeft = round(($expiryDate - $now) / (60 * 60 * 24));
                                                
                                                echo '<span class="fw-bold">' . date('M d, Y', $expiryDate) . '</span>';
                                                
                                                if ($daysLeft > 0) {
                                                echo ' <span class="badge bg-info">' . $daysLeft . ' days left</span>';
                                            } elseif ($daysLeft == 0) {
                                                echo ' <span class="badge bg-warning">Expires today</span>';
                                            } else {
                                                echo ' <span class="badge bg-danger">Expired</span>';
                                            }
                                            }
                                        ?>
                                    </td>
                                    <td>
                                        <?php if ($subscriber['is_premium_member']): ?>
                                            <span class="badge bg-success">Active</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger">Inactive</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <a href="view_user.php?id=<?php echo $subscriber['id']; ?>" class="btn btn-sm btn-info" title="View Details">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <button type="button" class="btn btn-sm btn-warning" 
                                                onclick="extendSubscription(<?php echo $subscriber['id']; ?>, '<?php echo htmlspecialchars($subscriber['username']); ?>')" 
                                                title="Extend Subscription">
                                            <i class="fas fa-calendar-plus"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-danger" 
                                                onclick="cancelSubscription(<?php echo $subscriber['id']; ?>, '<?php echo htmlspecialchars($subscriber['username']); ?>')" 
                                                title="Cancel Subscription">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                
                <div class="mt-3">
                    <p>Total Subscribers: <strong><?php echo count($subscribers); ?></strong></p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Extend Subscription Modal -->
<div class="modal fade" id="extendSubscriptionModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="premium_subscriptions.php" method="post">
                <input type="hidden" name="action" value="extend">
                <input type="hidden" name="user_id" id="extendUserId">
                
                <div class="modal-header">
                    <h5 class="modal-title">Extend Subscription</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>You are extending the premium subscription for <strong id="extendUsername"></strong>.</p>
                    
                    <div class="mb-3">
                        <label for="extension_months" class="form-label">Extension Period (Months)</label>
                        <input type="number" class="form-control" id="extension_months" name="extension_months" min="1" max="36" value="1" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="extension_reason" class="form-label">Reason (Optional)</label>
                        <textarea class="form-control" id="extension_reason" name="extension_reason" rows="2"></textarea>
                    </div>
                    
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> This will extend the user's current premium subscription by the specified number of months.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning">Extend Subscription</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Cancel Subscription Modal -->
<div class="modal fade" id="cancelSubscriptionModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="premium_subscriptions.php" method="post">
                <input type="hidden" name="action" value="cancel">
                <input type="hidden" name="user_id" id="cancelUserId">
                
                <div class="modal-header">
                    <h5 class="modal-title">Cancel Subscription</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle"></i> You are about to cancel the premium subscription for <strong id="cancelUsername"></strong>.
                    </div>
                    
                    <div class="mb-3">
                        <label for="cancellation_reason" class="form-label">Reason for Cancellation</label>
                        <textarea class="form-control" id="cancellation_reason" name="cancellation_reason" rows="3" required></textarea>
                    </div>
                    
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" id="notify_user" name="notify_user" value="1" checked>
                        <label class="form-check-label" for="notify_user">
                            Notify user by email
                        </label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-danger">Confirm Cancellation</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function extendSubscription(userId, username) {
        document.getElementById('extendUserId').value = userId;
        document.getElementById('extendUsername').textContent = username;
        var modal = new bootstrap.Modal(document.getElementById('extendSubscriptionModal'));
        modal.show();
    }
    
    function cancelSubscription(userId, username) {
        document.getElementById('cancelUserId').value = userId;
        document.getElementById('cancelUsername').textContent = username;
        var modal = new bootstrap.Modal(document.getElementById('cancelSubscriptionModal'));
        modal.show();
    }
</script>

<?php
// Handle subscription actions (extend/cancel)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $userId = isset($_POST['user_id']) ? (int)$_POST['user_id'] : 0;
    
    if ($userId > 0) {
        // Get user information
        $user = getUserById($userId);
        
        if ($user) {
            if ($_POST['action'] === 'extend' && isset($_POST['extension_months'])) {
                $extensionMonths = (int)$_POST['extension_months'];
                
                if ($extensionMonths > 0) {
                    try {
                        // Calculate new expiry date
                        $currentExpiry = new DateTime($user['premium_expiry']);
                        $newExpiry = $currentExpiry->modify("+{$extensionMonths} months")->format('Y-m-d H:i:s');
                        
                        // Update user's premium expiry
                        $stmt = $pdo->prepare("UPDATE users SET premium_expiry = ? WHERE id = ?");
                        $stmt->execute([$newExpiry, $userId]);
                        
                        // Log the extension
                        $reason = isset($_POST['extension_reason']) ? $_POST['extension_reason'] : 'Admin extension';
                        $adminId = $_SESSION['user_id'];
                        
                        $stmt = $pdo->prepare("INSERT INTO admin_logs (admin_id, action, entity_id, details) VALUES (?, ?, ?, ?)");
                        $stmt->execute([
                            $adminId,
                            'extend_premium',
                            $userId,
                            json_encode([
                                'extension_months' => $extensionMonths,
                                'old_expiry' => $user['premium_expiry'],
                                'new_expiry' => $newExpiry,
                                'reason' => $reason
                            ])
                        ]);
                        
                        $_SESSION['alert_type'] = 'success';
                        $_SESSION['alert_message'] = "Successfully extended {$user['username']}'s premium subscription by {$extensionMonths} months.";
                    } catch (Exception $e) {
                        error_log('Error extending subscription: ' . $e->getMessage());
                        $_SESSION['alert_type'] = 'danger';
                        $_SESSION['alert_message'] = 'Failed to extend subscription. Please try again.';
                    }
                }
            } elseif ($_POST['action'] === 'cancel') {
                try {
                    // Update user's premium status
                    $stmt = $pdo->prepare("UPDATE users SET is_premium_member = 0 WHERE id = ?");
                    $stmt->execute([$userId]);
                    
                    // Log the cancellation
                    $reason = isset($_POST['cancellation_reason']) ? $_POST['cancellation_reason'] : 'Admin cancellation';
                    $notifyUser = isset($_POST['notify_user']) && $_POST['notify_user'] == '1';
                    $adminId = $_SESSION['user_id'];
                    
                    $stmt = $pdo->prepare("INSERT INTO admin_logs (admin_id, action, entity_id, details) VALUES (?, ?, ?, ?)");
                    $stmt->execute([
                        $adminId,
                        'cancel_premium',
                        $userId,
                        json_encode([
                            'old_expiry' => $user['premium_expiry'],
                            'reason' => $reason,
                            'notify_user' => $notifyUser
                        ])
                    ]);
                    
                    // Notify user if requested
                    if ($notifyUser && !empty($user['email'])) {
                        // Here you'd implement email notification to the user
                        // For now, just log that we would send an email
                        error_log("Would send cancellation email to {$user['email']}");
                    }
                    
                    $_SESSION['alert_type'] = 'success';
                    $_SESSION['alert_message'] = "Successfully cancelled {$user['username']}'s premium subscription.";
                } catch (Exception $e) {
                    error_log('Error cancelling subscription: ' . $e->getMessage());
                    $_SESSION['alert_type'] = 'danger';
                    $_SESSION['alert_message'] = 'Failed to cancel subscription. Please try again.';
                }
            }
        }
    }
    
    // Redirect to avoid form resubmission
    header('Location: premium_subscriptions.php');
    exit;
}

// Include footer
include_once ROOT_PATH . '/app/views/admin/partials/footer.php';
?>

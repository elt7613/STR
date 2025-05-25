<?php
/**
 * Admin Premium Subscribers Management
 * 
 * Allows admins to:
 * - View all premium members
 * - Filter by date range
 * - Search for specific users
 * - Revoke premium status if needed
 */

// Include initialization file
require_once __DIR__ . '/../../includes/init.php';

// Include premium schema
require_once ROOT_PATH . '/app/config/premium_schema.php';

// Redirect if not admin
if (!isAdmin()) {
    header('Location: ../index.php');
    exit;
}

// Initialize variables
$successMessage = null;
$errorMessage = null;
$subscribers = [];
$searchTerm = isset($_GET['search']) ? trim($_GET['search']) : '';
$startDate = isset($_GET['start_date']) ? $_GET['start_date'] : '';
$endDate = isset($_GET['end_date']) ? $_GET['end_date'] : '';
$recent = isset($_GET['recent']) ? (bool)$_GET['recent'] : false;

// Handle premium status revocation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'revoke') {
    $userId = isset($_POST['user_id']) ? (int)$_POST['user_id'] : 0;
    
    if ($userId > 0) {
        try {
            $stmt = $pdo->prepare("UPDATE users SET is_premium_member = 0 WHERE id = ?");
            $success = $stmt->execute([$userId]);
            
            if ($success) {
                $successMessage = 'Premium status has been revoked successfully.';
            } else {
                $errorMessage = 'Failed to revoke premium status.';
            }
        } catch (PDOException $e) {
            error_log('Error revoking premium status: ' . $e->getMessage());
            $errorMessage = 'Database error occurred. Please try again.';
        }
    } else {
        $errorMessage = 'Invalid user ID.';
    }
}

// Build the query based on filters
$query = "SELECT u.id, u.username, u.email, u.created_at AS registered_date, 
                 p.created_at AS premium_date, p.payment_id, p.payment_amount, pp.name AS plan_name,
                 pp.duration_months
          FROM users u
          LEFT JOIN premium_payments p ON u.id = p.user_id AND p.payment_status = 'completed'
          LEFT JOIN premium_pricing pp ON p.plan_id = pp.id
          WHERE u.is_premium_member = 1 ";

$params = [];

if (!empty($searchTerm)) {
    $query .= "AND (u.username LIKE ? OR u.email LIKE ?) ";
    $params[] = "%$searchTerm%";
    $params[] = "%$searchTerm%";
}

if (!empty($startDate)) {
    $query .= "AND p.created_at >= ? ";
    $params[] = $startDate . ' 00:00:00';
}

if (!empty($endDate)) {
    $query .= "AND p.created_at <= ? ";
    $params[] = $endDate . ' 23:59:59';
}

if ($recent) {
    $query .= "AND p.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) ";
}

$query .= "ORDER BY p.created_at DESC";

// Execute query
try {
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $subscribers = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log('Error fetching premium subscribers: ' . $e->getMessage());
    $errorMessage = 'Error retrieving subscriber data. Please try again.';
}

// Page title and active menu
$pageTitle = 'Premium Subscribers';
$activeMenu = 'premium';

// Include admin header
include_once ROOT_PATH . '/app/views/admin/partials/header.php';
?>

<div class="container-fluid px-4">
    <h1 class="mt-4">Premium Subscribers</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="premium_management.php">Premium Management</a></li>
        <li class="breadcrumb-item active">Subscribers</li>
    </ol>
    
    <!-- Success/Error Messages -->
    <?php if (isset($successMessage)): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?php echo htmlspecialchars($successMessage); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
    
    <?php if (isset($errorMessage)): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?php echo htmlspecialchars($errorMessage); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
    
    <!-- Filter and Search -->
    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-filter me-1"></i>
            Filter Subscribers
        </div>
        <div class="card-body">
            <form action="premium_subscribers.php" method="get" class="row g-3">
                <div class="col-md-4">
                    <label for="search" class="form-label">Search</label>
                    <input type="text" class="form-control" id="search" name="search" value="<?php echo htmlspecialchars($searchTerm); ?>" placeholder="Username or email">
                </div>
                <div class="col-md-3">
                    <label for="start_date" class="form-label">From Date</label>
                    <input type="date" class="form-control" id="start_date" name="start_date" value="<?php echo $startDate; ?>">
                </div>
                <div class="col-md-3">
                    <label for="end_date" class="form-label">To Date</label>
                    <input type="date" class="form-control" id="end_date" name="end_date" value="<?php echo $endDate; ?>">
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary me-2">Filter</button>
                    <a href="premium_subscribers.php" class="btn btn-secondary">Reset</a>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Subscribers List -->
    <div class="card mb-4">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <i class="fas fa-users me-1"></i>
                    Premium Members
                    <?php if ($recent): ?>
                        <span class="badge bg-warning ms-2">Last 30 Days</span>
                    <?php endif; ?>
                </div>
                <div>
                    <span class="badge bg-primary"><?php echo count($subscribers); ?> subscribers found</span>
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Username</th>
                            <th>Email</th>
                            <th>Registered</th>
                            <th>Premium Since</th>
                            <th>Plan</th>
                            <th>Amount Paid</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($subscribers)): ?>
                            <tr>
                                <td colspan="8" class="text-center">No premium subscribers found.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($subscribers as $subscriber): ?>
                                <tr>
                                    <td><?php echo $subscriber['id']; ?></td>
                                    <td><?php echo htmlspecialchars($subscriber['username']); ?></td>
                                    <td><?php echo htmlspecialchars($subscriber['email']); ?></td>
                                    <td><?php echo date('Y-m-d', strtotime($subscriber['registered_date'])); ?></td>
                                    <td>
                                        <?php echo $subscriber['premium_date'] ? date('Y-m-d', strtotime($subscriber['premium_date'])) : 'N/A'; ?>
                                    </td>
                                    <td>
                                        <?php if (isset($subscriber['plan_name'])): ?>
                                            <?php echo htmlspecialchars($subscriber['plan_name']); ?>
                                            (<?php echo $subscriber['duration_months']; ?> months)
                                        <?php else: ?>
                                            N/A
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if (isset($subscriber['payment_amount'])): ?>
                                            ₹<?php echo number_format($subscriber['payment_amount'], 2); ?>
                                        <?php else: ?>
                                            N/A
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="../profile.php?id=<?php echo $subscriber['id']; ?>" class="btn btn-info" target="_blank">
                                                <i class="fas fa-user"></i>
                                            </a>
                                            <button type="button" class="btn btn-warning btn-revoke" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#revokeModal" 
                                                    data-id="<?php echo $subscriber['id']; ?>"
                                                    data-name="<?php echo htmlspecialchars($subscriber['username']); ?>">
                                                <i class="fas fa-ban"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Revoke Premium Modal -->
<div class="modal fade" id="revokeModal" tabindex="-1" aria-labelledby="revokeModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="premium_subscribers.php" method="post">
                <div class="modal-header">
                    <h5 class="modal-title" id="revokeModalLabel">Revoke Premium Status</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="action" value="revoke">
                    <input type="hidden" name="user_id" id="revoke_user_id">
                    
                    <p>Are you sure you want to revoke premium status for: <strong id="revoke_username"></strong>?</p>
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i> Warning: This will immediately remove all premium benefits from this user.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Revoke Premium</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Revoke premium modal data
    const revokeButtons = document.querySelectorAll('.btn-revoke');
    revokeButtons.forEach(button => {
        button.addEventListener('click', function() {
            const id = this.getAttribute('data-id');
            const name = this.getAttribute('data-name');
            
            document.getElementById('revoke_user_id').value = id;
            document.getElementById('revoke_username').textContent = name;
        });
    });
});
</script>

<?php
// Include admin footer
include_once ROOT_PATH . '/app/views/admin/partials/footer.php';
?>

<?php
/**
 * Premium Membership Export
 * 
 * Allows admins to export premium membership data for reporting
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
$pageTitle = 'Premium Data Export';
$activeMenu = 'premium';
$currentPage = 'premium_export';

// Get date parameters with defaults
$startDate = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-d', strtotime('-30 days'));
$endDate = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-d');

// Validate dates
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $startDate)) {
    $startDate = date('Y-m-d', strtotime('-30 days'));
}
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $endDate)) {
    $endDate = date('Y-m-d');
}

// Convert dates to database format
$startDateDb = $startDate . ' 00:00:00';
$endDateDb = $endDate . ' 23:59:59';

// Initialize variables
$exportData = [];
$error = null;

// Check if export is requested
$isExport = isset($_GET['export']) && $_GET['export'] == '1';

try {
    // Query to get premium membership data
    $stmt = $pdo->prepare("
        SELECT 
            p.id AS payment_id,
            p.user_id,
            u.username,
            u.email,
            pp.name AS plan_name,
            pp.price,
            pp.duration_months,
            p.payment_id AS transaction_id,
            p.payment_amount,
            p.payment_status,
            p.created_at
        FROM 
            premium_payments p
        JOIN 
            users u ON p.user_id = u.id
        JOIN 
            premium_pricing pp ON p.plan_id = pp.id
        WHERE 
            p.created_at BETWEEN ? AND ?
        ORDER BY 
            p.created_at DESC
    ");
    
    $stmt->execute([$startDateDb, $endDateDb]);
    $exportData = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // If CSV export is requested
    if ($isExport && !empty($exportData)) {
        // Set headers for CSV download
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="premium_data_' . $startDate . '_to_' . $endDate . '.csv"');
        
        // Open output stream
        $output = fopen('php://output', 'w');
        
        // Add CSV headers
        fputcsv($output, array_keys($exportData[0]));
        
        // Add data rows
        foreach ($exportData as $row) {
            fputcsv($output, $row);
        }
        
        // Close output stream
        fclose($output);
        exit;
    }
} catch (PDOException $e) {
    error_log('Premium export error: ' . $e->getMessage());
    $error = 'An error occurred while retrieving premium data. Please try again.';
}

// For regular page view, include header
if (!$isExport) {
    include_once ROOT_PATH . '/app/views/admin/partials/header.php';
}
?>

<!-- Main Content -->
<div class="container-fluid px-4">
    <h1 class="mt-4">Premium Membership Export</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="premium_management.php">Premium Management</a></li>
        <li class="breadcrumb-item active">Premium Export</li>
    </ol>
    
    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-file-export me-1"></i>
            Export Premium Membership Data
        </div>
        <div class="card-body">
            <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <form method="GET" action="premium_export.php" class="mb-4">
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group mb-3">
                            <label for="start_date">Start Date:</label>
                            <input type="date" id="start_date" name="start_date" class="form-control" value="<?php echo $startDate; ?>">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group mb-3">
                            <label for="end_date">End Date:</label>
                            <input type="date" id="end_date" name="end_date" class="form-control" value="<?php echo $endDate; ?>">
                        </div>
                    </div>
                    <div class="col-md-4 d-flex align-items-end">
                        <div class="form-group mb-3">
                            <button type="submit" class="btn btn-primary">Filter</button>
                            <?php if (!empty($exportData)): ?>
                                <a href="premium_export.php?start_date=<?php echo urlencode($startDate); ?>&end_date=<?php echo urlencode($endDate); ?>&export=1" class="btn btn-success ms-2">
                                    <i class="fas fa-file-csv me-1"></i> Export CSV
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </form>
            
            <?php if (empty($exportData)): ?>
                <div class="alert alert-info">No premium membership data found for the selected date range.</div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>User</th>
                                <th>Email</th>
                                <th>Plan</th>
                                <th>Amount</th>
                                <th>Duration</th>
                                <th>Status</th>
                                <th>Transaction ID</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($exportData as $row): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row['payment_id']); ?></td>
                                    <td><?php echo htmlspecialchars($row['username']); ?></td>
                                    <td><?php echo htmlspecialchars($row['email']); ?></td>
                                    <td><?php echo htmlspecialchars($row['plan_name']); ?></td>
                                    <td>₹<?php echo number_format($row['payment_amount'], 2); ?></td>
                                    <td><?php echo htmlspecialchars($row['duration_months']); ?> months</td>
                                    <td>
                                        <span class="badge <?php echo $row['payment_status'] == 'completed' ? 'bg-success' : 'bg-warning'; ?>">
                                            <?php echo ucfirst(htmlspecialchars($row['payment_status'])); ?>
                                        </span>
                                    </td>
                                    <td><?php echo htmlspecialchars($row['transaction_id']); ?></td>
                                    <td><?php echo date('Y-m-d H:i', strtotime($row['created_at'])); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                
                <div class="mt-3">
                    <p>Total Records: <strong><?php echo count($exportData); ?></strong></p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php
// Include footer
include_once ROOT_PATH . '/app/views/admin/partials/footer.php';
?>

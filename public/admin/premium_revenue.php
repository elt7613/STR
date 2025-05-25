<?php
/**
 * Admin Premium Revenue Tracking
 * 
 * Allows admins to:
 * - View revenue statistics from premium memberships
 * - Filter by date range
 * - View revenue by plan
 * - Export reports
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
$errorMessage = null;
$startDate = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-d', strtotime('-30 days'));
$endDate = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-d');
$revenueData = [];
$monthlyRevenue = [];
$planRevenue = [];

// Get revenue data based on date range
try {
    // Total revenue in date range
    $stmt = $pdo->prepare("SELECT COUNT(*) as transaction_count, SUM(payment_amount) as total_revenue 
                          FROM premium_payments 
                          WHERE payment_status = 'completed' 
                          AND created_at BETWEEN ? AND ?");
    $stmt->execute([$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
    $revenueData = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Monthly revenue breakdown
    $stmt = $pdo->prepare("SELECT 
                            DATE_FORMAT(created_at, '%Y-%m') as month,
                            COUNT(*) as subscription_count,
                            SUM(payment_amount) as monthly_revenue
                          FROM premium_payments 
                          WHERE payment_status = 'completed' 
                          AND created_at BETWEEN ? AND ? 
                          GROUP BY DATE_FORMAT(created_at, '%Y-%m')
                          ORDER BY month");
    $stmt->execute([$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
    $monthlyRevenue = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Revenue by plan
    $stmt = $pdo->prepare("SELECT 
                            pp.id as plan_id,
                            pp.name as plan_name,
                            COUNT(p.id) as subscription_count,
                            SUM(p.payment_amount) as plan_revenue
                          FROM premium_payments p
                          JOIN premium_pricing pp ON p.plan_id = pp.id
                          WHERE p.payment_status = 'completed' 
                          AND p.created_at BETWEEN ? AND ? 
                          GROUP BY pp.id
                          ORDER BY plan_revenue DESC");
    $stmt->execute([$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
    $planRevenue = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    error_log('Error fetching revenue data: ' . $e->getMessage());
    $errorMessage = 'Error retrieving revenue data. Please try again.';
}

// Prepare data for charts
$chartLabels = [];
$chartData = [];

foreach ($monthlyRevenue as $month) {
    $chartLabels[] = date('M Y', strtotime($month['month'] . '-01'));
    $chartData[] = $month['monthly_revenue'];
}

$chartLabels = json_encode($chartLabels);
$chartData = json_encode($chartData);

// Plan chart data
$planLabels = [];
$planData = [];
$planColors = ['#4e73df', '#1cc88a', '#36b9cc', '#f6c23e', '#e74a3b', '#858796'];

foreach ($planRevenue as $index => $plan) {
    $planLabels[] = $plan['plan_name'];
    $planData[] = $plan['plan_revenue'];
}

$planLabels = json_encode($planLabels);
$planData = json_encode($planData);

// Page title and active menu
$pageTitle = 'Premium Revenue';
$activeMenu = 'premium';

// Include admin header
include_once ROOT_PATH . '/app/views/admin/partials/header.php';
?>

<div class="container-fluid px-4">
    <h1 class="mt-4">Premium Revenue</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="premium_management.php">Premium Management</a></li>
        <li class="breadcrumb-item active">Revenue</li>
    </ol>
    
    <?php if (isset($errorMessage)): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?php echo htmlspecialchars($errorMessage); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
    
    <!-- Date Range Filter -->
    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-calendar me-1"></i>
            Date Range
        </div>
        <div class="card-body">
            <form action="premium_revenue.php" method="get" class="row g-3">
                <div class="col-md-4">
                    <label for="start_date" class="form-label">From Date</label>
                    <input type="date" class="form-control" id="start_date" name="start_date" value="<?php echo $startDate; ?>">
                </div>
                <div class="col-md-4">
                    <label for="end_date" class="form-label">To Date</label>
                    <input type="date" class="form-control" id="end_date" name="end_date" value="<?php echo $endDate; ?>">
                </div>
                <div class="col-md-4 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary me-2">Update</button>
                    <a href="premium_revenue.php" class="btn btn-secondary">Reset</a>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Revenue Overview Cards -->
    <div class="row">
        <div class="col-xl-3 col-md-6">
            <div class="card bg-primary text-white mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>Total Revenue</div>
                        <div class="h3 mb-0">₹<?php echo number_format($revenueData['total_revenue'] ?? 0, 2); ?></div>
                    </div>
                </div>
                <div class="card-footer d-flex align-items-center justify-content-between">
                    <span class="small text-white">For selected period</span>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card bg-success text-white mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>Transactions</div>
                        <div class="h3 mb-0"><?php echo $revenueData['transaction_count'] ?? 0; ?></div>
                    </div>
                </div>
                <div class="card-footer d-flex align-items-center justify-content-between">
                    <span class="small text-white">For selected period</span>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card bg-info text-white mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>Average Revenue</div>
                        <div class="h3 mb-0">
                            ₹<?php 
                                $avg = ($revenueData['transaction_count'] > 0) 
                                    ? ($revenueData['total_revenue'] / $revenueData['transaction_count']) 
                                    : 0;
                                echo number_format($avg, 2); 
                            ?>
                        </div>
                    </div>
                </div>
                <div class="card-footer d-flex align-items-center justify-content-between">
                    <span class="small text-white">Per transaction</span>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card bg-warning text-white mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>Report</div>
                        <div><i class="fas fa-download fa-2x"></i></div>
                    </div>
                </div>
                <div class="card-footer d-flex align-items-center justify-content-between">
                    <a href="premium_export.php?start_date=<?php echo $startDate; ?>&end_date=<?php echo $endDate; ?>" class="small text-white stretched-link">Export Data</a>
                    <div class="small text-white"><i class="fas fa-angle-right"></i></div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Monthly Revenue Chart -->
    <div class="row">
        <div class="col-xl-8">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-chart-bar me-1"></i>
                    Monthly Revenue
                </div>
                <div class="card-body">
                    <canvas id="monthlyRevenueChart" width="100%" height="40"></canvas>
                </div>
            </div>
        </div>
        <div class="col-xl-4">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-chart-pie me-1"></i>
                    Revenue by Plan
                </div>
                <div class="card-body">
                    <canvas id="planRevenueChart" width="100%" height="50"></canvas>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Revenue by Plan Table -->
    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-table me-1"></i>
            Revenue by Plan
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>Plan Name</th>
                            <th>Subscriptions</th>
                            <th>Revenue</th>
                            <th>Average Revenue</th>
                            <th>% of Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($planRevenue)): ?>
                            <tr>
                                <td colspan="5" class="text-center">No revenue data available for the selected period.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($planRevenue as $plan): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($plan['plan_name']); ?></td>
                                    <td><?php echo $plan['subscription_count']; ?></td>
                                    <td>₹<?php echo number_format($plan['plan_revenue'], 2); ?></td>
                                    <td>₹<?php echo number_format($plan['plan_revenue'] / $plan['subscription_count'], 2); ?></td>
                                    <td>
                                        <?php 
                                            $percentage = ($revenueData['total_revenue'] > 0) 
                                                ? ($plan['plan_revenue'] / $revenueData['total_revenue'] * 100) 
                                                : 0;
                                            echo number_format($percentage, 2) . '%'; 
                                        ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <!-- Monthly Revenue Table -->
    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-table me-1"></i>
            Monthly Revenue Breakdown
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>Month</th>
                            <th>Subscriptions</th>
                            <th>Revenue</th>
                            <th>Average Revenue</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($monthlyRevenue)): ?>
                            <tr>
                                <td colspan="4" class="text-center">No revenue data available for the selected period.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($monthlyRevenue as $month): ?>
                                <tr>
                                    <td><?php echo date('F Y', strtotime($month['month'] . '-01')); ?></td>
                                    <td><?php echo $month['subscription_count']; ?></td>
                                    <td>₹<?php echo number_format($month['monthly_revenue'], 2); ?></td>
                                    <td>₹<?php echo number_format($month['monthly_revenue'] / $month['subscription_count'], 2); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Monthly Revenue Chart
    const monthlyRevenueCtx = document.getElementById('monthlyRevenueChart');
    new Chart(monthlyRevenueCtx, {
        type: 'bar',
        data: {
            labels: <?php echo $chartLabels; ?>,
            datasets: [{
                label: 'Revenue (₹)',
                backgroundColor: 'rgba(78, 115, 223, 0.7)',
                borderColor: 'rgba(78, 115, 223, 1)',
                borderWidth: 1,
                data: <?php echo $chartData; ?>
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return '₹' + value.toLocaleString();
                        }
                    }
                }
            },
            plugins: {
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return 'Revenue: ₹' + context.raw.toLocaleString();
                        }
                    }
                }
            }
        }
    });
    
    // Plan Revenue Chart
    const planRevenueCtx = document.getElementById('planRevenueChart');
    new Chart(planRevenueCtx, {
        type: 'pie',
        data: {
            labels: <?php echo $planLabels; ?>,
            datasets: [{
                backgroundColor: [
                    '#4e73df', '#1cc88a', '#36b9cc', '#f6c23e', '#e74a3b', '#858796'
                ],
                data: <?php echo $planData; ?>
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return context.label + ': ₹' + context.raw.toLocaleString();
                        }
                    }
                }
            }
        }
    });
});
</script>

<?php
// Include admin footer
include_once ROOT_PATH . '/app/views/admin/partials/footer.php';
?>

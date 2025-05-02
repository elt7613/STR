<?php
/**
 * Admin - Run Migrations
 */

// Include initialization script
require_once __DIR__ . '/../../includes/init.php';

// Check if user is admin
if (!isAdmin()) {
    // Redirect to home page or show access denied
    header('Location: /index.php');
    exit;
}

$message = '';
$error = '';

// Process migration request
if (isset($_GET['run']) && $_GET['run'] === 'category_brand_relation') {
    try {
        // Include and run the migration script
        include ROOT_PATH . '/app/config/migrations/update_categories_add_brand_relation.php';
        $message = 'Migration completed successfully. Please check the server logs for details.';
    } catch (Exception $e) {
        $error = 'Migration failed: ' . $e->getMessage();
    }
}

// HTML output
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Run Migrations - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>
    <div class="container mt-5">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h1>Database Migrations</h1>
                        <a href="../admin/dashboard.php" class="btn btn-primary">Back to Dashboard</a>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($message)): ?>
                            <div class="alert alert-success"><?php echo $message; ?></div>
                        <?php endif; ?>
                        
                        <?php if (!empty($error)): ?>
                            <div class="alert alert-danger"><?php echo $error; ?></div>
                        <?php endif; ?>
                        
                        <h2>Available Migrations</h2>
                        <div class="list-group">
                            <div class="list-group-item">
                                <div class="d-flex w-100 justify-content-between">
                                    <h5 class="mb-1">Category-Brand Relationship</h5>
                                    <a href="?run=category_brand_relation" class="btn btn-primary btn-sm" onclick="return confirm('Are you sure you want to run this migration?')">
                                        Run Migration
                                    </a>
                                </div>
                                <p class="mb-1">Updates the database to associate categories with brands. This will set all existing categories to belong to the first brand in the system.</p>
                                <small class="text-muted">Migration file: update_categories_add_brand_relation.php</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 
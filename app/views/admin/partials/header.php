<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle ?? 'Admin Panel'; ?> - STR Admin</title>
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>
    <div class="admin-container">
        <div class="admin-sidebar">
            <div class="admin-logo">
                <i class="fas fa-car-side"></i> STR Admin
            </div>
            <div class="admin-user-info">
                <div class="admin-user-avatar">
                    <i class="fas fa-user-circle"></i>
                </div>
                <div class="admin-user-details">
                    <div class="admin-user-name"><?php echo $_SESSION['username'] ?? 'Admin'; ?></div>
                    <div class="admin-user-role">Administrator</div>
                </div>
            </div>
            <nav class="admin-nav">
                <?php 
                // Get the current script name from the URL
                $currentScript = basename($_SERVER['SCRIPT_NAME']);
                ?>
                <a href="dashboard.php" class="<?php echo $currentScript == 'dashboard.php' ? 'active' : ''; ?>">
                    <i class="fas fa-tachometer-alt"></i> Dashboard
                </a>
                <a href="manage_orders.php" class="<?php echo $currentScript == 'manage_orders.php' ? 'active' : ''; ?>">
                    <i class="fas fa-shopping-cart"></i> Orders
                </a>
                <a href="manage_vehicles.php" class="<?php echo $currentScript == 'manage_vehicles.php' ? 'active' : ''; ?>">
                    <i class="fas fa-car"></i> Vehicles
                </a>
                <a href="manage_brands.php" class="<?php echo $currentScript == 'manage_brands.php' ? 'active' : ''; ?>">
                    <i class="fas fa-tags"></i> Brands
                </a>
                <a href="manage_categories.php" class="<?php echo $currentScript == 'manage_categories.php' ? 'active' : ''; ?>">
                    <i class="fas fa-list"></i> Categories
                </a>
                <a href="manage_products.php" class="<?php echo $currentScript == 'manage_products.php' ? 'active' : ''; ?>">
                    <i class="fas fa-box"></i> Products
                </a>
                <div class="nav-divider"></div>
                <a href="../vehicle.php" target="_blank">
                    <i class="fas fa-external-link-alt"></i> View Frontend
                </a>
                <a href="../logout.php" class="logout-link">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </nav>
        </div>
        <div class="admin-content">
            <div class="admin-topbar">
                <div class="toggle-sidebar">
                    <i class="fas fa-bars"></i>
                </div>
                <div class="admin-topbar-right">
                    <div class="admin-time">
                        <i class="far fa-clock"></i> <?php echo date('d M Y, h:i A'); ?>
                    </div>
                </div>
            </div>
            
            <?php if (!empty($error)): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
                    <button class="close-alert"><i class="fas fa-times"></i></button>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($success)): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> <?php echo $success; ?>
                    <button class="close-alert"><i class="fas fa-times"></i></button>
                </div>
            <?php endif; ?> 
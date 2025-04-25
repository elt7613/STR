<?php
/**
 * Admin Dashboard Page
 */

// Include initialization script
require_once __DIR__ . '/../../includes/init.php';

// Check if user is admin (you'll need to add this function)
if (!isAdmin()) {
    // Redirect to home page or show access denied
    header('Location: ../index.php');
    exit;
}

// Handle messages from redirect
$success = isset($_GET['success']) ? $_GET['success'] : '';
$error = isset($_GET['error']) ? $_GET['error'] : '';

// Get all users
$users = getAllUsers();

// Get all vehicle submissions
$vehicleSubmissions = getAllVehicleSubmissions();

// Get brands count
$query = "SELECT COUNT(*) as count FROM brands";
$stmt = $pdo->prepare($query);
$stmt->execute();
$brandCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

// Get products count
$query = "SELECT COUNT(*) as count FROM products";
$stmt = $pdo->prepare($query);
$stmt->execute();
$productCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

// Get vehicle data counts
$query = "SELECT 
    (SELECT COUNT(*) FROM vehicle_makes) as makeCount,
    (SELECT COUNT(*) FROM vehicle_models) as modelCount,
    (SELECT COUNT(*) FROM vehicle_series) as seriesCount";
$stmt = $pdo->prepare($query);
$stmt->execute();
$vehicleCounts = $stmt->fetch(PDO::FETCH_ASSOC);

$makeCount = $vehicleCounts['makeCount'];
$modelCount = $vehicleCounts['modelCount'];
$seriesCount = $vehicleCounts['seriesCount'];

// Include admin dashboard view
require_once ROOT_PATH . '/app/views/admin/dashboard.php';
?> 
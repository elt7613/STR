<?php
/**
 * API endpoint to get devices for a specific series
 */

// Include initialization script
require_once __DIR__ . '/../../includes/init.php';

// Set headers for JSON response
header('Content-Type: application/json');

// Get series ID from request
$seriesId = isset($_GET['series_id']) ? intval($_GET['series_id']) : 0;

if ($seriesId <= 0) {
    echo json_encode([]);
    exit;
}

// Get devices for the specified series
$devices = getVehicleDevicesBySeries($seriesId);

// Return devices as JSON
echo json_encode($devices);
?> 
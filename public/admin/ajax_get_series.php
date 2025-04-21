<?php
/**
 * AJAX handler for fetching vehicle series by model ID
 */

// Include initialization script
require_once __DIR__ . '/../../includes/init.php';

// Check if user is admin
if (!isAdmin()) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Access denied']);
    exit;
}

// Get model ID from request
$modelId = isset($_GET['model_id']) ? intval($_GET['model_id']) : 0;

if (empty($modelId)) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Model ID is required']);
    exit;
}

// Get series for the specified model
$series = getVehicleSeriesByModel($modelId);

// Return JSON response
header('Content-Type: application/json');
echo json_encode(['success' => true, 'series' => $series]);
exit;
?> 
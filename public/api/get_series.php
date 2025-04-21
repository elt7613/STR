<?php
// Include initialization script
require_once __DIR__ . '/../../includes/init.php';

// Set content type to JSON
header('Content-Type: application/json');

// Check if model ID is provided
if (!isset($_GET['model_id']) || empty($_GET['model_id'])) {
    echo json_encode(['error' => 'Model ID is required']);
    exit;
}

$modelId = (int)$_GET['model_id'];
$series = getVehicleSeriesByModel($modelId);

// Return empty array if no series found instead of false
if (empty($series)) {
    echo json_encode([]);
    exit;
}

echo json_encode($series);
?> 
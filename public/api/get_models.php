<?php
// Include initialization script
require_once __DIR__ . '/../../includes/init.php';

// Set content type to JSON
header('Content-Type: application/json');

// Check if make ID is provided
if (!isset($_GET['make_id']) || empty($_GET['make_id'])) {
    echo json_encode(['error' => 'Make ID is required']);
    exit;
}

$makeId = (int)$_GET['make_id'];
$models = getVehicleModelsByMake($makeId);

// Return empty array if no models found instead of false
if (empty($models)) {
    echo json_encode([]);
    exit;
}

echo json_encode($models);
?> 
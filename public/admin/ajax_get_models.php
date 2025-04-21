<?php
/**
 * AJAX handler for fetching vehicle models by make ID
 */

// Include initialization script
require_once __DIR__ . '/../../includes/init.php';

// Check if user is admin
if (!isAdmin()) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Access denied']);
    exit;
}

// Get make ID from request
$makeId = isset($_GET['make_id']) ? intval($_GET['make_id']) : 0;

if (empty($makeId)) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Make ID is required']);
    exit;
}

// Get models for the specified make
$models = getVehicleModelsByMake($makeId);

// Return JSON response
header('Content-Type: application/json');
echo json_encode(['success' => true, 'models' => $models]);
exit;
?> 
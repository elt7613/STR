<?php
/**
 * Diagnostic script to debug product request issues
 */

// Start output buffering
ob_start();

// Basic response function
function sendResponse($data) {
    // Clean buffer
    if (ob_get_length()) ob_clean();
    
    // Set headers
    header('Content-Type: application/json');
    header('Cache-Control: no-cache, must-revalidate');
    
    // Send response
    echo json_encode($data);
    exit;
}

// Create a very simple response to test basic functionality
$response = [
    'success' => true,
    'message' => 'Debug response is working',
    'timestamp' => time(),
    'test_mode' => true
];

// Step 1: Test basic JSON response
sendResponse([
    'step' => 1,
    'message' => 'Basic JSON response is working'
]);
?> 
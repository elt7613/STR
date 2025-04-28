<?php
/**
 * Debug request endpoint for AJAX debugging
 * This file returns a simple JSON response to test if AJAX requests are working
 */

// Set headers for JSON response
header('Content-Type: application/json');
header('Cache-Control: no-cache, must-revalidate');

// Create a simple response
$response = [
    'success' => true,
    'message' => 'Debug request successful',
    'timestamp' => date('Y-m-d H:i:s')
];

// Return JSON response
echo json_encode($response);
exit;
?> 
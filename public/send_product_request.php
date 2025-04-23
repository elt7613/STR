<?php
/**
 * Handle product request and send email to admin - With detailed error logging
 */

// Start output buffering to prevent any unwanted output
ob_start();

// Turn on error reporting for debugging but don't display them
ini_set('display_errors', 0);
error_reporting(E_ALL);

// Include initialization file
require_once __DIR__ . '/../includes/init.php';

// Include email configuration
require_once ROOT_PATH . '/app/config/email.php';

// Function to send JSON response and exit
function sendJsonResponse($success, $message, $data = []) {
    // Clean any previous output
    if (ob_get_length()) ob_clean();
    
    // Set headers
    header('Content-Type: application/json');
    header('Cache-Control: no-cache, must-revalidate');
    
    // Create response array
    $response = [
        'success' => (bool)$success,
        'message' => (string)$message
    ];
    
    // Add any additional data
    if (!empty($data)) {
        $response['data'] = $data;
    }
    
    // Output JSON and exit
    echo json_encode($response);
    exit;
}

// Log function
function logDebug($message) {
    $logFile = __DIR__ . '/../logs/email_debug.log';
    $dir = dirname($logFile);
    
    // Create logs directory if it doesn't exist
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
    
    // Log with timestamp
    $logMessage = "[" . date('Y-m-d H:i:s') . "] " . $message . "\n";
    file_put_contents($logFile, $logMessage, FILE_APPEND);
    error_log($message); // Also log to PHP error log
}

logDebug("Script started - Product Request");

// Check if user is logged in
if (!isLoggedIn()) {
    logDebug("User not logged in");
    sendJsonResponse(false, 'You must be logged in to request a product');
}

// Get product ID from query parameters
$productId = isset($_GET['product_id']) ? intval($_GET['product_id']) : 0;

// Validate product ID
if ($productId <= 0) {
    logDebug("Invalid product ID: $productId");
    sendJsonResponse(false, 'Invalid product ID');
}

// Get product details
$product = getProductById($productId);
if (!$product) {
    logDebug("Product not found: $productId");
    sendJsonResponse(false, 'Product not found');
}

// Get user details
$userId = $_SESSION['user_id'];
$user = getUserById($userId);
if (!$user) {
    logDebug("User not found: $userId");
    sendJsonResponse(false, 'User not found');
}

logDebug("Sending email for product request - Product ID: $productId, User ID: $userId");

// Prepare email body
$htmlBody = "
<h2>New Product Request</h2>
<p>A user has requested a product from your store.</p>

<h3>Product Details:</h3>
<ul>
    <li><strong>Product:</strong> " . htmlspecialchars($product['title']) . "</li>
    <li><strong>Price:</strong> $" . number_format($product['amount'], 2) . "</li>
    <li><strong>Brand:</strong> " . htmlspecialchars($product['brand_name']) . "</li>";

// Add vehicle compatibility information if available
if (!empty($product['make_name']) || !empty($product['model_name']) || !empty($product['series_name'])) {
    $htmlBody .= "
    <li><strong>Vehicle Compatibility:</strong>
        <ul>";
    
    if (!empty($product['make_name'])) {
        $htmlBody .= "
            <li>Make: " . htmlspecialchars($product['make_name']) . "</li>";
    }
    
    if (!empty($product['model_name'])) {
        $htmlBody .= "
            <li>Model: " . htmlspecialchars($product['model_name']) . "</li>";
    }
    
    if (!empty($product['series_name'])) {
        $htmlBody .= "
            <li>Series: " . htmlspecialchars($product['series_name']) . "</li>";
    }
    
    $htmlBody .= "
        </ul>
    </li>";
}

$htmlBody .= "
</ul>

<h3>User Details:</h3>
<ul>
    <li><strong>Name:</strong> " . htmlspecialchars($user['username']) . "</li>
    <li><strong>Email:</strong> " . htmlspecialchars($user['email']) . "</li>
    <li><strong>Phone:</strong> " . htmlspecialchars($user['phone']) . "</li>
</ul>

<p>Please contact the customer to process this request.</p>
";

$plainTextBody = "
New Product Request

A user has requested a product from your store.

Product Details:
- Product: " . $product['title'] . "
- Price: $" . number_format($product['amount'], 2) . "
- Brand: " . $product['brand_name'];

// Add vehicle compatibility information to plain text version
if (!empty($product['make_name']) || !empty($product['model_name']) || !empty($product['series_name'])) {
    $plainTextBody .= "
- Vehicle Compatibility:";
    
    if (!empty($product['make_name'])) {
        $plainTextBody .= "
  * Make: " . $product['make_name'];
    }
    
    if (!empty($product['model_name'])) {
        $plainTextBody .= "
  * Model: " . $product['model_name'];
    }
    
    if (!empty($product['series_name'])) {
        $plainTextBody .= "
  * Series: " . $product['series_name'];
    }
}

$plainTextBody .= "

User Details:
- Name: " . $user['username'] . "
- Email: " . $user['email'] . "
- Phone: " . $user['phone'] . "

Please contact the customer to process this request.
";

// Try to send email
try {
    logDebug("About to call sendEmail function");
    $emailResult = sendEmail(
        "New Product Request: " . $product['title'],
        $htmlBody,
        $plainTextBody
    );
    
    logDebug("Email function returned: " . $emailResult);
    
    // Check if email was sent successfully (assuming sendEmail returns success message)
    if (strpos($emailResult, 'successfully') !== false) {
        logDebug("Email sent successfully");
        sendJsonResponse(true, 'Your request has been sent successfully', [
            'product' => $product['title'],
            'email_status' => $emailResult
        ]);
    } else {
        logDebug("Email sending failed: " . $emailResult);
        sendJsonResponse(false, 'Failed to send product request email', [
            'error' => $emailResult
        ]);
    }
} catch (Exception $e) {
    $errorMessage = "Exception caught while sending email: " . $e->getMessage();
    logDebug($errorMessage);
    sendJsonResponse(false, 'Error sending product request', [
        'error' => $errorMessage
    ]);
}
?> 
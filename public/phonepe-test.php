<?php
/**
 * PhonePe Integration Test File
 * This is a standalone test to check if PhonePe payment works with the current credentials
 */

// PhonePe API Keys (same as in config file)
define('PHONEPE_CLIENT_ID', 'TEST-M22LWDYST4N3E_25051');
define('PHONEPE_CLIENT_SECRET', 'YzZkNzQzYzEtOGU0ZS00M2RhLWI3MjktN2UwNmY0YzJjOTdh');

// API Endpoints
define('PHONEPE_API_UAT_ENDPOINT', 'https://api-preprod.phonepe.com/apis/pg-sandbox');
define('PHONEPE_API_PROD_ENDPOINT', 'https://api.phonepe.com/apis/hermes');

// Use the UAT/sandbox endpoint for test credentials
define('PHONEPE_API_ENDPOINT', strpos(PHONEPE_CLIENT_ID, 'TEST-') === 0 ? PHONEPE_API_UAT_ENDPOINT : PHONEPE_API_PROD_ENDPOINT);

// Simple HTML header
echo "<html><head><title>PhonePe Integration Test</title>";
echo "<style>body{font-family:Arial,sans-serif;margin:20px;line-height:1.6}
.container{max-width:800px;margin:0 auto;padding:20px;border:1px solid #ddd;border-radius:5px}
.success{color:green;font-weight:bold}
.error{color:red;font-weight:bold}
.code{background:#f5f5f5;padding:10px;border-radius:3px;font-family:monospace;overflow-x:auto}
</style>";
echo "</head><body><div class='container'>";
echo "<h1>PhonePe Payment Integration Test</h1>";

// Create a unique merchant transaction ID for this test
$merchantTransactionId = 'TEST_' . time();
$amountInRupees = 1.00; // Minimum test amount

// Build redirect URL (the URL that PhonePe will redirect to after payment)
$protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? "https" : "http";
$host = $_SERVER['HTTP_HOST'];
$redirectUrl = "$protocol://$host/phonepe-callback.php?test=1";

echo "<h2>Test Configuration</h2>";
echo "<ul>";
echo "<li><strong>Merchant ID:</strong> " . PHONEPE_CLIENT_ID . "</li>";
echo "<li><strong>Transaction ID:</strong> " . $merchantTransactionId . "</li>";
echo "<li><strong>Amount:</strong> ₹" . $amountInRupees . "</li>";
echo "<li><strong>Redirect URL:</strong> " . $redirectUrl . "</li>";
echo "<li><strong>API Endpoint:</strong> " . PHONEPE_API_ENDPOINT . "</li>";
echo "</ul>";

// Function to display debug information
function displayDebug($title, $data) {
    echo "<h3>$title</h3>";
    echo "<div class='code'>";
    if (is_array($data) || is_object($data)) {
        echo "<pre>" . htmlspecialchars(json_encode($data, JSON_PRETTY_PRINT)) . "</pre>";
    } else {
        echo "<pre>" . htmlspecialchars($data) . "</pre>";
    }
    echo "</div>";
}

try {
    echo "<h2>Creating PhonePe Payment</h2>";
    
    // Convert amount to paisa (PhonePe expects amount in smallest currency unit)
    $amountInPaise = round($amountInRupees * 100);
    
    // Build payment request data
    $paymentData = [
        "merchantId" => PHONEPE_CLIENT_ID,
        "merchantTransactionId" => $merchantTransactionId,
        "merchantUserId" => "MUID_" . time(),
        "amount" => $amountInPaise,
        "redirectUrl" => $redirectUrl,
        "redirectMode" => "REDIRECT",
        "callbackUrl" => $redirectUrl,
        "mobileNumber" => "",
        "paymentInstrument" => [
            "type" => "PAY_PAGE"
        ]
    ];
    
    // Add a message
    $paymentData["merchantOrderId"] = $merchantTransactionId;
    $paymentData["message"] = "Test payment at " . date('Y-m-d H:i:s');
    
    // Debug - show request data
    displayDebug("Payment Request Data", $paymentData);
    
    // Base64 encode the payment data
    $base64Data = base64_encode(json_encode($paymentData));
    
    // Calculate X-VERIFY header (SHA256 of base64 payload + "/" + salt)
    $string = $base64Data . "/" . PHONEPE_CLIENT_SECRET;
    $checksum = hash('sha256', $string);
    
    // Debug - show checksum details
    displayDebug("Base64 Encoded Payload", $base64Data);
    displayDebug("Checksum String", $string);
    displayDebug("Generated Checksum", $checksum);
    
    // Prepare request body - has to be a JSON with 'request' field containing base64 encoded data
    $requestBody = json_encode([
        'request' => $base64Data
    ]);
    
    // API endpoint for payment initiation
    $endpoint = PHONEPE_API_ENDPOINT . "/pg/v1/pay";
    
    // Debug - show request details
    displayDebug("API Endpoint", $endpoint);
    displayDebug("Request Body", $requestBody);
    displayDebug("Request Headers", [
        'Content-Type' => 'application/json',
        'X-CLIENT-ID' => PHONEPE_CLIENT_ID,
        'X-VERIFY' => $checksum
    ]);
    
    // Send request to PhonePe API
    $ch = curl_init($endpoint);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $requestBody);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'X-CLIENT-ID: ' . PHONEPE_CLIENT_ID,
        'X-VERIFY: ' . $checksum
    ]);
    
    // Execute the request
    $response = curl_exec($ch);
    $error = curl_error($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $requestInfo = curl_getinfo($ch);
    curl_close($ch);
    
    // Debug - show response details
    displayDebug("HTTP Status Code", $httpCode);
    displayDebug("Raw Response", $response);
    
    if ($error) {
        echo "<p class='error'>CURL Error: " . htmlspecialchars($error) . "</p>";
    } else {
        // Parse the response
        $responseData = json_decode($response, true);
        displayDebug("Parsed Response", $responseData);
        
        if ($httpCode == 200 && isset($responseData['success']) && $responseData['success'] === true) {
            if (isset($responseData['data']['instrumentResponse']['redirectInfo']['url'])) {
                $redirectUrl = $responseData['data']['instrumentResponse']['redirectInfo']['url'];
                
                echo "<p class='success'>Payment request successful! You can now proceed to the payment page.</p>";
                echo "<p><a href='" . htmlspecialchars($redirectUrl) . "' target='_blank'>Click here to go to PhonePe payment page</a></p>";
                
                // Add auto-redirect option
                echo "<p>You will be automatically redirected in 5 seconds...</p>";
                echo "<script>setTimeout(function() { window.location.href = '" . htmlspecialchars($redirectUrl) . "'; }, 5000);</script>";
            } else {
                echo "<p class='error'>Payment initiated but no redirect URL was provided</p>";
            }
        } else {
            // Handle specific PhonePe error codes
            $errorCode = $responseData['code'] ?? '';
            $errorMessage = $responseData['message'] ?? 'Unknown error';
            
            echo "<p class='error'>PhonePe Error: " . htmlspecialchars($errorMessage) . "</p>";
            echo "<p class='error'>Error Code: " . htmlspecialchars($errorCode) . "</p>";
            
            if (strpos($errorMessage, 'Key not found') !== false) {
                echo "<p><strong>Possible causes:</strong></p>";
                echo "<ul>";
                echo "<li>The merchant ID " . PHONEPE_CLIENT_ID . " may not be active in PhonePe's system</li>";
                echo "<li>Your test account might need to be activated in the PhonePe sandbox environment</li>";
                echo "<li>The salt/secret key might be incorrect</li>";
                echo "</ul>";
                
                echo "<p><strong>Recommended actions:</strong></p>";
                echo "<ul>";
                echo "<li>Contact PhonePe support to verify your merchant credentials</li>";
                echo "<li>Check if your account needs to be activated</li>";
                echo "<li>Verify that you're using the correct credentials provided by PhonePe</li>";
                echo "</ul>";
            }
        }
    }
    
} catch (Exception $e) {
    echo "<p class='error'>Error: " . htmlspecialchars($e->getMessage()) . "</p>";
}

echo "</div></body></html>";
?>

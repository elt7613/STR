<?php
/**
 * PhonePe Direct API Integration
 * A lightweight implementation without relying on the PhonePe SDK
 */

// PhonePe API Keys (Replace with your actual keys)
define('PHONEPE_CLIENT_ID', 'TEST-M22LWDYST4N3E_25051');
define('PHONEPE_CLIENT_SECRET', 'YzZkNzQzYzEtOGU0ZS00M2RhLWI3MjktN2UwNmY0YzJjOTdh');
define('PHONEPE_CLIENT_VERSION', 2);

// API Endpoints
define('PHONEPE_API_UAT_ENDPOINT', 'https://api-preprod.phonepe.com/apis/pg-sandbox');  // Test Environment
define('PHONEPE_API_PROD_ENDPOINT', 'https://api.phonepe.com/apis/hermes');          // Production Environment

// Use the correct endpoint based on your client ID (TEST- prefix indicates test environment)
define('PHONEPE_API_ENDPOINT', strpos(PHONEPE_CLIENT_ID, 'TEST-') === 0 ? PHONEPE_API_UAT_ENDPOINT : PHONEPE_API_PROD_ENDPOINT);

// Optional callback authentication credentials - not required for basic redirect flow
define('PHONEPE_CALLBACK_USERNAME', '');
define('PHONEPE_CALLBACK_PASSWORD', '');

/**
 * Generate SHA256 checksum for PhonePe API
 * 
 * @param array $data The request data to hash
 * @param string $salt The salt (your client secret)
 * @return string The generated checksum
 */
function generatePhonePeChecksum($data, $salt) {
    // Convert data to JSON string
    $dataJson = json_encode($data);
    
    // Calculate checksum: SHA256(JSON + "/" + salt) in base64
    $string = $dataJson . "/" . $salt;
    $checksum = hash('sha256', $string);
    
    return $checksum;
}

/**
 * Create a PhonePe payment request using direct API integration
 * 
 * @param string $orderNumber Order number (merchantOrderId)
 * @param float $amount Amount in INR
 * @param string $redirectUrl URL to redirect after payment
 * @param string $message Optional message for payment
 * @return array PhonePe payment data including redirect URL
 */
function createPhonePePayment($orderNumber, $amount, $redirectUrl, $message = null) {
    try {
        // Convert amount to paisa (PhonePe expects amount in smallest currency unit)
        $amountInPaise = round($amount * 100);
        
        // Ensure amount is at least 100 paise (₹1)
        if ($amountInPaise < 100) {
            $amountInPaise = 100;
        }
        
        error_log("Creating PhonePe payment: " . $orderNumber . ", Amount: " . $amountInPaise . " paise");
        
        // Build payment request data
        $paymentData = [
            "merchantId" => PHONEPE_CLIENT_ID,
            "merchantTransactionId" => $orderNumber,
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
        
        // Add message if provided
        if ($message) {
            $paymentData["merchantOrderId"] = $orderNumber;
            $paymentData["message"] = $message;
        }

        // Base64 encode the payment data
        $base64Data = base64_encode(json_encode($paymentData));
        
        // Calculate X-VERIFY header (SHA256 of base64 payload + "/" + salt)
        $string = $base64Data . "/" . PHONEPE_CLIENT_SECRET;
        $checksum = hash('sha256', $string);

        // API endpoint for payment initiation
        $endpoint = PHONEPE_API_ENDPOINT . "/pg/v1/pay";
        
        // Prepare request body - has to be a JSON with 'request' field containing base64 encoded data
        $requestBody = json_encode([
            'request' => $base64Data
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

        // Detailed logging before making the request
        error_log("PhonePe API request URL: " . $endpoint);
        error_log("PhonePe API request headers: X-CLIENT-ID=" . PHONEPE_CLIENT_ID . ", X-VERIFY=" . $checksum);
        error_log("PhonePe API request body: " . $requestBody);
        
        $response = curl_exec($ch);
        $error = curl_error($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $requestInfo = curl_getinfo($ch);
        curl_close($ch);

        error_log("PhonePe API HTTP code: " . $httpCode);
        error_log("PhonePe API raw response: " . $response);
        
        if ($error) {
            error_log("PhonePe curl error: " . $error);
            return [
                'success' => false,
                'error' => "Connection error: " . $error,
                'debug_info' => [
                    'http_code' => $httpCode,
                    'request_info' => $requestInfo,
                    'raw_response' => $response
                ]
            ];
        }

        $responseData = json_decode($response, true);
        error_log("PhonePe API parsed response: " . json_encode($responseData));

        if ($httpCode == 200 && isset($responseData['success']) && $responseData['success'] === true) {
            if (isset($responseData['data']['instrumentResponse']['redirectInfo']['url'])) {
                return [
                    'success' => true,
                    'order_id' => $responseData['data']['merchantTransactionId'] ?? $orderNumber,
                    'state' => 'PENDING',
                    'redirect_url' => $responseData['data']['instrumentResponse']['redirectInfo']['url']
                ];
            } else {
                error_log("PhonePe payment missing redirect URL: " . json_encode($responseData));
                return [
                    'success' => false,
                    'error' => 'Payment initiated but no redirect URL was provided',
                    'debug_info' => $responseData
                ];
            }
        } else {
            // Handle specific PhonePe error codes
            $errorCode = $responseData['code'] ?? '';
            $errorMessage = $responseData['message'] ?? 'Unknown error';
            
            // Map error codes to more helpful messages
            switch ($errorCode) {
                case 'PAYMENT_ERROR_INVALID_PARAMETERS':
                    $errorMessage = 'Invalid payment parameters: ' . $errorMessage;
                    break;
                case 'MERCHANT_VALIDATION_ERROR':
                    $errorMessage = 'Merchant validation failed: ' . $errorMessage . '. Please check your PhonePe merchant credentials.'; 
                    break;
                case 'INTERNAL_SERVER_ERROR':
                    $errorMessage = 'PhonePe server error: ' . $errorMessage . '. Please try again later.';
                    break;
                // Add more specific error handling as needed
                default:
                    if (strpos($errorMessage, 'Key not found') !== false) {
                        $errorMessage = 'Merchant key validation failed. Please check if your TEST-M22LWDYST4N3E_25051 merchant ID is valid and active.';
                    }
                    break;
            }
            
            error_log("PhonePe payment creation failed: " . $errorMessage . ", Code: " . $errorCode);
            return [
                'success' => false,
                'error' => $errorMessage,
                'error_code' => $errorCode,
                'debug_info' => $responseData
            ];
        }
    } catch (\Exception $e) {
        error_log('PhonePe payment creation failed: ' . $e->getMessage());
        error_log('PhonePe payment creation failed trace: ' . $e->getTraceAsString());
        return [
            'success' => false,
            'error' => $e->getMessage()
        ];
    }
}

/**
 * Verify PhonePe callback manually
 * 
 * @param array $headers HTTP headers from the request
 * @param array $responseBody JSON-decoded response body from PhonePe
 * @return array Verification result with transaction details
 */
function verifyPhonePeCallback($headers, $responseBody) {
    try {
        // Log the callback data for debugging
        error_log('PhonePe callback headers: ' . json_encode($headers));
        error_log('PhonePe callback body: ' . json_encode($responseBody));
        
        // Extract transaction details from the response
        if (isset($responseBody['data'])) {
            $data = $responseBody['data'];
            
            // Basic verification - you can enhance this based on your requirements
            $transactionId = $data['transactionId'] ?? null;
            $merchantTransactionId = $data['merchantTransactionId'] ?? null;
            $amount = isset($data['amount']) ? $data['amount'] / 100 : 0; // Convert from paisa to rupees
            $state = $data['state'] ?? 'UNKNOWN';
            
            return [
                'success' => true,
                'merchantOrderId' => $merchantTransactionId,
                'transactionId' => $transactionId,
                'amount' => $amount,
                'state' => $state
            ];
        }
        
        // If we can't find transaction details in the response
        return [
            'success' => false,
            'error' => 'Invalid callback response structure'
        ];
    } catch (\Exception $e) {
        error_log('PhonePe callback verification failed: ' . $e->getMessage());
        return [
            'success' => false,
            'error' => $e->getMessage()
        ];
    }
}

/**
 * Process PhonePe refund using direct API
 * 
 * @param string $merchantRefundId Unique refund ID
 * @param string $originalOrderId Original order ID
 * @param float $amount Amount to refund
 * @return array Refund response
 */
function processPhonePeRefund($merchantRefundId, $originalOrderId, $amount) {
    try {
        // Convert amount to paisa
        $amountInPaise = round($amount * 100);
        
        // Prepare refund request data
        $refundData = [
            "merchantId" => PHONEPE_CLIENT_ID,
            "merchantUserId" => "MUID_" . time(),
            "originalTransactionId" => $originalOrderId,
            "merchantTransactionId" => $merchantRefundId,
            "amount" => $amountInPaise,
            "callbackUrl" => "" // Optional callback URL for refund notifications
        ];
        
        // Calculate checksum
        $checksum = generatePhonePeChecksum($refundData, PHONEPE_CLIENT_SECRET);
        
        // Prepare final request
        $requestData = [
            "request" => base64_encode(json_encode($refundData)),
            "X-VERIFY" => $checksum
        ];
        
        // API endpoint for refund
        $endpoint = PHONEPE_API_ENDPOINT . "/pg/v1/refund";
        
        // Send request to PhonePe API
        $ch = curl_init($endpoint);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($requestData));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'X-CLIENT-ID: ' . PHONEPE_CLIENT_ID
        ]);
        
        $response = curl_exec($ch);
        $error = curl_error($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($error) {
            error_log("PhonePe refund curl error: " . $error);
            return [
                'success' => false,
                'error' => "Connection error: " . $error
            ];
        }
        
        $responseData = json_decode($response, true);
        error_log("PhonePe refund response: " . json_encode($responseData));
        
        if ($httpCode == 200 && isset($responseData['success']) && $responseData['success'] === true) {
            $data = $responseData['data'] ?? [];
            return [
                'success' => true,
                'refund_id' => $data['merchantTransactionId'] ?? $merchantRefundId,
                'original_order_id' => $originalOrderId,
                'state' => $data['state'] ?? 'INITIATED',
                'amount' => isset($data['amount']) ? $data['amount'] / 100 : $amount // Convert back to rupees
            ];
        } else {
            return [
                'success' => false,
                'error' => $responseData['message'] ?? 'Refund failed',
                'code' => $responseData['code'] ?? 'UNKNOWN_ERROR'
            ];
        }
    } catch (\Exception $e) {
        error_log('PhonePe refund failed: ' . $e->getMessage());
        return [
            'success' => false,
            'error' => $e->getMessage()
        ];
    }
}

/**
 * Check PhonePe refund status using direct API
 * 
 * @param string $merchantRefundId Refund ID to check
 * @return array Refund status response
 */
function checkPhonePeRefundStatus($merchantRefundId) {
    try {
        // API endpoint for refund status check
        $endpoint = PHONEPE_API_ENDPOINT . "/pg/v1/refund/" . PHONEPE_CLIENT_ID . "/" . $merchantRefundId;
        
        // Create X-VERIFY header
        $string = "/pg/v1/refund/" . PHONEPE_CLIENT_ID . "/" . $merchantRefundId . PHONEPE_CLIENT_SECRET;
        $checksum = hash('sha256', $string);
        
        // Send request to PhonePe API
        $ch = curl_init($endpoint);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'X-CLIENT-ID: ' . PHONEPE_CLIENT_ID,
            'X-VERIFY: ' . $checksum
        ]);

        $response = curl_exec($ch);
        $error = curl_error($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($error) {
            error_log("PhonePe refund status check curl error: " . $error);
            return [
                'success' => false,
                'error' => "Connection error: " . $error
            ];
        }

        $responseData = json_decode($response, true);
        error_log("PhonePe refund status check response: " . json_encode($responseData));

        if ($httpCode == 200 && isset($responseData['success']) && $responseData['success'] === true) {
            $data = $responseData['data'] ?? [];
            return [
                'success' => true,
                'refund_id' => $data['merchantTransactionId'] ?? $merchantRefundId,
                'original_order_id' => $data['originalTransactionId'] ?? '',
                'state' => $data['state'] ?? 'UNKNOWN',
                'amount' => isset($data['amount']) ? $data['amount'] / 100 : 0  // Convert paisa to rupees
            ];
        } else {
            return [
                'success' => false,
                'error' => $responseData['message'] ?? 'Refund status check failed',
                'code' => $responseData['code'] ?? 'UNKNOWN_ERROR'
            ];
        }
    } catch (\Exception $e) {
        error_log('PhonePe refund status check failed: ' . $e->getMessage());
        return [
            'success' => false,
            'error' => $e->getMessage()
        ];
    }
}
?>

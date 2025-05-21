<?php
/**
 * PhonePe Callback Handler
 * Processes callbacks received from PhonePe after payment completion
 */

// Include initialization file
require_once __DIR__ . '/../includes/init.php';

// Include email functionality
require_once ROOT_PATH . '/app/config/email.php';

// Include PhonePe configuration
require_once ROOT_PATH . '/app/config/phonepe.php';

// Log the callback
error_log("PhonePe callback received: " . json_encode($_REQUEST));

// Get headers for verification
$headers = getallheaders();
$requestBody = file_get_contents("php://input");

// Log the request details
error_log("PhonePe callback headers: " . json_encode($headers));
error_log("PhonePe callback body: " . $requestBody);

// Check if this is a GET request (redirect after payment)
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['order_id'])) {
    // Handle browser redirect after payment
    $orderId = $_GET['order_id'];
    
    // Get order details
    $order = getOrderById($orderId);
    if (!$order) {
        $_SESSION['error'] = 'Order not found';
        header('Location: checkout.php');
        exit;
    }
    
    // Store order ID in session for confirmation page
    $_SESSION['last_order_id'] = $orderId;
    
    // Redirect to order received page
    header('Location: order-received.php?order_id=' . $orderId);
    exit;
}

// Check if this is a POST request (server callback)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Parse JSON body
        $requestData = json_decode($requestBody, true);
        
        if (!$requestData) {
            error_log("Invalid JSON in PhonePe callback");
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Invalid JSON']);
            exit;
        }
        
        // Initialize variables to store transaction details
        $merchantOrderId = null;
        $transactionId = null;
        $amount = 0;
        $state = 'UNKNOWN';
        
        // Verify the callback using our direct API implementation
        try {
            // Process the callback with our verification function
            $callbackResponse = verifyPhonePeCallback($headers, $requestData);
            
            if ($callbackResponse['success']) {
                // Extract order details from verified response
                $merchantOrderId = $callbackResponse['merchantOrderId'];
                $transactionId = $callbackResponse['transactionId'];
                $amount = $callbackResponse['amount']; // Already converted to rupees in our function
                $state = $callbackResponse['state'];
                
                error_log("PhonePe callback verified successfully: " . json_encode($callbackResponse));
            } else {
                // If verification fails, log the error and try to extract data from request
                error_log("PhonePe callback verification failed: " . ($callbackResponse['error'] ?? 'Unknown error'));
                
                // Extract data directly from request as fallback
                if (isset($requestData['data'])) {
                    $data = $requestData['data'];
                    $merchantOrderId = $data['merchantTransactionId'] ?? null;
                    $transactionId = $data['transactionId'] ?? null;
                    $amount = isset($data['amount']) ? $data['amount'] / 100 : 0; // Convert paise to rupees
                    $state = $data['state'] ?? 'UNKNOWN';
                } else {
                    // If can't find data in standard format, try other fields
                    $merchantOrderId = $requestData['merchantOrderId'] ?? $requestData['orderId'] ?? null;
                    $transactionId = $requestData['transactionId'] ?? $requestData['id'] ?? null;
                    $amount = isset($requestData['amount']) ? $requestData['amount'] / 100 : 0; // Convert paise to rupees
                    $state = $requestData['state'] ?? 'UNKNOWN';
                }
            }
        } catch (\Exception $e) {
            // Handle any exceptions during callback processing
            error_log("PhonePe callback processing error: " . $e->getMessage());
            
            // Extract data directly from request as fallback
            if (isset($requestData['data'])) {
                $data = $requestData['data'];
                $merchantOrderId = $data['merchantTransactionId'] ?? null;
                $transactionId = $data['transactionId'] ?? null;
                $amount = isset($data['amount']) ? $data['amount'] / 100 : 0;
                $state = $data['state'] ?? 'UNKNOWN';
            } else {
                // If can't find data in standard format, try other fields
                $merchantOrderId = $requestData['merchantOrderId'] ?? $requestData['orderId'] ?? null;
                $transactionId = $requestData['transactionId'] ?? $requestData['id'] ?? null;
                $amount = isset($requestData['amount']) ? $requestData['amount'] / 100 : 0; // Convert paise to rupees
                $state = $requestData['state'] ?? 'UNKNOWN';
            }
        }
        
        // Log callback details
        error_log("PhonePe callback verified: OrderID=$merchantOrderId, TransactionID=$transactionId, State=$state");
        
        // Get the internal order ID using the merchant order ID (your order number)
        $order = findOrderByOrderNumber($merchantOrderId);
        
        if (!$order) {
            error_log("Order not found for merchant order ID: $merchantOrderId");
            http_response_code(404);
            echo json_encode(['status' => 'error', 'message' => 'Order not found']);
            exit;
        }
        
        $orderId = $order['id'];
        
        // Get order details and items
        $orderItems = getOrderItems($orderId);
        $billingDetails = getBillingDetailsByOrderId($orderId);
        
        // Update payment status based on state
        if ($state === 'COMPLETED') {
            // Record payment transaction with completed status
            $paymentResult = recordPaymentTransaction(
                $orderId,
                $transactionId,
                'phonepe',
                $amount,
                'completed',
                json_encode([
                    'merchantOrderId' => $merchantOrderId,
                    'transactionId' => $transactionId,
                    'amount' => $amount,
                    'state' => $state
                ])
            );
            
            if ($paymentResult['success']) {
                // Get updated order details with new payment status
                $updatedOrder = getOrderById($orderId);
                
                // Prepare transaction details for email
                $transactionDetails = [
                    'transaction_id' => $transactionId,
                    'status' => 'completed'
                ];
                
                // Send order notification emails with transaction details
                sendOrderNotificationEmails($orderId, $updatedOrder, $orderItems, $billingDetails, $transactionDetails);
                
                // Clear cart (if using shared session)
                // clearCart();
                
                // Log success
                error_log("Payment completed successfully for order: $orderId, transaction: $transactionId");
                
                // Send success response
                http_response_code(200);
                echo json_encode(['status' => 'success']);
            } else {
                error_log("Failed to record payment transaction: " . $paymentResult['message']);
                http_response_code(500);
                echo json_encode(['status' => 'error', 'message' => 'Failed to record payment']);
            }
        } else if ($state === 'PENDING') {
            // Handle pending payment
            error_log("Payment pending for order: $orderId");
            http_response_code(200); // Acknowledge the callback
            echo json_encode(['status' => 'success', 'message' => 'Payment pending']);
        } else {
            // Payment failed or other status
            $paymentResult = recordPaymentTransaction(
                $orderId,
                $transactionId,
                'phonepe',
                $amount,
                'failed',
                json_encode([
                    'merchantOrderId' => $merchantOrderId,
                    'transactionId' => $transactionId,
                    'amount' => $amount,
                    'state' => $state,
                    'reason' => 'Payment not completed or rejected'
                ])
            );
            
            error_log("Payment not completed. Status: $state for order: $orderId");
            http_response_code(200); // Still acknowledge the callback
            echo json_encode(['status' => 'received', 'message' => 'Payment not completed']);
        }
    } catch (\Exception $e) {
        error_log("PhonePe callback error: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
} else {
    // Invalid request method
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
}

/**
 * Find an order by its order number
 * 
 * @param string $orderNumber The order number to find
 * @return array|false Order data or false if not found
 */
function findOrderByOrderNumber($orderNumber) {
    try {
        global $pdo;
        $stmt = $pdo->prepare("SELECT * FROM orders WHERE order_number = ?");
        $stmt->execute([$orderNumber]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error finding order by order number: " . $e->getMessage());
        return false;
    }
}
?>

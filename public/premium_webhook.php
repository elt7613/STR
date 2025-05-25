<?php
/**
 * Premium Membership Webhook for Razorpay
 * 
 * This file handles asynchronous payment notifications from Razorpay
 */

// Include initialization file but suppress output
define('SUPPRESS_OUTPUT', true);
require_once __DIR__ . '/../includes/init.php';

// Include Razorpay configuration
require_once ROOT_PATH . '/app/config/razorpay.php';
require_once ROOT_PATH . '/app/config/premium_schema.php';

// Set headers for API response
header('Content-Type: application/json');

// Get the webhook payload
$webhookBody = file_get_contents('php://input');
$webhookData = json_decode($webhookBody, true);

// Verify webhook signature
$webhookSignature = isset($_SERVER['HTTP_X_RAZORPAY_SIGNATURE']) ? $_SERVER['HTTP_X_RAZORPAY_SIGNATURE'] : '';
$isValidSignature = false;

try {
    if (!empty($webhookSignature) && !empty($webhookBody)) {
        $expectedSignature = hash_hmac('sha256', $webhookBody, RAZORPAY_KEY_SECRET);
        $isValidSignature = hash_equals($expectedSignature, $webhookSignature);
    }
    
    if (!$isValidSignature) {
        error_log('Razorpay webhook: Invalid signature');
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Invalid signature']);
        exit;
    }
    
    // Process webhook data
    if (isset($webhookData['event']) && isset($webhookData['payload']) && isset($webhookData['payload']['payment']) && isset($webhookData['payload']['payment']['entity'])) {
        $event = $webhookData['event'];
        $paymentEntity = $webhookData['payload']['payment']['entity'];
        
        // Log webhook event
        error_log('Razorpay webhook received: ' . $event);
        error_log('Payment data: ' . json_encode($paymentEntity));
        
        // Handle payment events
        if ($event === 'payment.authorized' || $event === 'payment.captured') {
            $paymentId = $paymentEntity['id'];
            $orderId = $paymentEntity['order_id'];
            $notes = isset($paymentEntity['notes']) ? $paymentEntity['notes'] : [];
            
            // Extract user and plan information from notes
            $userId = isset($notes['user_id']) ? (int)$notes['user_id'] : 0;
            $planId = isset($notes['plan_id']) ? (int)$notes['plan_id'] : 0;
            
            if ($userId > 0 && $planId > 0) {
                // Get plan details
                $plan = getPremiumPricingPlan($planId);
                
                if ($plan) {
                    try {
                        // Begin transaction
                        $pdo->beginTransaction();
                        
                        // Update user to premium status
                        $stmt = $pdo->prepare("UPDATE users SET is_premium_member = 1 WHERE id = ?");
                        $success = $stmt->execute([$userId]);
                        
                        if ($success) {
                            // Check if payment record already exists
                            $stmt = $pdo->prepare("SELECT id FROM premium_payments WHERE payment_id = ?");
                            $stmt->execute([$paymentId]);
                            $existingPayment = $stmt->fetch();
                            
                            if (!$existingPayment) {
                                // Record the payment in database
                                $stmt = $pdo->prepare("INSERT INTO premium_payments 
                                                     (user_id, plan_id, payment_id, payment_amount, payment_status, order_reference) 
                                                     VALUES (?, ?, ?, ?, ?, ?)");
                                $stmt->execute([
                                    $userId,
                                    $planId,
                                    $paymentId,
                                    $plan['price'],
                                    'completed',
                                    'WEBHOOK_' . time()
                                ]);
                            } else {
                                // Update existing payment record
                                $stmt = $pdo->prepare("UPDATE premium_payments 
                                                     SET payment_status = 'completed' 
                                                     WHERE payment_id = ?");
                                $stmt->execute([$paymentId]);
                            }
                            
                            // Commit transaction
                            $pdo->commit();
                            
                            error_log("Premium membership activated for user ID: $userId");
                        } else {
                            // Rollback transaction
                            $pdo->rollBack();
                            error_log("Failed to update premium status for user ID: $userId");
                        }
                    } catch (Exception $e) {
                        // Rollback transaction
                        $pdo->rollBack();
                        error_log('Premium payment processing error: ' . $e->getMessage());
                    }
                } else {
                    error_log("Plan not found for ID: $planId");
                }
            } else {
                error_log("Invalid user_id or plan_id in payment notes");
            }
        } elseif ($event === 'payment.failed') {
            // Handle failed payment
            error_log('Payment failed: ' . $paymentEntity['id']);
        }
    }
    
    // Return success response
    http_response_code(200);
    echo json_encode(['status' => 'success']);
} catch (Exception $e) {
    error_log('Razorpay webhook error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Internal server error']);
}
?>

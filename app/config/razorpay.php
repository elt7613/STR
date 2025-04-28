<?php
/**
 * Razorpay Configuration
 */

// Razorpay API Keys (Replace with your actual keys)
define('RAZORPAY_KEY_ID', 'rzp_test_ZjwOOezkHZ5DsV');
define('RAZORPAY_KEY_SECRET', 'j9qSfEl8QLJGpL0rUsGDHGFH');

// Include Razorpay SDK (Install via Composer: composer require razorpay/razorpay)
require_once ROOT_PATH . '/vendor/autoload.php';
use Razorpay\Api\Api;

/**
 * Create a Razorpay API instance
 * 
 * @return \Razorpay\Api\Api
 */
function getRazorpayApi() {
    return new Api(RAZORPAY_KEY_ID, RAZORPAY_KEY_SECRET);
}

/**
 * Create a Razorpay order
 * 
 * @param string $orderNumber Order number
 * @param float $amount Amount in INR (multiplied by 100 as required by Razorpay)
 * @param array $notes Optional notes
 * @return array|null Razorpay order data or null on error
 */
function createRazorpayOrder($orderNumber, $amount, $notes = []) {
    try {
        $api = getRazorpayApi();
        
        // Convert amount to paise (Razorpay expects amount in smallest currency unit)
        $amountInPaise = $amount * 100;
        
        $orderData = [
            'receipt'         => $orderNumber,
            'amount'          => $amountInPaise,
            'currency'        => 'INR', // Razorpay supports multiple currencies
            'payment_capture' => 1,     // Auto-capture
            'notes'           => $notes
        ];
        
        $razorpayOrder = $api->order->create($orderData);
        return $razorpayOrder->toArray();
    } catch (\Exception $e) {
        error_log('Razorpay order creation failed: ' . $e->getMessage());
        return null;
    }
}

/**
 * Verify Razorpay payment signature
 * 
 * @param string $razorpayOrderId Razorpay Order ID
 * @param string $razorpayPaymentId Razorpay Payment ID
 * @param string $signature Razorpay Signature from callback
 * @return bool Whether signature is valid
 */
function verifyRazorpaySignature($razorpayOrderId, $razorpayPaymentId, $signature) {
    try {
        $api = getRazorpayApi();
        
        // Generate the expected signature
        $expectedSignature = hash_hmac('sha256', $razorpayOrderId . '|' . $razorpayPaymentId, RAZORPAY_KEY_SECRET);
        
        // Compare with the signature we received
        return hash_equals($expectedSignature, $signature);
    } catch (\Exception $e) {
        error_log('Razorpay signature verification failed: ' . $e->getMessage());
        return false;
    }
}

/**
 * Fetch Razorpay payment details
 * 
 * @param string $paymentId Razorpay Payment ID
 * @return array|null Payment details or null on error
 */
function fetchRazorpayPayment($paymentId) {
    try {
        $api = getRazorpayApi();
        $payment = $api->payment->fetch($paymentId);
        return $payment->toArray();
    } catch (\Exception $e) {
        error_log('Failed to fetch Razorpay payment: ' . $e->getMessage());
        return null;
    }
}
?> 
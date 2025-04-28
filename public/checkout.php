<?php
/**
 * Checkout page controller
 * Handles the checkout process
 */

// Include initialization file
require_once __DIR__ . '/../includes/init.php';

// Include email functionality
require_once ROOT_PATH . '/app/config/email.php';

// Initialize variables
$error = '';
$success = '';

// Get cart items and calculate totals
$cartItems = getCartItems();
$cartSubtotal = 0;
$shippingCost = 0;

// If cart is empty, redirect to cart page
if (empty($cartItems)) {
    header('Location: cart.php');
    exit;
}

// Calculate subtotal
foreach ($cartItems as $item) {
    $cartSubtotal += $item['amount'] * $item['quantity'];
}

// Calculate total
$cartTotal = $cartSubtotal + $shippingCost;

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check if payment method is selected
    if (!isset($_POST['payment_method']) || empty($_POST['payment_method'])) {
        $error = 'Please select a payment method';
    } else {
        $paymentMethod = $_POST['payment_method'];
        $orderNotes = isset($_POST['order_notes']) ? $_POST['order_notes'] : '';
        
        // Validate required fields
        $requiredFields = [
            'first_name', 'last_name', 'country', 'street_address_1',
            'city', 'state', 'postcode', 'phone', 'email'
        ];
        
        $missingFields = [];
        foreach ($requiredFields as $field) {
            if (!isset($_POST[$field]) || empty($_POST[$field])) {
                $missingFields[] = $field;
            }
        }
        
        if (!empty($missingFields)) {
            $error = 'Please fill in all required fields: ' . implode(', ', $missingFields);
        } else {
            // Get user ID if logged in
            $userId = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
            $sessionId = session_id();
            
            // Create order
            $orderResult = createOrder(
                $userId,
                $sessionId,
                $paymentMethod,
                $cartSubtotal,
                $shippingCost,
                $cartTotal,
                $orderNotes
            );
            
            if ($orderResult['success']) {
                $orderId = $orderResult['order_id'];
                
                // Add billing details
                $billingData = [
                    'first_name' => $_POST['first_name'],
                    'last_name' => $_POST['last_name'],
                    'company_name' => isset($_POST['company_name']) ? $_POST['company_name'] : null,
                    'country' => $_POST['country'],
                    'street_address_1' => $_POST['street_address_1'],
                    'street_address_2' => isset($_POST['street_address_2']) ? $_POST['street_address_2'] : null,
                    'city' => $_POST['city'],
                    'state' => $_POST['state'],
                    'postcode' => $_POST['postcode'],
                    'phone' => $_POST['phone'],
                    'email' => $_POST['email']
                ];
                
                $billingResult = addBillingDetails($orderId, $billingData);
                
                if ($billingResult['success']) {
                    // Process payment based on method
                    if ($paymentMethod === 'cod') {
                        // Cash on Delivery - just record the transaction
                        $paymentResult = recordPaymentTransaction(
                            $orderId,
                            null,
                            'cod',
                            $cartTotal,
                            'pending'
                        );
                        
                        if ($paymentResult['success']) {
                            // Send order notification emails
                            $order = getOrderById($orderId);
                            $orderItems = getOrderItems($orderId);
                            sendOrderNotificationEmails($orderId, $order, $orderItems, $billingData);
                            
                            // Clear cart
                            clearCart();
                            
                            // Store order ID in session for confirmation page
                            $_SESSION['last_order_id'] = $orderId;
                            
                            // Redirect to confirmation page
                            header('Location: order-received.php?order_id=' . $orderId);
                            exit;
                        } else {
                            $error = 'Payment processing error: ' . $paymentResult['message'];
                        }
                    } elseif ($paymentMethod === 'razorpay') {
                        // For Razorpay payment, create a pending transaction
                        $paymentResult = recordPaymentTransaction(
                            $orderId,
                            null, // transaction ID will be set after payment
                            'razorpay',
                            $cartTotal,
                            'pending' // Always start with pending status
                        );
                        
                        if (!$paymentResult['success']) {
                            $error = 'Payment processing error: ' . $paymentResult['message'];
                            header('Location: checkout.php');
                            exit;
                        }
                        
                        // Store order ID in session for the payment process
                        $_SESSION['pending_order_id'] = $orderId;
                        
                        // Redirect to Razorpay payment page
                        header('Location: razorpay-payment.php?order_id=' . $orderId);
                        exit;
                    } else {
                        $error = 'Invalid payment method';
                    }
                } else {
                    $error = 'Error saving billing details: ' . $billingResult['message'];
                }
            } else {
                $error = 'Error creating order: ' . $orderResult['message'];
            }
        }
    }
}

// Load the checkout view
require_once ROOT_PATH . '/app/views/checkout.php';
?> 
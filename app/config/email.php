<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Include PHPMailer library
require ROOT_PATH . '/vendor/autoload.php';

function sendEmail($subject, $htmlBody, $plainTextBody = '') {
    // Create a new PHPMailer instance
    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';  
        $mail->SMTPAuth   = true;
        $mail->Username   = 'strworks87@gmail.com';
        $mail->Password   = 'niab xgig lqtt nnjf'; 
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;  

        $adminEmail = "strworks87@gmail.com";

        $mail->setFrom('strworks87@gmail.com', 'STR Works');

        $mail->addAddress($adminEmail, "Admin"); 

        // Email content
        $mail->isHTML(true); 
        $mail->Subject = $subject;
        $mail->Body    = $htmlBody;
        $mail->AltBody = $plainTextBody ?: strip_tags($htmlBody);

        // Send the email
        $mail->send();
        return 'Email sent successfully!';
    } catch (Exception $e) {
        return "Failed to send email. Error: {$mail->ErrorInfo}";
    }
}

/**
 * Send order notification emails to customer and admin
 * 
 * @param int $orderId The order ID
 * @param array $order Order details
 * @param array $orderItems Order items
 * @param array $billingDetails Customer billing details
 * @param array|null $transactionDetails Optional payment transaction details
 * @return bool Success status
 */
function sendOrderNotificationEmails($orderId, $order, $orderItems, $billingDetails, $transactionDetails = null) {
    if (empty($billingDetails['email'])) {
        error_log("Cannot send order notification: customer email is missing");
        return false;
    }

    // Get the latest payment transaction details if not provided
    if (!$transactionDetails && $order['payment_method'] === 'razorpay') {
        $transactionDetails = getLatestPaymentTransaction($orderId);
    }

    // Customer email
    $customerSubject = "Your Order #" . $order['order_number'] . " has been received";
    $customerHtml = generateCustomerOrderEmail($order, $orderItems, $billingDetails, $transactionDetails);
    
    // Admin email
    $adminSubject = "New Order #" . $order['order_number'] . " has been placed";
    $adminHtml = generateAdminOrderEmail($order, $orderItems, $billingDetails, $transactionDetails);
    
    // Send emails
    $customerResult = sendEmailToCustomer($customerSubject, $customerHtml, $billingDetails['email'], $billingDetails['first_name'] . ' ' . $billingDetails['last_name']);
    $adminResult = sendEmail($adminSubject, $adminHtml);
    
    return $customerResult && $adminResult;
}

/**
 * Send email to a specific customer
 * 
 * @param string $subject Email subject
 * @param string $htmlBody HTML email body
 * @param string $toEmail Recipient email
 * @param string $toName Recipient name
 * @param string $plainTextBody Plain text email body (optional)
 * @return bool Success status
 */
function sendEmailToCustomer($subject, $htmlBody, $toEmail, $toName, $plainTextBody = '') {
    // Create a new PHPMailer instance
    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';  
        $mail->SMTPAuth   = true;
        $mail->Username   = 'strworks87@gmail.com';
        $mail->Password   = 'niab xgig lqtt nnjf';  
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;  

        $mail->setFrom('strworks87@gmail.com', 'STR Works');
        $mail->addAddress($toEmail, $toName); 

        // Email content
        $mail->isHTML(true); 
        $mail->Subject = $subject;
        $mail->Body    = $htmlBody;
        $mail->AltBody = $plainTextBody ?: strip_tags($htmlBody);

        // Send the email
        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Failed to send customer email: {$mail->ErrorInfo}");
        return false;
    }
}

/**
 * Generate HTML email for customer order confirmation
 * 
 * @param array $order Order details
 * @param array $orderItems Order items
 * @param array $billingDetails Billing details
 * @param array|null $transactionDetails Optional payment transaction details
 * @return string HTML email content
 */
function generateCustomerOrderEmail($order, $orderItems, $billingDetails, $transactionDetails = null) {
    $html = '
    <!DOCTYPE html>
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; }
            .header { background-color: #000; color: #fff; padding: 20px; text-align: center; }
            .header h1 { color: #ff5c8d; margin: 0; }
            .content { padding: 20px; }
            .order-info { margin-bottom: 30px; }
            .order-items { width: 100%; border-collapse: collapse; margin-bottom: 30px; }
            .order-items th, .order-items td { padding: 10px; text-align: left; border-bottom: 1px solid #ddd; }
            .order-items th { background-color: #f5f5f5; }
            .footer { padding: 20px; text-align: center; font-size: 12px; color: #777; }
            .total-row { font-weight: bold; }
            .pink { color: #ff5c8d; }
            .payment-info { background-color: #f9f9f9; padding: 15px; border-radius: 5px; margin-bottom: 20px; }
            .payment-success { color: #4CAF50; }
            .payment-pending { color: #FF9800; }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="header">
                <h1>STR Works</h1>
            </div>
            <div class="content">
                <h2>Thank You For Your Order!</h2>
                <p>Hello ' . htmlspecialchars($billingDetails['first_name']) . ',</p>
                <p>We have received your order and are working on it. Here are your order details:</p>
                
                <div class="order-info">
                    <p><strong>Order Number:</strong> ' . htmlspecialchars($order['order_number']) . '</p>
                    <p><strong>Date:</strong> ' . date('F j, Y', strtotime($order['created_at'])) . '</p>
                    <p><strong>Payment Method:</strong> ' . getPaymentMethodName($order['payment_method']) . '</p>';
    
    // Add payment status and transaction ID for online payments
    if ($order['payment_method'] === 'razorpay') {
        $statusClass = ($order['payment_status'] === 'completed') ? 'payment-success' : 'payment-pending';
        $html .= '
                    <div class="payment-info">
                        <p><strong>Payment Status:</strong> <span class="' . $statusClass . '">' . ucfirst($order['payment_status']) . '</span></p>';
        
        // Add transaction ID if available
        if ($transactionDetails && !empty($transactionDetails['transaction_id'])) {
            $html .= '
                        <p><strong>Transaction ID:</strong> ' . htmlspecialchars($transactionDetails['transaction_id']) . '</p>';
        }
        
        $html .= '
                    </div>';
    }
    
    $html .= '
                </div>
                
                <h3>Order Details:</h3>
                <table class="order-items">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Quantity</th>
                            <th>Price</th>
                            <th>Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>';
    
    foreach ($orderItems as $item) {
        $html .= '
                        <tr>
                            <td>' . htmlspecialchars($item['title']) . '</td>
                            <td>' . $item['quantity'] . '</td>
                            <td>$' . number_format($item['price'], 2) . '</td>
                            <td>$' . number_format($item['subtotal'], 2) . '</td>
                        </tr>';
    }
    
    $html .= '
                        <tr>
                            <td colspan="3" align="right"><strong>Subtotal:</strong></td>
                            <td>$' . number_format($order['subtotal'], 2) . '</td>
                        </tr>
                        <tr>
                            <td colspan="3" align="right"><strong>Shipping:</strong></td>
                            <td>$' . number_format($order['shipping_cost'], 2) . '</td>
                        </tr>
                        <tr class="total-row">
                            <td colspan="3" align="right"><strong>Total:</strong></td>
                            <td>$' . number_format($order['total'], 2) . '</td>
                        </tr>
                    </tbody>
                </table>
                
                <h3>Billing Address:</h3>
                <p>
                    ' . htmlspecialchars($billingDetails['first_name'] . ' ' . $billingDetails['last_name']) . '<br>
                    ' . (empty($billingDetails['company_name']) ? '' : htmlspecialchars($billingDetails['company_name']) . '<br>') . '
                    ' . htmlspecialchars($billingDetails['street_address_1']) . '<br>
                    ' . (empty($billingDetails['street_address_2']) ? '' : htmlspecialchars($billingDetails['street_address_2']) . '<br>') . '
                    ' . htmlspecialchars($billingDetails['city'] . ', ' . $billingDetails['state'] . ' ' . $billingDetails['postcode']) . '<br>
                    ' . htmlspecialchars($billingDetails['country']) . '<br>
                    <strong>Email:</strong> ' . htmlspecialchars($billingDetails['email']) . '<br>
                    <strong>Phone:</strong> ' . htmlspecialchars($billingDetails['phone']) . '
                </p>
                
                <p>If you have any questions about your order, please contact our customer support team.</p>
                <p>Thank you for shopping with us!</p>
            </div>
            <div class="footer">
                <p>&copy; ' . date('Y') . ' STR Works. All rights reserved.</p>
            </div>
        </div>
    </body>
    </html>';
    
    return $html;
}

/**
 * Generate HTML email for admin order notification
 * 
 * @param array $order Order details
 * @param array $orderItems Order items
 * @param array $billingDetails Billing details
 * @param array|null $transactionDetails Optional payment transaction details
 * @return string HTML email content
 */
function generateAdminOrderEmail($order, $orderItems, $billingDetails, $transactionDetails = null) {
    $html = '
    <!DOCTYPE html>
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; }
            .header { background-color: #000; color: #fff; padding: 20px; text-align: center; }
            .header h1 { color: #ff5c8d; margin: 0; }
            .content { padding: 20px; }
            .order-info { margin-bottom: 30px; }
            .order-items { width: 100%; border-collapse: collapse; margin-bottom: 30px; }
            .order-items th, .order-items td { padding: 10px; text-align: left; border-bottom: 1px solid #ddd; }
            .order-items th { background-color: #f5f5f5; }
            .customer-info { margin-bottom: 30px; }
            .footer { padding: 20px; text-align: center; font-size: 12px; color: #777; }
            .total-row { font-weight: bold; }
            .pink { color: #ff5c8d; }
            .payment-info { background-color: #f9f9f9; padding: 15px; border-radius: 5px; margin-bottom: 20px; }
            .payment-success { color: #4CAF50; }
            .payment-pending { color: #FF9800; }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="header">
                <h1>STR Works - New Order</h1>
            </div>
            <div class="content">
                <h2>New Order Received</h2>
                <p>A new order has been placed on your website. Details are below:</p>
                
                <div class="order-info">
                    <p><strong>Order Number:</strong> ' . htmlspecialchars($order['order_number']) . '</p>
                    <p><strong>Date:</strong> ' . date('F j, Y', strtotime($order['created_at'])) . '</p>
                    <p><strong>Payment Method:</strong> ' . getPaymentMethodName($order['payment_method']) . '</p>';
    
    // Add payment status and transaction ID with highlighting
    $statusClass = ($order['payment_status'] === 'completed') ? 'payment-success' : 'payment-pending';
    $html .= '
                    <div class="payment-info">
                        <p><strong>Payment Status:</strong> <span class="' . $statusClass . '">' . ucfirst($order['payment_status']) . '</span></p>';
    
    // Add transaction ID if available
    if ($transactionDetails && !empty($transactionDetails['transaction_id'])) {
        $html .= '
                        <p><strong>Transaction ID:</strong> ' . htmlspecialchars($transactionDetails['transaction_id']) . '</p>';
    }
    
    $html .= '
                    </div>
                </div>
                
                <h3>Order Items:</h3>
                <table class="order-items">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Quantity</th>
                            <th>Price</th>
                            <th>Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>';
    
    foreach ($orderItems as $item) {
        $html .= '
                        <tr>
                            <td>' . htmlspecialchars($item['title']) . '</td>
                            <td>' . $item['quantity'] . '</td>
                            <td>$' . number_format($item['price'], 2) . '</td>
                            <td>$' . number_format($item['subtotal'], 2) . '</td>
                        </tr>';
    }
    
    $html .= '
                        <tr>
                            <td colspan="3" align="right"><strong>Subtotal:</strong></td>
                            <td>$' . number_format($order['subtotal'], 2) . '</td>
                        </tr>
                        <tr>
                            <td colspan="3" align="right"><strong>Shipping:</strong></td>
                            <td>$' . number_format($order['shipping_cost'], 2) . '</td>
                        </tr>
                        <tr class="total-row">
                            <td colspan="3" align="right"><strong>Total:</strong></td>
                            <td>$' . number_format($order['total'], 2) . '</td>
                        </tr>
                    </tbody>
                </table>
                
                <h3>Customer Information:</h3>
                <div class="customer-info">
                    <p><strong>Name:</strong> ' . htmlspecialchars($billingDetails['first_name'] . ' ' . $billingDetails['last_name']) . '</p>
                    <p><strong>Email:</strong> ' . htmlspecialchars($billingDetails['email']) . '</p>
                    <p><strong>Phone:</strong> ' . htmlspecialchars($billingDetails['phone']) . '</p>
                </div>
                
                <h3>Billing Address:</h3>
                <p>
                    ' . htmlspecialchars($billingDetails['first_name'] . ' ' . $billingDetails['last_name']) . '<br>
                    ' . (empty($billingDetails['company_name']) ? '' : htmlspecialchars($billingDetails['company_name']) . '<br>') . '
                    ' . htmlspecialchars($billingDetails['street_address_1']) . '<br>
                    ' . (empty($billingDetails['street_address_2']) ? '' : htmlspecialchars($billingDetails['street_address_2']) . '<br>') . '
                    ' . htmlspecialchars($billingDetails['city'] . ', ' . $billingDetails['state'] . ' ' . $billingDetails['postcode']) . '<br>
                    ' . htmlspecialchars($billingDetails['country']) . '
                </p>
                
                <p>You can manage this order from your admin dashboard.</p>
            </div>
            <div class="footer">
                <p>&copy; ' . date('Y') . ' STR Works. All rights reserved.</p>
            </div>
        </div>
    </body>
    </html>';
    
    return $html;
}

/**
 * Get human-readable payment method name
 * 
 * @param string $methodCode Payment method code
 * @return string Human-readable payment method name
 */
function getPaymentMethodName($methodCode) {
    switch ($methodCode) {
        case 'cod':
            return 'Cash on Delivery';
        case 'razorpay':
            return 'Razorpay';
        default:
            return ucfirst($methodCode);
    }
}

/**
 * Get the latest payment transaction for an order
 * 
 * @param int $orderId Order ID
 * @return array|null Payment transaction details or null if not found
 */
function getLatestPaymentTransaction($orderId) {
    /** @var \PDO $pdo */
    global $pdo;
    
    if (!$pdo) {
        return null;
    }
    
    try {
        $stmt = $pdo->prepare("
            SELECT * FROM payment_transactions
            WHERE order_id = :order_id
            ORDER BY created_at DESC
            LIMIT 1
        ");
        
        $stmt->execute([':order_id' => $orderId]);
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (\PDOException $e) {
        error_log('Error fetching payment transaction: ' . $e->getMessage());
        return null;
    }
}

/**
 * Send notification to user about vehicle product search with no results
 * 
 * @param array $brand Brand details
 * @param array $filterDetails Vehicle filter details
 * @param string $userEmail User's email address
 * @param string $userName User's name
 * @param int|null $categoryId Category ID if applied in filter
 * @param array|null $categories All categories for lookup
 * @return bool Success status
 */
function sendVehicleSearchNotification($brand, $filterDetails, $userEmail, $userName, $categoryId = null, $categories = null) {
    if (empty($userEmail)) {
        return false;
    }

    // Prepare user-friendly email subject
    $subject = 'Your Vehicle Product Search - STR Works';
    
    // Create HTML email with proper styling to avoid spam filters
    $htmlBody = '
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>STR Works - Search Notification</title>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 0; }
            .container { max-width: 600px; margin: 0 auto; }
            .header { background-color: #000; color: #fff; padding: 20px; text-align: center; }
            .header h1 { color: #ff5c8d; margin: 0; }
            .content { padding: 20px; background-color: #ffffff; }
            .footer { padding: 20px; text-align: center; font-size: 12px; color: #777; background-color: #f5f5f5; }
            .pink { color: #ff5c8d; }
            .search-info { background-color: #f9f9f9; padding: 15px; border-radius: 5px; margin-bottom: 20px; border: 1px solid #eee; }
            ul { padding-left: 20px; }
            li { margin-bottom: 8px; }
            .btn { display: inline-block; padding: 10px 20px; background-color: #ff5c8d; color: white; text-decoration: none; border-radius: 4px; font-weight: bold; }
            .btn:hover { background-color: #ff83c2; }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="header">
                <h1>STR Works</h1>
            </div>
            <div class="content">
                <h2>Thank You For Your Search</h2>
                <p>Hello ' . htmlspecialchars($userName) . ',</p>
                <p>Thank you for using our product search. We noticed you were looking for products that we don\'t currently have in our inventory.</p>
                
                <div class="search-info">
                    <h3>Your Search Details:</h3>
                    <ul>
                        <li><strong>Brand:</strong> ' . htmlspecialchars($brand['name']) . '</li>';
    
    if (isset($filterDetails['make'])) {
        $htmlBody .= '
                        <li><strong>Make:</strong> ' . htmlspecialchars($filterDetails['make']) . '</li>';
    }
    
    if (isset($filterDetails['model'])) {
        $htmlBody .= '
                        <li><strong>Model:</strong> ' . htmlspecialchars($filterDetails['model']) . '</li>';
    }
    
    if (isset($filterDetails['series'])) {
        $htmlBody .= '
                        <li><strong>Series:</strong> ' . htmlspecialchars($filterDetails['series']) . '</li>';
    }
    
    if ($categoryId > 0 && $categories) {
        foreach ($categories as $category) {
            if ($category['id'] == $categoryId) {
                $htmlBody .= '
                        <li><strong>Category:</strong> ' . htmlspecialchars($category['name']) . '</li>';
                break;
            }
        }
    }
    
    $baseUrl = getBaseUrlForEmail();
    
    $htmlBody .= '
                    </ul>
                </div>
                
                <p>We\'ve taken note of your search and will consider adding these products to our inventory in the future.</p>
                <p>Thank you for your interest in STR Works products!</p>
            </div>
            <div class="footer">
                <p>&copy; ' . date('Y') . ' STR Works. All rights reserved.</p>
                <p>This email was sent to you because you searched for products on our website.</p>
            </div>
        </div>
    </body>
    </html>';
    
    // Send email to the user
    return sendEmailToCustomer($subject, $htmlBody, $userEmail, $userName);
}

/**
 * Helper function to get the base URL for emails
 */
function getBaseUrlForEmail() {
    // Try to get the server name and protocol
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://';
    $host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'example.com';
    $path = isset($_SERVER['PHP_SELF']) ? dirname($_SERVER['PHP_SELF']) : '';
    
    // Normalize path ending
    $path = rtrim($path, '/') . '/';
    
    // Return full base URL
    return $protocol . $host . $path;
}

/**
 * Send email notifications when an order is cancelled
 * 
 * @param int $orderId The cancelled order ID
 * @return bool Success status
 */
function sendOrderCancellationEmails($orderId) {
    /** @var \PDO $pdo */
    global $pdo;
    
    if (!$pdo) {
        error_log("Cannot send order cancellation email: database connection error");
        return false;
    }
    
    try {
        // Get order details
        $stmt = $pdo->prepare("
            SELECT o.*, u.email as user_email, u.username
            FROM orders o
            LEFT JOIN users u ON o.user_id = u.id 
            WHERE o.id = ?
        ");
        $stmt->execute([$orderId]);
        $order = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$order) {
            error_log("Cannot send order cancellation email: order not found");
            return false;
        }
        
        // Get order items
        $orderItems = getOrderItems($orderId);
        
        // Get billing details which contains the checkout email
        $billingDetails = getOrderBillingDetails($orderId);
        
        if (empty($billingDetails)) {
            error_log("Cannot send order cancellation email: billing details not found");
            return false;
        }
        
        // Use the email from billing details (checkout email) rather than user account email
        $customerEmail = $billingDetails['email'];
        $customerName = $billingDetails['first_name'] . ' ' . $billingDetails['last_name'];
        
        // Customer email
        $customerSubject = "Your Order #" . $order['order_number'] . " has been cancelled";
        $customerHtml = generateOrderCancellationEmail($order, $orderItems, $billingDetails, false);
        
        // Admin email
        $adminSubject = "Order #" . $order['order_number'] . " has been cancelled";
        $adminHtml = generateOrderCancellationEmail($order, $orderItems, $billingDetails, true);
        
        // Send emails
        $customerResult = sendEmailToCustomer($customerSubject, $customerHtml, $customerEmail, $customerName);
        $adminResult = sendEmail($adminSubject, $adminHtml);
        
        return $customerResult && $adminResult;
        
    } catch (PDOException $e) {
        error_log("Error sending cancellation email: " . $e->getMessage());
        return false;
    }
}

/**
 * Generate HTML email for order cancellation
 * 
 * @param array $order Order details
 * @param array $orderItems Order items
 * @param array $billingDetails Billing details
 * @param bool $isAdmin Whether this is for admin (true) or customer (false)
 * @return string HTML email content
 */
function generateOrderCancellationEmail($order, $orderItems, $billingDetails, $isAdmin = false) {
    $html = '
    <!DOCTYPE html>
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; }
            .header { background-color: #000; color: #fff; padding: 20px; text-align: center; }
            .header h1 { color: #ff5c8d; margin: 0; }
            .content { padding: 20px; }
            .order-info { margin-bottom: 30px; }
            .order-items { width: 100%; border-collapse: collapse; margin-bottom: 30px; }
            .order-items th, .order-items td { padding: 10px; text-align: left; border-bottom: 1px solid #ddd; }
            .order-items th { background-color: #f5f5f5; }
            .footer { padding: 20px; text-align: center; font-size: 12px; color: #777; }
            .total-row { font-weight: bold; }
            .pink { color: #ff5c8d; }
            .cancelled-badge { background-color: #f44336; color: white; padding: 5px 10px; border-radius: 3px; display: inline-block; }
            .cancelled-section { background-color: #ffebee; padding: 15px; border-radius: 5px; margin-bottom: 20px; border: 1px solid #ffcdd2; }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="header">
                <h1>STR Works</h1>
            </div>
            <div class="content">';
    
    if ($isAdmin) {
        $html .= '
                <h2>Order Cancellation Notification</h2>
                <p>An order has been cancelled. Details are below:</p>';
    } else {
        $html .= '
                <h2>Order Cancellation Confirmation</h2>
                <p>Hello ' . htmlspecialchars($billingDetails['first_name']) . ',</p>
                <p>Your order has been cancelled as requested. Details of the cancelled order are below:</p>';
    }
    
    // Cancellation information
    $html .= '
                <div class="cancelled-section">
                    <p><strong>Order Status:</strong> <span class="cancelled-badge">Cancelled</span></p>
                    <p><strong>Cancellation Date:</strong> ' . date('F j, Y, g:i a') . '</p>
                </div>
                
                <div class="order-info">
                    <p><strong>Order Number:</strong> ' . htmlspecialchars($order['order_number']) . '</p>
                    <p><strong>Original Order Date:</strong> ' . date('F j, Y', strtotime($order['created_at'])) . '</p>
                    <p><strong>Payment Method:</strong> ' . getPaymentMethodName($order['payment_method']) . '</p>
                </div>
                
                <h3>Cancelled Items:</h3>
                <table class="order-items">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Quantity</th>
                            <th>Price</th>
                            <th>Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>';
    
    foreach ($orderItems as $item) {
        $html .= '
                        <tr>
                            <td>' . htmlspecialchars($item['title']) . '</td>
                            <td>' . $item['quantity'] . '</td>
                            <td>₹' . number_format($item['price'], 2) . '</td>
                            <td>₹' . number_format($item['subtotal'], 2) . '</td>
                        </tr>';
    }
    
    $html .= '
                        <tr>
                            <td colspan="3" align="right"><strong>Subtotal:</strong></td>
                            <td>₹' . number_format($order['subtotal'], 2) . '</td>
                        </tr>';
                        
    if (!empty($order['shipping_cost']) && $order['shipping_cost'] > 0) {
        $html .= '
                        <tr>
                            <td colspan="3" align="right"><strong>Shipping:</strong></td>
                            <td>₹' . number_format($order['shipping_cost'], 2) . '</td>
                        </tr>';
    }
    
    $html .= '
                        <tr class="total-row">
                            <td colspan="3" align="right"><strong>Total:</strong></td>
                            <td>₹' . number_format($order['total'], 2) . '</td>
                        </tr>
                    </tbody>
                </table>';
    
    if ($isAdmin) {
        // Additional content for admin email
        $html .= '
                <h3>Customer Information:</h3>
                <p>
                    <strong>Name:</strong> ' . htmlspecialchars($billingDetails['first_name'] . ' ' . $billingDetails['last_name']) . '<br>
                    <strong>Email:</strong> ' . htmlspecialchars($billingDetails['email']) . '<br>
                    <strong>Phone:</strong> ' . htmlspecialchars($billingDetails['phone']) . '
                </p>
                
                <h3>Billing Address:</h3>
                <p>
                    ' . htmlspecialchars($billingDetails['street_address_1']) . '<br>
                    ' . (empty($billingDetails['street_address_2']) ? '' : htmlspecialchars($billingDetails['street_address_2']) . '<br>') . '
                    ' . htmlspecialchars($billingDetails['city'] . ', ' . $billingDetails['state'] . ' ' . $billingDetails['postcode']) . '<br>
                    ' . htmlspecialchars($billingDetails['country']) . '
                </p>';
    } else {
        // Additional content for customer email
        $html .= '
                <p>If you have any questions about your cancellation, please don\'t hesitate to contact our customer support team.</p>
                <p>Thank you for your understanding.</p>';
    }
    
    $html .= '
            </div>
            <div class="footer">
                <p>&copy; ' . date('Y') . ' STR Works. All rights reserved.</p>
            </div>
        </div>
    </body>
    </html>';
    
    return $html;
}

/**
 * Send notification to users when a product they purchased is updated
 * 
 * @param int $productId The ID of the updated product
 * @param array $productDetails Product details after update
 * @return bool Success status
 */
function sendProductUpdateNotification($productId, $productDetails) {
    /** @var \PDO $pdo */
    global $pdo;
    
    if (!$pdo) {
        error_log("Cannot send product update notification: database connection error");
        return false;
    }
    
    try {
        // Get the product details if not provided
        if (empty($productDetails)) {
            $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
            $stmt->execute([$productId]);
            $productDetails = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$productDetails) {
                error_log("Cannot send product update notification: product not found");
                return false;
            }
        }
        
        // Get users who purchased this product
        $stmt = $pdo->prepare("
            SELECT DISTINCT b.email, b.first_name, b.last_name 
            FROM order_items oi
            JOIN orders o ON oi.order_id = o.id
            JOIN order_billing_details b ON o.id = b.order_id
            WHERE oi.product_id = ? 
            AND o.status != 'cancelled'
        ");
        $stmt->execute([$productId]);
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (empty($users)) {
            // No users have purchased this product yet
            return true;
        }
        
        // Get product image URL
        $imageUrl = '';
        $stmt = $pdo->prepare("
            SELECT image_path FROM product_images 
            WHERE product_id = ? AND is_primary = 1
            LIMIT 1
        ");
        $stmt->execute([$productId]);
        $imageResult = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($imageResult) {
            $imageUrl = 'assets/img/products/' . $imageResult['image_path'];
        }
        
        // Get brand information
        $brandName = '';
        if (!empty($productDetails['brand_id'])) {
            $stmt = $pdo->prepare("SELECT name FROM brands WHERE id = ?");
            $stmt->execute([$productDetails['brand_id']]);
            $brandResult = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($brandResult) {
                $brandName = $brandResult['name'];
            }
        }
        
        // Send email to each user
        $count = 0;
        foreach ($users as $user) {
            $subject = "Product Update: " . $productDetails['title'];
            $htmlBody = generateProductUpdateEmail($productDetails, $user, $brandName, $imageUrl);
            
            $result = sendEmailToCustomer($subject, $htmlBody, $user['email'], $user['first_name'] . ' ' . $user['last_name']);
            if ($result) {
                $count++;
            }
        }
        
        error_log("Product update notification sent to $count users");
        return $count > 0;
        
    } catch (PDOException $e) {
        error_log("Error sending product update notification: " . $e->getMessage());
        return false;
    }
}

/**
 * Generate HTML email for product update notification
 * 
 * @param array $product Product details
 * @param array $user User information
 * @param string $brandName Brand name
 * @param string $imageUrl Product image URL (optional)
 * @return string HTML email content
 */
function generateProductUpdateEmail($product, $user, $brandName, $imageUrl = '') {
    $baseUrl = getBaseUrlForEmail();
    $productUrl = $baseUrl . 'product.php?id=' . $product['id'];
    
    $html = '
    <!DOCTYPE html>
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; }
            .header { background-color: #000; color: #fff; padding: 20px; text-align: center; }
            .header h1 { color: #ff5c8d; margin: 0; }
            .content { padding: 20px; }
            .footer { padding: 20px; text-align: center; font-size: 12px; color: #777; }
            .product-info { margin-bottom: 20px; border: 1px solid #eee; padding: 15px; border-radius: 5px; }
            .product-image { text-align: center; margin-bottom: 15px; }
            .product-image img { max-width: 100%; height: auto; max-height: 300px; }
            .product-title { font-size: 20px; font-weight: bold; color: #000; margin-bottom: 10px; }
            .product-price { font-size: 18px; color: #ff5c8d; font-weight: bold; margin-bottom: 10px; }
            .product-brand { font-size: 14px; color: #666; margin-bottom: 15px; }
            .product-description { margin-bottom: 15px; }
            .btn { display: inline-block; background-color: #ff5c8d; color: white; text-decoration: none; padding: 10px 20px; border-radius: 5px; font-weight: bold; }
            .update-badge { background-color: #4CAF50; color: white; padding: 5px 10px; border-radius: 3px; display: inline-block; font-size: 12px; }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="header">
                <h1>STR Works</h1>
            </div>
            <div class="content">
                <h2>Product Update Notification</h2>
                <p>Hello ' . htmlspecialchars($user['first_name']) . ',</p>
                <p>We wanted to let you know that a product you previously purchased has been updated.</p>
                
                <div class="product-info">
                    <span class="update-badge">Updated</span>';
    
    if (!empty($imageUrl)) {
        $html .= '
                    <div class="product-image">
                        <img src="' . $baseUrl . $imageUrl . '" alt="' . htmlspecialchars($product['title']) . '">
                    </div>';
    }
    
    $html .= '
                    <div class="product-title">' . htmlspecialchars($product['title']) . '</div>
                    <div class="product-price">₹' . number_format($product['amount'], 2) . '</div>';
    
    if (!empty($brandName)) {
        $html .= '
                    <div class="product-brand">Brand: ' . htmlspecialchars($brandName) . '</div>';
    }
    
    $html .= '
                    <div class="product-description">' . nl2br(htmlspecialchars($product['description'])) . '</div>
                    <a href="' . $productUrl . '" class="btn">View Product</a>
                </div>
                
                <p>Thank you for being a valued customer!</p>
            </div>
            <div class="footer">
                <p>&copy; ' . date('Y') . ' STR Works. All rights reserved.</p>
            </div>
        </div>
    </body>
    </html>';
    
    return $html;
}

/**
 * Send notification to user when their order status is updated
 * 
 * @param int $orderId The order ID
 * @param string $newStatus The new order status
 * @return bool Success status
 */
function sendOrderStatusUpdateEmail($orderId, $newStatus) {
    /** @var \PDO $pdo */
    global $pdo;
    
    if (!$pdo) {
        error_log("Cannot send order status update email: database connection error");
        return false;
    }
    
    // Don't send for cancellations (we have a separate function for that)
    if ($newStatus === 'cancelled') {
        return true;
    }
    
    try {
        // Get order details
        $stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ?");
        $stmt->execute([$orderId]);
        $order = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$order) {
            error_log("Cannot send order status update email: order not found");
            return false;
        }
        
        // Get order items
        $orderItems = getOrderItems($orderId);
        
        // Get billing details which contains the customer's email
        $billingDetails = getOrderBillingDetails($orderId);
        
        if (empty($billingDetails)) {
            error_log("Cannot send order status update email: billing details not found");
            return false;
        }
        
        // Get payment transaction details if available
        $transactionDetails = null;
        if ($order['payment_method'] === 'razorpay') {
            $transactionDetails = getLatestPaymentTransaction($orderId);
        }
        
        // Prepare email data
        $customerName = $billingDetails['first_name'] . ' ' . $billingDetails['last_name'];
        $customerEmail = $billingDetails['email'];
        
        // Create the email subject and content based on the new status
        $subject = "Order #" . $order['order_number'] . " Status Update: " . ucfirst($newStatus);
        $html = generateOrderStatusUpdateEmail($order, $orderItems, $billingDetails, $newStatus, $transactionDetails);
        
        // Send the email to the customer
        return sendEmailToCustomer($subject, $html, $customerEmail, $customerName);
        
    } catch (PDOException $e) {
        error_log("Error sending order status update email: " . $e->getMessage());
        return false;
    }
}

/**
 * Generate HTML email for order status update notification
 * 
 * @param array $order Order details
 * @param array $orderItems Order items
 * @param array $billingDetails Billing details
 * @param string $newStatus New order status
 * @param array|null $transactionDetails Payment transaction details (optional)
 * @return string HTML email content
 */
function generateOrderStatusUpdateEmail($order, $orderItems, $billingDetails, $newStatus, $transactionDetails = null) {
    // Status-specific information
    $statusInfo = [
        'pending' => [
            'icon' => 'clock',
            'color' => '#FF9800',
            'title' => 'Your Order is Pending',
            'message' => 'Thank you for your order. We have received your order and are reviewing it. You will receive another notification when your order is processed.'
        ],
        'processing' => [
            'icon' => 'cog',
            'color' => '#2196F3',
            'title' => 'Your Order is Being Processed',
            'message' => 'Good news! We are now processing your order. You will receive another notification when your order is shipped.'
        ],
        'shipped' => [
            'icon' => 'truck',
            'color' => '#9C27B0',
            'title' => 'Your Order Has Been Shipped',
            'message' => 'Great news! Your order has been shipped and is on its way to you. You can track your order with the details below.'
        ],
        'delivered' => [
            'icon' => 'check-circle',
            'color' => '#4CAF50',
            'title' => 'Your Order Has Been Delivered',
            'message' => 'Fantastic! Your order has been delivered. We hope you enjoy your purchase. Thank you for shopping with us!'
        ],
        'refunded' => [
            'icon' => 'undo',
            'color' => '#607D8B',
            'title' => 'Your Order Has Been Refunded',
            'message' => 'Your order has been refunded. The refund amount will be credited to your original payment method within 5-7 business days.'
        ]
    ];
    
    // Default values if status is not in our predefined list
    $statusData = $statusInfo[$newStatus] ?? [
        'icon' => 'info-circle',
        'color' => '#333333',
        'title' => 'Order Status Update: ' . ucfirst($newStatus),
        'message' => 'Your order status has been updated to ' . ucfirst($newStatus) . '.'
    ];
    
    $html = '
    <!DOCTYPE html>
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; }
            .header { background-color: #000; color: #fff; padding: 20px; text-align: center; }
            .header h1 { color: #ff5c8d; margin: 0; }
            .content { padding: 20px; }
            .order-info { margin-bottom: 30px; }
            .order-items { width: 100%; border-collapse: collapse; margin-bottom: 30px; }
            .order-items th, .order-items td { padding: 10px; text-align: left; border-bottom: 1px solid #ddd; }
            .order-items th { background-color: #f5f5f5; }
            .footer { padding: 20px; text-align: center; font-size: 12px; color: #777; }
            .total-row { font-weight: bold; }
            .pink { color: #ff5c8d; }
            .status-badge { background-color: ' . $statusData['color'] . '; color: white; padding: 8px 15px; border-radius: 5px; display: inline-block; margin-bottom: 20px; }
            .status-section { background-color: #f9f9f9; padding: 20px; border-radius: 5px; margin-bottom: 20px; border-left: 5px solid ' . $statusData['color'] . '; }
            .status-icon { font-size: 24px; margin-right: 10px; vertical-align: middle; }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="header">
                <h1>STR Works</h1>
            </div>
            <div class="content">
                <h2>' . $statusData['title'] . '</h2>
                <p>Hello ' . htmlspecialchars($billingDetails['first_name']) . ',</p>
                
                <div class="status-section">
                    <p><i class="fas fa-' . $statusData['icon'] . ' status-icon"></i> <span class="status-badge">' . ucfirst($newStatus) . '</span></p>
                    <p>' . $statusData['message'] . '</p>
                </div>
                
                <div class="order-info">
                    <p><strong>Order Number:</strong> ' . htmlspecialchars($order['order_number']) . '</p>
                    <p><strong>Order Date:</strong> ' . date('F j, Y', strtotime($order['created_at'])) . '</p>
                    <p><strong>Payment Method:</strong> ' . getPaymentMethodName($order['payment_method']) . '</p>';
    
    // Add payment status and transaction ID for online payments
    if ($order['payment_method'] === 'razorpay') {
        $statusClass = ($order['payment_status'] === 'completed') ? 'payment-success' : 'payment-pending';
        $html .= '
                    <p><strong>Payment Status:</strong> <span class="' . $statusClass . '">' . ucfirst($order['payment_status']) . '</span></p>';
        
        // Add transaction ID if available
        if ($transactionDetails && !empty($transactionDetails['transaction_id'])) {
            $html .= '
                    <p><strong>Transaction ID:</strong> ' . htmlspecialchars($transactionDetails['transaction_id']) . '</p>';
        }
    }
    
    $html .= '
                </div>
                
                <h3>Order Items:</h3>
                <table class="order-items">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Quantity</th>
                            <th>Price</th>
                            <th>Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>';
    
    foreach ($orderItems as $item) {
        $html .= '
                        <tr>
                            <td>' . htmlspecialchars($item['title']) . '</td>
                            <td>' . $item['quantity'] . '</td>
                            <td>₹' . number_format($item['price'], 2) . '</td>
                            <td>₹' . number_format($item['subtotal'], 2) . '</td>
                        </tr>';
    }
    
    $html .= '
                        <tr>
                            <td colspan="3" align="right"><strong>Subtotal:</strong></td>
                            <td>₹' . number_format($order['subtotal'], 2) . '</td>
                        </tr>';
                        
    if (!empty($order['shipping_cost']) && $order['shipping_cost'] > 0) {
        $html .= '
                        <tr>
                            <td colspan="3" align="right"><strong>Shipping:</strong></td>
                            <td>₹' . number_format($order['shipping_cost'], 2) . '</td>
                        </tr>';
    }
    
    $html .= '
                        <tr class="total-row">
                            <td colspan="3" align="right"><strong>Total:</strong></td>
                            <td>₹' . number_format($order['total'], 2) . '</td>
                        </tr>
                    </tbody>
                </table>
                
                <h3>Shipping Address:</h3>
                <p>
                    ' . htmlspecialchars($billingDetails['first_name'] . ' ' . $billingDetails['last_name']) . '<br>
                    ' . (empty($billingDetails['company_name']) ? '' : htmlspecialchars($billingDetails['company_name']) . '<br>') . '
                    ' . htmlspecialchars($billingDetails['street_address_1']) . '<br>
                    ' . (empty($billingDetails['street_address_2']) ? '' : htmlspecialchars($billingDetails['street_address_2']) . '<br>') . '
                    ' . htmlspecialchars($billingDetails['city'] . ', ' . $billingDetails['state'] . ' ' . $billingDetails['postcode']) . '<br>
                    ' . htmlspecialchars($billingDetails['country']) . '<br>
                    <strong>Phone:</strong> ' . htmlspecialchars($billingDetails['phone']) . '
                </p>
                
                <p>If you have any questions about your order, please contact our customer support team.</p>
                <p>Thank you for shopping with us!</p>
            </div>
            <div class="footer">
                <p>&copy; ' . date('Y') . ' STR Works. All rights reserved.</p>
            </div>
        </div>
    </body>
    </html>';
    
    return $html;
}

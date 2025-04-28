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
        $mail->Username   = 'djangochatbox@gmail.com';
        $mail->Password   = 'mbmk cavq qzpv gqai'; 
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;  

        $adminEmail = "xaioene@gmail.com";

        $mail->setFrom('djangochatbox@gmail.com', 'NetPy');

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
        $mail->Username   = 'djangochatbox@gmail.com';
        $mail->Password   = 'mbmk cavq qzpv gqai'; 
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;  

        $mail->setFrom('djangochatbox@gmail.com', 'STR Works');
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

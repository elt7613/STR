<?php 
$pageTitle = 'Order Received - STR Works';
$custom_css = 'order-received.css';
require_once ROOT_PATH . '/app/views/partials/header.php'; 
?>

<div class="container order-received-container">
    <h1 class="page-title">Order Received</h1>
    
    <!-- Progress Steps -->
    <div class="progress-steps">
        <div class="step">
            <div class="step-number">1</div>
            <span class="step-text">Shopping Cart</span>
            <div class="arrow">→</div>
        </div>
        <div class="step">
            <div class="step-number">2</div>
            <span class="step-text">Payment & Delivery Options</span>
            <div class="arrow">→</div>
        </div>
        <div class="step active">
            <div class="step-number pink">3</div>
            <span class="step-text">Order Received</span>
        </div>
    </div>
    
    <div class="order-confirmation">
        <div class="confirmation-icon">
            <i class="fas fa-check-circle"></i>
        </div>
        
        <h2>Thank You. Your Order Has Been Received.</h2>
        
        <div class="order-info">
            <div class="info-item">
                <span class="info-label">Order Number:</span>
                <span class="info-value"><?php echo htmlspecialchars($order['order_number']); ?></span>
            </div>
            
            <div class="info-item">
                <span class="info-label">Date:</span>
                <span class="info-value"><?php echo date('F j, Y', strtotime($order['created_at'])); ?></span>
            </div>
            
            <div class="info-item">
                <span class="info-label">Total:</span>
                <span class="info-value">$<?php echo number_format($order['total'], 2); ?></span>
            </div>
            
            <div class="info-item">
                <span class="info-label">Payment Method:</span>
                <span class="info-value">
                    <?php 
                    if ($order['payment_method'] === 'cod') {
                        echo 'Cash on Delivery';
                    } elseif ($order['payment_method'] === 'razorpay') {
                        echo 'Razorpay';
                        
                        // Display transaction ID if available
                        if (isset($paymentTransaction) && !empty($paymentTransaction['transaction_id'])) {
                            echo '<br><span class="transaction-id">Transaction ID: ' . htmlspecialchars($paymentTransaction['transaction_id']) . '</span>';
                        }
                    } else {
                        echo htmlspecialchars($order['payment_method']);
                    }
                    ?>
                </span>
            </div>
        </div>
        
        <div class="order-details">
            <h3>Order Details</h3>
            
            <table class="order-items-table">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orderItems as $item): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($item['title']); ?> × <?php echo $item['quantity']; ?></td>
                            <td>$<?php echo number_format($item['subtotal'], 2); ?></td>
                        </tr>
                    <?php endforeach; ?>
                    
                    <tr class="subtotal-row">
                        <td>Subtotal:</td>
                        <td>$<?php echo number_format($order['subtotal'], 2); ?></td>
                    </tr>
                    
                    <tr class="shipping-row">
                        <td>Shipping:</td>
                        <td>$<?php echo number_format($order['shipping_cost'], 2); ?></td>
                    </tr>
                    
                    <tr class="total-row">
                        <td>Total:</td>
                        <td>$<?php echo number_format($order['total'], 2); ?></td>
                    </tr>
                </tbody>
            </table>
        </div>
        
        <div class="customer-details">
            <h3>Customer Details</h3>
            
            <div class="details-columns">
                <div class="details-column">
                    <h4>Billing Address</h4>
                    <?php if ($billingDetails): ?>
                        <p>
                            <?php echo htmlspecialchars($billingDetails['first_name'] . ' ' . $billingDetails['last_name']); ?><br>
                            <?php if (!empty($billingDetails['company_name'])): ?>
                                <?php echo htmlspecialchars($billingDetails['company_name']); ?><br>
                            <?php endif; ?>
                            <?php echo htmlspecialchars($billingDetails['street_address_1']); ?><br>
                            <?php if (!empty($billingDetails['street_address_2'])): ?>
                                <?php echo htmlspecialchars($billingDetails['street_address_2']); ?><br>
                            <?php endif; ?>
                            <?php echo htmlspecialchars($billingDetails['city'] . ', ' . $billingDetails['state'] . ' ' . $billingDetails['postcode']); ?><br>
                            <?php echo htmlspecialchars($billingDetails['country']); ?><br>
                            <br>
                            <strong>Email:</strong> <?php echo htmlspecialchars($billingDetails['email']); ?><br>
                            <strong>Phone:</strong> <?php echo htmlspecialchars($billingDetails['phone']); ?>
                        </p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <div class="order-actions">
            <a href="shop.php" class="continue-shopping-btn">Continue Shopping</a>
        </div>
    </div>
</div>

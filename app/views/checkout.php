<?php 
$pageTitle = 'Checkout - STR Works';
$custom_css = 'checkout.css';
require_once ROOT_PATH . '/app/views/partials/header.php'; 
?>

<div class="container checkout-container">
    <h1 class="page-title">Checkout</h1>
    
    <!-- Progress Steps -->
    <div class="progress-steps">
        <div class="step">
            <div class="step-number">1</div>
            <span class="step-text">Shopping Cart</span>
            <div class="arrow">→</div>
        </div>
        <div class="step active">
            <div class="step-number pink">2</div>
            <span class="step-text">Payment & Delivery Options</span>
            <div class="arrow">→</div>
        </div>
        <div class="step">
            <div class="step-number">3</div>
            <span class="step-text">Order Received</span>
        </div>
    </div>
    
    <?php if (!empty($error)): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    
    <div class="checkout-content">
        <!-- Billing Details Form -->
        <div class="billing-details">
            <h2>Billing details</h2>
            
            <form method="post" id="checkout-form">
                <div class="form-row">
                    <div class="form-group">
                        <label for="first_name">First name <span class="required">*</span></label>
                        <input type="text" id="first_name" name="first_name" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="last_name">Last name <span class="required">*</span></label>
                        <input type="text" id="last_name" name="last_name" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="company_name">Company name (optional)</label>
                    <input type="text" id="company_name" name="company_name">
                </div>
                
                <div class="form-group">
                    <label for="country">Country / Region <span class="required">*</span></label>
                    <select id="country" name="country" required>
                        <option value="">Select a country...</option>
                        <option value="US">United States</option>
                        <option value="CA">Canada</option>
                        <option value="GB">United Kingdom</option>
                        <option value="IN">India</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="street_address_1">Street address <span class="required">*</span></label>
                    <input type="text" id="street_address_1" name="street_address_1" placeholder="House number and street name" required>
                </div>
                
                <div class="form-group">
                    <input type="text" id="street_address_2" name="street_address_2" placeholder="Apartment, suite, unit, etc. (optional)">
                </div>
                
                <div class="form-group">
                    <label for="city">Town / City <span class="required">*</span></label>
                    <input type="text" id="city" name="city" required>
                </div>
                
                <div class="form-group">
                    <label for="state">State / County <span class="required">*</span></label>
                    <input type="text" id="state" name="state" required>
                </div>
                
                <div class="form-group">
                    <label for="postcode">Postcode / ZIP <span class="required">*</span></label>
                    <input type="text" id="postcode" name="postcode" required>
                </div>
                
                <div class="form-group">
                    <label for="phone">Phone <span class="required">*</span></label>
                    <input type="tel" id="phone" name="phone" required>
                </div>
                
                <div class="form-group">
                    <label for="email">Email address <span class="required">*</span></label>
                    <input type="email" id="email" name="email" required>
                </div>
                
                <div class="form-group">
                    <h3>Additional information</h3>
                    <label for="order_notes">Order notes (optional)</label>
                    <textarea id="order_notes" name="order_notes" placeholder="Notes about your order, e.g. special notes for delivery"></textarea>
                </div>
            </form>
        </div>
        
        <!-- Order Summary -->
        <div class="order-summary">
            <h2>Your order</h2>
            
            <div class="order-details">
                <?php foreach ($cartItems as $item): ?>
                    <div class="order-item">
                        <span class="item-name"><?php echo htmlspecialchars($item['title']); ?> × <?php echo $item['quantity']; ?></span>
                        <span class="item-price">₹<?php echo number_format($item['amount'] * $item['quantity'], 2); ?></span>
                    </div>
                <?php endforeach; ?>
                
                <div class="order-subtotal">
                    <span>Subtotal</span>
                    <span>₹<?php echo number_format($cartSubtotal, 2); ?></span>
                </div>

                <?php if ($isPremiumUser && $discountAmount > 0): ?>
                <div class="order-discount premium-discount">
                    <span>Premium Discount (<?php echo number_format($discountPercentage, 0); ?>%)</span>
                    <span>-₹<?php echo number_format($discountAmount, 2); ?></span>
                </div>
                <?php endif; ?>
                
                <div class="order-subtotal">
                    <span>GST (18%)</span>
                    <span>₹<?php echo number_format($gstAmount, 2); ?></span>
                </div>
                
                <div class="order-total">
                    <span>Total</span>
                    <span>₹<?php echo number_format($cartTotal, 2); ?></span>
                </div>
                
                <?php if (!$isPremiumUser): ?>
                <div class="premium-note">
                    <p>Premium members receive a <?php echo number_format(getPremiumDiscountPercentage(), 0); ?>% discount. <a href="register.php?premium=1">Join now</a> or <a href="login.php">login</a> to your premium account.</p>
                </div>
                <?php endif; ?>
            </div>
            
            <div class="payment-methods">
                <h2>Payment</h2>
                
                <!-- <div class="payment-method">
                    <label for="payment_cod">
                        <input type="radio" id="payment_cod" name="payment_method" value="cod" form="checkout-form">
                        <span>Cash on delivery</span>
                    </label>
                    <p class="payment-description">Pay with cash upon delivery</p>
                </div> -->
                
                <div class="payment-method">
                    <label for="payment_phonepe">
                        <input type="radio" id="payment_phonepe" name="payment_method" value="phonepe" form="checkout-form" checked>
                        <span>PhonePe</span>
                    </label>
                    <div class="payment-logo">
                        <img src="assets/img/phonepe-logo.svg" alt="PhonePe" class="phonepe-logo">
                    </div>
                    <p class="payment-description">Pay securely with PhonePe. Credit/Debit Cards, Net Banking, UPI, and other payment methods supported.</p>
                </div>
                
                <button type="submit" class="place-order-btn" form="checkout-form" id="place-order-btn">Place order</button>
            </div>
        </div>
    </div>
</div>

<script src="assets/js/checkout.js" defer></script>
<script>
// Add additional debugging for the form submission
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('checkout-form');
    const submitBtn = document.getElementById('place-order-btn');
    
    if (form && submitBtn) {
        form.addEventListener('submit', function(e) {
            console.log('Form submitted');
            
            // Ensure payment method is selected
            const paymentMethod = document.querySelector('input[name="payment_method"]:checked');
            console.log('Selected payment method:', paymentMethod ? paymentMethod.value : 'none');
            
            if (!paymentMethod) {
                e.preventDefault();
                alert('Please select a payment method');
                return false;
            }
            
            // Add hidden input for payment method if it's not part of the form
            if (!document.querySelector('input[name="payment_method"]')) {
                const paymentInput = document.createElement('input');
                paymentInput.type = 'hidden';
                paymentInput.name = 'payment_method';
                paymentInput.value = 'phonepe'; // Default to PhonePe
                form.appendChild(paymentInput);
            }
            
            // Display loading state
            submitBtn.innerHTML = 'Processing...';
            submitBtn.disabled = true;
        });
    }
});
</script>

<style>
.premium-discount {
    background-color: #f8f2ff;
    padding: 10px;
    margin-bottom: 8px;
    border-radius: 5px;
    color: #6a1b9a;
    font-weight: bold;
}

.premium-note {
    background-color: #f8f8f8;
    padding: 12px;
    margin-top: 15px;
    border-radius: 5px;
    font-size: 0.9em;
    border-left: 3px solid #6a1b9a;
}

.premium-note a {
    color: #6a1b9a;
    text-decoration: underline;
}
</style>

<?php require_once __DIR__ . '/partials/footer.php'; ?>

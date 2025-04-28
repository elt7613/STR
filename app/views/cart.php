<?php 
$pageTitle = 'Shopping Cart - STR Works';
$custom_css = 'cart.css';
require_once ROOT_PATH . '/app/views/partials/header.php'; 
?>

<!-- Cart Page Section -->
<div class="container cart-page-container">
    <h1 class="page-title">Shopping Cart</h1>
    
    <!-- Progress Steps - Updated to match shop.html -->
    <div class="progress-steps">
        <div class="step active">
            <div class="step-number pink">1</div>
            <span class="step-text">Shopping Cart</span>
            <div class="arrow">→</div>
        </div>
        <div class="step">
            <div class="step-number">2</div>
            <span class="step-text">Payment & Delivery Options</span>
            <div class="arrow">→</div>
        </div>
        <div class="step">
            <div class="step-number">3</div>
            <span class="step-text">Order Received</span>
        </div>
    </div>
    
    <?php if (count($cartItems) > 0): ?>
        <!-- Cart Table -->
        <div class="cart-table">
            <div class="cart-header">
                <div class="col-6">Product</div>
                <div class="col-2">Price</div>
                <div class="col-2">Quantity</div>
                <div class="col-1">Subtotal</div>
                <div class="col-1">Remove</div>
            </div>
            
            <div id="cart-items-container">
                <?php foreach ($cartItems as $item): ?>
                    <div class="cart-item" data-id="<?php echo $item['id']; ?>">
                        <div class="product-info">
                            <a href="product.php?id=<?php echo $item['product_id']; ?>">
                                <img src="<?php echo !empty($item['image_path']) ? htmlspecialchars($item['image_path']) : 'assets/img/product-placeholder.jpg'; ?>" 
                                    alt="<?php echo htmlspecialchars($item['title']); ?>" 
                                    class="product-image">
                            </a>
                            <span class="product-name"><?php echo htmlspecialchars($item['title']); ?></span>
                        </div>
                        <div class="product-price">$<?php echo number_format($item['amount'], 2); ?></div>
                        <div class="quantity-control">
                            <input type="number" 
                                  value="<?php echo $item['quantity']; ?>" 
                                  class="quantity-input" 
                                  min="1" 
                                  max="99"
                                  data-item-id="<?php echo $item['id']; ?>">
                            <div class="quantity-buttons">
                                <button class="quantity-button increment" data-item-id="<?php echo $item['id']; ?>">▲</button>
                                <button class="quantity-button decrement" data-item-id="<?php echo $item['id']; ?>">▼</button>
                            </div>
                        </div>
                        <div class="product-subtotal">$<?php echo number_format($item['amount'] * $item['quantity'], 2); ?></div>
                        <div>
                            <button class="remove-item" data-item-id="<?php echo $item['id']; ?>">✕</button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        
        <!-- Coupon and Actions -->
        <div class="actions-row">
            <div class="coupon-section">
                <input type="text" placeholder="Coupon Code" class="coupon-input">
                <button class="coupon-button">APPLY COUPON</button>
            </div>
            <div class="cart-actions">
                <button class="continue-shopping" onclick="window.location.href='shop.php'">CONTINUE SHOPPING</button>
                <button class="update-cart" id="update-cart-btn">UPDATE CART</button>
            </div>
        </div>
        
        <!-- Cart Totals - Updated to match shop.html structure -->
        <div class="cart-totals">
            <h2>Cart totals</h2>
            <div class="totals-content">
                <div class="subtotal-row">
                    <span>Subtotal</span>
                    <span id="cart-subtotal">$<?php echo number_format($cartSubtotal, 2); ?></span>
                </div>
                <div class="subtotal-row">
                    <span>Shipping</span>
                    <span>Calculated at checkout</span>
                </div>
                <div class="total-row">
                    <span>Total</span>
                    <span id="cart-total" class="bold">$<?php echo number_format($cartSubtotal, 2); ?></span>
                </div>
            </div>
            <button class="checkout-button" onclick="window.location.href='checkout.php'">PROCEED TO CHECKOUT</button>
        </div>
    <?php else: ?>
        <!-- Empty Cart Message -->
        <div class="empty-cart">
            <div class="empty-cart-icon">
                <i class="fas fa-shopping-cart"></i>
            </div>
            <h2>Your cart is empty</h2>
            <p>Looks like you haven't added any products to your cart yet.</p>
            <a href="shop.php" class="continue-shopping-btn">CONTINUE SHOPPING</a>
        </div>
    <?php endif; ?>
</div>

<script src="assets/js/cart-page.js" defer></script>

<?php require_once ROOT_PATH . '/app/views/partials/footer.php'; ?> 
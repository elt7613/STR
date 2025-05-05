<?php 
$pageTitle = htmlspecialchars($product['title']) . ' - STR Works';
$custom_css = 'product.css';
require_once ROOT_PATH . '/app/views/partials/header.php'; 
?>

<!-- Product Detail Section -->
<div class="container product-container">
    <div class="product-content row">
        <!-- Product Gallery -->
        <div class="col-md-6 product-gallery">
            <div class="main-image-wrapper">
                <?php if (!empty($productImages)): ?>
                    <?php 
                    // Find primary image
                    $primaryImage = null;
                    foreach ($productImages as $image) {
                        if ($image['is_primary'] == 1) {
                            $primaryImage = $image;
                            break;
                        }
                    }
                    // If no primary image found, use the first image
                    if (!$primaryImage && !empty($productImages)) {
                        $primaryImage = $productImages[0];
                    }
                    ?>
                    
                    <img id="main-product-image" src="<?php echo !empty($primaryImage) ? htmlspecialchars($primaryImage['image_path']) : 'assets/img/product-placeholder.jpg'; ?>" class="main-image" alt="<?php echo htmlspecialchars($product['title']); ?>">
                </div>
                
                <div class="thumbnail-container">
                    <?php foreach ($productImages as $index => $image): ?>
                        <div class="thumbnail-wrapper <?php echo ($primaryImage && $image['id'] == $primaryImage['id']) ? 'active' : ''; ?>">
                            <img src="<?php echo htmlspecialchars($image['image_path']); ?>" class="thumbnail" alt="<?php echo htmlspecialchars($product['title']); ?> - Image <?php echo $index + 1; ?>" onclick="changeMainImage(this)">
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <img src="assets/img/product-placeholder.jpg" class="main-image" alt="<?php echo htmlspecialchars($product['title']); ?>">
            </div>
            <?php endif; ?>
        </div>
        
        <!-- Product Info -->
        <div class="col-md-6 product-info">
            <h1 class="product-title"><?php echo htmlspecialchars($product['title']); ?></h1>
            
            <?php if (!empty($product['brand_id']) && !empty($product['brand_name'])): ?>
            <div class="product-brand">
                <span class="brand-label">Brand:</span>
                <a href="brand.php?id=<?php echo $product['brand_id']; ?>" class="brand-value"><?php echo htmlspecialchars($product['brand_name']); ?></a>
            </div>
            <?php endif; ?>
            
            <?php if (!empty($productCategories)): ?>
            <div class="product-categories">
                <?php foreach ($productCategories as $category): ?>
                    <span class="category-badge"><?php echo htmlspecialchars($category['name']); ?></span>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
            
            <div class="price-container">
                <p class="product-price">₹<?php echo number_format($product['amount'], 2); ?></p>
            </div>
            
            <div class="product-description">
                <?php echo nl2br(htmlspecialchars($product['description'])); ?>
            </div>
            
            <?php if (!empty($product['make_name']) || !empty($product['model_name']) || !empty($product['series_name']) || !empty($product['device_name'])): ?>
            <div class="vehicle-compatibility">
                <h4 class="compatibility-title">Vehicle Compatibility</h4>
                <div class="compatibility-details">
                    <?php if (!empty($product['make_name'])): ?>
                    <div class="compatibility-item">
                        <span class="compatibility-label">Make:</span>
                        <span class="compatibility-value"><?php echo htmlspecialchars($product['make_name']); ?></span>
                    </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($product['model_name'])): ?>
                    <div class="compatibility-item">
                        <span class="compatibility-label">Model:</span>
                        <span class="compatibility-value"><?php echo htmlspecialchars($product['model_name']); ?></span>
                    </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($product['series_name'])): ?>
                    <div class="compatibility-item">
                        <span class="compatibility-label">Series:</span>
                        <span class="compatibility-value"><?php echo htmlspecialchars($product['series_name']); ?></span>
                    </div>
                    <?php endif; ?>

                    <?php if (!empty($product['device_name'])): ?>
                    <div class="compatibility-item">
                        <span class="compatibility-label">Device:</span>
                        <span class="compatibility-value"><?php echo htmlspecialchars($product['device_name']); ?></span>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>
            
            <div class="product-actions">
                <div class="quantity-selector">
                    <button class="quantity-btn minus-btn" type="button">-</button>
                    <input type="number" class="quantity-input" id="product-quantity" value="1" min="1" max="99">
                    <button class="quantity-btn plus-btn" type="button">+</button>
                </div>
                
                <div class="btn-group">
                    <?php if (isset($product['direct_buying']) && $product['direct_buying'] == 1): ?>
                        <button class="btn buy-now-btn" id="addToCartBtn" data-product-id="<?php echo $product['id']; ?>">
                            <i class="fas fa-shopping-cart"></i> Add to Cart
                        </button>
                    <?php else: ?>
                        <?php if (isLoggedIn()): ?>
                            <button class="btn request-btn" id="requestProductBtn" data-product-id="<?php echo $product['id']; ?>">
                                <i class="fas fa-envelope"></i> Request Product
                            </button>
                        <?php else: ?>
                            <a href="login.php?redirect=<?php echo urlencode('product.php?id=' . $product['id']); ?>" class="btn request-btn">
                                <i class="fas fa-sign-in-alt"></i> Login to Request
                            </a>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Related Products Section -->
<?php if (!empty($relatedProducts)): ?>
<div class="related-products">
    <div class="container">
        <h2 class="section-title">Related Products</h2>
        
        <div class="grid-container">
            <?php foreach ($relatedProducts as $relatedProduct): ?>
                <div class="product-card">
                    <a href="product.php?id=<?php echo $relatedProduct['id']; ?>">
                        <div class="product-image-container">
                            <img src="<?php echo !empty($relatedProduct['primary_image']) ? htmlspecialchars($relatedProduct['primary_image']) : 'assets/img/product-placeholder.jpg'; ?>" alt="<?php echo htmlspecialchars($relatedProduct['title']); ?>">
                        </div>
                        <div class="product-info">
                            <h5 class="product-title"><?php echo htmlspecialchars($relatedProduct['title']); ?></h5>
                            <div class="product-price">₹<?php echo number_format($relatedProduct['amount'], 2); ?></div>
                        </div>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
<?php endif; ?>

<script>
    // Change main image when thumbnail is clicked
    function changeMainImage(thumbnail) {
        document.getElementById('main-product-image').src = thumbnail.src;
        
        // Update active thumbnail
        document.querySelectorAll('.thumbnail-wrapper').forEach(wrapper => {
            wrapper.classList.remove('active');
        });
        thumbnail.closest('.thumbnail-wrapper').classList.add('active');
    }
    
    // Handle quantity buttons
    document.addEventListener('DOMContentLoaded', function() {
        const minusBtn = document.querySelector('.minus-btn');
        const plusBtn = document.querySelector('.plus-btn');
        const quantityInput = document.querySelector('.quantity-input');
        
        minusBtn.addEventListener('click', function() {
            const currentValue = parseInt(quantityInput.value);
            if (currentValue > 1) {
                quantityInput.value = currentValue - 1;
            }
        });
        
        plusBtn.addEventListener('click', function() {
            const currentValue = parseInt(quantityInput.value);
            if (currentValue < 99) {
                quantityInput.value = currentValue + 1;
            }
        });
        
        // Add to cart button
        const addToCartBtn = document.getElementById('addToCartBtn');
        if (addToCartBtn) {
            addToCartBtn.addEventListener('click', function() {
                const productId = this.getAttribute('data-product-id');
                const quantity = parseInt(document.getElementById('product-quantity').value);
                
                // Show loading state
                this.disabled = true;
                this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Adding to Cart...';
                
                // Call addToCart function from cart.js
                addToCart(productId, quantity, function(success, data) {
                    if (success) {
                        // Show success state
                        addToCartBtn.innerHTML = '<i class="fas fa-check"></i> Added to Cart';
                        setTimeout(() => {
                            addToCartBtn.disabled = false;
                            addToCartBtn.innerHTML = '<i class="fas fa-shopping-cart"></i> Add to Cart';
                        }, 2000);
                        
                        // Create success message
                        const alertDiv = document.createElement('div');
                        alertDiv.className = 'alert alert-success mt-3';
                        alertDiv.innerHTML = 'Product added to your cart!';
                        
                        // Insert alert after button
                        addToCartBtn.closest('.product-actions').insertAdjacentElement('afterend', alertDiv);
                        
                        // Auto-hide alert after 3 seconds
                        setTimeout(() => {
                            alertDiv.style.transition = 'opacity 0.5s';
                            alertDiv.style.opacity = '0';
                            setTimeout(() => alertDiv.remove(), 500);
                        }, 3000);
                    } else {
                        // Show error state
                        addToCartBtn.disabled = false;
                        addToCartBtn.innerHTML = '<i class="fas fa-shopping-cart"></i> Add to Cart';
                        
                        // Create error message
                        const alertDiv = document.createElement('div');
                        alertDiv.className = 'alert alert-danger mt-3';
                        alertDiv.innerHTML = 'Error adding to cart: ' + (data.message || 'Unknown error');
                        
                        // Insert alert after button
                        addToCartBtn.closest('.product-actions').insertAdjacentElement('afterend', alertDiv);
                        
                        // Auto-hide alert after 5 seconds
                        setTimeout(() => {
                            alertDiv.style.transition = 'opacity 0.5s';
                            alertDiv.style.opacity = '0';
                            setTimeout(() => alertDiv.remove(), 500);
                        }, 5000);
                    }
                });
            });
        }
        
        // Request product button
        const requestBtn = document.getElementById('requestProductBtn');
        if (requestBtn) {
            requestBtn.addEventListener('click', function() {
                const productId = this.getAttribute('data-product-id');
                
                // Show loading state
                this.disabled = true;
                this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Sending Request...';
                
                // Try using the debug endpoint first to test if basic AJAX is working
                fetch('debug_request.php')
                    .then(response => {
                        console.log('Debug response status:', response.status);
                        return response.json();
                    })
                    .then(debugData => {
                        console.log('Debug response:', debugData);
                        
                        // If debug worked, try the actual request
                        return fetch('send_product_request.php?product_id=' + productId);
                    })
                    .then(response => {
                        console.log('Product request status:', response.status);
                        if (!response.ok) {
                            throw new Error('Server returned ' + response.status + ': ' + response.statusText);
                        }
                        return response.json();
                    })
                    .then(data => {
                        this.disabled = false;
                        
                        if (data.success) {
                            // Show success message
                            this.innerHTML = '<i class="fas fa-check"></i> Request Sent';
                            this.classList.remove('request-btn');
                            this.classList.add('request-sent-btn');
                            
                            // Create alert message
                            const alertDiv = document.createElement('div');
                            alertDiv.className = 'alert alert-success mt-3';
                            alertDiv.innerHTML = 'Your request has been sent to our team. We will contact you soon.';
                            
                            // Insert alert after button
                            this.closest('.product-actions').insertAdjacentElement('afterend', alertDiv);
                            
                            // Auto-hide alert after 5 seconds
                            setTimeout(() => {
                                alertDiv.style.transition = 'opacity 0.5s';
                                alertDiv.style.opacity = '0';
                                setTimeout(() => alertDiv.remove(), 500);
                            }, 5000);
                        } else {
                            // Show error
                            this.innerHTML = '<i class="fas fa-envelope"></i> Request';
                            alert('Failed to send request: ' + (data.message || 'Unknown error'));
                        }
                    })
                    .catch(error => {
                        console.error('Error details:', error);
                        this.disabled = false;
                        this.innerHTML = '<i class="fas fa-envelope"></i> Request';
                        alert('Error sending request. Please try again. Details: ' + error.message);
                    });
            });
        }
    });
</script>

<style>
    /* Enhanced product price styles */
    .price-container {
        margin: 25px 0;
        padding: 10px 15px;
        background-color: #f8f9fa;
        border-left: 4px solid #ff5bae;
        border-radius: 0 0 0 0;
    }
    
    .product-price {
        font-size: 2.2rem;
        font-weight: 700;
        color: #ff5bae;
        margin: 0;
        display: inline-block;
    }
    
    /* Brand styles - moved to left alignment */
    .product-brand {
        margin: 10px 0;
        display: flex;
        align-items: center;
    }
    
    .brand-label {
        font-weight: 600;
        margin-right: 10px;
        color: #555;
    }
    
    .brand-value {
        color: #ff5bae;
        font-weight: 600;
        text-decoration: none;
        transition: color 0.2s ease;
    }
    
    .brand-value:hover {
        color: #e04b99;
        text-decoration: underline;
    }
    
    /* Vehicle compatibility improved styles */
    .vehicle-compatibility {
        margin: 25px 0;
        padding: 15px;
        background-color: #f8f9fa;
        border-radius: 4px;
        border: 1px solid #e9ecef;
    }
    
    .compatibility-title {
        font-size: 1.25rem;
        font-weight: 600;
        margin-bottom: 15px;
        color: #333;
        border-bottom: 2px solid #ff5bae;
        padding-bottom: 8px;
        display: inline-block;
    }
    
    .compatibility-details {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
        gap: 10px;
    }
    
    .compatibility-item {
        margin-bottom: 10px;
        padding: 8px 12px;
        background-color: white;
        border-radius: 3px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    }
    
    .compatibility-label {
        font-weight: 600;
        color: #555;
        margin-right: 5px;
    }
    
    .compatibility-value {
        color: #333;
        font-weight: 500;
    }
    
    /* Request button styles */
    .request-btn {
        background-color: #3498db;
        color: white;
        transition: background-color 0.3s ease;
        border-radius: 0;
    }
    
    .request-btn:hover {
        background-color: #2980b9;
    }
    
    .request-sent-btn {
        background-color: #27ae60;
        color: white;
        border-radius: 0;
    }
    
    .alert {
        padding: 12px 15px;
        border-radius: 0;
        margin-top: 15px;
    }
    
    .alert-success {
        background-color: #d4edda;
        color: #155724;
        border: 1px solid #c3e6cb;
    }
    
    /* Responsive adjustments */
    @media (max-width: 768px) {
        .price-container {
            margin: 15px 0;
        }
        
        .product-price {
            font-size: 1.8rem;
        }
        
        .compatibility-details {
            grid-template-columns: 1fr;
        }
    }
</style>

<?php require_once __DIR__ . '/partials/footer.php'; ?>
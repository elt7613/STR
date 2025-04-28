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
            
            <?php if (!empty($productCategories)): ?>
            <div class="product-categories">
                <?php foreach ($productCategories as $category): ?>
                    <span class="category-badge"><?php echo htmlspecialchars($category['name']); ?></span>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
            
            <p class="product-price">$<?php echo number_format($product['amount'], 2); ?></p>
            
            <div class="product-description">
                <?php echo nl2br(htmlspecialchars($product['description'])); ?>
            </div>
            
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
            
            <?php if (!empty($product['brand_id']) && !empty($product['brand_name'])): ?>
            <div class="product-meta">
                <span class="meta-label">Brand:</span>
                <a href="brand.php?id=<?php echo $product['brand_id']; ?>" class="meta-value brand-link"><?php echo htmlspecialchars($product['brand_name']); ?></a>
            </div>
            <?php endif; ?>
            
            <?php if (!empty($product['make_name']) || !empty($product['model_name']) || !empty($product['series_name'])): ?>
            <div class="product-compatibility">
                <h4>Vehicle Compatibility:</h4>
                <ul class="compatibility-list">
                    <?php if (!empty($product['make_name'])): ?>
                    <li><span>Make:</span> <?php echo htmlspecialchars($product['make_name']); ?></li>
                    <?php endif; ?>
                    
                    <?php if (!empty($product['model_name'])): ?>
                    <li><span>Model:</span> <?php echo htmlspecialchars($product['model_name']); ?></li>
                    <?php endif; ?>
                    
                    <?php if (!empty($product['series_name'])): ?>
                    <li><span>Series:</span> <?php echo htmlspecialchars($product['series_name']); ?></li>
                    <?php endif; ?>
                </ul>
            </div>
            <?php endif; ?>
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
                            <div class="product-price">$<?php echo number_format($relatedProduct['amount'], 2); ?></div>
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
    /* Request button styles */
    .request-btn {
        background-color: #3498db;
        color: white;
        transition: background-color 0.3s ease;
    }
    
    .request-btn:hover {
        background-color: #2980b9;
    }
    
    .request-sent-btn {
        background-color: #27ae60;
        color: white;
    }
    
    .alert {
        padding: 12px 15px;
        border-radius: 5px;
        margin-top: 15px;
    }
    
    .alert-success {
        background-color: #d4edda;
        color: #155724;
        border: 1px solid #c3e6cb;
    }
</style>

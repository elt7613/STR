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
                    
                    <img id="main-product-image" src="/<?php echo !empty($primaryImage) ? htmlspecialchars($primaryImage['image_path']) : 'assets/img/product-placeholder.jpg'; ?>" class="main-image" alt="<?php echo htmlspecialchars($product['title']); ?>">
                </div>
                
                <div class="thumbnail-container">
                    <?php foreach ($productImages as $index => $image): ?>
                        <div class="thumbnail-wrapper <?php echo ($primaryImage && $image['id'] == $primaryImage['id']) ? 'active' : ''; ?>">
                            <img src="/<?php echo htmlspecialchars($image['image_path']); ?>" class="thumbnail" alt="<?php echo htmlspecialchars($product['title']); ?> - Image <?php echo $index + 1; ?>" onclick="changeMainImage(this)">
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <img src="/assets/img/product-placeholder.jpg" class="main-image" alt="<?php echo htmlspecialchars($product['title']); ?>">
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
                    <input type="number" class="quantity-input" value="1" min="1" max="99">
                    <button class="quantity-btn plus-btn" type="button">+</button>
                </div>
                
                <div class="btn-group">
                    <button class="btn buy-now-btn">
                        <i class="fas fa-shopping-cart"></i> Add to Cart
                    </button>
                </div>
            </div>
            
            <?php if (!empty($product['brand_id']) && !empty($product['brand_name'])): ?>
            <div class="product-meta">
                <span class="meta-label">Brand:</span>
                <a href="/brand.php?id=<?php echo $product['brand_id']; ?>" class="meta-value brand-link"><?php echo htmlspecialchars($product['brand_name']); ?></a>
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
                    <a href="/product.php?id=<?php echo $relatedProduct['id']; ?>">
                        <div class="product-image-container">
                            <img src="/<?php echo !empty($relatedProduct['primary_image']) ? htmlspecialchars($relatedProduct['primary_image']) : 'assets/img/product-placeholder.jpg'; ?>" alt="<?php echo htmlspecialchars($relatedProduct['title']); ?>">
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
    });
</script>

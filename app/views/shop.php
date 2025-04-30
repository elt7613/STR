<?php 
$pageTitle = 'Shop - STR Works';
require_once ROOT_PATH . '/app/views/partials/header.php'; 
?>

<style>
    /* Shop styles */
    .brand-section {
        padding: 50px 0;
    }
    
    .main-title {
        text-align: center;
        margin-top: 50px;
        margin-bottom: 50px;
        font-weight: 600;
        font-size: 2.5rem;
        color: #333;
        position: relative;
    }
    
    .main-title:after {
        content: '';
        position: absolute;
        bottom: -15px;
        left: 50%;
        transform: translateX(-50%);
        width: 80px;
        height: 3px;
        background: #ff5bae;
    }
    
    .brand-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
        border-bottom: 2px solid #f0f0f0;
        padding-bottom: 15px;
    }
    
    .brand-name {
        font-size: 1.8rem;
        font-weight: 600;
        margin: 0;
        color: #333;
        position: relative;
        padding-left: 15px;
    }
    
    .brand-name:before {
        content: '';
        position: absolute;
        left: 0;
        top: 50%;
        transform: translateY(-50%);
        width: 4px;
        height: 70%;
        background: #ff5bae;
        border-radius: 2px;
    }
    
    .view-more-btn {
        background-color: #d2d2d2;
        color: black;
        border: 2px solid #d2d2d2;
        padding: 10px 25px;
        border-radius: 0;
        transition: all 0.3s ease;
        text-decoration: none;
        font-weight: 500;
    }
    
    .view-more-btn:hover {
        background-color: #ff83c2;
        border-color: #ff83c2;
        color: white;
        transform: translateY(-2px);
    }
    
    .brand-products {
        margin-bottom: 50px;
    }
    
    /* Grid layout styles */
    .grid-container {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 20px;
        margin-bottom: 20px;
    }
    
    @media (max-width: 992px) {
        .grid-container {
            grid-template-columns: repeat(3, 1fr);
        }
    }
    
    @media (max-width: 768px) {
        .grid-container {
            grid-template-columns: repeat(2, 1fr);
        }
        
        .brand-header {
            flex-direction: column;
            align-items: flex-start;
        }
        
        .view-more-btn {
            margin-top: 10px;
        }
    }
    
    @media (max-width: 576px) {
        .grid-container {
            grid-template-columns: repeat(1, 1fr);
        }
    }
    
    .product-card {
        position: relative;
        overflow: hidden;
        border: none;
        border-radius: 0;
    }
    
    .product-image-container {
        position: relative;
        aspect-ratio: 1/1;
        overflow: hidden;
        background-color: #f3eef0;
    }
    
    .product-card img {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.3s ease-in-out;
    }
    
    .product-card:hover img {
        transform: scale(1.1);
    }
    
    .product-info {
        padding: 15px 0;
    }
    
    .product-title {
        font-weight: 700;
        margin-bottom: 8px;
        overflow: hidden;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        color: #000;
        font-size: 1.15rem;
        text-decoration: none;
    }
    
    .product-title:hover {
        color: #ff5bae;
    }
    
    .product-price {
        font-weight: 600;
        font-size: 0.95rem;
        color: #666666;
    }
    
    .product-meta {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    
    .no-products {
        text-align: center;
        grid-column: 1 / -1;
        padding: 40px;
        background-color: #f8f9fa;
        border-radius: 12px;
        border: 2px dashed #ff5bae;
        color: #666;
    }
</style>

<!-- Main Shop Section -->
<div class="container">
    <h1 class="main-title">Our Brands</h1>
    
    <?php if (!empty($brands)): ?>
        <?php foreach ($brands as $brand): ?>
            <div class="brand-products">
                <div class="brand-header">
                    <h2 class="brand-name"><?php echo htmlspecialchars($brand['name']); ?></h2>
                    <a href="brand.php?id=<?php echo $brand['id']; ?>" class="view-more-btn">View More</a>
                </div>
                
                <div class="grid-container">
                    <?php 
                    // Get products for this brand, limited to 4
                    $brandProducts = [];
                    if (!empty($products)) {
                        $count = 0;
                        foreach ($products as $product) {
                            if ($product['brand_id'] == $brand['id'] && $count < 4) {
                                $brandProducts[] = $product;
                                $count++;
                            }
                        }
                    }
                    
                    if (!empty($brandProducts)): 
                        foreach ($brandProducts as $product): 
                    ?>
                        <div class="product-card">
                            <a href="product.php?id=<?php echo $product['id']; ?>">
                                <div class="product-image-container">
                                    <img src="<?php echo !empty($product['primary_image']) ? htmlspecialchars($product['primary_image']) : 'assets/img/product-placeholder.jpg'; ?>" alt="<?php echo htmlspecialchars($product['title']); ?>">
                                </div>
                                <div class="product-info">
                                    <h5 class="product-title"><?php echo htmlspecialchars($product['title']); ?></h5>
                                    <div class="product-price">$<?php echo number_format($product['amount'], 2); ?></div>
                                </div>
                            </a>
                        </div>
                    <?php 
                        endforeach;
                    else: 
                    ?>
                        <div class="no-products">
                            <p>No products available for this brand at the moment.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="text-center">
            <p>No brands available at the moment.</p>
        </div>
    <?php endif; ?>
</div>

<script>
    // Handle wishlist button clicks
    document.querySelectorAll('.wishlist-btn').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const icon = this.querySelector('i');
            if (icon.classList.contains('far')) {
                icon.classList.remove('far');
                icon.classList.add('fas');
                // Add to wishlist logic here
            } else {
                icon.classList.remove('fas');
                icon.classList.add('far');
                // Remove from wishlist logic here
            }
        });
    });
</script>
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
    
    .brand-categories {
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
    
    .category-card {
        position: relative;
        overflow: hidden;
        border: none;
        border-radius: 0;
        transition: transform 0.3s ease;
    }
    
    .category-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
    }
    
    .category-image-container {
        position: relative;
        aspect-ratio: 1/1;
        overflow: hidden;
        background-color: #f3eef0;
    }
    
    .category-card img {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.3s ease-in-out;
    }
    
    .category-card:hover img {
        transform: scale(1.1);
    }
    
    .category-info {
        padding: 15px;
        background-color: #fff;
        text-align: center;
    }
    
    .category-title {
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
    
    .category-title:hover {
        color: #ff5bae;
    }
    
    .no-categories {
        text-align: center;
        grid-column: 1 / -1;
        padding: 40px;
        background-color: #f8f9fa;
        border-radius: 12px;
        border: 2px dashed #ff5bae;
        color: #666;
    }
    
    .placeholder-image {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        background-color: #f3eef0;
        color: #999;
        font-weight: 500;
        font-size: 1.2rem;
    }
</style>

<!-- Main Shop Section -->
<div class="container">
    <h1 class="main-title">Shop by Categories</h1>
    
    <?php if (!empty($brands)): ?>
        <?php foreach ($brands as $brand): ?>
            <div class="brand-categories">
                <div class="brand-header">
                    <h2 class="brand-name"><?php echo htmlspecialchars($brand['name']); ?></h2>
                    <a href="brand.php?id=<?php echo $brand['id']; ?>" class="view-more-btn">View All Products</a>
                </div>
                
                <div class="grid-container">
                    <?php 
                    // Get categories for this brand
                    $categories = $brandCategories[$brand['id']] ?? [];
                    
                    if (!empty($categories)): 
                        foreach ($categories as $category): 
                    ?>
                        <div class="category-card">
                            <a href="category_products.php?id=<?php echo $category['id']; ?>">
                                <div class="category-image-container">
                                    <?php if (!empty($category['image_path'])): ?>
                                        <?php
                                        $original_path = $category['image_path'];
                                        
                                        // Fix for both local and hostinger environments
                        // First, normalize the path by removing any existing '../' prefixes
                        $normalized_path = preg_replace('/^\.\.\/+/', '', $original_path);
                        
                        // Then add the correct path prefix
                        // For paths that point to /public/ directory
                        if (strpos($normalized_path, 'public/') === 0) {
                            // Use the path as is without the 'public/' part since we're already in public context
                            $fixed_path = substr($normalized_path, 7); // Remove 'public/' prefix
                        } else if (strpos($normalized_path, 'uploads/') === 0) {
                            // Direct path to uploads folder
                            $fixed_path = $normalized_path;
                        } else {
                            // For other paths, keep the original behavior
                            $fixed_path = '../' . $normalized_path;
                        }                ?>
                                        <img src="<?php echo htmlspecialchars($fixed_path); ?>" alt="<?php echo htmlspecialchars($category['name']); ?>">
                                    <?php else: ?>
                                        <div class="placeholder-image">
                                            <span>Category</span>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div class="category-info">
                                    <h5 class="category-title"><?php echo htmlspecialchars($category['name']); ?></h5>
                                </div>
                            </a>
                        </div>
                    <?php 
                        endforeach;
                    else: 
                    ?>
                        <div class="no-categories">
                            <p>No categories available for this brand at the moment.</p>
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

<?php require_once ROOT_PATH . '/app/views/partials/footer.php'; ?>
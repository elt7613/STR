<?php
// Set page title
$pageTitle = 'Manage Products';

// Include header
require_once ROOT_PATH . '/app/views/admin/partials/header.php';
?>

<div class="admin-header">
    <h1>Manage Products</h1>
    <div>
        <a href="../admin/manage_brands.php" class="btn btn-primary me-2">Manage Brands</a>
        <a href="../admin/manage_categories.php" class="btn btn-primary me-2">Manage Categories</a>
        <a href="../admin/dashboard.php" class="btn btn-outline-secondary">Back to Dashboard</a>
    </div>
</div>

<?php if (!empty($error)): ?>
    <div class="alert alert-danger"><?php echo $error; ?></div>
<?php endif; ?>

<?php if (!empty($success)): ?>
    <div class="alert alert-success"><?php echo $success; ?></div>
<?php endif; ?>
<!-- Add/Edit Product Form -->
<div class="form-section">
    <h3><?php echo isset($currentProduct) ? 'Edit Product: ' . htmlspecialchars($currentProduct['title']) : 'Add New Product'; ?></h3>
    <form action="" method="post" enctype="multipart/form-data" id="productForm">
        <input type="hidden" name="action" value="<?php echo isset($currentProduct) ? 'update' : 'add'; ?>">
        
        <?php if (isset($currentProduct)): ?>
            <input type="hidden" name="product_id" value="<?php echo $currentProduct['id']; ?>">
        <?php endif; ?>
        
        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="brandId" class="form-label">Brand*</label>
                <select class="form-select" id="brandId" name="brand_id" required>
                    <option value="">-- Select Brand --</option>
                    <?php foreach ($brands as $brand): ?>
                        <option value="<?php echo $brand['id']; ?>" <?php echo (isset($currentProduct) && $currentProduct['brand_id'] == $brand['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($brand['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="col-md-6 mb-3">
                <label for="productTitle" class="form-label">Product Title*</label>
                <input type="text" class="form-control" id="productTitle" name="title" required 
                       value="<?php echo isset($currentProduct) ? htmlspecialchars($currentProduct['title']) : ''; ?>">
            </div>
        </div>
        
        <!-- Categories (Optional) -->
        <div class="mb-3">
            <label for="categories" class="form-label">Categories (Optional)</label>
            <select class="form-select" id="categories" name="categories[]" multiple>
                <?php foreach ($categories as $category): ?>
                    <option value="<?php echo $category['id']; ?>" <?php echo (isset($productCategories) && in_array($category['id'], $productCategories)) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($category['name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <small class="form-text text-muted">Hold Ctrl (Windows) or Cmd (Mac) to select multiple categories</small>
        </div>
        
        <!-- Vehicle Make, Model, Series fields (optional) -->
        <div class="row">
            <div class="col-md-4 mb-3">
                <label for="makeId" class="form-label">Vehicle Make (Optional)</label>
                <select class="form-select" id="makeId" name="make_id">
                    <option value="">-- Select Make --</option>
                    <?php foreach ($makes as $make): ?>
                        <option value="<?php echo $make['id']; ?>" <?php echo (isset($currentProduct) && isset($currentProduct['make_id']) && $currentProduct['make_id'] == $make['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($make['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="col-md-4 mb-3">
                <label for="modelId" class="form-label">Vehicle Model (Optional)</label>
                <select class="form-select" id="modelId" name="model_id" <?php echo empty($models) ? 'disabled' : ''; ?>>
                    <option value="">-- Select Model --</option>
                    <?php foreach ($models as $model): ?>
                        <option value="<?php echo $model['id']; ?>" <?php echo (isset($currentProduct) && isset($currentProduct['model_id']) && $currentProduct['model_id'] == $model['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($model['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="col-md-4 mb-3">
                <label for="seriesId" class="form-label">Vehicle Series (Optional)</label>
                <select class="form-select" id="seriesId" name="series_id" <?php echo empty($series) ? 'disabled' : ''; ?>>
                    <option value="">-- Select Series --</option>
                    <?php foreach ($series as $seriesItem): ?>
                        <option value="<?php echo $seriesItem['id']; ?>" <?php echo (isset($currentProduct) && isset($currentProduct['series_id']) && $currentProduct['series_id'] == $seriesItem['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($seriesItem['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="col-md-4 mb-3">
                <label for="deviceId" class="form-label">Vehicle Device (Optional)</label>
                <select class="form-select" id="deviceId" name="device_id" <?php echo empty($devices) ? 'disabled' : ''; ?>>
                    <option value="">-- Select Device --</option>
                    <?php if (!empty($devices)): ?>
                        <?php foreach ($devices as $device): ?>
                            <option value="<?php echo $device['id']; ?>" <?php echo (isset($currentProduct) && isset($currentProduct['device_id']) && $currentProduct['device_id'] == $device['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($device['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="productAmount" class="form-label">Price*</label>
                <div class="input-group">
                    <span class="input-group-text">$</span>
                    <input type="number" class="form-control" id="productAmount" name="amount" step="0.01" min="0.01" required 
                           value="<?php echo isset($currentProduct) ? htmlspecialchars($currentProduct['amount']) : ''; ?>">
                </div>
            </div>
            
            <div class="col-md-6 mb-3">
                <label for="productImages" class="form-label">Product Images<?php echo isset($currentProduct) ? '' : '*'; ?></label>
                <input type="file" class="form-control" id="productImages" name="images[]" accept="image/*" multiple <?php echo isset($currentProduct) ? '' : 'required'; ?>>
                <small class="form-text text-muted">You can select multiple images. The first image will be set as primary by default.</small>
                <div id="imagePreviewContainer" class="image-preview-container mt-2"></div>
            </div>
        </div>
        
        <div class="mb-4">
            <div class="direct-buying-card">
                <div class="direct-buying-header">
                    <div class="direct-buying-icon">
                        <i class="fas fa-shopping-cart"></i>
                    </div>
                    <div class="direct-buying-title">
                        <h5>Direct Buying</h5>
                    </div>
                    <div class="direct-buying-toggle">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="directBuying" name="direct_buying" value="1" 
                                <?php echo (isset($currentProduct) && isset($currentProduct['direct_buying']) && $currentProduct['direct_buying'] == 1) ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="directBuying">
                                <span class="toggle-status">
                                    <?php echo (isset($currentProduct) && isset($currentProduct['direct_buying']) && $currentProduct['direct_buying'] == 1) ? 'Enabled' : 'Disabled'; ?>
                                </span>
                            </label>
                        </div>
                    </div>
                </div>
                <div class="direct-buying-body">
                    <p>Enable this option to allow customers to purchase the product directly.</p>
                </div>
            </div>
        </div>
        
        <div class="mb-3">
            <label for="productDescription" class="form-label">Description*</label>
            <textarea class="form-control" id="productDescription" name="description" rows="5" required><?php echo isset($currentProduct) ? htmlspecialchars($currentProduct['description']) : ''; ?></textarea>
        </div>
        
        <!-- Display existing images for edit mode -->
        <?php if (isset($currentProduct) && !empty($productImages)): ?>
            <div class="mb-3">
                <label class="form-label">Existing Images</label>
                <div class="product-images-container compact">
                    <?php foreach ($productImages as $image): ?>
                        <div class="product-image-card">
                            <div class="image-container">
                                <img src="../<?php echo htmlspecialchars($image['image_path']); ?>" alt="Product image" class="product-thumbnail">
                                <?php if ($image['is_primary'] == 1): ?>
                                    <div class="primary-badge">Primary</div>
                                <?php endif; ?>
                            </div>
                            <div class="image-actions">
                                <?php if ($image['is_primary'] != 1): ?>
                                    <form action="" method="post" class="d-inline">
                                        <input type="hidden" name="action" value="set_primary">
                                        <input type="hidden" name="image_id" value="<?php echo $image['id']; ?>">
                                        <input type="hidden" name="product_id" value="<?php echo $currentProduct['id']; ?>">
                                        <button type="submit" class="image-action-btn btn-primary" title="Set as Primary">
                                            <i class="fas fa-star"></i>
                                        </button>
                                    </form>
                                <?php endif; ?>
                                
                                <form action="" method="post" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this image?');">
                                    <input type="hidden" name="action" value="delete_image">
                                    <input type="hidden" name="image_id" value="<?php echo $image['id']; ?>">
                                    <input type="hidden" name="product_id" value="<?php echo $currentProduct['id']; ?>">
                                    <button type="submit" class="image-action-btn btn-danger" title="Delete Image">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
        
        <button type="submit" class="btn btn-primary" id="submitProductBtn">
            <?php echo isset($currentProduct) ? 'Update Product' : 'Add Product'; ?>
        </button>
        
        <?php if (isset($currentProduct)): ?>
            <a href="../admin/manage_products.php" class="btn btn-outline-secondary ms-2">Cancel</a>
        <?php endif; ?>
    </form>
</div>

<!-- Products List -->
<h3>Existing Products</h3>

<?php if (empty($products)): ?>
    <div class="alert alert-info">No products found. Add your first product using the form above.</div>
<?php else: ?>
    <div class="table-responsive">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Image</th>
                    <th>Title</th>
                    <th>Brand</th>
                    <th>Categories</th>
                    <th>Vehicle Details</th>
                    <th>Price</th>
                    <th>Created</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($products as $product): ?>
                    <tr>
                        <td><?php echo $product['id']; ?></td>
                        <td>
                            <img src="<?php echo !empty($product['primary_image']) ? '../'.htmlspecialchars($product['primary_image']) : '../assets/img/product-placeholder.jpg'; ?>" alt="<?php echo htmlspecialchars($product['title']); ?>" class="product-image">
                        </td>
                        <td><?php echo htmlspecialchars($product['title']); ?></td>
                        <td><?php echo htmlspecialchars($product['brand_name']); ?></td>
                        <td>
                            <?php 
                            $productCats = getProductCategories($product['id']);
                            if (!empty($productCats)): 
                                foreach ($productCats as $cat): 
                            ?>
                                <span class="badge bg-secondary"><?php echo htmlspecialchars($cat['name']); ?></span>
                            <?php 
                                endforeach; 
                            else: 
                            ?>
                                <span class="text-muted">None</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if (!empty($product['make_name'])): ?>
                                <div><strong>Make:</strong> <?php echo htmlspecialchars($product['make_name']); ?></div>
                            <?php endif; ?>
                            
                            <?php if (!empty($product['model_name'])): ?>
                                <div><strong>Model:</strong> <?php echo htmlspecialchars($product['model_name']); ?></div>
                            <?php endif; ?>
                            
                            <?php if (!empty($product['series_name'])): ?>
                                <div><strong>Series:</strong> <?php echo htmlspecialchars($product['series_name']); ?></div>
                            <?php endif; ?>
                            
                            <?php if (!empty($product['device_name'])): ?>
                                <div><strong>Device:</strong> <?php echo htmlspecialchars($product['device_name']); ?></div>
                            <?php endif; ?>
                            
                            <?php if (empty($product['make_name']) && empty($product['model_name']) && empty($product['series_name']) && empty($product['device_name'])): ?>
                                <span class="text-muted">None specified</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            $<?php echo number_format($product['amount'], 2); ?>
                            <br>
                            <?php if (isset($product['direct_buying']) && $product['direct_buying'] == 1): ?>
                                <span class="badge bg-success"><i class="fas fa-shopping-cart"></i> Direct Buy Enabled</span>
                            <?php else: ?>
                                <span class="badge bg-secondary"><i class="fas fa-envelope"></i> Request Only</span>
                            <?php endif; ?>
                        </td>
                        <td><?php echo date('M j, Y', strtotime($product['created_at'])); ?></td>
                        <td>
                            <button type="button" class="btn btn-sm btn-primary edit-product-btn" data-product-id="<?php echo $product['id']; ?>">
                                <i class="fas fa-edit"></i> Edit
                            </button>
                            
                            <form action="" method="post" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this product? This action cannot be undone.');">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                                <button type="submit" class="btn btn-sm btn-danger">
                                    <i class="fas fa-trash"></i> Delete
                                </button>
                            </form>
                            
                            <a href="../product.php?id=<?php echo $product['id']; ?>" target="_blank" class="btn btn-sm btn-outline-secondary">
                                <i class="fas fa-eye"></i> View
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>

<!-- Edit Product Modal -->
<div class="modal fade" id="editProductModal" tabindex="-1" aria-labelledby="editProductModalLabel" aria-hidden="true" role="dialog">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editProductModalLabel">Edit Product</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" data-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="" method="post" enctype="multipart/form-data" id="editProductForm">
                    <input type="hidden" name="action" value="update">
                    <input type="hidden" name="product_id" id="edit_product_id">
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="edit_brandId" class="form-label">Brand*</label>
                            <select class="form-select" id="edit_brandId" name="brand_id" required>
                                <option value="">-- Select Brand --</option>
                                <?php foreach ($brands as $brand): ?>
                                    <option value="<?php echo $brand['id']; ?>">
                                        <?php echo htmlspecialchars($brand['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="edit_productTitle" class="form-label">Product Title*</label>
                            <input type="text" class="form-control" id="edit_productTitle" name="title" required>
                        </div>
                    </div>
                    
                    <!-- Categories (Optional) -->
                    <div class="mb-3">
                        <label for="edit_categories" class="form-label">Categories (Optional)</label>
                        <select class="form-select" id="edit_categories" name="categories[]" multiple>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?php echo $category['id']; ?>">
                                    <?php echo htmlspecialchars($category['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <small class="form-text text-muted">Hold Ctrl (Windows) or Cmd (Mac) to select multiple categories</small>
                    </div>
                    
                    <!-- Vehicle Make, Model, Series fields (optional) -->
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="edit_makeId" class="form-label">Vehicle Make (Optional)</label>
                            <select class="form-select" id="edit_makeId" name="make_id">
                                <option value="">-- Select Make --</option>
                                <?php foreach ($makes as $make): ?>
                                    <option value="<?php echo $make['id']; ?>">
                                        <?php echo htmlspecialchars($make['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="col-md-4 mb-3">
                            <label for="edit_modelId" class="form-label">Vehicle Model (Optional)</label>
                            <select class="form-select" id="edit_modelId" name="model_id">
                                <option value="">-- Select Model --</option>
                            </select>
                        </div>
                        
                        <div class="col-md-4 mb-3">
                            <label for="edit_seriesId" class="form-label">Vehicle Series (Optional)</label>
                            <select class="form-select" id="edit_seriesId" name="series_id">
                                <option value="">-- Select Series --</option>
                            </select>
                        </div>
                        
                        <div class="col-md-4 mb-3">
                            <label for="edit_deviceId" class="form-label">Vehicle Device (Optional)</label>
                            <select class="form-select" id="edit_deviceId" name="device_id">
                                <option value="">-- Select Device --</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="edit_productAmount" class="form-label">Price*</label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" class="form-control" id="edit_productAmount" name="amount" step="0.01" min="0.01" required>
                            </div>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="edit_productImages" class="form-label">Product Images</label>
                            <input type="file" class="form-control" id="edit_productImages" name="images[]" accept="image/*" multiple>
                            <small class="form-text text-muted">You can select multiple images. The first image will be set as primary by default.</small>
                            <div id="edit_imagePreviewContainer" class="image-preview-container mt-2"></div>
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <div class="direct-buying-card">
                            <div class="direct-buying-header">
                                <div class="direct-buying-icon">
                                    <i class="fas fa-shopping-cart"></i>
                                </div>
                                <div class="direct-buying-title">
                                    <h5>Direct Buying</h5>
                                </div>
                                <div class="direct-buying-toggle">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="edit_directBuying" name="direct_buying" value="1">
                                        <label class="form-check-label" for="edit_directBuying">
                                            <span class="edit_toggle-status">Disabled</span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="direct-buying-body">
                                <p>Enable this option to allow customers to purchase the product directly.</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="edit_productDescription" class="form-label">Description*</label>
                        <textarea class="form-control" id="edit_productDescription" name="description" rows="5" required></textarea>
                    </div>
                    
                    <!-- Display existing images -->
                    <div class="mb-3" id="edit_existingImagesContainer">
                        <label class="form-label">Existing Images</label>
                        <div class="product-images-container compact" id="edit_productImagesContainer">
                            <!-- Existing images will be loaded dynamically -->
                        </div>
                    </div>
                    
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary" id="updateProductBtn">Update Product</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once ROOT_PATH . '/app/views/admin/partials/footer.php'; ?>

<style>
/* Enhanced Image Management Styles */
.product-images-container.compact {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
    gap: 10px;
    margin-top: 10px;
}

.product-images-container.compact .product-image-card {
    padding: 5px;
    position: relative;
}

.product-images-container.compact .image-container {
    position: relative;
    width: 100%;
    height: 100px;
    overflow: hidden;
    margin-bottom: 5px;
    border-radius: var(--radius-sm);
}

.product-thumbnail {
    width: 100%;
    height: 100%;
    object-fit: cover;
    border-radius: var(--radius-sm);
}

.product-images-container.compact .primary-badge {
    top: 3px;
    right: 3px;
    font-size: 0.65rem;
    padding: 2px 5px;
}

.product-images-container.compact .image-actions {
    display: flex;
    justify-content: space-between;
    margin-top: 5px;
}

.product-images-container.compact .image-action-btn {
    flex: 1;
    padding: 4px;
    font-size: 0.7rem;
}

/* Image Preview Styles */
.image-preview-container {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    margin-top: 10px;
}

.image-preview-item {
    width: 80px;
    height: 80px;
    position: relative;
    border-radius: var(--radius-sm);
    overflow: hidden;
    border: 1px solid var(--gray-300);
}

.image-preview-item img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.remove-preview {
    position: absolute;
    top: 3px;
    right: 3px;
    background-color: rgba(239, 68, 68, 0.8);
    color: white;
    width: 20px;
    height: 20px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 10px;
    cursor: pointer;
    transition: background-color 0.2s;
}

.remove-preview:hover {
    background-color: var(--danger-color);
}

.d-inline {
    display: inline-block;
}

/* Direct Buying Card Styles */
.direct-buying-card {
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    overflow: hidden;
    background-color: #fff;
    transition: box-shadow 0.3s ease;
}

.direct-buying-card:hover {
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

.direct-buying-header {
    display: flex;
    align-items: center;
    padding: 16px 20px;
    background-color: #f8fafc;
    border-bottom: 1px solid #e2e8f0;
}

.direct-buying-icon {
    height: 36px;
    width: 36px;
    border-radius: 50%;
    background-color: #3b82f6;
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 16px;
    flex-shrink: 0;
}

.direct-buying-icon i {
    font-size: 16px;
}

.direct-buying-title {
    flex-grow: 1;
}

.direct-buying-title h5 {
    margin: 0;
    font-weight: 600;
    color: #1e293b;
}

.direct-buying-toggle {
    flex-shrink: 0;
}

.direct-buying-toggle .form-check {
    min-height: auto;
    margin: 0;
}

.direct-buying-toggle .form-check-input {
    width: 48px;
    height: 24px;
    cursor: pointer;
}

.direct-buying-toggle .form-check-input:checked {
    background-color: #10b981;
    border-color: #10b981;
}

.direct-buying-toggle .toggle-status {
    display: inline-block;
    margin-left: 8px;
    font-weight: 500;
}

.direct-buying-body {
    padding: 20px;
}

.direct-buying-body p {
    margin-bottom: 12px;
    color: #475569;
}

.direct-buying-benefits {
    display: flex;
    flex-wrap: wrap;
    margin-top: 16px;
    gap: 12px;
}

.benefit-item {
    flex: 1 1 30%;
    min-width: 180px;
    background-color: #f1f5f9;
    padding: 10px 14px;
    border-radius: 6px;
    font-size: 14px;
    color: #475569;
}

.benefit-item i {
    margin-right: 8px;
    color: #3b82f6;
}

/* Add responsive adjustments */
@media (max-width: 768px) {
    .direct-buying-header {
        flex-direction: column;
        align-items: flex-start;
    }
    
    .direct-buying-icon {
        margin-bottom: 12px;
    }
    
    .direct-buying-toggle {
        margin-top: 12px;
        align-self: flex-start;
        width: 100%;
    }
    
    .benefit-item {
        flex: 1 1 100%;
    }
}

/* Modal styles */
.modal-xl {
    max-width: 1140px;
}

.modal-content {
    border-radius: 8px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
}

.modal-header {
    background-color: #f8fafc;
    border-bottom: 1px solid #e2e8f0;
    border-top-left-radius: 8px;
    border-top-right-radius: 8px;
    padding: 16px 24px;
}

.modal-footer {
    background-color: #f8fafc;
    border-top: 1px solid #e2e8f0;
    padding: 16px 24px;
    border-bottom-left-radius: 8px;
    border-bottom-right-radius: 8px;
}

.modal-body {
    padding: 24px;
    max-height: calc(100vh - 200px);
    overflow-y: auto;
}

.modal-title {
    font-weight: 600;
    color: #1e293b;
}

/* Animation for modal */
.modal.fade .modal-dialog {
    transform: scale(0.95);
    transition: transform 0.2s ease-out;
}

.modal.show .modal-dialog {
    transform: scale(1);
}

/* Set primary and delete buttons in modal */
#edit_productImagesContainer .set-primary-btn,
#edit_productImagesContainer .delete-image-btn {
    margin: 0 2px;
    padding: 5px 8px;
    font-size: 12px;
    border-radius: 4px;
}

#edit_productImagesContainer .set-primary-btn {
    background-color: #3b82f6;
    border-color: #3b82f6;
    color: white;
}

#edit_productImagesContainer .delete-image-btn {
    background-color: #ef4444;
    border-color: #ef4444;
    color: white;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM content loaded - initializing edit product functionality');
    
    // Add a message to check if Bootstrap is available
    if (typeof bootstrap !== 'undefined') {
        console.log('Bootstrap JavaScript detected, version:', bootstrap.Modal ? 'v5+' : 'v4 or earlier');
    } else if (typeof $ !== 'undefined' && typeof $.fn.modal !== 'undefined') {
        console.log('jQuery Bootstrap detected');
    } else {
        console.warn('WARNING: Bootstrap JavaScript not detected. Modals may not work correctly.');
    }
    
    // Edit product button click handler
    const editButtons = document.querySelectorAll('.edit-product-btn');
    console.log('Found', editButtons.length, 'edit buttons');
    
    editButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const productId = this.getAttribute('data-product-id');
            console.log('Edit button clicked for product ID:', productId);
            openEditProductModal(productId);
        });
    });
    
    // Function to open and populate the edit product modal
    function openEditProductModal(productId) {
        // Reset form
        const editForm = document.getElementById('editProductForm');
        const editProductImagesContainer = document.getElementById('edit_productImagesContainer');
        const editImagePreviewContainer = document.getElementById('edit_imagePreviewContainer');
        const editProductIdField = document.getElementById('edit_product_id');
        const editModalElement = document.getElementById('editProductModal');
        
        // Check if all required elements exist
        if (!editForm || !editModalElement) {
            console.error('Critical elements missing:', {
                editForm: !!editForm,
                editModalElement: !!editModalElement
            });
            alert('Error: Could not find edit form elements. Please reload the page and try again.');
            return;
        }
        
        console.log('Modal element found:', !!editModalElement);
        
        // Reset form and containers
        editForm.reset();
        if (editProductIdField) editProductIdField.value = productId;
        if (editProductImagesContainer) editProductImagesContainer.innerHTML = '';
        if (editImagePreviewContainer) editImagePreviewContainer.innerHTML = '';
        
        // Use jQuery to show the modal (more reliable across Bootstrap versions)
        try {
            console.log('Showing modal via jQuery');
            $(editModalElement).modal('show');
        } catch (e) {
            console.error('jQuery modal method failed:', e);
            try {
                console.log('Attempting Bootstrap 5 modal initialization');
                const editModal = new bootstrap.Modal(editModalElement);
                editModal.show();
                console.log('Modal shown via Bootstrap 5 API');
            } catch (e2) {
                console.error('All modal methods failed:', e2);
                alert('Error showing edit form. Please try again or reload the page.');
                return;
            }
        }
        
        // Fetch product data
        fetchProductData();
        
        function fetchProductData() {
            // Fetch product data
            fetch(`../admin/manage_products.php?edit=${productId}&format=json`)
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! Status: ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    console.log('Product data received:', data);
                    
                    if (data.success) {
                        // Populate form fields
                        const product = data.product;
                        console.log('Product details:', product);
                        
                        // Basic fields
                        document.getElementById('edit_brandId').value = product.brand_id;
                        document.getElementById('edit_productTitle').value = product.title;
                        document.getElementById('edit_productAmount').value = product.amount;
                        document.getElementById('edit_productDescription').value = product.description;
                        
                        // Direct buying checkbox
                        const editDirectBuyingCheckbox = document.getElementById('edit_directBuying');
                        editDirectBuyingCheckbox.checked = product.direct_buying == 1;
                        const editToggleStatus = document.querySelector('.edit_toggle-status');
                        editToggleStatus.textContent = editDirectBuyingCheckbox.checked ? 'Enabled' : 'Disabled';
                        
                        // Categories (multi-select)
                        console.log('Categories data:', data.categories);
                        const editCategorySelect = document.getElementById('edit_categories');
                        
                        if (editCategorySelect && data.categories && Array.isArray(data.categories)) {
                            // First clear all selections
                            Array.from(editCategorySelect.options).forEach(option => {
                                option.selected = false;
                            });
                            
                            // Then set the selected options
                            Array.from(editCategorySelect.options).forEach(option => {
                                option.selected = data.categories.includes(parseInt(option.value));
                            });
                            
                            console.log('Updated category selections');
                        } else {
                            console.warn('Could not update categories:', {
                                editCategorySelect: !!editCategorySelect,
                                dataCategories: data.categories
                            });
                        }
                        
                        // Vehicle make, model, series
                        if (product.make_id) {
                            document.getElementById('edit_makeId').value = product.make_id;
                            
                            // Fetch and populate models
                            fetch(`../admin/ajax_get_models.php?make_id=${product.make_id}`)
                                .then(response => response.json())
                                .then(modelData => {
                                    if (modelData.success && modelData.models.length > 0) {
                                        const editModelSelect = document.getElementById('edit_modelId');
                                        editModelSelect.innerHTML = '<option value="">-- Select Model --</option>';
                                        editModelSelect.disabled = false;
                                        
                                        modelData.models.forEach(model => {
                                            const option = document.createElement('option');
                                            option.value = model.id;
                                            option.textContent = model.name;
                                            option.selected = model.id == product.model_id;
                                            editModelSelect.appendChild(option);
                                        });
                                        
                                        // If model is selected, fetch series
                                        if (product.model_id) {
                                            fetch(`../admin/ajax_get_series.php?model_id=${product.model_id}`)
                                                .then(response => response.json())
                                                .then(seriesData => {
                                                    if (seriesData.success && seriesData.series.length > 0) {
                                                        const editSeriesSelect = document.getElementById('edit_seriesId');
                                                        editSeriesSelect.innerHTML = '<option value="">-- Select Series --</option>';
                                                        editSeriesSelect.disabled = false;
                                                        
                                                        seriesData.series.forEach(series => {
                                                            const option = document.createElement('option');
                                                            option.value = series.id;
                                                            option.textContent = series.name;
                                                            option.selected = series.id == product.series_id;
                                                            editSeriesSelect.appendChild(option);
                                                        });
                                                        
                                                        // If series is selected, fetch devices
                                                        if (product.series_id) {
                                                            fetch(`../api/get_devices.php?series_id=${product.series_id}`)
                                                                .then(response => response.json())
                                                                .then(deviceData => {
                                                                    if (deviceData && deviceData.length > 0) {
                                                                        const editDeviceSelect = document.getElementById('edit_deviceId');
                                                                        editDeviceSelect.innerHTML = '<option value="">-- Select Device --</option>';
                                                                        editDeviceSelect.disabled = false;
                                                                        
                                                                        deviceData.forEach(device => {
                                                                            const option = document.createElement('option');
                                                                            option.value = device.id;
                                                                            option.textContent = device.name;
                                                                            option.selected = device.id == product.device_id;
                                                                            editDeviceSelect.appendChild(option);
                                                                        });
                                                                    }
                                                                })
                                                                .catch(error => {
                                                                    console.error('Error fetching device data:', error);
                                                                });
                                                        }
                                                    }
                                                })
                                                .catch(error => {
                                                    console.error('Error fetching series data:', error);
                                                });
                                        }
                                    }
                                })
                                .catch(error => {
                                    console.error('Error fetching model data:', error);
                                });
                        }
                        
                        // Load product images
                        console.log('Images data:', data.images);
                        const editExistingImagesContainer = document.getElementById('edit_existingImagesContainer');
                        const editImagesContainer = document.getElementById('edit_productImagesContainer');
                        
                        if (editImagesContainer && data.images && Array.isArray(data.images) && data.images.length > 0) {
                            editImagesContainer.innerHTML = '';
                            
                            if (editExistingImagesContainer) {
                                editExistingImagesContainer.style.display = 'block';
                            }
                            
                            data.images.forEach(image => {
                                console.log('Processing image:', image);
                                const imageCard = document.createElement('div');
                                imageCard.className = 'product-image-card';
                                imageCard.innerHTML = `
                                    <div class="image-container">
                                        <img src="../${image.image_path}" alt="Product image" class="product-thumbnail">
                                        ${image.is_primary == 1 ? '<div class="primary-badge">Primary</div>' : ''}
                                    </div>
                                    <div class="image-actions">
                                        ${image.is_primary != 1 ? `
                                            <button type="button" class="image-action-btn btn-primary set-primary-btn" 
                                                    data-image-id="${image.id}" data-product-id="${productId}" title="Set as Primary">
                                                <i class="fas fa-star"></i>
                                            </button>
                                        ` : ''}
                                        <button type="button" class="image-action-btn btn-danger delete-image-btn" 
                                                data-image-id="${image.id}" data-product-id="${productId}" title="Delete Image">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                `;
                                editImagesContainer.appendChild(imageCard);
                            });
                            
                            // Add event listeners for image actions
                            editImagesContainer.querySelectorAll('.set-primary-btn').forEach(btn => {
                                btn.addEventListener('click', function() {
                                    const imageId = this.getAttribute('data-image-id');
                                    const productId = this.getAttribute('data-product-id');
                                    setPrimaryImage(imageId, productId);
                                });
                            });
                            
                            editImagesContainer.querySelectorAll('.delete-image-btn').forEach(btn => {
                                btn.addEventListener('click', function() {
                                    if (confirm('Are you sure you want to delete this image?')) {
                                        const imageId = this.getAttribute('data-image-id');
                                        const productId = this.getAttribute('data-product-id');
                                        deleteProductImage(imageId, productId);
                                    }
                                });
                            });
                        } else {
                            console.warn('No images to display or container not found:', {
                                editImagesContainer: !!editImagesContainer,
                                imagesData: data.images
                            });
                            
                            if (editExistingImagesContainer) {
                                editExistingImagesContainer.style.display = 'none';
                            }
                        }
                    } else {
                        console.error('Failed to load product data:', data.message);
                        alert('Error loading product data. Please try again.');
                    }
                })
                .catch(error => {
                    console.error('Error fetching product data:', error);
                    alert('Error loading product data. Please try again.');
                });
        }
    }
    
    // Function to set an image as primary
    function setPrimaryImage(imageId, productId) {
        const formData = new FormData();
        formData.append('action', 'set_primary');
        formData.append('image_id', imageId);
        formData.append('product_id', productId);
        
        fetch('../admin/manage_products.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Refresh the images in the modal
                openEditProductModal(productId);
            } else {
                alert('Error setting primary image: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error setting primary image:', error);
            alert('Error setting primary image. Please try again.');
        });
    }
    
    // Function to delete a product image
    function deleteProductImage(imageId, productId) {
        const formData = new FormData();
        formData.append('action', 'delete_image');
        formData.append('image_id', imageId);
        formData.append('product_id', productId);
        
        fetch('../admin/manage_products.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Refresh the images in the modal
                openEditProductModal(productId);
            } else {
                alert('Error deleting image: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error deleting image:', error);
            alert('Error deleting image. Please try again.');
        });
    }
    
    // File input change event for edit modal image preview
    const editFileInput = document.getElementById('edit_productImages');
    const editPreviewContainer = document.getElementById('edit_imagePreviewContainer');
    
    if (editFileInput) {
        editFileInput.addEventListener('change', function() {
            editPreviewContainer.innerHTML = '';
            
            if (this.files) {
                Array.from(this.files).forEach((file, index) => {
                    if (!file.type.match('image.*')) {
                        return;
                    }
                    
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        const previewItem = document.createElement('div');
                        previewItem.className = 'image-preview-item';
                        previewItem.innerHTML = `
                            <img src="${e.target.result}" alt="Preview">
                            <div class="remove-preview" data-index="${index}" title="Remove">
                                <i class="fas fa-times"></i>
                            </div>
                        `;
                        editPreviewContainer.appendChild(previewItem);
                        
                        // Add event listener to remove button
                        previewItem.querySelector('.remove-preview').addEventListener('click', function() {
                            previewItem.remove();
                        });
                    };
                    
                    reader.readAsDataURL(file);
                });
            }
        });
    }
    
    // Toggle status label update for direct buying in edit modal
    const editDirectBuyingCheckbox = document.getElementById('edit_directBuying');
    const editToggleStatus = document.querySelector('.edit_toggle-status');
    
    if (editDirectBuyingCheckbox && editToggleStatus) {
        editDirectBuyingCheckbox.addEventListener('change', function() {
            editToggleStatus.textContent = this.checked ? 'Enabled' : 'Disabled';
        });
    }
    
    // Define all edit form variables once and only once
    const editProductForm = document.getElementById('editProductForm');
    const editMakeSelect = document.getElementById('edit_makeId');
    const editModelSelect = document.getElementById('edit_modelId');
    const editSeriesSelect = document.getElementById('edit_seriesId');
    const editDeviceSelect = document.getElementById('edit_deviceId');
    const editBrandSelect = document.getElementById('edit_brandId');
    const editCategorySelect = document.getElementById('edit_categories');

    // Handle edit product form submission
    if (editProductForm) {
        editProductForm.addEventListener('submit', function(e) {
            e.preventDefault();
            console.log('Edit product form submitted');
            const formData = new FormData(this);
            
            fetch('../admin/manage_products.php', {
                method: 'POST',
                body: formData
            })
            .then(response => {
                // If response is not JSON, reload the page to show the updated data
                const contentType = response.headers.get('content-type');
                if (!contentType || !contentType.includes('application/json')) {
                    window.location.reload();
                    return;
                }
                
                return response.json();
            })
            .then(data => {
                if (data && data.success) {
                    // Close the modal - use jQuery first as it's more reliable
                    const editModalElement = document.getElementById('editProductModal');
                    try {
                        console.log('Attempting to close modal with jQuery');
                        $(editModalElement).modal('hide');
                        console.log('Modal hidden via jQuery');
                    } catch (e) {
                        console.log('jQuery modal hiding failed, trying Bootstrap 5:', e);
                        try {
                            // Try Bootstrap 5 way
                            const editModal = bootstrap.Modal.getInstance(editModalElement);
                            if (editModal) {
                                editModal.hide();
                                console.log('Modal hidden via Bootstrap 5 API');
                            } else {
                                throw new Error('No Bootstrap 5 modal instance found');
                            }
                        } catch (e2) {
                            console.error('All modal closing methods failed:', e2);
                            // Try direct manipulation as last resort
                            try {
                                editModalElement.classList.remove('show');
                                editModalElement.style.display = 'none';
                                document.body.classList.remove('modal-open');
                                const backdrops = document.querySelectorAll('.modal-backdrop');
                                backdrops.forEach(el => el.remove());
                                console.log('Modal hidden via direct DOM manipulation');
                            } catch (e3) {
                                console.error('Even direct manipulation failed:', e3);
                            }
                        }
                    }
                    
                    // Show success message and reload page
                    alert(data.message || 'Product updated successfully!');
                    window.location.reload();
                } else if (data) {
                    alert(data.message || 'Error updating product. Please try again.');
                }
            })
            .catch(error => {
                console.error('Error updating product:', error);
                alert('Error updating product. Please try again.');
            });
        });
    }

    // Make sure the Close button in the modal works
    const closeModalButtons = document.querySelectorAll('[data-bs-dismiss="modal"], [data-dismiss="modal"]');
    closeModalButtons.forEach(button => {
        button.addEventListener('click', function() {
            const modalElement = this.closest('.modal');
            if (modalElement) {
                try {
                    $(modalElement).modal('hide');
                } catch (e) {
                    console.log('jQuery modal hide failed, trying other methods');
                    try {
                        const modal = bootstrap.Modal.getInstance(modalElement);
                        if (modal) modal.hide();
                    } catch (e2) {
                        console.error('All methods failed, trying direct manipulation');
                        modalElement.classList.remove('show');
                        modalElement.style.display = 'none';
                        document.body.classList.remove('modal-open');
                        const backdrops = document.querySelectorAll('.modal-backdrop');
                        backdrops.forEach(el => el.remove());
                    }
                }
            }
        });
    });

    // Brand change handler for Add Product form
    const brandSelect = document.getElementById('brandId');
    const categorySelect = document.getElementById('categories');
    
    if (brandSelect && categorySelect) {
        brandSelect.addEventListener('change', function() {
            fetchCategoriesForBrand(this.value, categorySelect);
        });
    }
    
    // Handle brand change in edit form
    if (editBrandSelect && editCategorySelect) {
        editBrandSelect.addEventListener('change', function() {
            fetchCategoriesForBrand(this.value, editCategorySelect);
        });
    }
    
    // Make model and series fields in edit modal depend on make selection
    if (editMakeSelect && editModelSelect) {
        editMakeSelect.addEventListener('change', function() {
            const makeId = this.value;
            
            // Reset and disable model and series
            editModelSelect.innerHTML = '<option value="">-- Select Model --</option>';
            editModelSelect.disabled = !makeId;
            
            if (editSeriesSelect) {
                editSeriesSelect.innerHTML = '<option value="">-- Select Series --</option>';
                editSeriesSelect.disabled = true;
            }
            
            // Reset and disable devices too
            if (editDeviceSelect) {
                editDeviceSelect.innerHTML = '<option value="">-- Select Device --</option>';
                editDeviceSelect.disabled = true;
            }
            
            if (makeId) {
                // Fetch models for selected make via AJAX
                fetch(`../admin/ajax_get_models.php?make_id=${makeId}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success && data.models.length > 0) {
                            data.models.forEach(model => {
                                const option = document.createElement('option');
                                option.value = model.id;
                                option.textContent = model.name;
                                editModelSelect.appendChild(option);
                            });
                            editModelSelect.disabled = false;
                        }
                    })
                    .catch(error => console.error('Error fetching models:', error));
            }
        });
    }
    
    if (editModelSelect && editSeriesSelect) {
        editModelSelect.addEventListener('change', function() {
            const modelId = this.value;
            
            // Reset and disable series
            editSeriesSelect.innerHTML = '<option value="">-- Select Series --</option>';
            editSeriesSelect.disabled = !modelId;
            
            // Reset and disable devices too
            if (editDeviceSelect) {
                editDeviceSelect.innerHTML = '<option value="">-- Select Device --</option>';
                editDeviceSelect.disabled = true;
            }
            
            if (modelId) {
                // Fetch series for selected model via AJAX
                fetch(`../admin/ajax_get_series.php?model_id=${modelId}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success && data.series.length > 0) {
                            data.series.forEach(series => {
                                const option = document.createElement('option');
                                option.value = series.id;
                                option.textContent = series.name;
                                editSeriesSelect.appendChild(option);
                            });
                            editSeriesSelect.disabled = false;
                        }
                    })
                    .catch(error => console.error('Error fetching series:', error));
            }
        });
    }

    // Device loading based on series selection
    if (editSeriesSelect && editDeviceSelect) {
        editSeriesSelect.addEventListener('change', function() {
            const seriesId = this.value;
            
            // Reset and disable devices
            editDeviceSelect.innerHTML = '<option value="">-- Select Device --</option>';
            editDeviceSelect.disabled = !seriesId;
            
            if (seriesId) {
                // Fetch devices for selected series via AJAX
                fetch(`../api/get_devices.php?series_id=${seriesId}`)
                    .then(response => response.json())
                    .then(devices => {
                        if (devices && devices.length > 0) {
                            devices.forEach(device => {
                                const option = document.createElement('option');
                                option.value = device.id;
                                option.textContent = device.name;
                                editDeviceSelect.appendChild(option);
                            });
                            editDeviceSelect.disabled = false;
                        }
                    })
                    .catch(error => console.error('Error fetching devices:', error));
            }
        });
    }
    
    // File input change event for image preview
    const fileInput = document.getElementById('productImages');
    const previewContainer = document.getElementById('imagePreviewContainer');
    
    if (fileInput) {
        fileInput.addEventListener('change', function() {
            previewContainer.innerHTML = '';
            
            if (this.files) {
                Array.from(this.files).forEach((file, index) => {
                    if (!file.type.match('image.*')) {
                        return;
                    }
                    
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        const previewItem = document.createElement('div');
                        previewItem.className = 'image-preview-item';
                        previewItem.innerHTML = `
                            <img src="${e.target.result}" alt="Preview">
                            <div class="remove-preview" data-index="${index}" title="Remove">
                                <i class="fas fa-times"></i>
                            </div>
                        `;
                        previewContainer.appendChild(previewItem);
                        
                        // Add event listener to remove button
                        previewItem.querySelector('.remove-preview').addEventListener('click', function() {
                            previewItem.remove();
                            // Note: We can't actually remove files from the FileList, 
                            // but we can clear and recreate it if needed, or just hide the preview
                        });
                    };
                    
                    reader.readAsDataURL(file);
                });
            }
        });
    }
    
    // Force enctype attribute on the form when editing products with existing images
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        if (form.querySelector('#productImages')) {
            form.setAttribute('enctype', 'multipart/form-data');
        }
    });
    
    // Make model and series fields depend on make selection
    const makeSelect = document.getElementById('makeId');
    const modelSelect = document.getElementById('modelId');
    const seriesSelect = document.getElementById('seriesId');
    
    if (makeSelect && modelSelect) {
        makeSelect.addEventListener('change', function() {
            const makeId = this.value;
            
            // Reset and disable model and series
            modelSelect.innerHTML = '<option value="">-- Select Model --</option>';
            modelSelect.disabled = !makeId;
            
            if (seriesSelect) {
                seriesSelect.innerHTML = '<option value="">-- Select Series --</option>';
                seriesSelect.disabled = true;
            }
            
            if (makeId) {
                // Fetch models for selected make via AJAX
                fetch(`../admin/ajax_get_models.php?make_id=${makeId}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success && data.models.length > 0) {
                            data.models.forEach(model => {
                                const option = document.createElement('option');
                                option.value = model.id;
                                option.textContent = model.name;
                                modelSelect.appendChild(option);
                            });
                        }
                    })
                    .catch(error => console.error('Error fetching models:', error));
            }
        });
    }
    
    if (modelSelect && seriesSelect) {
        modelSelect.addEventListener('change', function() {
            const modelId = this.value;
            
            // Reset and disable series
            seriesSelect.innerHTML = '<option value="">-- Select Series --</option>';
            seriesSelect.disabled = !modelId;
            
            if (modelId) {
                // Fetch series for selected model via AJAX
                fetch(`../admin/ajax_get_series.php?model_id=${modelId}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success && data.series.length > 0) {
                            data.series.forEach(series => {
                                const option = document.createElement('option');
                                option.value = series.id;
                                option.textContent = series.name;
                                seriesSelect.appendChild(option);
                            });
                        }
                    })
                    .catch(error => console.error('Error fetching series:', error));
            }
        });
    }
    
    // Make device dropdown dependent on series selection
    $('#seriesId').change(function() {
        var seriesId = $(this).val();
        var deviceSelect = $('#deviceId');
        
        // Reset dropdown
        deviceSelect.html('<option value="">-- Select Device --</option>');
        
        if (seriesId) {
            deviceSelect.prop('disabled', true);
            $.ajax({
                url: '../api/get_devices.php',
                data: { series_id: seriesId },
                dataType: 'json',
                success: function(data) {
                    if (data && data.length > 0) {
                        $.each(data, function(i, device) {
                            deviceSelect.append($('<option></option>').val(device.id).text(device.name));
                        });
                        deviceSelect.prop('disabled', false);
                    } else {
                        deviceSelect.prop('disabled', true);
                    }
                },
                error: function() {
                    alert('Failed to load devices');
                }
            });
        } else {
            deviceSelect.prop('disabled', true);
        }
    });

    // Initialize select2 for categories dropdown
    $('#categories').select2({
        placeholder: 'Select categories',
        width: '100%'
    });
    
    // Make model dropdown dependent on make selection
    $('#makeId').change(function() {
        var makeId = $(this).val();
        var modelSelect = $('#modelId');
        var seriesSelect = $('#seriesId');
        var deviceSelect = $('#deviceId');
        
        // Reset dropdowns
        modelSelect.html('<option value="">-- Select Model --</option>');
        seriesSelect.html('<option value="">-- Select Series --</option>');
        deviceSelect.html('<option value="">-- Select Device --</option>');
        seriesSelect.prop('disabled', true);
        deviceSelect.prop('disabled', true);
        
        if (makeId) {
            modelSelect.prop('disabled', true);
            $.ajax({
                url: '../api/get_models.php',
                data: { make_id: makeId },
                dataType: 'json',
                success: function(data) {
                    if (data && data.length > 0) {
                        $.each(data, function(i, model) {
                            modelSelect.append($('<option></option>').val(model.id).text(model.name));
                        });
                        modelSelect.prop('disabled', false);
                            } else {
                        modelSelect.prop('disabled', true);
                    }
                },
                error: function() {
                    alert('Failed to load models');
                }
            });
        } else {
            modelSelect.prop('disabled', true);
        }
    });
    
    // Make series dropdown dependent on model selection
    $('#modelId').change(function() {
        var modelId = $(this).val();
        var seriesSelect = $('#seriesId');
        var deviceSelect = $('#deviceId');
        
        // Reset dropdowns
        seriesSelect.html('<option value="">-- Select Series --</option>');
        deviceSelect.html('<option value="">-- Select Device --</option>');
        deviceSelect.prop('disabled', true);
        
        if (modelId) {
            seriesSelect.prop('disabled', true);
            $.ajax({
                url: '../api/get_series.php',
                data: { model_id: modelId },
                dataType: 'json',
                success: function(data) {
                    if (data && data.length > 0) {
                        $.each(data, function(i, series) {
                            seriesSelect.append($('<option></option>').val(series.id).text(series.name));
                        });
                        seriesSelect.prop('disabled', false);
                    } else {
                        seriesSelect.prop('disabled', true);
                    }
                },
                error: function() {
                    alert('Failed to load series');
                }
            });
        } else {
            seriesSelect.prop('disabled', true);
        }
    });
    
    // Make device dropdown dependent on series selection
    $('#seriesId').change(function() {
        var seriesId = $(this).val();
        var deviceSelect = $('#deviceId');
        
        // Reset dropdown
        deviceSelect.html('<option value="">-- Select Device --</option>');
        
        if (seriesId) {
            deviceSelect.prop('disabled', true);
            $.ajax({
                url: '../api/get_devices.php',
                data: { series_id: seriesId },
                dataType: 'json',
                success: function(data) {
                    if (data && data.length > 0) {
                        $.each(data, function(i, device) {
                            deviceSelect.append($('<option></option>').val(device.id).text(device.name));
                        });
                        deviceSelect.prop('disabled', false);
                } else {
                        deviceSelect.prop('disabled', true);
                    }
                },
                error: function() {
                    alert('Failed to load devices');
                }
            });
        } else {
            deviceSelect.prop('disabled', true);
        }
    });
});
</script> 
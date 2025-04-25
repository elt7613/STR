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
                            
                            <?php if (empty($product['make_name']) && empty($product['model_name']) && empty($product['series_name'])): ?>
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
                            <form action="" method="post" class="d-inline">
                                <input type="hidden" name="action" value="edit">
                                <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                                <button type="submit" class="btn btn-sm btn-primary">
                                    <i class="fas fa-edit"></i> Edit
                                </button>
                            </form>
                            
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
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Make sure forms in image actions work properly
    document.querySelectorAll('.image-action-btn').forEach(button => {
        button.addEventListener('click', function(e) {
            // Ensure the parent form is submitted
            if (e.target !== button) {
                e.preventDefault();
                button.form.submit();
            }
        });
    });
    
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
    
    // Fix for product form submission
    const productForm = document.getElementById('productForm');
    const submitProductBtn = document.getElementById('submitProductBtn');
    
    if (productForm && submitProductBtn) {
        submitProductBtn.addEventListener('click', function(e) {
            e.preventDefault();
            console.log('Form submitted via button click');
            productForm.submit();
        });
        
        productForm.addEventListener('submit', function(e) {
            console.log('Form submitted via form submit event');
            // Allow the form to submit normally
        });
    }
    
    // Toggle status label update for direct buying
    const directBuyingCheckbox = document.getElementById('directBuying');
    const toggleStatus = document.querySelector('.toggle-status');
    
    if (directBuyingCheckbox && toggleStatus) {
        directBuyingCheckbox.addEventListener('change', function() {
            toggleStatus.textContent = this.checked ? 'Enabled' : 'Disabled';
        });
    }
});
</script> 
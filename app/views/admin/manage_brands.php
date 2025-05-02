<?php
// Set page title
$pageTitle = 'Manage Brands';

// Include header
require_once ROOT_PATH . '/app/views/admin/partials/header.php';
?>

<div class="admin-header">
    <h1><i class="fas fa-tags"></i> Manage Brands</h1>
    <div class="admin-actions">
        <a href="dashboard.php" class="btn btn-outline-secondary"></a>
            <i class="fas fa-arrow-left"></i> Back to Dashboard
        </a>
    </div>
</div>

<?php if (!empty($error)): ?>
    <div class="alert alert-danger"><?php echo $error; ?></div>
<?php endif; ?>

<?php if (!empty($success)): ?>
    <div class="alert alert-success"><?php echo $success; ?></div>
<?php endif; ?>

<!-- Add/Edit Brand Form -->
<div class="form-section">
    <div class="section-header">
        <h3>
            <?php if (isset($_GET['edit'])): ?>
                <i class="fas fa-edit"></i> Edit Brand
            <?php else: ?>
                <i class="fas fa-plus-circle"></i> Add New Brand
            <?php endif; ?>
        </h3>
    </div>
    
    <form action="" method="post" enctype="multipart/form-data" class="animated-form">
        <input type="hidden" name="action" value="<?php echo isset($_GET['edit']) ? 'update' : 'add'; ?>">
        
        <?php if (isset($_GET['edit'])): ?>
            <?php 
            $editBrandId = intval($_GET['edit']);
            $editBrand = null;
            foreach ($brands as $brand) {
                if ($brand['id'] == $editBrandId) {
                    $editBrand = $brand;
                    break;
                }
            }
            ?>
            <?php if ($editBrand): ?>
                <input type="hidden" name="brand_id" value="<?php echo $editBrand['id']; ?>">
            <?php endif; ?>
        <?php endif; ?>
        
        <div class="form-layout">
            <div class="form-group">
                <label for="brandName" class="form-label">Brand Name*</label>
                <div class="input-with-icon">
                    <i class="fas fa-tag"></i>
                    <input type="text" class="form-control" id="brandName" name="name" required 
                           value="<?php echo isset($editBrand) ? htmlspecialchars($editBrand['name']) : ''; ?>"
                           placeholder="Enter brand name">
                </div>
                <small class="form-hint">The name of the brand as it will appear to users</small>
            </div>
            
            <div class="form-group">
                <label for="brandSequence" class="form-label">Display Sequence</label>
                <div class="input-with-icon">
                    <i class="fas fa-sort-numeric-down"></i>
                    <input type="number" class="form-control" id="brandSequence" name="sequence" min="1" 
                           value="<?php echo isset($editBrand) ? htmlspecialchars($editBrand['sequence']) : '999'; ?>"
                           placeholder="Enter sequence number">
                </div>
                <small class="form-hint">Lower numbers will appear first (1 is highest priority)</small>
            </div>
            
            <div class="form-group">
                <label for="brandImage" class="form-label">
                    <?php echo isset($editBrand) ? 'Brand Image (Leave empty to keep current)' : 'Brand Image*'; ?>
                </label>
                <div class="custom-file-upload">
                    <input type="file" id="brandImage" name="image" class="form-control" 
                           <?php echo !isset($editBrand) ? 'required' : ''; ?>
                           accept="image/*">
                    <label for="brandImage">
                        <i class="fas fa-cloud-upload-alt"></i>
                        <span class="file-label">Choose a file...</span>
                    </label>
                </div>
                <small class="form-hint">Recommended size: 200x100 pixels, PNG or JPG format</small>
                
                <?php if (isset($editBrand) && !empty($editBrand['image'])): ?>
                    <div class="current-image">
                        <span>Current Image:</span>
                        <img src="../<?php echo htmlspecialchars($editBrand['image']); ?>" alt="<?php echo htmlspecialchars($editBrand['name']); ?>" class="img-thumbnail" style="max-height: 60px;">
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="form-actions">
            <button type="submit" class="btn btn-primary">
                <?php echo isset($_GET['edit']) ? '<i class="fas fa-save"></i> Update Brand' : '<i class="fas fa-plus-circle"></i> Add Brand'; ?>
            </button>
            
            <?php if (isset($_GET['edit'])): ?>
                <a href="manage_brands.php" class="btn btn-outline-secondary">
                    <i class="fas fa-times"></i> Cancel
                </a>
            <?php endif; ?>
        </div>
    </form>
</div>

<!-- Brands List -->
<div class="brand-list-container">
    <div class="section-header">
        <h3><i class="fas fa-tags"></i> Existing Brands</h3>
        <div class="section-actions">
            <div class="search-container">
                <input type="text" id="brandSearch" class="search-input" placeholder="Search brands...">
                <i class="fas fa-search search-icon"></i>
            </div>
        </div>
    </div>

    <?php if (empty($brands)): ?>
        <div class="empty-state">
            <div class="empty-state-icon">
                <i class="fas fa-tags"></i>
            </div>
            <h3>No Brands Found</h3>
            <p>Start by adding your first brand using the form above.</p>
        </div>
    <?php else: ?>
        <div class="brands-grid">
            <?php foreach ($brands as $brand): ?>
                <div class="brand-card" data-brand-name="<?php echo htmlspecialchars(strtolower($brand['name'])); ?>">
                    <div class="brand-card-header">
                        <span class="brand-id">#<?php echo $brand['id']; ?></span>
                        <span class="brand-sequence">Sequence: <?php echo $brand['sequence']; ?></span>
                        <div class="brand-actions dropdown">
                            <button class="dropdown-toggle">
                                <i class="fas fa-ellipsis-v"></i>
                            </button>
                            <div class="dropdown-menu">
                                <a href="manage_brands.php?edit=<?php echo $brand['id']; ?>" class="dropdown-item">
                                    <i class="fas fa-edit"></i> Edit
                                </a>
                                <a href="../brand.php?id=<?php echo $brand['id']; ?>" target="_blank" class="dropdown-item">
                                    <i class="fas fa-eye"></i> View Frontend
                                </a>
                                <form action="" method="post" onsubmit="return confirm('Are you sure you want to delete this brand? This will also delete all products associated with this brand.');">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="brand_id" value="<?php echo $brand['id']; ?>">
                                    <button type="submit" class="dropdown-item text-danger">
                                        <i class="fas fa-trash"></i> Delete
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                    
                    <div class="brand-image-container">
                        <img src="../<?php echo htmlspecialchars($brand['image']); ?>" alt="<?php echo htmlspecialchars($brand['name']); ?>" class="brand-card-image">
                    </div>
                    
                    <div class="brand-card-content">
                        <h4 class="brand-name"><?php echo htmlspecialchars($brand['name']); ?></h4>
                        <div class="brand-meta">
                            <span class="brand-date">
                                <i class="fas fa-calendar-alt"></i> 
                                <?php echo date('M j, Y', strtotime($brand['created_at'])); ?>
                            </span>
                        </div>
                    </div>
                    
                    <div class="brand-card-footer">
                        <a href="manage_brands.php?edit=<?php echo $brand['id']; ?>" class="btn btn-sm btn-primary">
                            <i class="fas fa-edit"></i> Edit
                        </a>
                        <a href="../brand.php?id=<?php echo $brand['id']; ?>" target="_blank" class="btn btn-sm btn-outline-secondary">
                            <i class="fas fa-eye"></i> View
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<style>
/* Brand Management Specific Styles */
.form-layout {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1.5rem;
    margin-bottom: 1.5rem;
}

.form-group {
    margin-bottom: 1.25rem;
}

.form-hint {
    display: block;
    color: var(--gray-500);
    font-size: 0.8rem;
    margin-top: 0.5rem;
}

.input-with-icon {
    position: relative;
}

.input-with-icon i {
    position: absolute;
    left: 1rem;
    top: 50%;
    transform: translateY(-50%);
    color: var(--gray-500);
}

.input-with-icon .form-control {
    padding-left: 2.5rem;
}

.file-upload-container {
    position: relative;
    margin-bottom: 0.5rem;
}

.file-upload {
    position: absolute;
    left: 0;
    top: 0;
    opacity: 0;
    width: 100%;
    height: 100%;
    cursor: pointer;
}

.file-upload-label {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.75rem 1rem;
    background-color: var(--gray-100);
    border: 1px dashed var(--gray-300);
    border-radius: var(--radius-md);
    cursor: pointer;
    transition: all var(--transition-speed);
}

.file-upload-label:hover {
    background-color: var(--gray-200);
}

.file-name {
    margin-top: 0.5rem;
    font-size: 0.85rem;
    color: var(--gray-600);
}

.current-image-container {
    margin-top: 1rem;
    padding: 1rem;
    background-color: var(--gray-100);
    border-radius: var(--radius-md);
}

.current-image-label {
    font-weight: 500;
    margin-bottom: 0.5rem;
    color: var(--gray-700);
}

.brand-image-preview {
    width: 120px;
    height: 120px;
    object-fit: contain;
    border-radius: var(--radius-sm);
    border: 1px solid var(--gray-300);
    background-color: white;
    padding: 0.5rem;
}

.form-actions {
    display: flex;
    gap: 0.75rem;
    margin-top: 1.5rem;
    padding-top: 1.5rem;
    border-top: 1px solid var(--gray-200);
}

.section-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1.5rem;
    padding-bottom: 1rem;
    border-bottom: 1px solid var(--gray-200);
}

.section-header h3 {
    margin: 0;
    font-size: 1.25rem;
    font-weight: 600;
    color: var(--gray-800);
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.section-actions {
    display: flex;
    gap: 0.75rem;
}

.search-container {
    position: relative;
}

.search-input {
    padding: 0.5rem 1rem 0.5rem 2.5rem;
    border: 1px solid var(--gray-300);
    border-radius: 2rem;
    font-size: 0.9rem;
    width: 250px;
    transition: all var(--transition-speed);
}

.search-input:focus {
    outline: none;
    border-color: var(--primary-color);
    box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.15);
    width: 300px;
}

.search-icon {
    position: absolute;
    left: 1rem;
    top: 50%;
    transform: translateY(-50%);
    color: var(--gray-500);
}

.brand-list-container {
    margin-top: 2rem;
    padding: 0 1.5rem;
}

.brands-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
    gap: 1.5rem;
    margin-top: 1rem;
}

.brand-card {
    background-color: white;
    border-radius: var(--radius-md);
    box-shadow: var(--shadow-sm);
    overflow: hidden;
    transition: all var(--transition-speed);
    position: relative;
    display: flex;
    flex-direction: column;
}

.brand-card:hover {
    transform: translateY(-5px);
    box-shadow: var(--shadow-md);
}

.brand-card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.75rem;
    background-color: var(--light-bg);
    border-bottom: 1px solid var(--border-color);
}

.brand-id {
    font-size: 0.8rem;
    color: var(--gray-500);
    font-weight: 500;
}

.brand-sequence {
    font-size: 0.8rem;
    color: var(--primary-color);
    font-weight: 500;
    background-color: rgba(var(--primary-rgb), 0.1);
    padding: 0.2rem 0.5rem;
    border-radius: 20px;
}

.brand-actions {
    position: relative;
}

.dropdown-toggle {
    background: none;
    border: none;
    width: 30px;
    height: 30px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: background-color var(--transition-speed);
}

.dropdown-toggle:hover {
    background-color: var(--gray-200);
}

.dropdown-menu {
    position: absolute;
    right: 0;
    top: 100%;
    background-color: white;
    border-radius: var(--radius-md);
    min-width: 180px;
    box-shadow: var(--shadow-md);
    padding: 0.5rem 0;
    z-index: 10;
    display: none;
}

.dropdown:hover .dropdown-menu {
    display: block;
    animation: fadeIn 0.2s ease;
}

.dropdown-item {
    display: flex;
    align-items: center;
    padding: 0.5rem 1rem;
    color: var(--gray-700);
    text-decoration: none;
    transition: background-color var(--transition-speed);
    cursor: pointer;
    border: none;
    background: none;
    width: 100%;
    text-align: left;
    font-size: 0.9rem;
}

.dropdown-item i {
    margin-right: 0.75rem;
    width: 16px;
}

.dropdown-item:hover {
    background-color: var(--gray-100);
}

.text-danger {
    color: var(--danger-color) !important;
}

.brand-image-container {
    padding: 1.5rem;
    display: flex;
    justify-content: center;
    align-items: center;
    height: 180px;
    overflow: hidden;
    background-color: white;
}

.brand-card-image {
    max-width: 100%;
    max-height: 100%;
    object-fit: contain;
}

.brand-card-content {
    padding: 1rem;
    flex: 1;
}

.brand-name {
    font-size: 1.1rem;
    font-weight: 600;
    margin: 0 0 0.5rem;
    color: var(--gray-800);
}

.brand-meta {
    display: flex;
    align-items: center;
    color: var(--gray-500);
    font-size: 0.85rem;
}

.brand-meta i {
    margin-right: 0.25rem;
}

.brand-card-footer {
    padding: 1rem;
    border-top: 1px solid var(--gray-200);
    display: flex;
    gap: 0.5rem;
}

@media (max-width: 992px) {
    .form-layout {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 768px) {
    .section-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 1rem;
    }
    
    .section-actions, .search-container {
        width: 100%;
    }
    
    .search-input {
        width: 100%;
    }
    
    .search-input:focus {
        width: 100%;
    }
    
    .brand-card-footer {
        flex-direction: column;
    }
    
    .brand-card-footer .btn {
        width: 100%;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // File upload preview
    const fileUpload = document.getElementById('brandImage');
    const fileLabel = document.querySelector('.file-upload-label span');
    const fileName = document.querySelector('.file-name');
    
    if (fileUpload) {
        fileUpload.addEventListener('change', function() {
            if (this.files.length > 0) {
                fileName.textContent = this.files[0].name;
                fileLabel.textContent = 'File selected';
            } else {
                fileName.textContent = '';
                fileLabel.textContent = 'Choose image file...';
            }
        });
    }
    
    // Brand search functionality
    const searchInput = document.getElementById('brandSearch');
    const brandCards = document.querySelectorAll('.brand-card');
    
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase().trim();
            
            brandCards.forEach(card => {
                const brandName = card.getAttribute('data-brand-name');
                
                if (brandName.includes(searchTerm)) {
                    card.style.display = 'flex';
                } else {
                    card.style.display = 'none';
                }
            });
        });
    }
});
</script>

<?php require_once ROOT_PATH . '/app/views/admin/partials/footer.php'; ?> 
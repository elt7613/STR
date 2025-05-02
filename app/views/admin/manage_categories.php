<?php
// Set page title
$pageTitle = 'Manage Categories';

// Include header
require_once ROOT_PATH . '/app/views/admin/partials/header.php';
?>

<div class="admin-header">
    <h1>Manage Categories</h1>
    <div>
        <a href="../admin/manage_brands.php" class="btn btn-primary me-2">Manage Brands</a>
        <a href="../admin/manage_products.php" class="btn btn-primary me-2">Manage Products</a>
        <a href="../admin/dashboard.php" class="btn btn-outline-secondary">Back to Dashboard</a>
    </div>
</div>

<?php if (!empty($error)): ?>
    <div class="alert alert-danger"><?php echo $error; ?></div>
<?php endif; ?>

<?php if (!empty($success)): ?>
    <div class="alert alert-success"><?php echo $success; ?></div>
<?php endif; ?>

<div class="row mt-4 px-3">
    <!-- Brand Filter -->
    <div class="col-12 mb-4">
        <div class="card">
            <div class="card-header">
                Filter Categories by Brand
            </div>
            <div class="card-body">
                <form method="get" class="row g-3 align-items-end">
                    <div class="col-md-6">
                        <label for="filter-brand" class="form-label">Select Brand</label>
                        <select id="filter-brand" name="brand_id" class="form-select">
                            <option value="0">All Brands</option>
                            <?php foreach ($brands as $brand): ?>
                                <option value="<?php echo $brand['id']; ?>" <?php echo $filterBrandId == $brand['id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($brand['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <button type="submit" class="btn btn-primary">Filter</button>
                        <?php if ($filterBrandId > 0): ?>
                            <a href="../admin/manage_categories.php" class="btn btn-outline-secondary">Clear Filter</a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <?php echo $currentCategory ? 'Edit Category' : 'Add New Category'; ?>
            </div>
            <div class="card-body">
                <form method="post">
                    <input type="hidden" name="action" value="<?php echo $currentCategory ? 'update' : 'add'; ?>">
                    
                    <?php if ($currentCategory): ?>
                    <input type="hidden" name="category_id" value="<?php echo $currentCategory['id']; ?>">
                    <?php endif; ?>
                    
                    <div class="mb-3">
                        <label for="brand_id" class="form-label">Brand *</label>
                        <select class="form-select" id="brand_id" name="brand_id" required>
                            <option value="">-- Select Brand --</option>
                            <?php foreach ($brands as $brand): ?>
                                <option value="<?php echo $brand['id']; ?>" <?php echo ($currentCategory && $currentCategory['brand_id'] == $brand['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($brand['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <small class="form-text text-muted">Select the brand this category belongs to</small>
                    </div>
                    
                    <div class="mb-3">
                        <label for="name" class="form-label">Category Name *</label>
                        <input type="text" class="form-control" id="name" name="name" value="<?php echo $currentCategory ? htmlspecialchars($currentCategory['name']) : ''; ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="description" class="form-label">Description (Optional)</label>
                        <textarea class="form-control" id="description" name="description" rows="3"><?php echo $currentCategory ? htmlspecialchars($currentCategory['description']) : ''; ?></textarea>
                    </div>
                    
                    <button type="submit" class="btn btn-primary"><?php echo $currentCategory ? 'Update Category' : 'Add Category'; ?></button>
                    
                    <?php if ($currentCategory): ?>
                    <a href="../admin/manage_categories.php" class="btn btn-secondary">Cancel</a>
                    <?php endif; ?>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                Categories List <?php echo $filterBrandId > 0 ? 'for ' . htmlspecialchars(getBrandById($filterBrandId)['name']) : ''; ?>
            </div>
            <div class="card-body">
                <?php if (empty($categories)): ?>
                <p class="text-center">No categories found.</p>
                <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-bordered table-hover admin-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Brand</th>
                                <th>Description</th>
                                <th>Date Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($categories as $category): ?>
                            <tr>
                                <td><?php echo $category['id']; ?></td>
                                <td><?php echo htmlspecialchars($category['name']); ?></td>
                                <td><?php echo htmlspecialchars($category['brand_name'] ?? 'Unknown'); ?></td>
                                <td><?php echo htmlspecialchars($category['description'] ?? ''); ?></td>
                                <td><?php echo date('Y-m-d H:i', strtotime($category['created_at'])); ?></td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <form method="post" class="me-1 d-inline">
                                            <input type="hidden" name="action" value="edit">
                                            <input type="hidden" name="category_id" value="<?php echo $category['id']; ?>">
                                            <button type="submit" class="btn btn-sm btn-primary">
                                                <i class="fas fa-edit"></i> Edit
                                            </button>
                                        </form>
                                        
                                        <form method="post" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this category?');">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="category_id" value="<?php echo $category['id']; ?>">
                                            <button type="submit" class="btn btn-sm btn-danger">
                                                <i class="fas fa-trash"></i> Delete
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once ROOT_PATH . '/app/views/admin/partials/footer.php'; ?> 
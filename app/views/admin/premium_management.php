<?php
/**
 * Admin Premium Membership Management View
 */
$pageTitle = 'Premium Membership Management';
?>

<div class="container-fluid px-4">
    <h1 class="mt-4">Premium Membership Management</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
        <li class="breadcrumb-item active">Premium Management</li>
    </ol>
    
    <!-- Success/Error Messages -->
    <?php if (isset($successMessage)): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?php echo htmlspecialchars($successMessage); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
    
    <?php if (isset($errorMessage)): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?php echo htmlspecialchars($errorMessage); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
    
    <!-- Premium Plans List -->
    <div class="card mb-4">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <i class="fas fa-crown me-1"></i>
                    Premium Plans
                </div>
                <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addPlanModal">
                    <i class="fas fa-plus"></i> Add New Plan
                </button>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Description</th>
                            <th>Price (₹)</th>
                            <th>Duration (Months)</th>
                            <th>Status</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($premiumPlans)): ?>
                            <tr>
                                <td colspan="8" class="text-center">No premium plans found.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($premiumPlans as $plan): ?>
                                <tr>
                                    <td><?php echo $plan['id']; ?></td>
                                    <td><?php echo htmlspecialchars($plan['name']); ?></td>
                                    <td>
                                        <?php
                                            $desc = htmlspecialchars($plan['description']);
                                            echo (strlen($desc) > 80) ? substr($desc, 0, 77) . '...' : $desc;
                                        ?>
                                    </td>
                                    <td><?php echo number_format($plan['price'], 2); ?></td>
                                    <td><?php echo $plan['duration_months']; ?></td>
                                    <td>
                                        <?php if ($plan['is_active'] == 1): ?>
                                            <span class="badge bg-success">Active</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger">Inactive</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo date('Y-m-d', strtotime($plan['created_at'])); ?></td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <button type="button" class="btn btn-info btn-edit-plan" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#editPlanModal" 
                                                data-id="<?php echo $plan['id']; ?>"
                                                data-name="<?php echo htmlspecialchars($plan['name']); ?>"
                                                data-description="<?php echo htmlspecialchars($plan['description']); ?>"
                                                data-price="<?php echo $plan['price']; ?>"
                                                data-duration="<?php echo $plan['duration_months']; ?>"
                                                data-active="<?php echo $plan['is_active']; ?>"
                                                data-recommended="<?php echo $plan['is_recommended']; ?>">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button type="button" class="btn btn-danger btn-delete-plan" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#deletePlanModal" 
                                                data-id="<?php echo $plan['id']; ?>"
                                                data-name="<?php echo htmlspecialchars($plan['name']); ?>">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                            <a href="premium_subscriptions.php?plan_id=<?php echo $plan['id']; ?>" 
                                               class="btn btn-secondary" title="View subscribers">
                                                <i class="fas fa-users"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <!-- Premium Subscription Stats -->
    <div class="row">
        <div class="col-xl-3 col-md-6">
            <div class="card bg-primary text-white mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>Total Premium Members</div>
                        <div class="h3 mb-0"><?php echo $totalPremiumMembers; ?></div>
                    </div>
                </div>
                <div class="card-footer d-flex align-items-center justify-content-between">
                    <a class="small text-white stretched-link" href="premium_subscribers.php">View Details</a>
                    <div class="small text-white"><i class="fas fa-angle-right"></i></div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card bg-success text-white mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>Total Revenue</div>
                        <div class="h3 mb-0">₹<?php echo number_format($totalRevenue, 2); ?></div>
                    </div>
                </div>
                <div class="card-footer d-flex align-items-center justify-content-between">
                    <a class="small text-white stretched-link" href="premium_revenue.php">View Details</a>
                    <div class="small text-white"><i class="fas fa-angle-right"></i></div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card bg-info text-white mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>Active Plans</div>
                        <div class="h3 mb-0"><?php echo $activePlans; ?></div>
                    </div>
                </div>
                <div class="card-footer d-flex align-items-center justify-content-between">
                    <a class="small text-white stretched-link" href="#" onclick="return false;">Current Page</a>
                    <div class="small text-white"><i class="fas fa-angle-right"></i></div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Plan Modal -->
<div class="modal fade" id="addPlanModal" tabindex="-1" aria-labelledby="addPlanModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="premium_management.php" method="post">
                <div class="modal-header">
                    <h5 class="modal-title" id="addPlanModalLabel">Add New Premium Plan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="action" value="add">
                    
                    <div class="mb-3">
                        <label for="name" class="form-label">Plan Name</label>
                        <input type="text" class="form-control" id="name" name="name" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="3" required></textarea>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="price" class="form-label">Price (₹)</label>
                            <input type="number" class="form-control" id="price" name="price" min="0" step="0.01" required>
                        </div>
                        <div class="col-md-6">
                            <label for="duration_months" class="form-label">Duration (Months)</label>
                            <input type="number" class="form-control" id="duration_months" name="duration_months" min="1" required>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="is_active" name="is_active" value="1" checked>
                                <label class="form-check-label" for="is_active">Active</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="is_recommended" name="is_recommended" value="1">
                                <label class="form-check-label" for="is_recommended">Recommended</label>
                                <div class="form-text small">Only one plan can be recommended</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Plan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Plan Modal -->
<div class="modal fade" id="editPlanModal" tabindex="-1" aria-labelledby="editPlanModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="premium_management.php" method="post">
                <div class="modal-header">
                    <h5 class="modal-title" id="editPlanModalLabel">Edit Premium Plan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" name="plan_id" id="edit_plan_id">
                    
                    <div class="mb-3">
                        <label for="edit_name" class="form-label">Plan Name</label>
                        <input type="text" class="form-control" id="edit_name" name="name" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="edit_description" class="form-label">Description</label>
                        <textarea class="form-control" id="edit_description" name="description" rows="3" required></textarea>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="edit_price" class="form-label">Price (₹)</label>
                            <input type="number" class="form-control" id="edit_price" name="price" min="0" step="0.01" required>
                        </div>
                        <div class="col-md-6">
                            <label for="edit_duration_months" class="form-label">Duration (Months)</label>
                            <input type="number" class="form-control" id="edit_duration_months" name="duration_months" min="1" required>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="edit_is_active" name="is_active" value="1">
                                <label class="form-check-label" for="edit_is_active">Active</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="edit_is_recommended" name="is_recommended" value="1">
                                <label class="form-check-label" for="edit_is_recommended">Recommended</label>
                                <div class="form-text small">Only one plan can be recommended</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Plan Modal -->
<div class="modal fade" id="deletePlanModal" tabindex="-1" aria-labelledby="deletePlanModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="premium_management.php" method="post">
                <div class="modal-header">
                    <h5 class="modal-title" id="deletePlanModalLabel">Delete Premium Plan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="plan_id" id="delete_plan_id">
                    
                    <p>Are you sure you want to delete the plan: <strong id="delete_plan_name"></strong>?</p>
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i> Warning: This action cannot be undone. Existing subscriptions won't be affected.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Delete Plan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Edit plan modal data
    const editButtons = document.querySelectorAll('.btn-edit-plan');
    editButtons.forEach(button => {
        button.addEventListener('click', function() {
            const id = this.getAttribute('data-id');
            const name = this.getAttribute('data-name');
            const description = this.getAttribute('data-description');
            const price = this.getAttribute('data-price');
            const duration = this.getAttribute('data-duration');
            const active = this.getAttribute('data-active');
            const recommended = this.getAttribute('data-recommended');
            
            document.getElementById('edit_plan_id').value = id;
            document.getElementById('edit_name').value = name;
            document.getElementById('edit_description').value = description;
            document.getElementById('edit_price').value = price;
            document.getElementById('edit_duration_months').value = duration;
            document.getElementById('edit_is_active').checked = active == 1;
            document.getElementById('edit_is_recommended').checked = recommended == 1;
        });
    });
    
    // Delete plan modal data
    const deleteButtons = document.querySelectorAll('.btn-delete-plan');
    deleteButtons.forEach(button => {
        button.addEventListener('click', function() {
            const id = this.getAttribute('data-id');
            const name = this.getAttribute('data-name');
            
            document.getElementById('delete_plan_id').value = id;
            document.getElementById('delete_plan_name').textContent = name;
        });
    });
});
</script>

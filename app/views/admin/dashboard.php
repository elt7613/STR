<?php 
// Set page title
$pageTitle = 'Admin Dashboard';

// Include header
require_once ROOT_PATH . '/app/views/admin/partials/header.php'; 
?>

<div class="admin-header">
    <h1><i class="fas fa-tachometer-alt"></i> Admin Dashboard</h1>
    <div class="admin-actions">
        <a href="../logout.php" class="btn btn-danger">
            <i class="fas fa-sign-out-alt"></i> Logout
        </a>
    </div>
</div>

<!-- Dashboard Statistics -->
<div class="dashboard-stats">
    <div class="stat-card">
        <div class="stat-icon">
            <i class="fas fa-users"></i>
        </div>
        <div class="stat-content">
            <div class="stat-value"><?php echo count($users ?? []); ?></div>
            <div class="stat-label">Registered Users</div>
        </div>
        <div class="stat-change increase">
            <i class="fas fa-arrow-up"></i> 12%
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon">
            <i class="fas fa-shopping-cart"></i>
        </div>
        <div class="stat-content">
            <div class="stat-value"><?php echo isset($orderCount) ? intval($orderCount) : 0; ?></div>
            <div class="stat-label">Total Orders</div>
        </div>
        <div class="stat-change <?php echo isset($orderCount) && $orderCount > 0 ? 'increase' : 'decrease'; ?>">
            <i class="fas fa-<?php echo isset($orderCount) && $orderCount > 0 ? 'arrow-up' : 'arrow-down'; ?>"></i> 
            <?php echo isset($pendingOrderCount) && $pendingOrderCount > 0 ? $pendingOrderCount . ' pending' : '0 pending'; ?>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon">
            <i class="fas fa-car"></i>
        </div>
        <div class="stat-content">
            <div class="stat-value"><?php echo count($vehicleSubmissions ?? []); ?></div>
            <div class="stat-label">Vehicle Submissions</div>
        </div>
        <div class="stat-change increase">
            <i class="fas fa-arrow-up"></i> 5%
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon">
            <i class="fas fa-box"></i>
        </div>
        <div class="stat-content">
            <div class="stat-value"><?php echo isset($productCount) ? intval($productCount) : 0; ?></div>
            <div class="stat-label">Products</div>
        </div>
        <div class="stat-change <?php echo isset($productCount) && $productCount > 0 ? 'increase' : 'decrease'; ?>">
            <i class="fas fa-<?php echo isset($productCount) && $productCount > 0 ? 'arrow-up' : 'arrow-down'; ?>"></i> 
            <?php echo isset($productCount) && $productCount > 0 ? round(min($productCount * 2, 100)) . '%' : '0%'; ?>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon">
            <i class="fas fa-tags"></i>
        </div>
        <div class="stat-content">
            <div class="stat-value"><?php echo isset($brandCount) ? intval($brandCount) : 0; ?></div>
            <div class="stat-label">Brands</div>
        </div>
        <div class="stat-change <?php echo isset($brandCount) && $brandCount > 0 ? 'increase' : 'decrease'; ?>">
            <i class="fas fa-<?php echo isset($brandCount) && $brandCount > 0 ? 'arrow-up' : 'arrow-down'; ?>"></i> 
            <?php echo isset($brandCount) && $brandCount > 0 ? round(min($brandCount * 5, 100)) . '%' : '0%'; ?>
        </div>
    </div>
</div>

<div class="admin-quicklinks">
    <h3><i class="fas fa-cogs"></i> Shop Management</h3>
    <div class="quick-action-buttons">
        <a href="manage_orders.php" class="quick-action-btn">
            <i class="fas fa-shopping-cart"></i>
            <span>Manage Orders</span>
        </a>
        <a href="manage_brands.php" class="quick-action-btn">
            <i class="fas fa-tags"></i>
            <span>Manage Brands</span>
        </a>
        <a href="manage_categories.php" class="quick-action-btn">
            <i class="fas fa-list"></i>
            <span>Manage Categories</span>
        </a>
        <a href="manage_products.php" class="quick-action-btn">
            <i class="fas fa-box"></i>
            <span>Manage Products</span>
        </a>
        <a href="manage_vehicles.php" class="quick-action-btn">
            <i class="fas fa-car"></i>
            <span>Manage Vehicles</span>
        </a>
    </div>
</div>

<div class="admin-tabs">
    <button class="tab-button active" data-tab="users">
        <i class="fas fa-users"></i> Users
    </button>
    <button class="tab-button" data-tab="submissions">
        <i class="fas fa-car-side"></i> Vehicle Submissions
    </button>
    <button class="tab-button" data-tab="vehicles">
        <i class="fas fa-car"></i> Vehicle Data
    </button>
</div>

<div id="users-tab" class="tab-content active">
    <div class="content-header">
        <h2><i class="fas fa-users"></i> Registered Users</h2>
    </div>
    
    <?php if (empty($users)): ?>
        <div class="empty-state">
            <div class="empty-state-icon">
                <i class="fas fa-users"></i>
            </div>
            <h3>No Users Found</h3>
            <p>There are no registered users in the system yet.</p>
        </div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Status</th>
                        <th>Created At</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($user['id']); ?></td>
                        <td>
                            <div class="user-info">
                                <div class="user-avatar">
                                    <i class="fas fa-user"></i>
                                </div>
                                <span><?php echo htmlspecialchars($user['username']); ?></span>
                            </div>
                        </td>
                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                        <td>
                            <?php if ($user['is_admin'] == 1): ?>
                                <span class="badge badge-admin">Admin</span>
                            <?php endif; ?>
                            <?php if ($user['is_premium_member'] == 1): ?>
                                <span class="badge badge-premium">Premium</span>
                            <?php else: ?>
                                <span class="badge badge-standard">Standard</span>
                            <?php endif; ?>
                        </td>
                        <td><?php echo htmlspecialchars($user['created_at']); ?></td>
                        <td>
                            <div class="action-buttons">
                                <button class="btn btn-primary btn-small edit-user-btn" 
                                        data-user-id="<?php echo $user['id']; ?>"
                                        data-username="<?php echo htmlspecialchars($user['username']); ?>"
                                        data-email="<?php echo htmlspecialchars($user['email']); ?>"
                                        data-is-premium="<?php echo $user['is_premium_member']; ?>">
                                    <i class="fas fa-edit"></i> Edit
                                </button>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<!-- User Edit Modal -->
<div id="editUserModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3><i class="fas fa-user-edit"></i> Edit User</h3>
            <button class="close-modal"><i class="fas fa-times"></i></button>
        </div>
        <form id="editUserForm" action="update_user.php" method="post">
            <div class="modal-body">
                <input type="hidden" id="editUserId" name="user_id">
                
                <div class="form-group">
                    <label for="editUsername" class="form-label">Username</label>
                    <div class="input-with-icon">
                        <i class="fas fa-user"></i>
                        <input type="text" id="editUsername" name="username" class="form-control" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="editEmail" class="form-label">Email</label>
                    <div class="input-with-icon">
                        <i class="fas fa-envelope"></i>
                        <input type="email" id="editEmail" name="email" class="form-control" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="editPassword" class="form-label">New Password (leave blank to keep current)</label>
                    <div class="input-with-icon">
                        <i class="fas fa-lock"></i>
                        <input type="password" id="editPassword" name="password" class="form-control">
                    </div>
                    <small class="form-hint">Minimum 8 characters</small>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Membership Status</label>
                    <div class="toggle-switch">
                        <input type="checkbox" id="editIsPremium" name="is_premium_member" value="1">
                        <label for="editIsPremium">Premium Member</label>
                    </div>
                </div>
            </div>
            
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary cancel-btn">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Changes</button>
            </div>
        </form>
    </div>
</div>

<div id="submissions-tab" class="tab-content">
    <div class="content-header">
        <h2><i class="fas fa-car-side"></i> Vehicle Submissions</h2>
        <div class="content-actions">
            <button id="exportSubmissions" class="btn btn-outline-secondary btn-small">
                <i class="fas fa-download"></i> Export
            </button>
        </div>
    </div>
    
    <?php if (empty($vehicleSubmissions)): ?>
        <div class="empty-state">
            <div class="empty-state-icon">
                <i class="fas fa-car-side"></i>
            </div>
            <h3>No Submissions Found</h3>
            <p>There are no vehicle submissions in the system yet.</p>
        </div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="admin-table" id="submissionsTable">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>User</th>
                        <th>Vehicle</th>
                        <th>Contact</th>
                        <th>Submitted On</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($vehicleSubmissions as $submission): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($submission['id']); ?></td>
                        <td><?php echo htmlspecialchars(getUsernameById($submission['user_id'])); ?></td>
                        <td>
                            <div class="vehicle-info">
                                <span class="vehicle-make"><?php echo htmlspecialchars(getVehicleMakeName($submission['make_id'])); ?></span>
                                <span class="vehicle-model"><?php echo htmlspecialchars(getVehicleModelName($submission['model_id'])); ?></span>
                                <span class="vehicle-series badge"><?php echo htmlspecialchars(getVehicleSeriesName($submission['series_id'])); ?></span>
                            </div>
                        </td>
                        <td>
                            <div class="contact-info">
                                <div><i class="fas fa-phone"></i> <?php echo htmlspecialchars($submission['phone']); ?></div>
                                <div><i class="fas fa-envelope"></i> <?php echo htmlspecialchars($submission['email']); ?></div>
                            </div>
                        </td>
                        <td><?php echo htmlspecialchars($submission['created_at']); ?></td>
                        <td>
                            <div class="action-buttons">
                                <button class="btn btn-primary btn-small view-submission-btn" 
                                        data-submission-id="<?php echo $submission['id']; ?>"
                                        data-user-id="<?php echo $submission['user_id']; ?>"
                                        data-username="<?php echo htmlspecialchars(getUsernameById($submission['user_id'])); ?>"
                                        data-make="<?php echo htmlspecialchars(getVehicleMakeName($submission['make_id'])); ?>"
                                        data-model="<?php echo htmlspecialchars(getVehicleModelName($submission['model_id'])); ?>"
                                        data-series="<?php echo htmlspecialchars(getVehicleSeriesName($submission['series_id'])); ?>"
                                        data-phone="<?php echo htmlspecialchars($submission['phone']); ?>"
                                        data-email="<?php echo htmlspecialchars($submission['email']); ?>"
                                        data-message="<?php echo htmlspecialchars($submission['message'] ?? ''); ?>"
                                        data-created="<?php echo htmlspecialchars($submission['created_at']); ?>">
                                    <i class="fas fa-eye"></i> View
                                </button>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<!-- Submission View Modal -->
<div id="viewSubmissionModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3><i class="fas fa-car-side"></i> Vehicle Submission Details</h3>
            <button class="close-modal"><i class="fas fa-times"></i></button>
        </div>
        <div class="modal-body">
            <div class="submission-details">
                <div class="submission-section">
                    <h4><i class="fas fa-info-circle"></i> Basic Information</h4>
                    <div class="detail-row">
                        <div class="detail-label">Submission ID:</div>
                        <div class="detail-value" id="submissionId"></div>
                    </div>
                    <div class="detail-row">
                        <div class="detail-label">Submitted On:</div>
                        <div class="detail-value" id="submissionDate"></div>
                    </div>
                    <div class="detail-row">
                        <div class="detail-label">Submitted By:</div>
                        <div class="detail-value" id="submissionUser"></div>
                    </div>
                </div>
                
                <div class="submission-section">
                    <h4><i class="fas fa-car"></i> Vehicle Information</h4>
                    <div class="detail-row">
                        <div class="detail-label">Make:</div>
                        <div class="detail-value" id="vehicleMake"></div>
                    </div>
                    <div class="detail-row">
                        <div class="detail-label">Model:</div>
                        <div class="detail-value" id="vehicleModel"></div>
                    </div>
                    <div class="detail-row">
                        <div class="detail-label">Series:</div>
                        <div class="detail-value" id="vehicleSeries"></div>
                    </div>
                </div>
                
                <div class="submission-section">
                    <h4><i class="fas fa-address-card"></i> Contact Information</h4>
                    <div class="detail-row">
                        <div class="detail-label">Phone Number:</div>
                        <div class="detail-value" id="contactPhone"></div>
                    </div>
                    <div class="detail-row">
                        <div class="detail-label">Email Address:</div>
                        <div class="detail-value" id="contactEmail"></div>
                    </div>
                </div>
                
                <div class="submission-section">
                    <h4><i class="fas fa-comment"></i> Additional Information</h4>
                    <div class="message-box" id="submissionMessage">
                        No additional information provided.
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-outline-secondary cancel-btn">Close</button>
            <button type="button" class="btn btn-primary" id="printSubmission">
                <i class="fas fa-print"></i> Print
            </button>
        </div>
    </div>
</div>

<div id="vehicles-tab" class="tab-content">
    <div class="content-header">
        <h2><i class="fas fa-car"></i> Vehicle Data Management</h2>
    </div>
    
    <div class="vehicle-management-summary">
        <p>Manage vehicle makes, models, and series for the vehicle submission form.</p>
        
        <div class="vehicle-data-stats">
            <div class="vehicle-stat-card">
                <div class="stat-icon">
                    <i class="fas fa-car-side"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-value"><?php echo isset($makeCount) ? intval($makeCount) : 0; ?></div>
                    <div class="stat-label">Makes</div>
                </div>
            </div>
            
            <div class="vehicle-stat-card">
                <div class="stat-icon">
                    <i class="fas fa-car"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-value"><?php echo isset($modelCount) ? intval($modelCount) : 0; ?></div>
                    <div class="stat-label">Models</div>
                </div>
            </div>
            
            <div class="vehicle-stat-card">
                <div class="stat-icon">
                    <i class="fas fa-cogs"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-value"><?php echo isset($seriesCount) ? intval($seriesCount) : 0; ?></div>
                    <div class="stat-label">Series</div>
                </div>
            </div>
        </div>
        
        <a href="../admin/manage_vehicles.php" class="btn btn-primary">
            <i class="fas fa-car"></i> Go to Vehicle Management
        </a>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const tabButtons = document.querySelectorAll('.tab-button');
    const tabContents = document.querySelectorAll('.tab-content');
    
    tabButtons.forEach(button => {
        button.addEventListener('click', function() {
            // Remove active class from all buttons and contents
            tabButtons.forEach(btn => btn.classList.remove('active'));
            tabContents.forEach(content => content.classList.remove('active'));
            
            // Add active class to clicked button
            this.classList.add('active');
            
            // Show corresponding tab content
            const tabId = this.getAttribute('data-tab');
            document.getElementById(`${tabId}-tab`).classList.add('active');
        });
    });

    // User edit modal functionality
    const editModal = document.getElementById('editUserModal');
    const editForm = document.getElementById('editUserForm');
    const editButtons = document.querySelectorAll('.edit-user-btn');
    const closeModal = document.querySelector('.close-modal');
    const cancelBtn = document.querySelector('.cancel-btn');
    
    // User ID, username and email fields
    const userIdField = document.getElementById('editUserId');
    const usernameField = document.getElementById('editUsername');
    const emailField = document.getElementById('editEmail');
    
    // Open modal when edit button is clicked
    editButtons.forEach(button => {
        button.addEventListener('click', function() {
            // Get user data from button attributes
            const userId = this.getAttribute('data-user-id');
            const username = this.getAttribute('data-username');
            const email = this.getAttribute('data-email');
            const isPremium = this.getAttribute('data-is-premium');
            
            // Set values in the form
            userIdField.value = userId;
            usernameField.value = username;
            emailField.value = email;
            
            // Set membership status
            const isPremiumCheckbox = document.getElementById('editIsPremium');
            isPremiumCheckbox.checked = isPremium === '1';
            
            // Show modal
            editModal.classList.add('active');
        });
    });
    
    // Close modal when X button is clicked
    if (closeModal) {
        closeModal.addEventListener('click', function() {
            editModal.classList.remove('active');
        });
    }
    
    // Close modal when Cancel button is clicked
    if (cancelBtn) {
        cancelBtn.addEventListener('click', function() {
            editModal.classList.remove('active');
        });
    }
    
    // Close modal when clicking outside
    window.addEventListener('click', function(event) {
        if (event.target === editModal) {
            editModal.classList.remove('active');
        }
    });
    
    // Form submission handling
    if (editForm) {
        editForm.addEventListener('submit', function(event) {
            // Add any form validation here if needed
        });
    }

    // Vehicle submission view modal functionality
    const submissionModal = document.getElementById('viewSubmissionModal');
    const viewButtons = document.querySelectorAll('.view-submission-btn');
    const closeSubmissionModal = submissionModal?.querySelector('.close-modal');
    const cancelSubmissionBtn = submissionModal?.querySelector('.cancel-btn');
    
    // Submission detail elements
    const submissionId = document.getElementById('submissionId');
    const submissionDate = document.getElementById('submissionDate');
    const submissionUser = document.getElementById('submissionUser');
    const vehicleMake = document.getElementById('vehicleMake');
    const vehicleModel = document.getElementById('vehicleModel');
    const vehicleSeries = document.getElementById('vehicleSeries');
    const contactPhone = document.getElementById('contactPhone');
    const contactEmail = document.getElementById('contactEmail');
    const submissionMessage = document.getElementById('submissionMessage');
    
    // Open modal when view button is clicked
    viewButtons.forEach(button => {
        button.addEventListener('click', function() {
            // Get submission data from button attributes
            const id = this.getAttribute('data-submission-id');
            const username = this.getAttribute('data-username');
            const make = this.getAttribute('data-make');
            const model = this.getAttribute('data-model');
            const series = this.getAttribute('data-series');
            const phone = this.getAttribute('data-phone');
            const email = this.getAttribute('data-email');
            const message = this.getAttribute('data-message');
            const created = this.getAttribute('data-created');
            
            // Set values in the modal
            submissionId.textContent = id;
            submissionDate.textContent = created;
            submissionUser.textContent = username;
            vehicleMake.textContent = make;
            vehicleModel.textContent = model;
            vehicleSeries.textContent = series;
            contactPhone.textContent = phone;
            contactEmail.textContent = email;
            submissionMessage.textContent = message || 'No additional information provided.';
            
            // Show modal
            submissionModal.classList.add('active');
        });
    });
    
    // Close modal when close button is clicked
    if (closeSubmissionModal) {
        closeSubmissionModal.addEventListener('click', function() {
            submissionModal.classList.remove('active');
        });
    }
    
    // Close modal when cancel/close button is clicked
    if (cancelSubmissionBtn) {
        cancelSubmissionBtn.addEventListener('click', function() {
            submissionModal.classList.remove('active');
        });
    }
    
    // Close modal when clicking outside the modal content
    if (submissionModal) {
        submissionModal.addEventListener('click', function(event) {
            if (event.target === submissionModal) {
                submissionModal.classList.remove('active');
            }
        });
    }
    
    // Print submission details
    const printSubmissionBtn = document.getElementById('printSubmission');
    if (printSubmissionBtn) {
        printSubmissionBtn.addEventListener('click', function() {
            const printWindow = window.open('', '_blank');
            
            // Build HTML content for printing
            let printContent = `
                <!DOCTYPE html>
                <html>
                <head>
                    <title>Vehicle Submission #${submissionId.textContent}</title>
                    <style>
                        body { font-family: Arial, sans-serif; line-height: 1.6; }
                        .header { text-align: center; margin-bottom: 20px; }
                        .header h1 { margin-bottom: 5px; }
                        .submission-section { margin-bottom: 30px; }
                        .submission-section h2 { 
                            border-bottom: 1px solid #ddd; 
                            padding-bottom: 10px; 
                            margin-bottom: 15px; 
                        }
                        .detail-row { display: flex; margin-bottom: 10px; }
                        .detail-label { font-weight: bold; width: 180px; }
                        .message-box { 
                            border: 1px solid #ddd; 
                            padding: 15px; 
                            border-radius: 5px; 
                            margin-top: 10px; 
                        }
                        .footer { margin-top: 30px; text-align: center; font-size: 12px; color: #666; }
                    </style>
                </head>
                <body>
                    <div class="header">
                        <h1>Vehicle Submission Details</h1>
                        <p>Submission ID: ${submissionId.textContent} | Date: ${submissionDate.textContent}</p>
                    </div>
                    
                    <div class="submission-section">
                        <h2>Basic Information</h2>
                        <div class="detail-row">
                            <div class="detail-label">Submitted By:</div>
                            <div class="detail-value">${submissionUser.textContent}</div>
                        </div>
                        <div class="detail-row">
                            <div class="detail-label">Submission Date:</div>
                            <div class="detail-value">${submissionDate.textContent}</div>
                        </div>
                    </div>
                    
                    <div class="submission-section">
                        <h2>Vehicle Information</h2>
                        <div class="detail-row">
                            <div class="detail-label">Make:</div>
                            <div class="detail-value">${vehicleMake.textContent}</div>
                        </div>
                        <div class="detail-row">
                            <div class="detail-label">Model:</div>
                            <div class="detail-value">${vehicleModel.textContent}</div>
                        </div>
                        <div class="detail-row">
                            <div class="detail-label">Series:</div>
                            <div class="detail-value">${vehicleSeries.textContent}</div>
                        </div>
                    </div>
                    
                    <div class="submission-section">
                        <h2>Contact Information</h2>
                        <div class="detail-row">
                            <div class="detail-label">Phone Number:</div>
                            <div class="detail-value">${contactPhone.textContent}</div>
                        </div>
                        <div class="detail-row">
                            <div class="detail-label">Email Address:</div>
                            <div class="detail-value">${contactEmail.textContent}</div>
                        </div>
                    </div>
                    
                    <div class="submission-section">
                        <h2>Additional Information</h2>
                        <div class="message-box">${submissionMessage.textContent}</div>
                    </div>
                    
                    <div class="footer">
                        <p>Printed from STR Admin Panel | ${new Date().toLocaleString()}</p>
                    </div>
                </body>
                </html>
            `;
            
            printWindow.document.open();
            printWindow.document.write(printContent);
            printWindow.document.close();
            
            // Wait for content to load before printing
            printWindow.onload = function() {
                printWindow.print();
                // printWindow.close(); // Uncomment to automatically close after print dialog
            };
        });
    }
    
    // Export submissions to CSV
    const exportBtn = document.getElementById('exportSubmissions');
    if (exportBtn) {
        exportBtn.addEventListener('click', function() {
            const table = document.getElementById('submissionsTable');
            if (!table) return;
            
            // Prepare CSV content
            let csvContent = "data:text/csv;charset=utf-8,";
            
            // Get all rows from table
            const rows = table.querySelectorAll('tr');
            
            // Add header row
            const headerRow = rows[0];
            const headers = headerRow.querySelectorAll('th');
            const headerValues = [];
            
            // Skip the last column (Actions)
            for (let i = 0; i < headers.length - 1; i++) {
                headerValues.push('"' + headers[i].textContent.trim() + '"');
            }
            csvContent += headerValues.join(',') + '\r\n';
            
            // Add data rows
            for (let i = 1; i < rows.length; i++) {
                const row = rows[i];
                const cols = row.querySelectorAll('td');
                const rowValues = [];
                
                // Skip the last column (Actions)
                for (let j = 0; j < cols.length - 1; j++) {
                    // Special handling for vehicle and contact info cells that have multiple lines
                    if (j === 2) { // Vehicle info column
                        const make = cols[j].querySelector('.vehicle-make')?.textContent || '';
                        const model = cols[j].querySelector('.vehicle-model')?.textContent || '';
                        const series = cols[j].querySelector('.vehicle-series')?.textContent || '';
                        rowValues.push('"' + make + ' ' + model + ' ' + series + '"');
                    } else if (j === 3) { // Contact info column
                        const phone = cols[j].querySelector('.contact-info div:first-child')?.textContent.replace('phone_icon', '').trim() || '';
                        const email = cols[j].querySelector('.contact-info div:last-child')?.textContent.replace('email_icon', '').trim() || '';
                        rowValues.push('"' + phone + ' / ' + email + '"');
                    } else {
                        // Regular text content for other columns
                        rowValues.push('"' + cols[j].textContent.trim() + '"');
                    }
                }
                
                csvContent += rowValues.join(',') + '\r\n';
            }
            
            // Create download link and trigger download
            const encodedUri = encodeURI(csvContent);
            const link = document.createElement('a');
            link.setAttribute('href', encodedUri);
            link.setAttribute('download', 'vehicle_submissions_' + new Date().toISOString().slice(0, 10) + '.csv');
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        });
    }
});
</script>

<style>
/* Additional Dashboard Styles */
.dashboard-stats {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
    gap: 1.5rem;
    margin-bottom: 2rem;
    padding: 0 1.5rem;
}

.stat-card {
    background-color: white;
    border-radius: var(--radius-md);
    padding: 1.5rem;
    display: flex;
    align-items: center;
    box-shadow: var(--shadow-sm);
    transition: transform 0.3s, box-shadow 0.3s;
    position: relative;
    overflow: hidden;
}

.stat-card:hover {
    transform: translateY(-5px);
    box-shadow: var(--shadow-md);
}

.stat-icon {
    width: 48px;
    height: 48px;
    border-radius: 12px;
    background-color: rgba(67, 97, 238, 0.1);
    color: var(--primary-color);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    margin-right: 1rem;
}

.stat-content {
    flex: 1;
}

.stat-value {
    font-size: 1.75rem;
    font-weight: 700;
    color: var(--gray-800);
    margin-bottom: 0.25rem;
}

.stat-label {
    color: var(--gray-500);
    font-size: 0.875rem;
}

.stat-change {
    position: absolute;
    top: 1rem;
    right: 1rem;
    font-size: 0.75rem;
    font-weight: 500;
    padding: 0.25rem 0.5rem;
    border-radius: 1rem;
}

.stat-change.increase {
    background-color: rgba(16, 185, 129, 0.1);
    color: var(--success-color);
}

.stat-change.decrease {
    background-color: rgba(239, 68, 68, 0.1);
    color: var(--danger-color);
}

.quick-action-buttons {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap: 1rem;
    margin-top: 1rem;
}

.quick-action-btn {
    background-color: white;
    border: 1px solid var(--gray-200);
    border-radius: var(--radius-md);
    padding: 1.25rem;
    text-align: center;
    text-decoration: none;
    color: var(--gray-700);
    transition: all var(--transition-speed);
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0.75rem;
}

.quick-action-btn i {
    font-size: 2rem;
    color: var(--primary-color);
    margin-bottom: 0.5rem;
}

.quick-action-btn span {
    font-weight: 500;
}

.quick-action-btn:hover {
    border-color: var(--primary-color);
    transform: translateY(-5px);
    box-shadow: var(--shadow-md);
    color: var(--primary-color);
}

.content-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1.5rem;
}

.content-header h2 {
    font-size: 1.5rem;
    font-weight: 600;
    color: var(--gray-800);
    margin: 0;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.content-actions {
    display: flex;
    gap: 0.5rem;
}

.table-responsive {
    overflow-x: auto;
    border-radius: var(--radius-md);
    box-shadow: var(--shadow-sm);
}

.user-info {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.user-avatar {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    background-color: var(--gray-200);
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--gray-700);
}

.action-buttons {
    display: flex;
    gap: 0.5rem;
    flex-wrap: wrap;
}

.vehicle-info {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
}

.vehicle-make {
    font-weight: 600;
    color: var(--gray-800);
}

.vehicle-model {
    color: var(--gray-600);
    font-size: 0.9rem;
}

.badge {
    display: inline-block;
    padding: 0.25rem 0.5rem;
    border-radius: 1rem;
    font-size: 0.75rem;
    font-weight: 500;
    background-color: var(--primary-color);
    color: white;
    margin-top: 0.25rem;
}

.contact-info {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
    font-size: 0.9rem;
}

.contact-info i {
    color: var(--gray-500);
    width: 16px;
}

.empty-state {
    text-align: center;
    padding: 2rem;
    background-color: white;
    border-radius: var(--radius-md);
    border: 1px dashed var(--gray-300);
}

.empty-state-icon {
    font-size: 3rem;
    color: var(--gray-400);
    margin-bottom: 1rem;
}

.empty-state h3 {
    color: var(--gray-800);
    margin-bottom: 0.5rem;
}

.empty-state p {
    color: var(--gray-500);
}

.vehicle-management-summary {
    background-color: white;
    border-radius: var(--radius-md);
    padding: 2rem;
    text-align: center;
    box-shadow: var(--shadow-sm);
}

.vehicle-data-stats {
    display: flex;
    justify-content: center;
    gap: 2rem;
    margin: 2rem 0;
}

.vehicle-stat-card {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0.75rem;
}

.vehicle-stat-card .stat-icon {
    margin-right: 0;
    width: 64px;
    height: 64px;
    border-radius: 1rem;
}

.vehicle-stat-card .stat-content {
    text-align: center;
}

@media (max-width: 992px) {
    .dashboard-stats {
        grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    }
}

@media (max-width: 768px) {
    .dashboard-stats {
        grid-template-columns: 1fr;
    }
    
    .vehicle-data-stats {
        flex-direction: column;
        gap: 1rem;
    }
    
    .content-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 1rem;
    }
    
    .content-actions {
        width: 100%;
    }
    
    .content-actions .btn {
        flex: 1;
    }
}

/* Modal Styles */
.modal {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.5);
    z-index: 1000;
    align-items: center;
    justify-content: center;
}

.modal.active {
    display: flex;
    animation: fadeIn 0.3s ease;
}

.modal-content {
    background-color: white;
    border-radius: var(--radius-md);
    width: 100%;
    max-width: 500px;
    box-shadow: var(--shadow-lg);
    overflow: hidden;
    animation: slideUp 0.3s ease-out;
}

@keyframes slideUp {
    from { transform: translateY(30px); opacity: 0; }
    to { transform: translateY(0); opacity: 1; }
}

.modal-header {
    padding: 1.25rem;
    border-bottom: 1px solid var(--gray-200);
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.modal-header h3 {
    margin: 0;
    font-size: 1.25rem;
    font-weight: 600;
    color: var(--gray-800);
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.close-modal {
    background: none;
    border: none;
    font-size: 1.25rem;
    color: var(--gray-500);
    cursor: pointer;
    transition: color var(--transition-speed);
}

.close-modal:hover {
    color: var(--gray-800);
}

.modal-body {
    padding: 1.5rem;
}

.modal-footer {
    padding: 1.25rem;
    border-top: 1px solid var(--gray-200);
    display: flex;
    justify-content: flex-end;
    gap: 0.75rem;
}

/* Additional styles for submission details */
.submission-details {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

.submission-section {
    background-color: var(--gray-100);
    border-radius: var(--radius-md);
    padding: 1.25rem;
}

.submission-section h4 {
    margin-top: 0;
    margin-bottom: 1rem;
    color: var(--gray-700);
    font-size: 1.1rem;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    border-bottom: 1px solid var(--gray-200);
    padding-bottom: 0.75rem;
}

.detail-row {
    display: flex;
    margin-bottom: 0.75rem;
}

.detail-label {
    font-weight: 500;
    color: var(--gray-600);
    width: 140px;
    flex-shrink: 0;
}

.detail-value {
    color: var(--gray-800);
}

.message-box {
    background-color: white;
    border: 1px solid var(--gray-300);
    border-radius: var(--radius-sm);
    padding: 1rem;
    min-height: 80px;
    color: var(--gray-700);
    line-height: 1.6;
}
</style>

<?php require_once ROOT_PATH . '/app/views/admin/partials/footer.php'; ?> 
<?php 
// Set page title
$pageTitle = 'My Profile - STR Works';
$custom_css = 'profile.css';

// Helper function to get icon for order status
function getStatusIcon($status) {
    switch (strtolower($status)) {
        case 'pending':
            return 'clock';
        case 'processing':
            return 'spinner';
        case 'completed':
            return 'check-circle';
        case 'cancelled':
            return 'times-circle';
        case 'failed':
            return 'exclamation-circle';
        default:
            return 'info-circle';
    }
}

// Include header
require_once __DIR__ . '/partials/header.php'; 
?>

<div class="container profile-container">
    <h1 class="profile-title">My Account</h1>
    
    <?php if (!empty($error)): ?>
        <div class="alert alert-danger">
            <?php echo $error; ?>
        </div>
    <?php endif; ?>
    
    <?php if (!empty($success)): ?>
        <div class="alert alert-success">
            <?php echo $success; ?>
        </div>
    <?php endif; ?>
    
    <div class="profile-tabs">
        <ul class="nav-tabs">
            <li class="<?php echo $currentTab === 'profile' ? 'active' : ''; ?>">
                <a href="profile.php?tab=profile">Profile</a>
            </li>
            <li class="<?php echo $currentTab === 'orders' ? 'active' : ''; ?>">
                <a href="profile.php?tab=orders">Orders</a>
            </li>
            <li class="<?php echo $currentTab === 'vehicles' ? 'active' : ''; ?>">
                <a href="profile.php?tab=vehicles">My Vehicles</a>
            </li>
        </ul>
    </div>
    
    <div class="tab-content">
        <?php if ($currentTab === 'profile'): ?>
            <!-- Profile information tab -->
            <div class="profile-info">
                <h2>Profile Information</h2>
                <p class="profile-status">
                    <?php if (isset($user['is_premium_member']) && $user['is_premium_member'] == 1): ?>
                        <span class="premium-badge"><i class="fas fa-crown"></i> Premium Member</span>
                    <?php else: ?>
                        <span class="regular-badge"><i class="fas fa-user"></i> Regular Member</span>
                    <?php endif; ?>
                </p>
                
                <div class="profile-image-section">
                    <div class="profile-image-container">
                        <?php if (!empty($user['profile_image']) && file_exists($_SERVER['DOCUMENT_ROOT'] . '/uploads/profile_images/' . $user['profile_image'])): ?>
                            <?php
                            // Fix for both local and hostinger environments
                            $img_path = 'uploads/profile_images/' . htmlspecialchars($user['profile_image']);
                            
                            // Make sure we have a valid path that works in both environments
                            if (defined('BASE_URL')) {
                                $img_url = rtrim(BASE_URL, '/') . '/' . $img_path;
                            } else {
                                $img_url = '/' . $img_path;
                            }
                            ?>
                            <img src="<?php echo $img_url; ?>" alt="Profile Image" class="profile-image">
                        <?php else: ?>
                            <div class="profile-image-placeholder">
                                <i class="fas fa-user"></i>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="profile-image-actions">
                        <?php if (!empty($user['profile_image'])): ?>
                            <form action="profile.php?tab=profile" method="POST">
                                <button type="submit" name="remove_profile_image" class="btn-outline btn-sm btn-delete">
                                    <i class="fas fa-trash"></i> Remove Photo
                                </button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
                
                <form action="profile.php?tab=profile" method="POST" class="profile-form" enctype="multipart/form-data">
                    <div class="profile-form-content">
                        <div class="form-group profile-form-full">
                            <label for="profile_image">Update Profile Image</label>
                            <div class="file-input-wrapper">
                                <input type="file" id="profile_image" name="profile_image" class="form-control-file" accept="image/jpeg,image/png,image/gif">
                                <label for="profile_image" class="file-input-button">
                                    <i class="fas fa-upload"></i> Choose File
                                </label>
                                <small class="form-text text-muted">Accepted formats: JPEG, PNG, GIF. Maximum size: 5MB.</small>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="username">Username</label>
                            <input type="text" id="username" name="username" class="form-control" value="<?php echo htmlspecialchars($user['username'] ?? ''); ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="email">Email Address</label>
                            <input type="email" id="email" name="email" class="form-control" value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>" required>
                        </div>
                        
                        <div class="form-group profile-form-full">
                            <label for="phone">Phone</label>
                            <input type="text" id="phone" name="phone" class="form-control" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>">
                        </div>
                    </div>
                    
                    <h3 class="password-heading">Change Password</h3>
                    <div class="password-section">
                        <div class="profile-form-content">
                            <div class="form-group">
                                <label for="current_password">Current Password</label>
                                <input type="password" id="current_password" name="current_password" class="form-control">
                            </div>
                            
                            <div class="form-group">
                                <label for="new_password">New Password</label>
                                <input type="password" id="new_password" name="new_password" class="form-control">
                                <small class="form-text text-muted">Must be at least 8 characters long</small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="action-buttons">
                        <button type="submit" name="update_profile" class="btn-primary">
                            <i class="fas fa-save"></i> Save Changes
                        </button>
                    </div>
                </form>
            </div>
        <?php elseif ($currentTab === 'orders'): ?>
            <!-- Orders tab -->
            <div class="orders-list">
                <h2>My Orders</h2>
                
                <?php if (empty($orders)): ?>
                    <div class="no-orders">
                        <p>You haven't placed any orders yet.</p>
                        <a href="shop.php" class="btn-outline">
                            <i class="fas fa-shopping-cart"></i> Shop Now
                        </a>
                    </div>
                <?php else: ?>
                    <div class="order-table-container">
                        <table class="order-table">
                            <thead>
                                <tr>
                                    <th>Order #</th>
                                    <th>Date</th>
                                    <th>Status</th>
                                    <th>Total</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($orders as $order): ?>
                                    <tr>
                                        <td><a href="#" class="order-number" data-order-id="<?php echo $order['id']; ?>"><?php echo htmlspecialchars($order['order_number']); ?></a></td>
                                        <td><?php echo date('M d, Y', strtotime($order['created_at'])); ?></td>
                                        <td>
                                            <span class="order-status status-<?php echo strtolower($order['status']); ?>">
                                                <i class="fas fa-<?php echo getStatusIcon($order['status']); ?>"></i>
                                                <?php echo ucfirst(htmlspecialchars($order['status'])); ?>
                                            </span>
                                        </td>
                                        <td><strong>₹<?php echo number_format($order['total'], 2); ?></strong></td>
                                        <td class="order-actions">
                                            <button class="view-order-btn" data-order-id="<?php echo $order['id']; ?>">
                                                <i class="fas fa-eye"></i> View
                                            </button>
                                            
                                            <?php if ($order['status'] === 'pending' || $order['status'] === 'processing'): ?>
                                                <form method="POST" action="profile.php?tab=orders" class="cancel-order-form" onsubmit="return confirm('Are you sure you want to cancel this order?');">
                                                    <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                                    <button type="submit" name="cancel_order" class="cancel-order-btn">
                                                        <i class="fas fa-times"></i> Cancel
                                                    </button>
                                                </form>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Order Details Modal -->
                    <div id="orderModal" class="modal">
                        <div class="modal-content">
                            <span class="close-modal">&times;</span>
                            <div id="orderDetails" class="order-details">
                                <!-- Order details will be loaded here dynamically -->
                                <div class="loading">Loading order details...</div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        <?php elseif ($currentTab === 'vehicles'): ?>
            <!-- Vehicles tab -->
            <div class="vehicles-list">
                <h2>My Vehicles</h2>
                <div class="add-vehicle-section">
                    <button id="addVehicleBtn" class="btn-primary">
                        <i class="fas fa-car"></i> Add Vehicle Details
                    </button>
                </div>
                
                <?php if (empty($vehicles)): ?>
                    <div class="no-vehicles">
                        <p>You haven't added any vehicle information yet.</p>
                    </div>
                <?php else: ?>
                    <div class="vehicles-grid">
                        <?php foreach ($vehicles as $vehicle): ?>
                            <div class="vehicle-card">
                                <div class="vehicle-card-header">
                                    <h3><?php echo htmlspecialchars($vehicle['brand']); ?> <?php echo htmlspecialchars($vehicle['model']); ?></h3>
                                    <?php if (!empty($vehicle['series'])): ?>
                                        <p class="vehicle-series"><?php echo htmlspecialchars($vehicle['series']); ?></p>
                                    <?php endif; ?>
                                </div>
                                
                                <?php if (!empty($vehicle['images'])): ?>
                                    <div class="vehicle-images">
                                        <?php foreach ($vehicle['images'] as $index => $image): ?>
                                            <?php if ($index === 0): ?>
                                                <img src="/uploads/vehicle_images/<?php echo htmlspecialchars($image['image_path']); ?>" alt="Vehicle Image" class="vehicle-primary-image">
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                        <?php if (count($vehicle['images']) > 1): ?>
                                            <div class="vehicle-image-count">+<?php echo count($vehicle['images']) - 1; ?> more</div>
                                        <?php endif; ?>
                                    </div>
                                <?php else: ?>
                                    <div class="vehicle-no-image">
                                        <i class="fas fa-car"></i>
                                    </div>
                                <?php endif; ?>
                                
                                <div class="vehicle-card-actions">
                                    <button class="btn-outline btn-sm view-vehicle-images" data-vehicle-id="<?php echo $vehicle['id']; ?>">
                                        <i class="fas fa-images"></i> View Images
                                    </button>
                                    <form action="profile.php?tab=vehicles" method="POST" onsubmit="return confirm('Are you sure you want to delete this vehicle information?');">
                                        <input type="hidden" name="vehicle_id" value="<?php echo $vehicle['id']; ?>">
                                        <button type="submit" name="delete_vehicle" class="btn-outline btn-sm btn-delete">
                                            <i class="fas fa-trash"></i> Delete
                                        </button>
                                    </form>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Vehicle Form Modal -->
<div id="vehicleFormModal" class="modal">
    <div class="modal-content">
        <span class="close">&times;</span>
        <h2>Add Vehicle Details</h2>
        <form action="profile.php?tab=vehicles" method="POST" enctype="multipart/form-data" class="vehicle-form">
            <div class="form-group">
                <label for="vehicle_brand">Vehicle Brand <span class="required">*</span></label>
                <input type="text" id="vehicle_brand" name="vehicle_brand" class="form-control" required>
            </div>
            
            <div class="form-group">
                <label for="vehicle_model">Vehicle Model <span class="required">*</span></label>
                <input type="text" id="vehicle_model" name="vehicle_model" class="form-control" required>
            </div>
            
            <div class="form-group">
                <label for="vehicle_series">Vehicle Series (Optional)</label>
                <input type="text" id="vehicle_series" name="vehicle_series" class="form-control">
            </div>
            
            <div class="form-group">
                <label for="vehicle_images">Vehicle Images (Multiple Allowed)</label>
                <div class="file-input-wrapper">
                    <input type="file" id="vehicle_images" name="vehicle_images[]" class="form-control-file" accept="image/jpeg,image/png,image/gif,image/webp" multiple>
                    <label for="vehicle_images" class="file-input-button">
                        <i class="fas fa-upload"></i> Choose Images
                    </label>
                </div>
                <div id="selected-images-preview" class="selected-images-preview"></div>
            </div>
            
            <div class="form-actions">
                <button type="submit" name="submit_vehicle_info" class="btn-primary">
                    <i class="fas fa-save"></i> Submit Vehicle Information
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Vehicle Images Modal -->
<div id="vehicleImagesModal" class="modal">
    <div class="modal-content image-gallery-modal">
        <span class="close">&times;</span>
        <h2>Vehicle Images</h2>
        <div class="image-gallery" id="vehicleImageGallery"></div>
    </div>
</div>

<!-- Add custom JavaScript for order details modal -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Get modal elements
    const modal = document.getElementById('orderModal');
    const orderDetails = document.getElementById('orderDetails');
    const closeBtn = document.querySelector('.close-modal');
    
    // Add click event to view order buttons
    const viewOrderBtns = document.querySelectorAll('.view-order-btn, .order-number');
    viewOrderBtns.forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const orderId = this.getAttribute('data-order-id');
            
            // Show modal
            modal.style.display = 'block';
            orderDetails.innerHTML = '<div class="loading">Loading order details...</div>';
            
            // Fetch order details using AJAX
            fetch('api/get_order_details.php?order_id=' + orderId)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        displayOrderDetails(data.order, data.items, data.billing);
                    } else {
                        orderDetails.innerHTML = '<div class="error">Error loading order details: ' + data.message + '</div>';
                    }
                })
                .catch(error => {
                    orderDetails.innerHTML = '<div class="error">Error loading order details. Please try again later.</div>';
                    console.error('Error fetching order details:', error);
                });
        });
    });
    
    // Close modal when clicking the close button
    if (closeBtn) {
        closeBtn.addEventListener('click', function() {
            modal.style.display = 'none';
        });
    }
    
    // Close modal when clicking outside the modal content
    window.addEventListener('click', function(event) {
        if (event.target === modal) {
            modal.style.display = 'none';
        }
    });
    
    // Function to display order details in the modal
    function displayOrderDetails(order, items, billing) {
        let html = `
            <h3><i class="fas fa-shopping-bag"></i> Order #${order.order_number}</h3>
            <div class="order-meta">
                <div class="order-date">
                    <strong><i class="far fa-calendar-alt"></i> Date:</strong> ${new Date(order.created_at).toLocaleDateString()}
                </div>
                <div class="order-status">
                    <strong><i class="fas fa-info-circle"></i> Status:</strong> 
                    <span class="status-${order.status.toLowerCase()}">${order.status.charAt(0).toUpperCase() + order.status.slice(1)}</span>
                </div>
                <div class="payment-status">
                    <strong><i class="fas fa-credit-card"></i> Payment:</strong> 
                    <span class="status-${order.payment_status.toLowerCase()}">${order.payment_status.charAt(0).toUpperCase() + order.payment_status.slice(1)}</span>
                </div>
            </div>
            
            <div class="order-items">
                <h4><i class="fas fa-box"></i> Items</h4>
                <div class="order-table-container">
                    <table class="items-table">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Price</th>
                                <th>Quantity</th>
                                <th>Total</th>
                            </tr>
                        </thead>
                        <tbody>
        `;
        
        // Add order items
        items.forEach(item => {
            html += `
                <tr>
                    <td>${item.title}</td>
                    <td>₹${parseFloat(item.price).toFixed(2)}</td>
                    <td>${item.quantity}</td>
                    <td><strong>₹${parseFloat(item.subtotal).toFixed(2)}</strong></td>
                </tr>
            `;
        });
        
        html += `
                    </tbody>
                </table>
                </div>
            </div>
            
            <div class="order-totals">
                <div class="totals-row">
                    <span>Subtotal:</span>
                    <span>₹${parseFloat(order.subtotal).toFixed(2)}</span>
                </div>
        `;
        
        // Add discount if applicable
        if (parseFloat(order.discount_amount) > 0) {
            html += `
                <div class="totals-row discount">
                    <span><i class="fas fa-tag"></i> Discount (${order.discount_percentage}%):</span>
                    <span>-₹${parseFloat(order.discount_amount).toFixed(2)}</span>
                </div>
            `;
        }
        
        // Add GST
        html += `
                <div class="totals-row">
                    <span>GST (18%):</span>
                    <span>₹${parseFloat(order.gst_amount).toFixed(2)}</span>
                </div>
        `;
        
        // Add shipping cost if applicable
        if (parseFloat(order.shipping_cost) > 0) {
            html += `
                <div class="totals-row">
                    <span><i class="fas fa-truck"></i> Shipping:</span>
                    <span>₹${parseFloat(order.shipping_cost).toFixed(2)}</span>
                </div>
            `;
        }
        
        // Add total
        html += `
                <div class="totals-row total">
                    <span>Total:</span>
                    <span>₹${parseFloat(order.total).toFixed(2)}</span>
                </div>
            </div>
            
            <div class="order-address">
                <h4><i class="fas fa-map-marker-alt"></i> Billing & Shipping Address</h4>
                <p>
                    ${billing.first_name} ${billing.last_name}<br>
                    ${billing.street_address_1}<br>
                    ${billing.street_address_2 ? billing.street_address_2 + '<br>' : ''}
                    ${billing.city}, ${billing.state} ${billing.postcode}<br>
                    ${billing.country}<br>
                    <strong><i class="fas fa-phone"></i> Phone:</strong> ${billing.phone}<br>
                    <strong><i class="fas fa-envelope"></i> Email:</strong> ${billing.email}
                </p>
            </div>
        `;
        
        orderDetails.innerHTML = html;
    }
});
</script>

<!-- JavaScript to handle file input display and modal functionality -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Handle custom file input display for profile image
        const fileInput = document.getElementById('profile_image');
        const fileLabel = document.querySelector('.file-input-button');
        
        if(fileInput && fileLabel) {
            fileInput.addEventListener('change', function() {
                if(fileInput.files.length > 0) {
                    const fileName = fileInput.files[0].name;
                    fileLabel.innerHTML = '<i class="fas fa-file"></i> ' + fileName;
                } else {
                    fileLabel.innerHTML = '<i class="fas fa-upload"></i> Choose File';
                }
            });
        }
        
        // Handle vehicle modal
        const vehicleModal = document.getElementById('vehicleFormModal');
        const addVehicleBtn = document.getElementById('addVehicleBtn');
        const vehicleModalClose = vehicleModal.querySelector('.close');
        
        if(addVehicleBtn) {
            addVehicleBtn.addEventListener('click', function() {
                vehicleModal.style.display = 'block';
            });
        }
        
        if(vehicleModalClose) {
            vehicleModalClose.addEventListener('click', function() {
                vehicleModal.style.display = 'none';
            });
        }
        
        // Handle vehicle images modal
        const imagesModal = document.getElementById('vehicleImagesModal');
        const imagesModalClose = imagesModal.querySelector('.close');
        const viewImagesBtns = document.querySelectorAll('.view-vehicle-images');
        const imageGallery = document.getElementById('vehicleImageGallery');
        
        viewImagesBtns.forEach(function(btn) {
            btn.addEventListener('click', function() {
                const vehicleId = this.getAttribute('data-vehicle-id');
                const vehicleCards = document.querySelectorAll('.vehicle-card');
                let vehicleImages = [];
                
                // Find the vehicle card with matching vehicle ID
                vehicleCards.forEach(function(card) {
                    const cardVehicleId = card.querySelector('[name="vehicle_id"]').value;
                    if (cardVehicleId === vehicleId) {
                        // Get all image sources from this card
                        const imageEl = card.querySelector('.vehicle-primary-image');
                        if (imageEl) {
                            vehicleImages.push(imageEl.src);
                        }
                        
                        // Handle additional images if any
                        <?php if (!empty($vehicles)): ?>
                        <?php foreach ($vehicles as $vehicle): ?>
                            if (vehicleId === '<?php echo $vehicle['id']; ?>') {
                                <?php foreach ($vehicle['images'] as $index => $image): ?>
                                    <?php if ($index > 0): ?> // Skip the first image as it's already added above
                                    vehicleImages.push('/uploads/vehicle_images/<?php echo htmlspecialchars($image['image_path']); ?>');
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            }
                        <?php endforeach; ?>
                        <?php endif; ?>
                    }
                });
                
                // Populate the image gallery
                imageGallery.innerHTML = '';
                if (vehicleImages.length > 0) {
                    vehicleImages.forEach(function(src) {
                        const imgContainer = document.createElement('div');
                        imgContainer.className = 'gallery-image';
                        
                        const img = document.createElement('img');
                        img.src = src;
                        img.alt = 'Vehicle Image';
                        
                        imgContainer.appendChild(img);
                        imageGallery.appendChild(imgContainer);
                    });
                } else {
                    imageGallery.innerHTML = '<p>No images available for this vehicle.</p>';
                }
                
                imagesModal.style.display = 'block';
            });
        });
        
        if(imagesModalClose) {
            imagesModalClose.addEventListener('click', function() {
                imagesModal.style.display = 'none';
            });
        }
        
        // Multiple file input preview for vehicle images
        const vehicleImagesInput = document.getElementById('vehicle_images');
        const selectedImagesPreview = document.getElementById('selected-images-preview');
        
        if(vehicleImagesInput && selectedImagesPreview) {
            vehicleImagesInput.addEventListener('change', function() {
                selectedImagesPreview.innerHTML = '';
                
                if(this.files.length > 0) {
                    // Update file input label
                    const vehicleFileLabel = document.querySelector('#vehicle_images + .file-input-button');
                    if(vehicleFileLabel) {
                        vehicleFileLabel.innerHTML = `<i class="fas fa-images"></i> ${this.files.length} ${this.files.length === 1 ? 'Image' : 'Images'} Selected`;
                    }
                    
                    // Create preview for each image
                    for(let i = 0; i < this.files.length; i++) {
                        const file = this.files[i];
                        if (file.type.match('image.*')) {
                            const reader = new FileReader();
                            
                            reader.onload = function(e) {
                                const imgContainer = document.createElement('div');
                                imgContainer.className = 'image-preview';
                                
                                const img = document.createElement('img');
                                img.src = e.target.result;
                                img.title = file.name;
                                
                                imgContainer.appendChild(img);
                                selectedImagesPreview.appendChild(imgContainer);
                            };
                            
                            reader.readAsDataURL(file);
                        }
                    }
                } else {
                    const vehicleFileLabel = document.querySelector('#vehicle_images + .file-input-button');
                    if(vehicleFileLabel) {
                        vehicleFileLabel.innerHTML = '<i class="fas fa-upload"></i> Choose Images';
                    }
                }
            });
        }
        
        // Close modals when clicking outside
        window.addEventListener('click', function(event) {
            if (event.target === vehicleModal) {
                vehicleModal.style.display = 'none';
            }
            if (event.target === imagesModal) {
                imagesModal.style.display = 'none';
            }
        });
    });
</script>

<?php require_once __DIR__ . '/partials/footer.php'; ?> 
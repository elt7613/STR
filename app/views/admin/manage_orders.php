<?php 
// Set page title
$pageTitle = 'Manage Orders';

// Include header
require_once ROOT_PATH . '/app/views/admin/partials/header.php'; 
?>

<div class="admin-header">
    <h1><i class="fas fa-shopping-cart"></i> Manage Orders</h1>
    <div class="admin-actions">
        <a href="dashboard.php" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left"></i> Back to Dashboard
        </a>
    </div>
</div>

<?php if ($action === 'list'): ?>
    <!-- Status Filter Section -->
    <div class="filter-card">
        <div class="filter-header">
            <h3><i class="fas fa-filter"></i> Filter Orders</h3>
            <button type="button" class="toggle-filter-btn" id="toggleFilterBtn">
                <i class="fas fa-chevron-down"></i>
            </button>
        </div>
        <div class="filter-body" id="filterBody">
            <form action="manage_orders.php" method="get" class="row">
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="order_number" class="form-label">Order Number</label>
                        <input type="text" id="order_number" name="order_number" class="form-control" placeholder="e.g. ORD-20250427-49B08" value="<?php echo isset($_GET['order_number']) ? htmlspecialchars($_GET['order_number']) : ''; ?>">
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="status" class="form-label">Order Status</label>
                        <select name="status" id="status" class="form-select">
                            <option value="">All Statuses</option>
                            <option value="pending" <?php echo (isset($_GET['status']) && $_GET['status'] === 'pending') ? 'selected' : ''; ?>>Pending</option>
                            <option value="processing" <?php echo (isset($_GET['status']) && $_GET['status'] === 'processing') ? 'selected' : ''; ?>>Processing</option>
                            <option value="shipped" <?php echo (isset($_GET['status']) && $_GET['status'] === 'shipped') ? 'selected' : ''; ?>>Shipped</option>
                            <option value="delivered" <?php echo (isset($_GET['status']) && $_GET['status'] === 'delivered') ? 'selected' : ''; ?>>Delivered</option>
                            <option value="cancelled" <?php echo (isset($_GET['status']) && $_GET['status'] === 'cancelled') ? 'selected' : ''; ?>>Cancelled</option>
                            <option value="refunded" <?php echo (isset($_GET['status']) && $_GET['status'] === 'refunded') ? 'selected' : ''; ?>>Refunded</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="payment_status" class="form-label">Payment Status</label>
                        <select name="payment_status" id="payment_status" class="form-select">
                            <option value="">All Payment Statuses</option>
                            <option value="pending" <?php echo (isset($_GET['payment_status']) && $_GET['payment_status'] === 'pending') ? 'selected' : ''; ?>>Pending</option>
                            <option value="completed" <?php echo (isset($_GET['payment_status']) && $_GET['payment_status'] === 'completed') ? 'selected' : ''; ?>>Completed</option>
                            <option value="failed" <?php echo (isset($_GET['payment_status']) && $_GET['payment_status'] === 'failed') ? 'selected' : ''; ?>>Failed</option>
                            <option value="refunded" <?php echo (isset($_GET['payment_status']) && $_GET['payment_status'] === 'refunded') ? 'selected' : ''; ?>>Refunded</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="start_date" class="form-label">From Date</label>
                        <input type="date" id="start_date" name="start_date" class="form-control" value="<?php echo isset($_GET['start_date']) ? htmlspecialchars($_GET['start_date']) : ''; ?>">
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="end_date" class="form-label">To Date</label>
                        <input type="date" id="end_date" name="end_date" class="form-control" value="<?php echo isset($_GET['end_date']) ? htmlspecialchars($_GET['end_date']) : ''; ?>">
                    </div>
                </div>
                <div class="filter-actions">
                    <button type="reset" class="btn btn-outline-secondary" onclick="window.location='manage_orders.php'">
                        <i class="fas fa-undo"></i> Reset
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search"></i> Apply Filters
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Order Status Summary -->
    <div class="status-summary">
        <div class="status-card pending">
            <div class="status-icon"><i class="fas fa-clock"></i></div>
            <div class="status-count"><?php echo getOrderCountByStatus('pending') ?: 0; ?></div>
            <div class="status-label">Pending</div>
        </div>
        <div class="status-card processing">
            <div class="status-icon"><i class="fas fa-cog fa-spin"></i></div>
            <div class="status-count"><?php echo getOrderCountByStatus('processing') ?: 0; ?></div>
            <div class="status-label">Processing</div>
        </div>
        <div class="status-card shipped">
            <div class="status-icon"><i class="fas fa-truck"></i></div>
            <div class="status-count"><?php echo getOrderCountByStatus('shipped') ?: 0; ?></div>
            <div class="status-label">Shipped</div>
        </div>
        <div class="status-card delivered">
            <div class="status-icon"><i class="fas fa-check-circle"></i></div>
            <div class="status-count"><?php echo getOrderCountByStatus('delivered') ?: 0; ?></div>
            <div class="status-label">Delivered</div>
        </div>
        <div class="status-card cancelled">
            <div class="status-icon"><i class="fas fa-times-circle"></i></div>
            <div class="status-count"><?php echo getOrderCountByStatus('cancelled') ?: 0; ?></div>
            <div class="status-label">Cancelled</div>
        </div>
    </div>

    <!-- Orders Table -->
    <div class="table-responsive">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Order #</th>
                    <th>Date</th>
                    <th>Items</th>
                    <th>Total</th>
                    <th>Status</th>
                    <th>Payment</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($orders)): ?>
                    <tr>
                        <td colspan="7">
                            <div class="empty-state small">
                                <div class="empty-state-icon">
                                    <i class="fas fa-shopping-cart"></i>
                                </div>
                                <h4>No Orders Found</h4>
                                <p>There are no orders matching your criteria.</p>
                            </div>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($orders as $order): ?>
                    <tr>
                        <td>
                            <div class="order-number">
                                <strong><?php echo htmlspecialchars($order['order_number']); ?></strong>
                                <div class="order-id small">#<?php echo $order['id']; ?></div>
                            </div>
                        </td>
                        <td>
                            <div class="order-date">
                                <div><?php echo date('M d, Y', strtotime($order['created_at'])); ?></div>
                                <div class="time-small"><?php echo date('h:i A', strtotime($order['created_at'])); ?></div>
                            </div>
                        </td>
                        <td>
                            <?php
                            // Get number of items in the order
                            $itemsCount = 0;
                            try {
                                $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM order_items WHERE order_id = ?");
                                $stmt->execute([$order['id']]);
                                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                                $itemsCount = $result['count'] ?? 0;
                            } catch (Exception $e) {
                                $itemsCount = '?';
                            }
                            ?>
                            <span class="badge bg-light text-dark"><?php echo $itemsCount; ?> item<?php echo $itemsCount !== 1 ? 's' : ''; ?></span>
                        </td>
                        <td>
                            <div class="price-tag">
                                <?php echo number_format($order['total'], 2); ?>
                                <small><?php echo htmlspecialchars($order['currency'] ?? 'INR'); ?></small>
                            </div>
                        </td>
                        <td>
                            <span class="status-badge status-<?php echo strtolower($order['status']); ?>">
                                <?php echo ucfirst($order['status']); ?>
                            </span>
                        </td>
                        <td>
                            <span class="status-badge payment-<?php echo strtolower($order['payment_status'] ?? 'pending'); ?>">
                                <?php echo ucfirst($order['payment_status'] ?? 'pending'); ?>
                            </span>
                        </td>
                        <td>
                            <div class="action-buttons">
                            <a href="manage_orders.php?action=view&id=<?php echo $order['id']; ?>" class="btn btn-primary btn-small">
                                    <i class="fas fa-eye"></i> View
                            </a>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

<?php elseif ($action === 'view' && $orderDetails): ?>
    <!-- Back Button -->
    <div class="mb-4">
        <a href="manage_orders.php" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left"></i> Back to Orders
        </a>
    </div>

    <!-- Order Header -->
    <div class="order-header card mb-4">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="order-title">Order #<?php echo htmlspecialchars($orderDetails['order_number']); ?></h2>
        <div class="order-date">
            <i class="far fa-calendar-alt"></i> 
            <?php echo date('F d, Y h:i A', strtotime($orderDetails['created_at'])); ?>
                    </div>
                </div>
                <div class="order-status-section">
                    <form action="manage_orders.php" method="post" class="status-form">
                        <input type="hidden" name="order_id" value="<?php echo $orderDetails['id']; ?>">
                        <div class="form-group">
                            <label for="status" class="form-label">Order Status:</label>
                            <div class="d-flex">
                                <select name="status" id="status" class="form-select status-select">
                                    <option value="pending" <?php echo $orderDetails['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                    <option value="processing" <?php echo $orderDetails['status'] === 'processing' ? 'selected' : ''; ?>>Processing</option>
                                    <option value="shipped" <?php echo $orderDetails['status'] === 'shipped' ? 'selected' : ''; ?>>Shipped</option>
                                    <option value="delivered" <?php echo $orderDetails['status'] === 'delivered' ? 'selected' : ''; ?>>Delivered</option>
                                    <option value="cancelled" <?php echo $orderDetails['status'] === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                    <option value="refunded" <?php echo $orderDetails['status'] === 'refunded' ? 'selected' : ''; ?>>Refunded</option>
                                </select>
                                <button type="submit" name="update_status" class="btn btn-primary ms-2">
                                    <i class="fas fa-save"></i> Update
                                </button>
                            </div>
                        </div>
                        <?php if (strtolower($orderDetails['payment_method']) === 'cod' || 
                                 strtolower($orderDetails['payment_method']) === 'cash on delivery'): ?>
                            <div class="mt-2 info-note">
                                <i class="fas fa-info-circle text-primary"></i> 
                                Note: When a Cash on Delivery order is marked as "delivered", the payment status will automatically be updated to "completed".
                            </div>
                        <?php endif; ?>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <div class="order-details-grid">
        <!-- Order Summary -->
        <div class="order-summary card">
            <div class="card-header">
                <h3><i class="fas fa-info-circle"></i> Order Summary</h3>
            </div>
            <div class="card-body">
                <div class="summary-item">
                    <div class="label">Subtotal:</div>
                    <div class="value"><?php echo number_format($orderDetails['subtotal'], 2); ?> <?php echo $orderDetails['currency'] ?? 'INR'; ?></div>
                </div>
                <div class="summary-item">
                    <div class="label">Shipping:</div>
                    <div class="value"><?php echo number_format($orderDetails['shipping_cost'], 2); ?> <?php echo $orderDetails['currency'] ?? 'INR'; ?></div>
                </div>
                <div class="summary-item">
                    <div class="label">GST (18%):</div>
                    <div class="value"><?php echo number_format($orderDetails['gst_amount'], 2); ?> <?php echo $orderDetails['currency'] ?? 'INR'; ?></div>
                </div>
                <div class="summary-item total">
                    <div class="label">Total:</div>
                    <div class="value"><?php echo number_format($orderDetails['total'], 2); ?> <?php echo $orderDetails['currency'] ?? 'INR'; ?></div>
                </div>
                <div class="summary-item">
                    <div class="label">Payment Method:</div>
                    <div class="value"><?php echo htmlspecialchars($orderDetails['payment_method']); ?></div>
                </div>
                <div class="summary-item">
                    <div class="label">Payment Status:</div>
                    <div class="value">
                        <span class="status-badge payment-<?php echo strtolower(!empty($orderDetails['payment_status']) ? $orderDetails['payment_status'] : 'pending'); ?>">
                            <?php echo ucfirst(!empty($orderDetails['payment_status']) ? $orderDetails['payment_status'] : 'pending'); ?>
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Customer Information -->
        <div class="customer-info card">
            <div class="card-header">
                <h3><i class="fas fa-user"></i> Customer Information</h3>
            </div>
            <div class="card-body">
                <?php if ($billingDetails): ?>
                <div class="customer-name">
                    <?php 
                    $firstName = isset($billingDetails['first_name']) ? htmlspecialchars($billingDetails['first_name']) : 'N/A';
                    $lastName = isset($billingDetails['last_name']) ? htmlspecialchars($billingDetails['last_name']) : '';
                    echo $firstName . ' ' . $lastName; 
                    ?>
                </div>
                <div class="customer-contact">
                    <div><i class="fas fa-envelope"></i> <?php echo isset($billingDetails['email']) ? htmlspecialchars($billingDetails['email']) : 'N/A'; ?></div>
                    <div><i class="fas fa-phone"></i> <?php echo isset($billingDetails['phone']) ? htmlspecialchars($billingDetails['phone']) : 'N/A'; ?></div>
                </div>
                
                <div class="billing-address">
                    <h4><i class="fas fa-map-marker-alt"></i> Billing Address</h4>
                    <address>
                        <?php echo isset($billingDetails['address_line1']) ? htmlspecialchars($billingDetails['address_line1']) : 'N/A'; ?><br>
                        <?php if (!empty($billingDetails['address_line2'])): ?>
                            <?php echo htmlspecialchars($billingDetails['address_line2']); ?><br>
                        <?php endif; ?>
                        <?php echo isset($billingDetails['city']) ? htmlspecialchars($billingDetails['city']) : 'N/A'; ?>, 
                        <?php echo isset($billingDetails['state']) ? htmlspecialchars($billingDetails['state']) : 'N/A'; ?> 
                        <?php echo isset($billingDetails['postal_code']) ? htmlspecialchars($billingDetails['postal_code']) : 'N/A'; ?><br>
                        <?php echo isset($billingDetails['country']) ? htmlspecialchars($billingDetails['country']) : 'N/A'; ?>
                    </address>
                </div>
                <?php else: ?>
                <div class="empty-state small">
                    <div class="empty-state-icon">
                        <i class="fas fa-user-slash"></i>
                    </div>
                    <h4>No Customer Details</h4>
                    <p>Billing information is not available for this order.</p>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Payment Transactions -->
        <div class="payment-transactions card">
            <div class="card-header">
                <h3><i class="fas fa-credit-card"></i> Payment Transactions</h3>
            </div>
            <div class="card-body">
                <?php if (!empty($paymentDetails)): ?>
                    <?php foreach ($paymentDetails as $payment): ?>
                    <div class="payment-item">
                        <div class="transaction-id">
                            <strong>Transaction ID:</strong> <?php echo isset($payment['transaction_id']) ? htmlspecialchars($payment['transaction_id']) : 'N/A'; ?>
                        </div>
                        <div class="payment-details">
                            <div>
                                <i class="fas fa-money-bill-wave"></i> 
                                <?php echo number_format($payment['amount'], 2); ?> <?php echo isset($payment['currency']) ? htmlspecialchars($payment['currency']) : 'INR'; ?>
                            </div>
                            <div>
                                <i class="fas fa-clock"></i> 
                                <?php echo date('M d, Y h:i A', strtotime($payment['created_at'])); ?>
                            </div>
                            <div>
                                <i class="fas fa-credit-card"></i> 
                                <?php echo isset($payment['payment_method']) ? htmlspecialchars($payment['payment_method']) : 'N/A'; ?>
                            </div>
                            <div>
                                <span class="status-badge payment-<?php echo strtolower($payment['status']); ?>">
                                    <?php echo ucfirst($payment['status']); ?>
                                </span>
                            </div>
                        </div>
                        <?php if (!empty($payment['gateway_response'])): ?>
                        <div class="gateway-response mt-3">
                            <details>
                                <summary>Gateway Response</summary>
                                <pre class="response-data"><?php echo htmlspecialchars($payment['gateway_response']); ?></pre>
                            </details>
                        </div>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                <div class="empty-state small">
                    <div class="empty-state-icon">
                        <i class="fas fa-exclamation-circle"></i>
                    </div>
                    <h4>No Payment Records</h4>
                    <p>No payment transactions recorded for this order.</p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Order Items Section -->
    <div class="order-items-section card">
        <div class="card-header">
            <h3><i class="fas fa-box-open"></i> Order Items</h3>
        </div>
        <div class="card-body">
            <?php if (empty($orderItems)): ?>
                <div class="empty-state small">
                    <div class="empty-state-icon">
                        <i class="fas fa-shopping-basket"></i>
                    </div>
                    <h4>No Items Found</h4>
                    <p>This order doesn't have any items.</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="admin-table order-items-table">
                        <thead>
                            <tr>
                                <th class="item-image">Image</th>
                                <th>Product</th>
                                <th>Price</th>
                                <th>Quantity</th>
                                <th>Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($orderItems as $item): ?>
                            <tr>
                                <td class="item-image">
                                    <?php if (!empty($item['image_path'])): ?>
                                        <img src="<?php echo htmlspecialchars($item['image_path']); ?>" alt="Product image">
                                    <?php else: ?>
                                        <div class="no-image">
                                            <i class="fas fa-image"></i>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="item-title"><?php echo htmlspecialchars($item['title']); ?></div>
                                    <a href="../product.php?id=<?php echo $item['product_id']; ?>" class="btn-link small" target="_blank">
                                        <i class="fas fa-external-link-alt"></i> View Product
                                    </a>
                                </td>
                                <td>
                                    <div class="price-tag">
                                        <?php echo number_format($item['price'], 2); ?>
                                        <small><?php echo $orderDetails['currency'] ?? 'INR'; ?></small>
                                    </div>
                                </td>
                                <td>
                                    <?php echo $item['quantity']; ?>
                                </td>
                                <td>
                                    <div class="price-tag">
                                        <?php echo number_format($item['subtotal'], 2); ?>
                                        <small><?php echo $orderDetails['currency'] ?? 'INR'; ?></small>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="4" class="text-end"><strong>Subtotal</strong></td>
                                <td>
                                    <div class="price-tag">
                                        <?php echo number_format($orderDetails['subtotal'], 2); ?>
                                        <small><?php echo $orderDetails['currency'] ?? 'INR'; ?></small>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="4" class="text-end"><strong>Shipping</strong></td>
                                <td>
                                    <div class="price-tag">
                                        <?php echo number_format($orderDetails['shipping_cost'], 2); ?>
                                        <small><?php echo $orderDetails['currency'] ?? 'INR'; ?></small>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="4" class="text-end"><strong>GST (18%)</strong></td>
                                <td>
                                    <div class="price-tag">
                                        <?php echo number_format($orderDetails['gst_amount'], 2); ?>
                                        <small><?php echo $orderDetails['currency'] ?? 'INR'; ?></small>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="4" class="text-end"><strong>Total</strong></td>
                                <td>
                                    <div class="price-tag total">
                                        <?php echo number_format($orderDetails['total'], 2); ?>
                                        <small><?php echo $orderDetails['currency'] ?? 'INR'; ?></small>
                                    </div>
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Order Notes Section -->
    <?php if (!empty($orderDetails['notes'])): ?>
    <div class="order-notes">
        <h3><i class="fas fa-sticky-note"></i> Order Notes</h3>
        <div class="notes-content">
            <?php echo nl2br(htmlspecialchars($orderDetails['notes'])); ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- Admin Actions Section -->
    <div class="admin-actions-section card mt-4">
        <div class="card-header">
            <h3><i class="fas fa-cogs"></i> Admin Actions</h3>
        </div>
        <div class="card-body">
            <div class="action-buttons">
                <a href="#" class="btn btn-primary" onclick="window.print()">
                    <i class="fas fa-print"></i> Print Order
                </a>
                <?php if ($orderDetails['status'] !== 'cancelled'): ?>
                <form action="manage_orders.php" method="post" class="d-inline" onsubmit="return confirm('Are you sure you want to cancel this order?');">
                    <input type="hidden" name="order_id" value="<?php echo $orderDetails['id']; ?>">
                    <input type="hidden" name="status" value="cancelled">
                    <button type="submit" name="update_status" class="btn btn-danger">
                        <i class="fas fa-ban"></i> Cancel Order
                    </button>
                </form>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Print-specific styles
        var style = document.createElement('style');
        style.innerHTML = `
            @media print {
                .admin-sidebar, .admin-topbar, .admin-header, .admin-actions-section,
                .admin-footer, .status-form, .btn, .gateway-response, .empty-state {
                    display: none !important;
                }
                body, .admin-content {
                    margin: 0;
                    padding: 0;
                    max-width: 100% !important;
                    width: 100% !important;
                }
                .order-header {
                    margin-top: 20px;
                }
                .card {
                    box-shadow: none !important;
                    margin-bottom: 20px !important;
                    page-break-inside: avoid;
                }
                .order-details-grid {
                    display: block;
                }
                .order-details-grid > div {
                    margin-bottom: 20px;
                }
                .price-tag, .status-badge {
                    color: #000 !important;
                    background: none !important;
                }
            }
        `;
        document.head.appendChild(style);
    });
    </script>
<?php endif; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Toggle filter section
    const toggleFilterBtn = document.getElementById('toggleFilterBtn');
    const filterBody = document.getElementById('filterBody');
    
    if (toggleFilterBtn && filterBody) {
        // Show filters by default
        filterBody.classList.add('show');
        toggleFilterBtn.querySelector('i').classList.remove('fa-chevron-down');
        toggleFilterBtn.querySelector('i').classList.add('fa-chevron-up');
        
        toggleFilterBtn.addEventListener('click', function() {
            filterBody.classList.toggle('show');
            this.querySelector('i').classList.toggle('fa-chevron-down');
            this.querySelector('i').classList.toggle('fa-chevron-up');
        });
    }
});
</script>

<style>
/* Additional Order Management Styles */
.order-number {
    display: flex;
    flex-direction: column;
}

.order-id {
    color: var(--gray-500);
    font-size: 0.8rem;
}

.time-small {
    color: var(--gray-500);
    font-size: 0.8rem;
}

.customer-info {
    display: flex;
    flex-direction: column;
}

.customer-email {
    color: var(--gray-500);
    font-size: 0.8rem;
}

.ms-2 {
    margin-left: 0.5rem;
}

.order-title {
    font-size: 1.5rem;
    font-weight: 600;
    margin: 0;
    color: var(--gray-800);
}

.status-select {
    min-width: 150px;
}

.order-header {
    margin-bottom: 1.5rem;
}

.text-end {
    text-align: right;
}

.gateway-response {
    margin-top: 1rem;
}

.response-data {
    background: var(--gray-100);
    padding: 1rem;
    border-radius: var(--radius-sm);
    white-space: pre-wrap;
    font-size: 0.8rem;
    max-height: 150px;
    overflow-y: auto;
}

.price-tag.total {
    font-size: 1.2rem;
    color: var(--gray-900);
}

.mt-3 {
    margin-top: 1rem;
}

.mt-4 {
    margin-top: 1.5rem;
}

.mb-4 {
    margin-bottom: 1.5rem;
}

.action-buttons {
    display: flex;
    gap: 0.75rem;
}

details summary {
    cursor: pointer;
    color: var(--primary-color);
    font-weight: 500;
}

details summary:hover {
    text-decoration: underline;
}

/* Make tables more responsive */
@media (max-width: 768px) {
    .admin-table th, .admin-table td {
        padding: 0.75rem 0.5rem;
    }
    
    .action-buttons {
        flex-direction: column;
        gap: 0.5rem;
    }
    
    .action-buttons .btn {
        width: 100%;
    }
    
    .order-header .d-flex {
        flex-direction: column;
        gap: 1rem;
    }
}

/* Info note styling */
.info-note {
    background-color: rgba(67, 97, 238, 0.1);
    padding: 0.75rem;
    border-left: 3px solid var(--primary-color);
    border-radius: var(--radius-sm);
    font-size: 0.9rem;
    color: var(--gray-700);
}

.text-primary {
    color: var(--primary-color);
}
</style>

<?php require_once ROOT_PATH . '/app/views/admin/partials/footer.php'; ?> 
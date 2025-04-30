/**
 * Cart Page JavaScript
 * Handles cart page interactions including quantity updates, 
 * item removal, and cart updates
 */

document.addEventListener('DOMContentLoaded', function() {
    // Initialize cart page functionality
    initCartPage();
});

/**
 * Initialize all cart page event listeners and functionality
 */
function initCartPage() {
    // Get elements
    const cartItemsContainer = document.getElementById('cart-items-container');
    const updateCartBtn = document.getElementById('update-cart-btn');
    
    // Set up event listeners
    if (cartItemsContainer) {
        // Quantity increment/decrement buttons
        cartItemsContainer.addEventListener('click', function(event) {
            // Handle increment button clicks
            if (event.target.classList.contains('increment')) {
                const itemId = event.target.dataset.itemId;
                const inputEl = document.querySelector(`.quantity-input[data-item-id="${itemId}"]`);
                
                if (inputEl) {
                    let newValue = parseInt(inputEl.value) + 1;
                    if (newValue > 99) newValue = 99;
                    inputEl.value = newValue;
                    inputEl.dispatchEvent(new Event('change'));
                }
            }
            
            // Handle decrement button clicks
            if (event.target.classList.contains('decrement')) {
                const itemId = event.target.dataset.itemId;
                const inputEl = document.querySelector(`.quantity-input[data-item-id="${itemId}"]`);
                
                if (inputEl) {
                    let newValue = parseInt(inputEl.value) - 1;
                    if (newValue < 1) newValue = 1;
                    inputEl.value = newValue;
                    inputEl.dispatchEvent(new Event('change'));
                }
            }
            
            // Handle remove item button clicks
            if (event.target.classList.contains('remove-item')) {
                const itemId = event.target.dataset.itemId;
                if (itemId) {
                    removeCartItem(itemId);
                }
            }
        });
        
        // Quantity input changes
        cartItemsContainer.addEventListener('change', function(event) {
            if (event.target.classList.contains('quantity-input')) {
                const itemId = event.target.dataset.itemId;
                let quantity = parseInt(event.target.value);
                
                // Validate quantity
                if (isNaN(quantity) || quantity < 1) {
                    quantity = 1;
                    event.target.value = quantity;
                } else if (quantity > 99) {
                    quantity = 99;
                    event.target.value = quantity;
                }
                
                // Update the subtotal display
                updateItemSubtotal(itemId, quantity);
            }
        });
    }
    
    // Update cart button
    if (updateCartBtn) {
        updateCartBtn.addEventListener('click', function() {
            updateCart();
        });
    }
    
    // Continue shopping button
    const continueShoppingBtn = document.querySelector('.continue-shopping');
    if (continueShoppingBtn) {
        continueShoppingBtn.addEventListener('click', function(e) {
            e.preventDefault();
            window.location.href = 'shop.php';
        });
    }
    
    // Checkout button
    const checkoutBtn = document.querySelector('.checkout-button');
    if (checkoutBtn) {
        checkoutBtn.addEventListener('click', function(e) {
            e.preventDefault();
            window.location.href = 'checkout.php';
        });
    }
}

/**
 * Update the subtotal for a cart item
 * 
 * @param {string} itemId Cart item ID
 * @param {number} quantity New quantity
 */
function updateItemSubtotal(itemId, quantity) {
    const item = document.querySelector(`.cart-item[data-id="${itemId}"]`);
    
    if (item) {
        const priceEl = item.querySelector('.product-price');
        const subtotalEl = item.querySelector('.product-subtotal');
        
        if (priceEl && subtotalEl) {
            // Extract the price (remove $ and convert to number)
            const price = parseFloat(priceEl.textContent.replace('$', ''));
            
            // Calculate new subtotal
            const subtotal = price * quantity;
            
            // Update the subtotal display
            subtotalEl.textContent = '$' + subtotal.toFixed(2);
            
            // Update cart totals
            updateCartTotals();
        }
    }
}

/**
 * Calculate and update the cart totals
 */
function updateCartTotals() {
    let subtotal = 0;
    
    // Get all cart items
    const items = document.querySelectorAll('.cart-item');
    
    // Calculate subtotal
    items.forEach(function(item) {
        const subtotalEl = item.querySelector('.product-subtotal');
        if (subtotalEl) {
            const itemSubtotal = parseFloat(subtotalEl.textContent.replace('$', ''));
            subtotal += itemSubtotal;
        }
    });
    
    // Update subtotal and total display - updated for new structure
    const subtotalEl = document.getElementById('cart-subtotal');
    const totalEl = document.getElementById('cart-total');
    
    if (subtotalEl) {
        subtotalEl.textContent = '$' + subtotal.toFixed(2);
    }
    
    if (totalEl) {
        totalEl.textContent = '$' + subtotal.toFixed(2);
    }
}

/**
 * Update all cart items with their new quantities
 */
function updateCart() {
    // Show loading state
    const updateBtn = document.getElementById('update-cart-btn');
    if (updateBtn) {
        updateBtn.disabled = true;
        updateBtn.textContent = 'UPDATING...';
    }
    
    // Get all quantity inputs
    const quantityInputs = document.querySelectorAll('.quantity-input');
    const updates = [];
    
    // Build an array of updates
    quantityInputs.forEach(function(input) {
        const itemId = input.dataset.itemId;
        const quantity = parseInt(input.value);
        
        if (itemId && !isNaN(quantity) && quantity > 0) {
            updates.push({ id: itemId, quantity: quantity });
        }
    });
    
    // Process updates sequentially
    const updatePromises = updates.map(function(update) {
        return updateCartItemQuantity(update.id, update.quantity);
    });
    
    // When all updates are complete
    Promise.all(updatePromises)
        .then(function() {
            // Show success message
            showNotification('Cart updated successfully', 'success');
            
            // Refresh the page
            window.location.reload();
        })
        .catch(function(error) {
            // Show error message
            showNotification('Error updating cart: ' + error.message, 'error');
            
            // Reset button state
            if (updateBtn) {
                updateBtn.disabled = false;
                updateBtn.textContent = 'UPDATE CART';
            }
        });
}

/**
 * Update a cart item quantity via the API
 * 
 * @param {string} itemId Cart item ID
 * @param {number} quantity New quantity
 * @returns {Promise} Promise that resolves when the update is complete
 */
function updateCartItemQuantity(itemId, quantity) {
    return new Promise(function(resolve, reject) {
        // Create request body
        const data = {
            cart_item_id: itemId,
            quantity: quantity
        };
        
        // Send the update request
        fetch('api/cart.php', {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(data)
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                // Update header cart count
                updateCartCount(data.count);
                resolve();
            } else {
                reject(new Error(data.message || 'Unknown error'));
            }
        })
        .catch(error => {
            reject(error);
        });
    });
}

/**
 * Remove an item from the cart
 * 
 * @param {string} itemId Cart item ID
 */
function removeCartItem(itemId) {
    // Get the item element
    const itemEl = document.querySelector(`.cart-item[data-id="${itemId}"]`);
    
    if (itemEl) {
        // Add removing class for animation
        itemEl.classList.add('removing');
    }
    
    // Create request data
    const data = {
        cart_item_id: itemId
    };
    
    // Send the delete request
    fetch('api/cart.php', {
        method: 'DELETE',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(data)
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            // Update header cart count
            updateCartCount(data.count);
            
            // If the item element exists, remove it
            if (itemEl) {
                // Remove the item after animation
                setTimeout(function() {
                    itemEl.remove();
                    
                    // Update cart totals
                    updateCartTotals();
                    
                    // Check if cart is empty
                    const cartItems = document.querySelectorAll('.cart-item');
                    if (cartItems.length === 0) {
                        // Reload page to show empty cart template
                        window.location.reload();
                    }
                }, 300);
            }
            
            // Show success notification
            showNotification('Item removed from cart', 'success');
        } else {
            // Show error notification
            showNotification('Error removing item: ' + (data.message || 'Unknown error'), 'error');
        }
    })
    .catch(error => {
        console.error('Error removing cart item:', error);
        showNotification('Error removing item: ' + error.message, 'error');
    });
}

/**
 * Update the cart count in the header
 * 
 * @param {number} count New cart count
 */
function updateCartCount(count) {
    const cartCount = document.getElementById('cartCount');
    
    if (cartCount) {
        count = parseInt(count) || 0;
        
        // Update the count display
        cartCount.textContent = count;
        
        // Show/hide based on count
        if (count > 0) {
            cartCount.classList.remove('empty');
        } else {
            cartCount.classList.add('empty');
        }
    }
}

/**
 * Show a notification message
 * 
 * @param {string} message Message to display
 * @param {string} type Message type (success, error)
 */
function showNotification(message, type) {
    // Create notification element
    const notification = document.createElement('div');
    notification.className = 'notification ' + type;
    notification.textContent = message;
    
    // Add to page
    document.body.appendChild(notification);
    
    // Show notification
    setTimeout(function() {
        notification.classList.add('show');
    }, 10);
    
    // Auto-hide notification
    setTimeout(function() {
        notification.classList.remove('show');
        
        // Remove from DOM after animation
        setTimeout(function() {
            notification.remove();
        }, 300);
    }, 3000);
} 
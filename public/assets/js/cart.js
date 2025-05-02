/**
 * Cart functionality for STR Works
 */
document.addEventListener('DOMContentLoaded', function() {
    // Initialize cart
    initializeCart();
    console.log('Cart initialized');
    
    // Toggle cart dropdown when clicking cart button
    const cartButton = document.getElementById('cartButton');
    const cartDropdown = document.getElementById('cartDropdown');
    
    if (cartButton && cartDropdown) {
        cartButton.addEventListener('click', function(e) {
            e.stopPropagation(); // Prevent event from bubbling up
            
            // Toggle the cart dropdown
            cartDropdown.classList.toggle('active');
            
            // If opening the cart, refresh its contents
            if (cartDropdown.classList.contains('active')) {
                console.log('Fetching cart items...');
                fetchCartItems();
            }
        });
        
        // Close cart when clicking outside
        document.addEventListener('click', function(event) {
            if (cartDropdown.classList.contains('active') && 
                !cartDropdown.contains(event.target) && 
                !cartButton.contains(event.target)) {
                cartDropdown.classList.remove('active');
            }
        });
    }
});

/**
 * Initialize the cart elements
 */
function initializeCart() {
    // Initial cart count
    updateCartCount(document.getElementById('cartCount').textContent);
    
    // Set up event delegation for cart item interactions
    const cartItems = document.getElementById('cartItems');
    if (cartItems) {
        cartItems.addEventListener('click', function(event) {
            // Handle remove item button clicks
            if (event.target.closest('.remove-item')) {
                const cartItem = event.target.closest('.cart-item');
                const cartItemId = cartItem.dataset.id;
                
                if (cartItemId) {
                    removeFromCart(cartItemId);
                }
            }
        });
    }
}

/**
 * Fetch cart items from the API
 */
function fetchCartItems() {
    fetch('api/cart.php')
        .then(response => {
            console.log('API Response Status:', response.status);
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            console.log('Cart API Response:', data);
            if (data.success) {
                console.log('Cart Items Count:', data.items ? data.items.length : 0);
                updateCartItems(data.items);
                updateCartCount(data.count);
            } else {
                console.error('API returned error:', data.message || 'Unknown error');
            }
        })
        .catch(error => {
            console.error('Error fetching cart items:', error);
        });
}

/**
 * Update the cart items display
 * 
 * @param {Array} items Cart items
 */
function updateCartItems(items) {
    const cartItemsContainer = document.getElementById('cartItems');
    
    if (!cartItemsContainer) {
        console.error('Cart items container not found');
        return;
    }
    
    // Clear current items
    cartItemsContainer.innerHTML = '';
    
    if (!items || items.length === 0) {
        console.log('No items in cart, showing empty message');
        // Show empty cart message
        cartItemsContainer.innerHTML = '<p class="empty-cart-message">Your cart is empty</p>';
        return;
    }
    
    console.log('Rendering', items.length, 'cart items');
    
    // Add each item to the cart - using design from main.html
    items.forEach(item => {
        console.log('Rendering cart item:', item);
        const imageUrl = item.image_path || 'assets/img/product-placeholder.jpg';
        const itemTotal = (parseFloat(item.amount) * parseInt(item.quantity)).toFixed(2);
        
        const itemElement = document.createElement('div');
        itemElement.className = 'flex justify-between text-white items-center p-2';
        itemElement.dataset.id = item.id;
        
        itemElement.innerHTML = `
            <img src="${imageUrl}" alt="${item.title}" class="w-20 h-20 object-cover" />
            <div>
                <h4 class="text-sm">${item.title}</h4>
                <p class="text-sm text-white">₹${parseFloat(item.amount).toFixed(2)}</p>
                <span class="text-sm">Qty: ${item.quantity}</span>
            </div>
            <button onclick="removeFromCart(${item.id})" class="text-gray-500 hover:text-red-600 remove-item">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        `;
        
        cartItemsContainer.appendChild(itemElement);
    });
}

/**
 * Update the cart count display
 * 
 * @param {number} count Number of items in cart
 */
function updateCartCount(count) {
    const cartCount = document.getElementById('cartCount');
    
    if (cartCount) {
        count = parseInt(count) || 0;
        console.log('Updating cart count to:', count);
        
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
 * Add a product to the cart
 * 
 * @param {number} productId Product ID
 * @param {number} quantity Quantity to add
 * @param {function} callback Optional callback function
 */
function addToCart(productId, quantity, callback) {
    console.log('Adding to cart:', productId, 'quantity:', quantity);
    
    // Show loading indicator
    updateCartCount('...');
    
    // Create the request data
    const data = {
        product_id: productId,
        quantity: quantity
    };
    
    console.log('Cart request data:', data);
    
    // Send the request to the API
    fetch('api/cart.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(data)
    })
    .then(response => {
        console.log('Add to cart response status:', response.status);
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        return response.json();
    })
    .then(data => {
        console.log('Add to cart response:', data);
        if (data.success) {
            // Update cart count
            updateCartCount(data.count);
            
            // Flash the cart icon to indicate success
            const cartButton = document.getElementById('cartButton');
            if (cartButton) {
                cartButton.classList.add('flash');
                setTimeout(() => {
                    cartButton.classList.remove('flash');
                }, 500);
            }
            
            // Call the callback if provided
            if (typeof callback === 'function') {
                callback(true, data);
            }
        } else {
            console.error('Error adding to cart:', data.message);
            
            // Call the callback if provided
            if (typeof callback === 'function') {
                callback(false, data);
            }
        }
    })
    .catch(error => {
        console.error('Error adding to cart:', error);
        
        // Reset cart count on error
        fetchCartItems();
        
        // Call the callback if provided
        if (typeof callback === 'function') {
            callback(false, { message: error.message });
        }
    });
}

/**
 * Remove an item from the cart
 * 
 * @param {number} cartItemId Cart item ID
 */
function removeFromCart(cartItemId) {
    console.log('Removing item from cart:', cartItemId);
    
    // Create the request data
    const data = {
        cart_item_id: cartItemId
    };
    
    // Send the request to the API
    fetch('api/cart.php', {
        method: 'DELETE',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(data)
    })
    .then(response => {
        console.log('Remove from cart response status:', response.status);
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        return response.json();
    })
    .then(data => {
        console.log('Remove from cart response:', data);
        if (data.success) {
            // Update the cart count
            updateCartCount(data.count);
            
            // Remove the item from the UI - using the new format
            const cartItem = document.querySelector(`[data-id="${cartItemId}"]`);
            if (cartItem) {
                cartItem.style.opacity = 0;
                setTimeout(() => {
                    cartItem.remove();
                    
                    // Check if cart is empty
                    const cartItems = document.getElementById('cartItems');
                    if (cartItems && !cartItems.querySelector('[data-id]')) {
                        cartItems.innerHTML = '<p class="empty-cart-message">Your cart is empty</p>';
                    }
                }, 300);
            }
        } else {
            console.error('Error removing item from cart:', data.message);
        }
    })
    .catch(error => {
        console.error('Error removing item from cart:', error);
    });
} 
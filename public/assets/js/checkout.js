/**
 * Checkout page JavaScript
 */

document.addEventListener('DOMContentLoaded', function() {
    // Initialize checkout form
    initCheckoutForm();
});

/**
 * Initialize checkout form and related functionality
 */
function initCheckoutForm() {
    const checkoutForm = document.getElementById('checkout-form');
    
    if (checkoutForm) {
        // Form submission
        checkoutForm.addEventListener('submit', function(event) {
            // Validate payment method is selected
            const paymentMethod = document.querySelector('input[name="payment_method"]:checked');
            if (!paymentMethod) {
                event.preventDefault();
                showError('Please select a payment method');
                return;
            }
            
            // Basic form validation - HTML5 required attributes handle most of it
            if (!checkoutForm.checkValidity()) {
                return; // Let browser handle required field validation
            }
            
            // Show loading state on the button when submitting
            const submitButton = document.querySelector('.place-order-btn');
            if (submitButton) {
                submitButton.innerHTML = 'Processing...';
                submitButton.disabled = true;
            }
        });
        
        // Payment method selection visual feedback
        const paymentMethodInputs = document.querySelectorAll('input[name="payment_method"]');
        paymentMethodInputs.forEach(function(input) {
            input.addEventListener('change', function() {
                // Highlight selected payment method
                const allMethods = document.querySelectorAll('.payment-method');
                allMethods.forEach(function(method) {
                    method.classList.remove('selected');
                });
                
                if (this.checked) {
                    this.closest('.payment-method').classList.add('selected');
                }
            });
        });
        
        // Fill form with stored data if available
        fillFormWithStoredData();
        
        // Store form data on input changes
        checkoutForm.addEventListener('input', function(event) {
            if (event.target.name && event.target.value) {
                storeFormData(event.target.name, event.target.value);
            }
        });
    }
}

/**
 * Show error message
 * 
 * @param {string} message Error message
 */
function showError(message) {
    // Check if error element already exists
    let errorElement = document.querySelector('.alert-danger');
    
    if (!errorElement) {
        // Create new error element
        errorElement = document.createElement('div');
        errorElement.className = 'alert alert-danger';
        
        // Insert at the top of the checkout content
        const checkoutContent = document.querySelector('.checkout-content');
        if (checkoutContent) {
            checkoutContent.parentNode.insertBefore(errorElement, checkoutContent);
        } else {
            // Fallback - insert at the top of the container
            const container = document.querySelector('.checkout-container');
            if (container) {
                container.insertBefore(errorElement, container.firstChild);
            }
        }
    }
    
    // Set error message
    errorElement.textContent = message;
    
    // Scroll to the error message
    errorElement.scrollIntoView({ behavior: 'smooth', block: 'center' });
}

/**
 * Store form field value in session storage
 * 
 * @param {string} fieldName Form field name
 * @param {string} value Field value
 */
function storeFormData(fieldName, value) {
    try {
        // Get existing stored data or initialize empty object
        const storedData = JSON.parse(sessionStorage.getItem('checkout_form_data') || '{}');
        
        // Update field value
        storedData[fieldName] = value;
        
        // Save back to session storage
        sessionStorage.setItem('checkout_form_data', JSON.stringify(storedData));
    } catch (error) {
        console.error('Error storing form data:', error);
    }
}

/**
 * Fill form with stored data from session storage
 */
function fillFormWithStoredData() {
    try {
        const storedData = JSON.parse(sessionStorage.getItem('checkout_form_data') || '{}');
        
        // Fill form fields with stored values
        for (const fieldName in storedData) {
            const field = document.querySelector(`[name="${fieldName}"]`);
            if (field) {
                // Handle different input types
                if (field.type === 'radio' || field.type === 'checkbox') {
                    if (field.value === storedData[fieldName]) {
                        field.checked = true;
                    }
                } else {
                    field.value = storedData[fieldName];
                }
            }
        }
    } catch (error) {
        console.error('Error filling form with stored data:', error);
    }
} 
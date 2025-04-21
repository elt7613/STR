document.addEventListener('DOMContentLoaded', function() {
    // Mobile menu toggle functionality
    const mobileMenuBtn = document.querySelector('.mobile-menu-btn');
    const nav = document.querySelector('.nav');
    
    if (mobileMenuBtn && nav) {
        mobileMenuBtn.addEventListener('click', function(e) {
            e.stopPropagation(); // Prevent event bubbling
            
            // Toggle active class on nav
            nav.classList.toggle('active');
            
            // Toggle active class on menu button (for animation)
            mobileMenuBtn.classList.toggle('active');
            
            // Toggle aria-expanded for accessibility
            const expanded = mobileMenuBtn.getAttribute('aria-expanded') === 'true' || false;
            mobileMenuBtn.setAttribute('aria-expanded', !expanded);
        });
        
        // Close mobile menu when clicking outside
        document.addEventListener('click', function(event) {
            if (nav.classList.contains('active') && 
                !nav.contains(event.target) && 
                !mobileMenuBtn.contains(event.target)) {
                nav.classList.remove('active');
                mobileMenuBtn.classList.remove('active');
                mobileMenuBtn.setAttribute('aria-expanded', 'false');
            }
        });
        
        // Add fix for iOS touch events (needed for hover effects to work properly on touch devices)
        if ('ontouchstart' in window) {
            document.querySelectorAll('.dropdown .nav-link').forEach(function(link) {
                link.addEventListener('touchstart', function(e) {
                    // This empty handler helps activate hover state on first touch on iOS
                    // Don't prevent default to allow the hover state to trigger
                });
            });
        }
    }
    
    console.log('Mobile menu script loaded with hover-based dropdowns');
}); 
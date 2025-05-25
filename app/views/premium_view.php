<?php
/**
 * Premium Membership View
 */
$pageTitle = 'Premium Membership';
$activePage = 'premium';

// Get premium plans
$premiumPlans = getPremiumPricingPlans();
?>

<div class="container premium-container">
    <div class="premium-header">
        <h1>Upgrade to Premium Membership</h1>
        <p>Join our premium membership program to unlock exclusive benefits and enhance your experience.</p>
    </div>

    <div class="premium-plans">
        <?php foreach ($premiumPlans as $plan): ?>
            <div class="premium-plan <?php echo ($plan['is_recommended'] == 1) ? 'featured' : ''; ?>">
                <?php if ($plan['is_recommended'] == 1): ?>
                <span class="recommended-badge">RECOMMENDED</span>
                <?php endif; ?>
                <h2><?php echo htmlspecialchars($plan['name']); ?></h2>
                <div class="price">
                    ₹<?php echo number_format($plan['price'], 2); ?>
                    <small>for <?php echo $plan['duration_months']; ?> months</small>
                </div>
                <div class="description">
                    <?php echo htmlspecialchars($plan['description']); ?>
                </div>
                <div class="features">
                    <ul>
                        <li><i class="fas fa-check"></i> Special discounts on all services</li>
                        <li><i class="fas fa-check"></i> Priority customer support</li>
                        <li><i class="fas fa-check"></i> Exclusive access to premium content</li>
                        <li><i class="fas fa-check"></i> Advanced features and tools</li>
                    </ul>
                </div>
                <form action="premium_payment.php" method="post">
                    <input type="hidden" name="plan_id" value="<?php echo $plan['id']; ?>">
                    <button type="submit" class="btn-premium">Subscribe Now</button>
                </form>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="premium-benefits">
        <h2>Premium Membership Benefits</h2>
        <div class="benefits-grid">
            <div class="benefit-item">
                <i class="fas fa-tags"></i>
                <h3>Exclusive Discounts</h3>
                <p>Get special discounts on all services and products throughout your membership.</p>
            </div>
            <div class="benefit-item">
                <i class="fas fa-headset"></i>
                <h3>Priority Support</h3>
                <p>Enjoy priority customer support with faster response times.</p>
            </div>
            <div class="benefit-item">
                <i class="fas fa-unlock-alt"></i>
                <h3>Premium Features</h3>
                <p>Access to exclusive premium features not available to regular users.</p>
            </div>
            <div class="benefit-item">
                <i class="fas fa-star"></i>
                <h3>VIP Status</h3>
                <p>Stand out with a premium badge on your profile and get recognized.</p>
            </div>
        </div>
    </div>

    <div class="premium-faq">
        <h2>Frequently Asked Questions</h2>
        <div class="faq-item">
            <div class="faq-question">
                How long does my premium membership last? <i class="fas fa-chevron-down"></i>
            </div>
            <div class="faq-answer">
                Your premium membership will last for the duration specified in your chosen plan. Most plans are valid for 12 months from the date of activation.
            </div>
        </div>
        <div class="faq-item">
            <div class="faq-question">
                Can I cancel my premium membership? <i class="fas fa-chevron-down"></i>
            </div>
            <div class="faq-answer">
                Yes, you can cancel your premium membership at any time. However, we do not provide refunds for unused portions of your subscription period.
            </div>
        </div>
        <div class="faq-item">
            <div class="faq-question">
                How do I access premium features? <i class="fas fa-chevron-down"></i>
            </div>
            <div class="faq-answer">
                Once your payment is confirmed, your account will automatically be upgraded to premium status. You'll immediately gain access to all premium features.
            </div>
        </div>
        <div class="faq-item">
            <div class="faq-question">
                Are there any additional fees? <i class="fas fa-chevron-down"></i>
            </div>
            <div class="faq-answer">
                No, the price you see is the total price for the membership duration. There are no hidden fees or additional charges.
            </div>
        </div>
    </div>
</div>

<script>
    // FAQ Accordion
    document.addEventListener('DOMContentLoaded', function() {
        const faqQuestions = document.querySelectorAll('.faq-question');
        
        faqQuestions.forEach(question => {
            question.addEventListener('click', function() {
                const answer = this.nextElementSibling;
                const isActive = answer.classList.contains('active');
                
                // Close all answers
                document.querySelectorAll('.faq-answer').forEach(ans => {
                    ans.classList.remove('active');
                });
                
                // Toggle current answer
                if (!isActive) {
                    answer.classList.add('active');
                }
            });
        });
    });
</script>

<?php
/**
 * Main landing page
 */
require_once '../bootstrap.php';

// Default to monthly billing for display purposes
$billingInterval = isset($_GET['billing']) && $_GET['billing'] === 'yearly' ? 'year' : 'month';

// Get all active products for pricing section (both monthly and yearly)
$productModel = new \Models\Product();
$allProducts = array_merge(
    $productModel->getActiveByInterval('month'),
    $productModel->getActiveByInterval('year')
);

// Organize products by type to ensure we have both monthly and yearly options
$productsByType = [];
foreach ($allProducts as $product) {
    $productsByType[$product['type']][$product['billing_interval']] = $product;
}

// Flatten the array for display
$products = [];
foreach ($productsByType as $type => $intervals) {
    foreach ($intervals as $interval => $product) {
        $products[] = $product;
    }
}

//// Add auth modal CSS
$additionalCss = '<link rel="stylesheet" href="assets/css/auth-modal.css">';

// Include header
include_once '../template/header.php';
?>

<!-- Hero/Intro Section -->
<section id="intro" class="hero-section">
    <div class="container">
        <div class="hero-content">
            <div class="hero-text">
                <h1 class="hero-title"><?= config('app.name') ?></h1>
                <p class="hero-description">Premium subscription services tailored for businesses of all sizes. Scale efficiently with our Dev Test Evaluation.</p>
                <div class="hero-buttons">
                    <a href="#pricing" class="btn btn-primary scroll-link">View Pricing</a>
                    <a href="#features" class="btn btn-outline scroll-link">Learn More</a>
                </div>
            </div>
            <div class="hero-video">
                <div class="video-thumbnail" data-video-id="XZmGGAbHqa0">
                    <div class="play-button">
                        <i class="fas fa-play"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Video Modal -->
    <div id="videoModal" class="modal">
        <div class="modal-content">
            <span class="close-modal">&times;</span>
            <div class="video-container">
                <iframe id="videoFrame" width="100%" height="100%" src="" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
            </div>
        </div>
    </div>
</section>

<!-- Features Section -->
<section id="features" class="features-section">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title">Why Choose Our Service</h2>
            <p class="section-description">Our subscription service provides everything you need to grow your business efficiently.</p>
        </div>
        
        <div class="features-grid">
            <!-- Feature 1 -->
            <div class="feature-card">
                <div class="feature-icon primary">
                    <i class="fas fa-bolt"></i>
                </div>
                <h3 class="feature-title">Fast Implementation</h3>
                <p class="feature-description">Get up and running quickly with our streamlined onboarding process and intuitive interface.</p>
            </div>
            
            <!-- Feature 2 -->
            <div class="feature-card">
                <div class="feature-icon secondary">
                    <i class="fas fa-shield-alt"></i>
                </div>
                <h3 class="feature-title">Secure & Reliable</h3>
                <p class="feature-description">Enterprise-grade security with 99.9% uptime guarantee. Your data is always safe and accessible.</p>
            </div>
            
            <!-- Feature 3 -->
            <div class="feature-card">
                <div class="feature-icon accent">
                    <i class="fas fa-chart-line"></i>
                </div>
                <h3 class="feature-title">Scalable Solution</h3>
                <p class="feature-description">Easily scale up or down as your business needs change, with flexible subscription options.</p>
            </div>
            
            <!-- Feature 4 -->
            <div class="feature-card">
                <div class="feature-icon primary">
                    <i class="fas fa-headset"></i>
                </div>
                <h3 class="feature-title">Premium Support</h3>
                <p class="feature-description">Dedicated customer support team available to help you with any questions or issues.</p>
            </div>
            
            <!-- Feature 5 -->
            <div class="feature-card">
                <div class="feature-icon secondary">
                    <i class="fas fa-sync-alt"></i>
                </div>
                <h3 class="feature-title">Regular Updates</h3>
                <p class="feature-description">Continuous improvements and new features added regularly to enhance your experience.</p>
            </div>
            
            <!-- Feature 6 -->
            <div class="feature-card">
                <div class="feature-icon accent">
                    <i class="fas fa-file-invoice-dollar"></i>
                </div>
                <h3 class="feature-title">Transparent Billing</h3>
                <p class="feature-description">Clear and transparent billing with no hidden fees. Pay only for what you need.</p>
            </div>
        </div>
    </div>
</section>

<!-- Pricing Section -->
<section id="pricing" class="pricing-section">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title">Simple, Transparent Pricing</h2>
            <p class="section-description">Choose the plan that works best for your business needs.</p>
        </div>
        
        <!-- Billing toggle switch -->
        <div class="billing-toggle">
            <span class="billing-option <?= $billingInterval === 'month' ? 'active' : '' ?>">Monthly</span>
            <label class="switch">
                <input type="checkbox" id="billing-toggle" <?= $billingInterval === 'year' ? 'checked' : '' ?>>
                <span class="slider round"></span>
            </label>
            <span class="billing-option <?= $billingInterval === 'year' ? 'active' : '' ?>">Yearly <span class="discount">Save 10%</span></span>
        </div>
        
        <div class="pricing-cards">
            <?php foreach ($products as $product): ?>
                <?php
                $featured = $product['type'] === 'medium';
                $cardClass = $featured ? 'pricing-card featured' : 'pricing-card';
                ?>
                
                <div class="<?= $cardClass ?>" data-interval="<?= $product['billing_interval'] ?>">
                    <div class="pricing-header <?= $featured ? 'featured-header' : '' ?>">
                        <h3 class="pricing-title"><?= htmlspecialchars(ucfirst($product['type'])) ?></h3>
                        <p class="pricing-subtitle"><?= $product['billing_interval'] === 'month' ? 'Monthly' : 'Annual' ?> Billing</p>
                    </div>
                    
                    <div class="pricing-content">
                        <div class="pricing-price">
                            <span class="price">€<?= number_format($product['price'] / 100, 0) ?></span>
                            <span class="period">/ <?= $product['billing_interval'] ?></span>
                        </div>
                        
                        <ul class="pricing-features">
                            <?php if ($product['type'] === 'small'): ?>
                                <li><i class="fas fa-check"></i> Basic features</li>
                                <li><i class="fas fa-check"></i> Up to 5 users</li>
                                <li><i class="fas fa-check"></i> 5GB storage</li>
                                <li><i class="fas fa-check"></i> Email support</li>
                            <?php elseif ($product['type'] === 'medium'): ?>
                                <li><i class="fas fa-check"></i> All Small features</li>
                                <li><i class="fas fa-check"></i> Up to 20 users</li>
                                <li><i class="fas fa-check"></i> 20GB storage</li>
                                <li><i class="fas fa-check"></i> Priority support</li>
                                <li><i class="fas fa-check"></i> Advanced analytics</li>
                            <?php else: ?>
                                <li><i class="fas fa-check"></i> All Medium features</li>
                                <li><i class="fas fa-check"></i> Unlimited users</li>
                                <li><i class="fas fa-check"></i> 100GB storage</li>
                                <li><i class="fas fa-check"></i> 24/7 phone support</li>
                                <li><i class="fas fa-check"></i> Custom integrations</li>
                                <li><i class="fas fa-check"></i> Dedicated account manager</li>
                            <?php endif; ?>
                        </ul>
                        
                        <?php if (isset($_SESSION['user_id'])): ?>
                            <a href="/subscribe.php?price_id=<?= $product['stripe_price_id'] ?>" class="btn <?= $featured ? 'btn-primary' : 'btn-outline' ?>">
                                <?= $featured ? 'Get Started' : 'Choose Plan' ?>
                            </a>
                        <?php else: ?>
                            <a href="#" class="btn <?= $featured ? 'btn-primary' : 'btn-outline' ?> open-login-modal">
                                Login to Subscribe
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        
        <div class="pricing-note">
            <p>Need a custom plan? <a href="#contact" class="scroll-link">Contact us</a> for more information.</p>
        </div>
    </div>
</section>

<!-- Contact Section -->
<section id="contact" class="contact-section">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title">Get in Touch</h2>
            <p class="section-description">Have questions or need assistance? Our team is here to help.</p>
        </div>
        
        <div class="contact-card">
            <div class="contact-info consultant-bg">
                <h3 class="contact-info-title">Contact Information</h3>
                
                <div class="contact-details">
                    <div class="contact-detail">
                        <i class="fas fa-map-marker-alt"></i>
                        <div>
                            <h4>Address</h4>
                            <p>123 Business Street, Suite 100<br>City, Country</p>
                        </div>
                    </div>
                    
                    <div class="contact-detail">
                        <i class="fas fa-phone"></i>
                        <div>
                            <h4>Phone</h4>
                            <p>+1 (123) 456-7890</p>
                        </div>
                    </div>
                    
                    <div class="contact-detail">
                        <i class="fas fa-envelope"></i>
                        <div>
                            <h4>Email</h4>
                            <p>info@example.com</p>
                        </div>
                    </div>
                </div>
                
                <div class="social-links">
                    <h4>Follow Us</h4>
                    <div class="social-icons">
                        <a href="#" class="social-icon"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" class="social-icon"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="social-icon"><i class="fab fa-linkedin-in"></i></a>
                    </div>
                </div>
            </div>
            
            <div class="contact-form">
                <h3 class="contact-form-title">Send Us a Message</h3>
                
                <!-- Success message -->
                <div id="contact-success" class="flash-message flash-success" style="display: none;">
                    <div class="flash-content">
                        <i class="fas fa-check-circle"></i>
                        <p></p>
                    </div>
                </div>
                
                <!-- Error message -->
                <div id="contact-error" class="flash-message flash-error" style="display: none;">
                    <div class="flash-content">
                        <i class="fas fa-exclamation-circle"></i>
                        <p></p>
                    </div>
                </div>
                
                <form id="contact-form">
                    <?= generateCsrfToken() ?>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="name">Full Name</label>
                            <input type="text" id="name" name="name" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="email">Email Address</label>
                            <input type="email" id="email" name="email" required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="subject">Subject</label>
                        <input type="text" id="subject" name="subject" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="message">Message</label>
                        <textarea id="message" name="message" rows="5" required></textarea>
                    </div>
                    
                    <button type="submit" id="contact-submit" class="btn btn-primary">Send Message</button>
                </form>
            </div>
        </div>
    </div>
</section>

<!-- Login Modal -->
<div class="auth-modal" id="login-modal">
    <div class="auth-modal-container">
        <button class="auth-close">
            <i class="fas fa-times"></i>
        </button>
        
        <div class="auth-modal-left">
            <div class="auth-modal-logo"><?= config('app.name') ?></div>
            <img src="assets/images/login-illustration.svg" alt="Login" class="auth-modal-image">
            <p>Welcome back! Access your account to manage your subscriptions.</p>
        </div>
        
        <div class="auth-modal-right">
            <h2 class="auth-modal-title">Log In</h2>
            <p class="auth-modal-subtitle">Enter your credentials to access your account</p>
            
            <div id="login-error" class="flash-message flash-error" style="display: none;">
                <div class="flash-content">
                    <i class="fas fa-exclamation-circle"></i>
                    <p></p>
                </div>
            </div>
            
            <form id="login-form" class="auth-form">
                <div class="auth-form-group">
                    <label for="login-email">Email Address</label>
                    <input type="email" id="login-email" name="email" required>
                </div>
                
                <div class="auth-form-group">
                    <label for="login-password">Password</label>
                    <input type="password" id="login-password" name="password" required>
                </div>
                
                <div class="auth-form-checkbox">
                    <input type="checkbox" id="remember" name="remember">
                    <label for="remember">Remember me</label>
                </div>
                
                <button type="submit" class="auth-form-submit">Log In</button>
                
                <div class="auth-form-footer">
                    Don't have an account? <a href="#" id="switch-to-register">Sign Up</a>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Register Modal -->
<div class="auth-modal" id="register-modal">
    <div class="auth-modal-container">
        <button class="auth-close">
            <i class="fas fa-times"></i>
        </button>
        
        <div class="auth-modal-left">
            <div class="auth-modal-logo"><?= config('app.name') ?></div>
            <img src="assets/images/register-illustration.svg" alt="Register" class="auth-modal-image">
            <p>Join our platform and discover premium subscription services for your business.</p>
        </div>
        
        <div class="auth-modal-right">
            <h2 class="auth-modal-title">Sign Up</h2>
            <p class="auth-modal-subtitle">Create your account to get started</p>
            
            <div id="register-error" class="flash-message flash-error" style="display: none;">
                <div class="flash-content">
                    <i class="fas fa-exclamation-circle"></i>
                    <p></p>
                </div>
            </div>
            
            <form id="register-form" class="auth-form">
                <div class="auth-form-group">
                    <label for="register-first-name">First Name</label>
                    <input type="text" id="register-first-name" name="first_name" required>
                </div>
                
                <div class="auth-form-group">
                    <label for="register-last-name">Last Name</label>
                    <input type="text" id="register-last-name" name="last_name" required>
                </div>
                
                <div class="auth-form-group">
                    <label for="register-email">Email Address</label>
                    <input type="email" id="register-email" name="email" required>
                </div>
                
                <div class="auth-form-group">
                    <label for="register-password">Password</label>
                    <input type="password" id="register-password" name="password" required>
                </div>
                
                <div class="auth-form-checkbox">
                    <input type="checkbox" id="terms" name="terms" required>
                    <label for="terms">I agree to the <a href="/terms.php">Terms of Service</a> and <a href="/privacy.php">Privacy Policy</a></label>
                </div>
                
                <button type="submit" class="auth-form-submit">Sign Up</button>
                
                <div class="auth-form-footer">
                    Already have an account? <a href="#" id="switch-to-login">Log In</a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include_once '../template/footer.php'; ?>

</main>

<footer class="footer">
    <div class="container">
        <div class="footer-content">
            <div class="footer-logo">
                <a href="/" class="footer-logo-link">
                    <?= config('app.name') ?>
                </a>
                <p class="footer-tagline">Premium subscription services for businesses of all sizes.</p>
            </div>
            
            <div class="footer-links">
                <div class="footer-links-column">
                    <h4 class="footer-links-title">Company</h4>
                    <ul class="footer-links-list">
                        <li><a href="/about.php">About Us</a></li>
                        <li><a href="/careers.php">Careers</a></li>
                        <li><a href="/blog.php">Blog</a></li>
                        <li><a href="/press.php">Press</a></li>
                    </ul>
                </div>
                
                <div class="footer-links-column">
                    <h4 class="footer-links-title">Product</h4>
                    <ul class="footer-links-list">
                        <li><a href="/#features">Features</a></li>
                        <li><a href="/#pricing">Pricing</a></li>
                        <li><a href="/security.php">Security</a></li>
                        <li><a href="/api.php">API</a></li>
                    </ul>
                </div>
                
                <div class="footer-links-column">
                    <h4 class="footer-links-title">Resources</h4>
                    <ul class="footer-links-list">
                        <li><a href="/docs.php">Documentation</a></li>
                        <li><a href="/guides.php">Guides</a></li>
                        <li><a href="/support.php">Support</a></li>
                        <li><a href="/status.php">Status</a></li>
                    </ul>
                </div>
                
                <div class="footer-links-column">
                    <h4 class="footer-links-title">Legal</h4>
                    <ul class="footer-links-list">
                        <li><a href="/privacy.php">Privacy Policy</a></li>
                        <li><a href="/terms.php">Terms of Service</a></li>
                        <li><a href="/cookies.php">Cookie Policy</a></li>
                        <li><a href="/gdpr.php">GDPR</a></li>
                    </ul>
                </div>
            </div>
        </div>
        
        <div class="footer-bottom">
            <p class="footer-copyright">&copy; <?= date('Y') ?> <?= config('app.name') ?>. All rights reserved.</p>
            
            <div class="footer-social">
                <a href="#" class="footer-social-link"><i class="fab fa-facebook-f"></i></a>
                <a href="#" class="footer-social-link"><i class="fab fa-twitter"></i></a>
                <a href="#" class="footer-social-link"><i class="fab fa-linkedin-in"></i></a>
                <a href="#" class="footer-social-link"><i class="fab fa-instagram"></i></a>
            </div>
        </div>
    </div>
</footer>

<!-- JavaScript -->
<script src="assets/js/main.js"></script>
</body>
</html>
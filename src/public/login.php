<?php
/**
 * Login page
 */

require_once '../config/init.php';

use Controllers\AuthController;

$authController = new AuthController();

// Check if user is already logged in
if ($authController->isLoggedIn()) {
    // Redirect to appropriate dashboard
    if ($authController->isAdmin()) {
        redirect('/admin/dashboard.php');
    } else {
        redirect('/account.php');
    }
}

// Process login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($authController->login($_POST)) {
        // Redirect to appropriate dashboard
        if ($authController->isAdmin()) {
            redirect('/admin/dashboard.php');
        } else {
            redirect('/account.php');
        }
    }
}

// Page title
$pageTitle = 'Login';

// Include auth header (without navbar)
require_once '../template/auth-header.php';
?>

<section class="auth-section">
    <div class="container">
        <div class="auth-container">
            <div class="auth-card">
                <h2 class="auth-title">Login to Your Account</h2>
                
                <?php if (hasFlash('error')): ?>
                    <div class="flash-message error">
                        <?= getFlash('error') ?>
                    </div>
                <?php endif; ?>
                
                <?php if (hasFlash('success')): ?>
                    <div class="flash-message success">
                        <?= getFlash('success') ?>
                    </div>
                <?php endif; ?>
                
                <form action="/login.php" method="POST" class="auth-form">
                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <input type="email" id="email" name="email" required class="form-control" placeholder="Enter your email">
                    </div>
                    
                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password" required class="form-control" placeholder="Enter your password">
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary btn-block">
                            <i class="fas fa-sign-in-alt"></i> Login
                        </button>
                    </div>
                </form>
                
                <div class="auth-footer">
                    <p>Don't have an account? <a href="/register.php">Register</a></p>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require_once '../template/auth-footer.php'; ?>

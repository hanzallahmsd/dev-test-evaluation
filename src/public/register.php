<?php
/**
 * Register page
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

// Process registration form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userId = $authController->register($_POST);
    if ($userId) {
        // Registration successful, redirect to login page
        redirect('/login.php');
    }
}

// Page title
$pageTitle = 'Register';

// Include auth header (without navbar)
require_once '../template/auth-header.php';
?>

<section class="auth-section">
    <div class="container">
        <div class="auth-container">
            <div class="auth-card">
                <h2 class="auth-title">Create an Account</h2>
                
                <?php if (hasFlash('error')): ?>
                    <div class="flash-message error">
                        <?= getFlash('error') ?>
                    </div>
                <?php endif; ?>
                
                <form action="/register.php" method="POST" class="auth-form">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="first_name">First Name</label>
                            <input type="text" id="first_name" name="first_name" required class="form-control" placeholder="Enter your first name">
                        </div>
                        
                        <div class="form-group">
                            <label for="last_name">Last Name</label>
                            <input type="text" id="last_name" name="last_name" required class="form-control" placeholder="Enter your last name">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <input type="email" id="email" name="email" required class="form-control" placeholder="Enter your email">
                    </div>
                    
                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password" required class="form-control" placeholder="Create a password">
                        <small class="form-text">Password must be at least 8 characters long</small>
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary btn-block">
                            <i class="fas fa-user-plus"></i> Register
                        </button>
                    </div>
                </form>
                
                <div class="auth-footer">
                    <p>Already have an account? <a href="/login.php">Login</a></p>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require_once '../template/auth-footer.php'; ?>

<?php
/**
 * Subscription success page
 */
require_once '../bootstrap.php';

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    redirect('/');
}

// Set page title
$pageTitle = 'Subscription Successful';
include_once '../template/header.php';
?>

<!-- Success Section -->
<section class="success-section">
    <div class="container">
        <div class="success-container">
            <div class="success-icon">
                <i class="fas fa-check-circle"></i>
            </div>
            <h1>Subscription Successful!</h1>
            <p>Thank you for subscribing to our service. Your subscription has been activated successfully.</p>
            <div class="success-actions">
                <a href="account.php" class="btn btn-primary">View My Account</a>
                <a href="/" class="btn btn-outline">Return to Homepage</a>
            </div>
        </div>
    </div>
</section>

<?php
// Include footer
include_once '../template/footer.php';
?>

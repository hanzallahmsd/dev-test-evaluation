<?php
/**
 * Subscription cancellation page
 */
require_once '../bootstrap.php';

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    redirect('/');
}

// Set page title
$pageTitle = 'Subscription Cancelled';
include_once '../template/header.php';
?>

<!-- Cancel Section -->
<section class="cancel-section">
    <div class="container">
        <div class="cancel-container">
            <div class="cancel-icon">
                <i class="fas fa-times-circle"></i>
            </div>
            <h1>Subscription Cancelled</h1>
            <p>Your subscription process has been cancelled. No charges have been made to your account.</p>
            <div class="cancel-actions">
                <a href="#pricing" class="btn btn-primary">View Plans</a>
                <a href="account.php" class="btn btn-outline">My Account</a>
            </div>
        </div>
    </div>
</section>

<?php
// Include footer
include_once '../template/footer.php';
?>

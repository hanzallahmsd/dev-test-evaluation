<?php
/**
 * Manage subscription page
 */
require_once '../bootstrap.php';

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    redirect('/');
}

// Get user data
$userModel = new \Models\User();
$user = $userModel->find($_SESSION['user_id']);

// Get subscription data
$subscriptionModel = new \Models\Subscription();
$subscription = $subscriptionModel->getActiveSubscriptionForUser($_SESSION['user_id']);

// Redirect if no active subscription
if (!$subscription) {
    flash('error', 'You do not have an active subscription to manage');
    redirect('/account.php');
}

// Handle subscription cancellation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'cancel') {
    // Validate CSRF token
    if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
        flash('error', 'Invalid security token. Please try again.');
        redirect('/manage-subscription.php');
    }
    
    $subscriptionController = new \Controllers\SubscriptionController();
    $result = $subscriptionController->cancelSubscription($subscription['id']);
    
    if ($result) {
        flash('success', 'Your subscription has been cancelled successfully. You will still have access until the end of your current billing period.');
        redirect('/account.php');
    } else {
        flash('error', 'There was a problem cancelling your subscription. Please try again or contact support.');
    }
}

// Get product data
$productModel = new \Models\Product();
$product = null;
if ($subscription) {
    $products = $productModel->getByTypeAndInterval($subscription['plan_type'], $subscription['billing_interval']);
    if (!empty($products)) {
        $product = $products[0];
    }
}

// Include header
$pageTitle = 'Manage Subscription';
include_once '../template/header.php';
?>

<!-- Manage Subscription Section -->
<section class="manage-subscription-section">
    <div class="container">
        <div class="section-header">
            <h1 class="section-title">Manage Your Subscription</h1>
            <p class="section-description">Review and manage your current subscription plan.</p>
        </div>
        
        <div class="subscription-container">
            <div class="subscription-card">
                <div class="subscription-header">
                    <h2>Current Plan</h2>
                    <span class="subscription-status subscription-status-<?= $subscription['status'] ?>">
                        <?= ucfirst($subscription['status']) ?>
                    </span>
                </div>
                
                <div class="subscription-details">
                    <div class="subscription-info">
                        <div class="subscription-info-item">
                            <span class="label">Plan Type:</span>
                            <span class="value"><?= ucfirst($subscription['plan_type']) ?> Plan</span>
                        </div>
                        
                        <?php if ($product): ?>
                        <div class="subscription-info-item">
                            <span class="label">Price:</span>
                            <span class="value">
                                <?= number_format($product['price'], 2) ?> <?= strtoupper($product['currency']) ?>/<?= $product['billing_interval'] ?>
                            </span>
                        </div>
                        <?php endif; ?>
                        
                        <div class="subscription-info-item">
                            <span class="label">Billing Period:</span>
                            <span class="value">
                                <?= date('F j, Y', strtotime($subscription['current_period_start'])) ?> - 
                                <?= date('F j, Y', strtotime($subscription['current_period_end'])) ?>
                            </span>
                        </div>
                        
                        <div class="subscription-info-item">
                            <span class="label">Next Billing Date:</span>
                            <span class="value">
                                <?= date('F j, Y', strtotime($subscription['current_period_end'])) ?>
                            </span>
                        </div>
                        
                        <?php if ($subscription['cancel_at_period_end']): ?>
                        <div class="subscription-info-item">
                            <span class="label">Cancellation Status:</span>
                            <span class="value cancel-notice">
                                <i class="fas fa-info-circle"></i>
                                Your subscription is set to cancel at the end of the current billing period.
                            </span>
                        </div>
                        <?php endif; ?>
                    </div>
                    
                    <?php if (!$subscription['cancel_at_period_end']): ?>
                    <div class="subscription-actions">
                        <h3>Subscription Options</h3>
                        
                        <div class="action-buttons">
                            <a href="#pricing" class="btn btn-outline">Change Plan</a>
                            
                            <button id="cancel-subscription-btn" class="btn btn-danger">Cancel Subscription</button>
                            
                            <div id="cancel-confirmation" class="cancel-confirmation" style="display: none;">
                                <div class="cancel-confirmation-content">
                                    <h4>Are you sure you want to cancel?</h4>
                                    <p>Your subscription will remain active until the end of your current billing period on <strong><?= date('F j, Y', strtotime($subscription['current_period_end'])) ?></strong>.</p>
                                    
                                    <form action="/manage-subscription.php" method="POST">
                                        <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                                        <input type="hidden" name="action" value="cancel">
                                        
                                        <div class="form-actions">
                                            <button type="button" id="cancel-no" class="btn btn-outline">No, Keep My Subscription</button>
                                            <button type="submit" class="btn btn-danger">Yes, Cancel My Subscription</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="subscription-faq">
                <h3>Frequently Asked Questions</h3>
                
                <div class="faq-item">
                    <h4>What happens when I cancel my subscription?</h4>
                    <p>Your subscription will remain active until the end of your current billing period. After that, your account will revert to a free account with limited features.</p>
                </div>
                
                <div class="faq-item">
                    <h4>Can I change my plan?</h4>
                    <p>Yes, you can upgrade or downgrade your plan at any time. If you upgrade, the new pricing will be prorated for the remainder of your billing period.</p>
                </div>
                
                <div class="faq-item">
                    <h4>How do I update my payment information?</h4>
                    <p>To update your payment information, please contact our support team at support@example.com.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const cancelBtn = document.getElementById('cancel-subscription-btn');
    const cancelConfirmation = document.getElementById('cancel-confirmation');
    const cancelNo = document.getElementById('cancel-no');
    
    if (cancelBtn) {
        cancelBtn.addEventListener('click', function() {
            cancelConfirmation.style.display = 'flex';
        });
    }
    
    if (cancelNo) {
        cancelNo.addEventListener('click', function() {
            cancelConfirmation.style.display = 'none';
        });
    }
});
</script>

<?php
// Include footer
include_once '../template/footer.php';
?>

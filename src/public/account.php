<?php
/**
 * User account page
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

// Get product data if subscription exists
$productModel = new \Models\Product();
$product = null;
if ($subscription) {
    $products = $productModel->getByTypeAndInterval($subscription['plan_type'], 'month');
    if (!empty($products)) {
        $product = $products[0];
    }
}

// Include header
$pageTitle = 'My Account';
include_once '../template/header.php';
?>

<!-- Account Section -->
<section class="account-section">
    <div class="container">
        <div class="section-header">
            <h1 class="section-title">My Account</h1>
            <p class="section-description">Manage your account and subscription details.</p>
        </div>
        
        <div class="account-container">
            <div class="account-card">
                <div class="account-card-header">
                    <h2>Account Information</h2>
                </div>
                <div class="account-card-body">
                    <div class="account-info-item">
                        <span class="label">Name:</span>
                        <span class="value"><?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?></span>
                    </div>
                    <div class="account-info-item">
                        <span class="label">Email:</span>
                        <span class="value"><?= htmlspecialchars($user['email']) ?></span>
                    </div>
                    <div class="account-info-item">
                        <span class="label">Account Type:</span>
                        <span class="value"><?= ucfirst(htmlspecialchars($user['role'])) ?></span>
                    </div>
                    <div class="account-info-item">
                        <span class="label">Member Since:</span>
                        <span class="value"><?= date('F j, Y', strtotime($user['created_at'])) ?></span>
                    </div>
                </div>
            </div>
            
            <div class="account-card">
                <div class="account-card-header">
                    <h2>Subscription Details</h2>
                </div>
                <div class="account-card-body">
                    <?php if ($subscription): ?>
                        <div class="subscription-active">
                            <div class="subscription-status subscription-status-<?= $subscription['status'] ?>">
                                <i class="fas fa-check-circle"></i>
                                <span><?= ucfirst($subscription['status']) ?></span>
                            </div>
                            
                            <div class="account-info-item">
                                <span class="label">Plan:</span>
                                <span class="value"><?= ucfirst($subscription['plan_type']) ?> Plan</span>
                            </div>
                            
                            <?php if ($product): ?>
                            <div class="account-info-item">
                                <span class="label">Price:</span>
                                <span class="value"><?= number_format($product['price'], 2) ?> <?= strtoupper($product['currency']) ?>/<?= $product['billing_interval'] ?></span>
                            </div>
                            <?php endif; ?>
                            
                            <div class="account-info-item">
                                <span class="label">Current Period:</span>
                                <span class="value">
                                    <?= date('F j, Y', strtotime($subscription['current_period_start'])) ?> - 
                                    <?= date('F j, Y', strtotime($subscription['current_period_end'])) ?>
                                </span>
                            </div>
                            
                            <div class="subscription-actions">
                                <a href="manage-subscription.php" class="btn btn-outline btn-sm">Manage Subscription</a>
                                <button class="btn btn-danger btn-sm" id="cancel-subscription" data-subscription-id="<?= $subscription['id'] ?>">Cancel Subscription</button>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="subscription-inactive">
                            <p>You don't have an active subscription.</p>
                            <a href="#pricing" class="btn btn-primary">View Plans</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="account-card">
                <div class="account-card-header">
                    <h2>Recent Invoices</h2>
                </div>
                <div class="account-card-body">
                    <div class="recent-invoices">
                        <?php 
                        $invoiceModel = new \Models\Invoice();
                        $invoices = $invoiceModel->getRecentForUser($_SESSION['user_id'], 3);
                        
                        if (!empty($invoices)): 
                        ?>
                            <table class="invoice-table">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Amount</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($invoices as $invoice): ?>
                                    <tr>
                                        <td><?= date('M j, Y', strtotime($invoice['invoice_date'])) ?></td>
                                        <td><?= number_format($invoice['amount'], 2) ?> <?= strtoupper($invoice['currency']) ?></td>
                                        <td><span class="invoice-status invoice-status-<?= $invoice['status'] ?>"><?= ucfirst($invoice['status']) ?></span></td>
                                        <td><a href="invoice.php?id=<?= $invoice['id'] ?>" class="btn btn-sm btn-outline">View</a></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                            <div class="view-all-link">
                                <a href="invoices.php">View All Invoices</a>
                            </div>
                        <?php else: ?>
                            <p>No invoices found.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php
// Include footer
include_once '../template/footer.php';
?>

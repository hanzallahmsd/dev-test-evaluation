<?php
/**
 * Individual invoice view
 */
require_once '../bootstrap.php';

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    redirect('/');
}

// Check if invoice ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    flash('error', 'Invalid invoice ID');
    redirect('/invoices.php');
}

$invoiceId = (int)$_GET['id'];

// Get invoice data
$invoiceModel = new \Models\Invoice();
$invoice = $invoiceModel->find($invoiceId);

// Check if invoice exists and belongs to the current user
if (!$invoice || $invoice['user_id'] != $_SESSION['user_id']) {
    flash('error', 'Invoice not found or you do not have permission to view it');
    redirect('/invoices.php');
}

// Get subscription data
$subscriptionModel = new \Models\Subscription();
$subscription = $subscriptionModel->find($invoice['subscription_id']);

// Get product data
$productModel = new \Models\Product();
$product = null;
if ($subscription) {
    $products = $productModel->getByTypeAndInterval($subscription['plan_type'], 'month');
    if (!empty($products)) {
        $product = $products[0];
    }
}

// Get user data
$userModel = new \Models\User();
$user = $userModel->find($_SESSION['user_id']);

// Include header
$pageTitle = 'Invoice #' . substr($invoice['stripe_invoice_id'], 0, 8);
include_once '../template/header.php';
?>

<!-- Invoice Section -->
<section class="invoice-section">
    <div class="container">
        <div class="invoice-container">
            <div class="invoice-header">
                <div class="invoice-title">
                    <h1>Invoice</h1>
                    <div class="invoice-id">#<?= substr($invoice['stripe_invoice_id'], 0, 8) ?></div>
                </div>
                <div class="invoice-actions">
                    <a href="invoices.php" class="btn btn-outline"><i class="fas fa-arrow-left"></i> Back to Invoices</a>
                    <?php if ($invoice['status'] === 'paid'): ?>
                    <a href="download-invoice.php?id=<?= $invoice['id'] ?>" class="btn btn-primary"><i class="fas fa-download"></i> Download PDF</a>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="invoice-status-bar">
                <div class="invoice-status invoice-status-<?= $invoice['status'] ?>">
                    <?php if ($invoice['status'] === 'paid'): ?>
                        <i class="fas fa-check-circle"></i> Paid
                    <?php elseif ($invoice['status'] === 'open'): ?>
                        <i class="fas fa-clock"></i> Pending Payment
                    <?php else: ?>
                        <i class="fas fa-exclamation-circle"></i> <?= ucfirst($invoice['status']) ?>
                    <?php endif; ?>
                </div>
                <div class="invoice-date">
                    <span>Invoice Date:</span> <?= date('F j, Y', strtotime($invoice['invoice_date'])) ?>
                </div>
            </div>
            
            <div class="invoice-details">
                <div class="invoice-company">
                    <h3><?= config('app.name') ?></h3>
                    <p>123 Business Street</p>
                    <p>Silicon Valley, CA 94000</p>
                    <p>United States</p>
                    <p>support@example.com</p>
                </div>
                
                <div class="invoice-customer">
                    <h3>Bill To:</h3>
                    <p><?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?></p>
                    <p><?= htmlspecialchars($user['email']) ?></p>
                </div>
            </div>
            
            <div class="invoice-items">
                <table class="invoice-table">
                    <thead>
                        <tr>
                            <th>Description</th>
                            <th>Period</th>
                            <th>Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>
                                <?php if ($product): ?>
                                    <?= htmlspecialchars($product['name']) ?>
                                <?php else: ?>
                                    <?= ucfirst($subscription['plan_type']) ?> Plan
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($subscription): ?>
                                    <?= date('M j, Y', strtotime($subscription['current_period_start'])) ?> - 
                                    <?= date('M j, Y', strtotime($subscription['current_period_end'])) ?>
                                <?php else: ?>
                                    -
                                <?php endif; ?>
                            </td>
                            <td><?= number_format($invoice['amount'], 2) ?> <?= strtoupper($invoice['currency']) ?></td>
                        </tr>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="2" class="text-right">Subtotal</td>
                            <td><?= number_format($invoice['amount'], 2) ?> <?= strtoupper($invoice['currency']) ?></td>
                        </tr>
                        <tr>
                            <td colspan="2" class="text-right">Tax</td>
                            <td>0.00 <?= strtoupper($invoice['currency']) ?></td>
                        </tr>
                        <tr class="total-row">
                            <td colspan="2" class="text-right">Total</td>
                            <td><?= number_format($invoice['amount'], 2) ?> <?= strtoupper($invoice['currency']) ?></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
            
            <div class="invoice-footer">
                <p>Thank you for your business!</p>
                <?php if ($invoice['status'] === 'open'): ?>
                <div class="payment-button">
                    <a href="pay-invoice.php?id=<?= $invoice['id'] ?>" class="btn btn-primary">Pay Now</a>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<?php
// Include footer
include_once '../template/footer.php';
?>

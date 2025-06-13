<?php
/**
 * User invoices page
 */
require_once '../bootstrap.php';

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    redirect('/');
}

// Pagination parameters
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;

// Get invoices with pagination
$invoiceModel = new \Models\Invoice();
$invoices = $invoiceModel->getPaginatedForUser($_SESSION['user_id'], $page, $limit);
$totalInvoices = $invoiceModel->countForUser($_SESSION['user_id']);
$totalPages = ceil($totalInvoices / $limit);

// Include header
$pageTitle = 'My Invoices';
include_once '../template/header.php';
?>

<!-- Invoices Section -->
<section class="invoices-section">
    <div class="container">
        <div class="section-header">
            <h1 class="section-title">My Invoices</h1>
            <p class="section-description">View and download your invoice history.</p>
        </div>
        
        <div class="invoices-container">
            <?php if (!empty($invoices)): ?>
                <div class="invoice-filters">
                    <div class="filter-group">
                        <label for="status-filter">Status:</label>
                        <select id="status-filter" class="form-select">
                            <option value="all">All</option>
                            <option value="paid">Paid</option>
                            <option value="open">Open</option>
                            <option value="failed">Failed</option>
                        </select>
                    </div>
                    <div class="filter-group">
                        <label for="date-filter">Date Range:</label>
                        <select id="date-filter" class="form-select">
                            <option value="all">All Time</option>
                            <option value="month">This Month</option>
                            <option value="quarter">Last 3 Months</option>
                            <option value="year">This Year</option>
                        </select>
                    </div>
                </div>
                
                <div class="table-responsive">
                    <table class="invoice-table">
                        <thead>
                            <tr>
                                <th>Invoice #</th>
                                <th>Date</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($invoices as $invoice): ?>
                            <tr data-status="<?= $invoice['status'] ?>">
                                <td><?= substr($invoice['stripe_invoice_id'], 0, 8) ?></td>
                                <td><?= date('M j, Y', strtotime($invoice['invoice_date'])) ?></td>
                                <td><?= number_format($invoice['amount'], 2) ?> <?= strtoupper($invoice['currency']) ?></td>
                                <td><span class="invoice-status invoice-status-<?= $invoice['status'] ?>"><?= ucfirst($invoice['status']) ?></span></td>
                                <td>
                                    <a href="invoice.php?id=<?= $invoice['id'] ?>" class="btn btn-sm btn-outline">View</a>
                                    <?php if ($invoice['status'] === 'paid'): ?>
                                    <a href="download-invoice.php?id=<?= $invoice['id'] ?>" class="btn btn-sm btn-primary">Download</a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                
                <?php if ($totalPages > 1): ?>
                <div class="pagination">
                    <?php if ($page > 1): ?>
                        <a href="?page=<?= $page - 1 ?>" class="pagination-item">&laquo; Previous</a>
                    <?php endif; ?>
                    
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <a href="?page=<?= $i ?>" class="pagination-item <?= $i === $page ? 'active' : '' ?>"><?= $i ?></a>
                    <?php endfor; ?>
                    
                    <?php if ($page < $totalPages): ?>
                        <a href="?page=<?= $page + 1 ?>" class="pagination-item">Next &raquo;</a>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
                
            <?php else: ?>
                <div class="no-invoices">
                    <div class="empty-state">
                        <i class="fas fa-file-invoice"></i>
                        <h3>No Invoices Found</h3>
                        <p>You don't have any invoices yet. They will appear here once you subscribe to a plan.</p>
                        <a href="#pricing" class="btn btn-primary">View Plans</a>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Filter functionality
    const statusFilter = document.getElementById('status-filter');
    const dateFilter = document.getElementById('date-filter');
    const invoiceRows = document.querySelectorAll('.invoice-table tbody tr');
    
    if (statusFilter) {
        statusFilter.addEventListener('change', filterInvoices);
    }
    
    if (dateFilter) {
        dateFilter.addEventListener('change', filterInvoices);
    }
    
    function filterInvoices() {
        const statusValue = statusFilter.value;
        const dateValue = dateFilter.value;
        
        invoiceRows.forEach(row => {
            const status = row.dataset.status;
            const date = new Date(row.cells[1].textContent);
            let showByStatus = true;
            let showByDate = true;
            
            // Status filtering
            if (statusValue !== 'all' && status !== statusValue) {
                showByStatus = false;
            }
            
            // Date filtering
            if (dateValue !== 'all') {
                const now = new Date();
                const startOfMonth = new Date(now.getFullYear(), now.getMonth(), 1);
                const startOfYear = new Date(now.getFullYear(), 0, 1);
                const threeMonthsAgo = new Date();
                threeMonthsAgo.setMonth(now.getMonth() - 3);
                
                if (dateValue === 'month' && date < startOfMonth) {
                    showByDate = false;
                } else if (dateValue === 'quarter' && date < threeMonthsAgo) {
                    showByDate = false;
                } else if (dateValue === 'year' && date < startOfYear) {
                    showByDate = false;
                }
            }
            
            row.style.display = (showByStatus && showByDate) ? '' : 'none';
        });
    }
});
</script>

<?php
// Include footer
include_once '../template/footer.php';
?>

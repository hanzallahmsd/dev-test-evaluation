<?php
/**
 * Admin Invoices Management
 */
require_once '../../config/init.php';

use Controllers\AuthController;
use Models\User;
use Models\Invoice;
use Models\Subscription;

// Check if user is logged in and is admin
$authController = new AuthController();
if (!$authController->isAdmin()) {
    redirect('/login.php');
}

// Initialize models
$userModel = new User();
$invoiceModel = new Invoice();
$subscriptionModel = new Subscription();

// Handle export action
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'export_invoices') {
        $format = isset($_POST['format']) ? $_POST['format'] : 'excel';
        $period = isset($_POST['period']) ? $_POST['period'] : 'all';
        
        // Get date range based on period
        $startDate = null;
        $endDate = date('Y-m-d H:i:s');
        
        switch ($period) {
            case 'today':
                $startDate = date('Y-m-d 00:00:00');
                break;
            case 'week':
                $startDate = date('Y-m-d 00:00:00', strtotime('-7 days'));
                break;
            case 'month':
                $startDate = date('Y-m-d 00:00:00', strtotime('-30 days'));
                break;
            case 'quarter':
                $startDate = date('Y-m-d 00:00:00', strtotime('-90 days'));
                break;
            case 'year':
                $startDate = date('Y-m-d 00:00:00', strtotime('-365 days'));
                break;
        }
        
        // Get invoices for export
        $invoices = $invoiceModel->getInvoicesForExport($startDate, $endDate);
        
        if ($format === 'excel') {
            // Export to Excel
            header('Content-Type: application/vnd.ms-excel');
            header('Content-Disposition: attachment; filename="invoices_export_' . date('Y-m-d') . '.xls"');
            
            echo "Invoice ID\tCustomer\tEmail\tAmount\tCurrency\tStatus\tDate\n";
            
            foreach ($invoices as $invoice) {
                echo $invoice['stripe_invoice_id'] . "\t";
                echo $invoice['first_name'] . ' ' . $invoice['last_name'] . "\t";
                echo $invoice['email'] . "\t";
                echo $invoice['amount'] . "\t";
                echo $invoice['currency'] . "\t";
                echo $invoice['status'] . "\t";
                echo date('Y-m-d', strtotime($invoice['invoice_date'])) . "\n";
            }
            
            exit;
        } elseif ($format === 'pdf') {
            // TODO: Implement PDF export
            flash('warning', 'PDF export is not yet implemented');
        }
    }
}

// Get all invoices with user data
$invoices = $invoiceModel->getAllWithUserData();

// Include admin layout
require_once '../../template/admin-layout.php';
?>

<div class="mb-6 flex justify-between items-center">
    <div>
        <h1 class="text-2xl font-bold text-gray-800">Invoice Management</h1>
        <p class="text-gray-600">View and manage customer invoices</p>
    </div>
    <div>
        <button id="export-btn" class="btn btn-primary">
            <i class="fas fa-file-export"></i> Export
        </button>
    </div>
</div>

<!-- Invoice Filters -->
<div class="card mb-6">
    <div class="flex flex-wrap gap-4">
        <div class="form-group mb-0">
            <label for="status-filter">Status</label>
            <select id="status-filter" class="form-control">
                <option value="">All Statuses</option>
                <option value="paid">Paid</option>
                <option value="open">Open</option>
                <option value="uncollectible">Uncollectible</option>
                <option value="void">Void</option>
            </select>
        </div>
        
        <div class="form-group mb-0">
            <label for="date-filter">Date Range</label>
            <select id="date-filter" class="form-control">
                <option value="">All Time</option>
                <option value="today">Today</option>
                <option value="week">Last 7 Days</option>
                <option value="month">Last 30 Days</option>
                <option value="quarter">Last 90 Days</option>
                <option value="year">Last Year</option>
            </select>
        </div>
        
        <div class="form-group mb-0">
            <label for="search-filter">Search</label>
            <input type="text" id="search-filter" class="form-control" placeholder="Search by customer or invoice ID">
        </div>
    </div>
</div>

<!-- Invoices Table -->
<div class="card mb-6">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200" id="invoices-table">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Invoice</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Customer</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php if (empty($invoices)): ?>
                    <tr>
                        <td colspan="6" class="px-6 py-4 text-center text-gray-500">No invoices found</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($invoices as $invoice): ?>
                        <?php 
                        // Determine status class
                        $statusClass = 'bg-gray-100 text-gray-800';
                        
                        switch ($invoice['status']) {
                            case 'paid':
                                $statusClass = 'bg-green-100 text-green-800';
                                break;
                            case 'open':
                                $statusClass = 'bg-blue-100 text-blue-800';
                                break;
                            case 'uncollectible':
                                $statusClass = 'bg-red-100 text-red-800';
                                break;
                            case 'void':
                                $statusClass = 'bg-gray-100 text-gray-800';
                                break;
                        }
                        
                        // Format amount with currency
                        $formattedAmount = $invoice['currency'] === 'EUR' ? '€' . number_format($invoice['amount'], 2) : number_format($invoice['amount'], 2) . ' ' . $invoice['currency'];
                        
                        // Format date
                        $invoiceDate = date('M d, Y', strtotime($invoice['invoice_date']));
                        
                        // Search data
                        $searchData = strtolower($invoice['stripe_invoice_id'] . ' ' . $invoice['first_name'] . ' ' . $invoice['last_name'] . ' ' . $invoice['email']);
                        ?>
                        <tr data-status="<?= $invoice['status'] ?>" data-date="<?= $invoice['invoice_date'] ?>" data-search="<?= $searchData ?>">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900"><?= substr($invoice['stripe_invoice_id'], 0, 8) ?>...</div>
                                <div class="text-xs text-gray-500"><?= $invoice['stripe_invoice_id'] ?></div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-8 w-8 rounded-full bg-primary text-white flex items-center justify-center">
                                        <span class="font-bold"><?= substr($invoice['first_name'], 0, 1) . substr($invoice['last_name'], 0, 1) ?></span>
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-gray-900">
                                            <?= htmlspecialchars($invoice['first_name'] . ' ' . $invoice['last_name']) ?>
                                        </div>
                                        <div class="text-xs text-gray-500">
                                            <?= htmlspecialchars($invoice['email']) ?>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900"><?= $formattedAmount ?></div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?= $statusClass ?>">
                                    <?= ucfirst($invoice['status']) ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <?= $invoiceDate ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex space-x-2">
                                    <a href="/admin/invoice-details.php?id=<?= $invoice['id'] ?>" class="text-primary hover:text-blue-700">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="https://dashboard.stripe.com/invoices/<?= $invoice['stripe_invoice_id'] ?>" target="_blank" class="text-secondary hover:text-indigo-700">
                                        <i class="fas fa-external-link-alt"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Export Modal -->
<div id="export-modal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title">Export Invoices</h3>
            <button class="modal-close">&times;</button>
        </div>
        <div class="modal-body">
            <form action="/admin/invoices.php" method="POST" id="export-form">
                <input type="hidden" name="action" value="export_invoices">
                
                <div class="form-group">
                    <label for="format">Export Format</label>
                    <select id="format" name="format" class="form-control">
                        <option value="excel">Excel (.xls)</option>
                        <option value="pdf">PDF</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="period">Time Period</label>
                    <select id="period" name="period" class="form-control">
                        <option value="all">All Time</option>
                        <option value="today">Today</option>
                        <option value="week">Last 7 Days</option>
                        <option value="month">Last 30 Days</option>
                        <option value="quarter">Last 90 Days</option>
                        <option value="year">Last Year</option>
                    </select>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Export</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Export modal
        const exportBtn = document.getElementById('export-btn');
        const exportModal = document.getElementById('export-modal');
        const exportModalClose = exportModal.querySelector('.modal-close');
        
        exportBtn.addEventListener('click', function() {
            exportModal.classList.add('show');
        });
        
        exportModalClose.addEventListener('click', function() {
            exportModal.classList.remove('show');
        });
        
        // Close modal when clicking outside
        window.addEventListener('click', function(event) {
            if (event.target === exportModal) {
                exportModal.classList.remove('show');
            }
        });
        
        // Filtering functionality
        const statusFilter = document.getElementById('status-filter');
        const dateFilter = document.getElementById('date-filter');
        const searchFilter = document.getElementById('search-filter');
        const rows = document.querySelectorAll('#invoices-table tbody tr');
        
        function applyFilters() {
            const statusValue = statusFilter.value.toLowerCase();
            const dateValue = dateFilter.value;
            const searchValue = searchFilter.value.toLowerCase();
            
            let startDate = null;
            
            // Calculate date range based on filter
            if (dateValue) {
                const now = new Date();
                
                switch (dateValue) {
                    case 'today':
                        startDate = new Date(now.setHours(0, 0, 0, 0));
                        break;
                    case 'week':
                        startDate = new Date(now.setDate(now.getDate() - 7));
                        break;
                    case 'month':
                        startDate = new Date(now.setDate(now.getDate() - 30));
                        break;
                    case 'quarter':
                        startDate = new Date(now.setDate(now.getDate() - 90));
                        break;
                    case 'year':
                        startDate = new Date(now.setDate(now.getDate() - 365));
                        break;
                }
            }
            
            rows.forEach(row => {
                const status = row.getAttribute('data-status');
                const dateStr = row.getAttribute('data-date');
                const searchText = row.getAttribute('data-search');
                
                const statusMatch = !statusValue || status === statusValue;
                const searchMatch = !searchValue || searchText.includes(searchValue);
                
                let dateMatch = true;
                if (startDate) {
                    const rowDate = new Date(dateStr);
                    dateMatch = rowDate >= startDate;
                }
                
                if (statusMatch && dateMatch && searchMatch) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        }
        
        statusFilter.addEventListener('change', applyFilters);
        dateFilter.addEventListener('change', applyFilters);
        searchFilter.addEventListener('input', applyFilters);
    });
</script>

<?php require_once '../../template/admin-footer.php'; ?>

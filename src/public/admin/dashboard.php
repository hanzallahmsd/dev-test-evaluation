<?php
/**
 * Admin Dashboard
 */
require_once '../../bootstrap.php';

// Initialize controllers
$adminController = new \App\Controllers\AdminController();

// Get dashboard statistics
$stats = $adminController->getDashboardStats();

// Get recent subscriptions
$recentSubscriptions = $adminController->getRecentSubscriptions(5);

// Get recent invoices
$recentInvoices = $adminController->getRecentInvoices(5);

// Include admin layout
include_once '../../template/admin-layout.php';
?>

<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-800">Dashboard</h1>
    <p class="text-gray-600">Welcome to the admin dashboard. Here's an overview of your subscription service.</p>
</div>

<!-- Stats Cards -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <div class="card bg-white border-l-4 border-primary">
        <div class="flex items-center">
            <div class="rounded-full bg-primary bg-opacity-10 p-3 mr-4">
                <i class="fas fa-users text-primary text-xl"></i>
            </div>
            <div>
                <h3 class="text-lg font-semibold text-gray-700">Total Customers</h3>
                <p class="text-2xl font-bold text-primary"><?= $stats['total_customers'] ?></p>
            </div>
        </div>
    </div>
    
    <div class="card bg-white border-l-4 border-secondary">
        <div class="flex items-center">
            <div class="rounded-full bg-secondary bg-opacity-10 p-3 mr-4">
                <i class="fas fa-credit-card text-secondary text-xl"></i>
            </div>
            <div>
                <h3 class="text-lg font-semibold text-gray-700">Active Subscriptions</h3>
                <p class="text-2xl font-bold text-secondary"><?= $stats['active_subscriptions'] ?></p>
            </div>
        </div>
    </div>
    
    <div class="card bg-white border-l-4 border-accent">
        <div class="flex items-center">
            <div class="rounded-full bg-accent bg-opacity-10 p-3 mr-4">
                <i class="fas fa-file-invoice-dollar text-accent text-xl"></i>
            </div>
            <div>
                <h3 class="text-lg font-semibold text-gray-700">Monthly Revenue</h3>
                <p class="text-2xl font-bold text-accent">€<?= number_format($stats['monthly_revenue'], 2) ?></p>
            </div>
        </div>
    </div>
    
    <div class="card bg-white border-l-4 border-dark">
        <div class="flex items-center">
            <div class="rounded-full bg-dark bg-opacity-10 p-3 mr-4">
                <i class="fas fa-chart-line text-dark text-xl"></i>
            </div>
            <div>
                <h3 class="text-lg font-semibold text-gray-700">Conversion Rate</h3>
                <p class="text-2xl font-bold text-dark"><?= $stats['conversion_rate'] ?>%</p>
            </div>
        </div>
    </div>
</div>

<!-- Charts -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
    <div class="card">
        <h3 class="text-lg font-semibold text-gray-700 mb-4">Revenue Trends</h3>
        <canvas id="revenueChart" height="300"></canvas>
    </div>
    
    <div class="card">
        <h3 class="text-lg font-semibold text-gray-700 mb-4">Subscription Distribution</h3>
        <canvas id="subscriptionChart" height="300"></canvas>
    </div>
</div>

<!-- Recent Activity -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <!-- Recent Subscriptions -->
    <div class="card">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-semibold text-gray-700">Recent Subscriptions</h3>
            <a href="/admin/subscriptions.php" class="text-primary hover:text-blue-700 text-sm">View All</a>
        </div>
        
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>Customer</th>
                        <th>Plan</th>
                        <th>Status</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($recentSubscriptions)): ?>
                        <tr>
                            <td colspan="4" class="text-center py-4">No recent subscriptions found</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($recentSubscriptions as $subscription): ?>
                            <tr>
                                <td class="font-medium"><?= htmlspecialchars($subscription['customer_name']) ?></td>
                                <td><?= htmlspecialchars($subscription['product_name']) ?></td>
                                <td>
                                    <?php if ($subscription['status'] === 'active'): ?>
                                        <span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-800">Active</span>
                                    <?php elseif ($subscription['status'] === 'canceled'): ?>
                                        <span class="px-2 py-1 text-xs rounded-full bg-red-100 text-red-800">Canceled</span>
                                    <?php elseif ($subscription['status'] === 'past_due'): ?>
                                        <span class="px-2 py-1 text-xs rounded-full bg-yellow-100 text-yellow-800">Past Due</span>
                                    <?php else: ?>
                                        <span class="px-2 py-1 text-xs rounded-full bg-gray-100 text-gray-800"><?= ucfirst($subscription['status']) ?></span>
                                    <?php endif; ?>
                                </td>
                                <td><?= date('M d, Y', strtotime($subscription['created_at'])) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <!-- Recent Invoices -->
    <div class="card">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-semibold text-gray-700">Recent Invoices</h3>
            <a href="/admin/invoices.php" class="text-primary hover:text-blue-700 text-sm">View All</a>
        </div>
        
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>Invoice #</th>
                        <th>Customer</th>
                        <th>Amount</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($recentInvoices)): ?>
                        <tr>
                            <td colspan="4" class="text-center py-4">No recent invoices found</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($recentInvoices as $invoice): ?>
                            <tr>
                                <td class="font-medium"><?= substr($invoice['stripe_invoice_id'], 0, 8) ?></td>
                                <td><?= htmlspecialchars($invoice['customer_name']) ?></td>
                                <td>€<?= number_format($invoice['amount'] / 100, 2) ?></td>
                                <td>
                                    <?php if ($invoice['status'] === 'paid'): ?>
                                        <span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-800">Paid</span>
                                    <?php elseif ($invoice['status'] === 'open'): ?>
                                        <span class="px-2 py-1 text-xs rounded-full bg-blue-100 text-blue-800">Open</span>
                                    <?php elseif ($invoice['status'] === 'uncollectible'): ?>
                                        <span class="px-2 py-1 text-xs rounded-full bg-red-100 text-red-800">Uncollectible</span>
                                    <?php else: ?>
                                        <span class="px-2 py-1 text-xs rounded-full bg-gray-100 text-gray-800"><?= ucfirst($invoice['status']) ?></span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="mt-8">
    <h3 class="text-lg font-semibold text-gray-700 mb-4">Quick Actions</h3>
    <div class="flex flex-wrap gap-4">
        <a href="/admin/customers.php?action=new" class="btn-primary">
            <i class="fas fa-user-plus mr-2"></i> Add Customer
        </a>
        <a href="/admin/customers.php?action=import" class="btn-secondary">
            <i class="fas fa-file-import mr-2"></i> Import Customers
        </a>
        <a href="/admin/reports.php" class="btn-accent">
            <i class="fas fa-chart-bar mr-2"></i> Generate Reports
        </a>
    </div>
</div>

<script>
    // Revenue Chart
    const revenueCtx = document.getElementById('revenueChart').getContext('2d');
    const revenueChart = new Chart(revenueCtx, {
        type: 'line',
        data: {
            labels: <?= json_encode(array_keys($stats['revenue_data'])) ?>,
            datasets: [{
                label: 'Monthly Revenue (€)',
                data: <?= json_encode(array_values($stats['revenue_data'])) ?>,
                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                borderColor: 'rgba(59, 130, 246, 1)',
                borderWidth: 2,
                tension: 0.4,
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return '€' + value;
                        }
                    }
                }
            }
        }
    });
    
    // Subscription Chart
    const subscriptionCtx = document.getElementById('subscriptionChart').getContext('2d');
    const subscriptionChart = new Chart(subscriptionCtx, {
        type: 'doughnut',
        data: {
            labels: <?= json_encode(array_keys($stats['subscription_distribution'])) ?>,
            datasets: [{
                data: <?= json_encode(array_values($stats['subscription_distribution'])) ?>,
                backgroundColor: [
                    'rgba(59, 130, 246, 0.8)',
                    'rgba(16, 185, 129, 0.8)',
                    'rgba(139, 92, 246, 0.8)',
                ],
                borderColor: [
                    'rgba(59, 130, 246, 1)',
                    'rgba(16, 185, 129, 1)',
                    'rgba(139, 92, 246, 1)',
                ],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });
</script>

<?php
// Include admin footer
include_once '../../template/admin-footer.php';
?>

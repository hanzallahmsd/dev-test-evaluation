<?php
/**
 * Admin Dashboard
 */
require_once '../../config/init.php';

use Controllers\AdminController;
use Models\User;
use Models\Subscription;
use Models\Invoice;

// Check if user is logged in and is admin
$authController = new Controllers\AuthController();
if (!$authController->isAdmin()) {
    redirect('/login.php');
}

// Initialize controllers
$adminController = new AdminController();

// Get dashboard statistics
$stats = $adminController->getDashboardStats();

// Get recent subscriptions
$recentSubscriptions = $adminController->getRecentSubscriptions(5);

// Get recent invoices
$recentInvoices = $adminController->getRecentInvoices(5);

// Include admin layout
include_once '../../template/admin-layout.php';
?>

<div class="admin-header mb-6">
    <h1 class="admin-title">Dashboard</h1>
    <p class="text-gray-600">Welcome to the admin dashboard. Here's an overview of your subscription service.</p>
</div>

<!-- Stats Cards -->
<div class="stats-grid">
    <div class="stat-card primary">
        <div class="flex items-center">
            <div class="stat-icon primary">
                <i class="fas fa-users fa-lg"></i>
            </div>
            <div>
                <h3 class="stat-title">Total Customers</h3>
                <p class="stat-value"><?= $stats['total_customers'] ?></p>
            </div>
        </div>
    </div>
    
    <div class="stat-card secondary">
        <div class="flex items-center">
            <div class="stat-icon secondary">
                <i class="fas fa-credit-card fa-lg"></i>
            </div>
            <div>
                <h3 class="stat-title">Active Subscriptions</h3>
                <p class="stat-value"><?= $stats['active_subscriptions'] ?></p>
            </div>
        </div>
    </div>
    
    <div class="stat-card accent">
        <div class="flex items-center">
            <div class="stat-icon accent">
                <i class="fas fa-file-invoice-dollar fa-lg"></i>
            </div>
            <div>
                <h3 class="stat-title">Monthly Revenue</h3>
                <p class="stat-value">€<?= number_format(is_array($stats['monthly_revenue']) ? array_sum(array_column($stats['monthly_revenue'], 'revenue')) : 0, 2) ?></p>
            </div>
        </div>
    </div>
    
    <div class="stat-card dark">
        <div class="flex items-center">
            <div class="stat-icon dark">
                <i class="fas fa-chart-line fa-lg"></i>
            </div>
            <div>
                <h3 class="stat-title">Conversion Rate</h3>
                <p class="stat-value"><?= isset($stats['conversion_rate']) ? $stats['conversion_rate'] : '0' ?>%</p>
            </div>
        </div>
    </div>
</div>

<!-- Charts -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
    <div class="admin-card">
        <div class="admin-card-header">
            <h3 class="admin-card-title">Revenue Trends</h3>
        </div>
        <div class="admin-card-body">
            <canvas id="revenueChart" height="300"></canvas>
        </div>
    </div>
    
    <div class="admin-card">
        <div class="admin-card-header">
            <h3 class="admin-card-title">Subscription Distribution</h3>
        </div>
        <div class="admin-card-body">
            <canvas id="subscriptionChart" height="300"></canvas>
        </div>
    </div>
</div>

<!-- Recent Activity -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <!-- Recent Subscriptions -->
    <div class="admin-card">
        <div class="admin-card-header">
            <h3 class="admin-card-title">Recent Subscriptions</h3>
            <a href="/admin/subscriptions.php" class="admin-btn admin-btn-primary admin-btn-sm">
                <i class="fas fa-eye admin-btn-icon"></i> View All
            </a>
        </div>
        
        <div class="admin-card-body">
            <div class="overflow-x-auto">
                <table class="admin-table w-full">
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
                                <?php 
                                // Fix for missing customer_name and product_name
                                $customerName = isset($subscription['first_name']) ? 
                                    htmlspecialchars($subscription['first_name'] . ' ' . $subscription['last_name']) : 
                                    'Unknown';
                                    
                                $planName = isset($subscription['plan_name']) ? 
                                    htmlspecialchars($subscription['plan_name']) : 
                                    htmlspecialchars($subscription['plan_type']);
                                ?>
                                <tr>
                                    <td class="font-medium"><?= $customerName ?></td>
                                    <td><?= $planName ?></td>
                                    <td>
                                        <span class="status-badge <?= $subscription['status'] ?>">
                                            <?= ucfirst($subscription['status']) ?>
                                        </span>
                                    </td>
                                    <td><?= date('M d, Y', strtotime($subscription['created_at'])) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <!-- Recent Invoices -->
    <div class="admin-card">
        <div class="admin-card-header">
            <h3 class="admin-card-title">Recent Invoices</h3>
            <a href="/admin/invoices.php" class="admin-btn admin-btn-primary admin-btn-sm">
                <i class="fas fa-eye admin-btn-icon"></i> View All
            </a>
        </div>
        
        <div class="admin-card-body">
            <div class="overflow-x-auto">
                <table class="admin-table w-full">
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
                                <?php
                                // Fix for missing customer_name
                                $customerName = isset($invoice['first_name']) ? 
                                    htmlspecialchars($invoice['first_name'] . ' ' . $invoice['last_name']) : 
                                    'Unknown';
                                    
                                // Fix for amount display
                                $amount = isset($invoice['amount']) ? $invoice['amount'] : 0;
                                // Check if amount needs to be divided by 100 (Stripe stores in cents)
                                $amount = ($amount > 1000) ? $amount / 100 : $amount;
                                ?>
                                <tr>
                                    <td class="font-medium"><?= substr($invoice['stripe_invoice_id'], 0, 8) ?></td>
                                    <td><?= $customerName ?></td>
                                    <td>€<?= number_format($amount, 2) ?></td>
                                    <td>
                                        <span class="status-badge <?= $invoice['status'] ?>">
                                            <?= ucfirst($invoice['status']) ?>
                                        </span>
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
<div class="admin-card mt-8">
    <div class="admin-card-header">
        <h3 class="admin-card-title">Quick Actions</h3>
    </div>
    <div class="admin-card-body">
        <div class="flex flex-wrap gap-4">
            <a href="/admin/customers.php?action=new" class="admin-btn admin-btn-primary">
                <i class="fas fa-user-plus admin-btn-icon"></i> Add Customer
            </a>
            <a href="/admin/customers.php?action=import" class="admin-btn admin-btn-secondary">
                <i class="fas fa-file-import admin-btn-icon"></i> Import Customers
            </a>
            <a href="/admin/reports.php" class="admin-btn admin-btn-accent">
                <i class="fas fa-chart-bar admin-btn-icon"></i> Generate Reports
            </a>
        </div>
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

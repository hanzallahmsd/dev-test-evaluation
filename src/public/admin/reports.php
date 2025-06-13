<?php
/**
 * Admin Reports and Analytics
 */
require_once '../../config/init.php';

use Controllers\AuthController;
use Models\User;
use Models\Subscription;
use Models\Invoice;
use Models\Product;

// Check if user is logged in and is admin
$authController = new AuthController();
if (!$authController->isAdmin()) {
    redirect('/login.php');
}

// Initialize models
$userModel = new User();
$subscriptionModel = new Subscription();
$invoiceModel = new Invoice();
$productModel = new Product();

// Get report data
$totalCustomers = $userModel->countCustomers();
$activeSubscriptions = $subscriptionModel->countByStatus('active');
$monthlyRevenue = $invoiceModel->getMonthlyRevenue();
$yearlyRevenue = $invoiceModel->getYearlyRevenue();

// Get subscription distribution by plan
$subscriptionsByPlan = $subscriptionModel->getSubscriptionsByPlan();

// Get revenue by month for the last 12 months
$revenueByMonth = $invoiceModel->getRevenueByMonth(12);

// Get customer growth by month for the last 12 months
$customerGrowthByMonth = $userModel->getCustomerGrowthByMonth(12);

// Include admin layout
require_once '../../template/admin-layout.php';
?>

<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-800">Reports & Analytics</h1>
    <p class="text-gray-600">View detailed reports and analytics for your subscription business</p>
</div>

<!-- Summary Cards -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <div class="card bg-white border-l-4 border-primary">
        <div class="flex items-center">
            <div class="rounded-full bg-primary bg-opacity-10 p-3 mr-4">
                <i class="fas fa-users text-primary text-xl"></i>
            </div>
            <div>
                <h3 class="text-lg font-semibold text-gray-700">Total Customers</h3>
                <p class="text-2xl font-bold text-primary"><?= $totalCustomers ?></p>
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
                <p class="text-2xl font-bold text-secondary"><?= $activeSubscriptions ?></p>
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
                <p class="text-2xl font-bold text-accent">€<?= number_format($monthlyRevenue, 2) ?></p>
            </div>
        </div>
    </div>
    
    <div class="card bg-white border-l-4 border-dark">
        <div class="flex items-center">
            <div class="rounded-full bg-dark bg-opacity-10 p-3 mr-4">
                <i class="fas fa-chart-line text-dark text-xl"></i>
            </div>
            <div>
                <h3 class="text-lg font-semibold text-gray-700">Yearly Revenue</h3>
                <p class="text-2xl font-bold text-dark">€<?= number_format($yearlyRevenue, 2) ?></p>
            </div>
        </div>
    </div>
</div>

<!-- Revenue Chart -->
<div class="card mb-8">
    <h3 class="text-lg font-semibold text-gray-700 mb-4">Revenue Trends</h3>
    <div class="flex justify-end mb-4">
        <div class="form-group mb-0">
            <select id="revenue-period" class="form-control">
                <option value="12">Last 12 Months</option>
                <option value="6">Last 6 Months</option>
                <option value="3">Last 3 Months</option>
            </select>
        </div>
    </div>
    <canvas id="revenueChart" height="300"></canvas>
</div>

<!-- Customer Growth Chart -->
<div class="card mb-8">
    <h3 class="text-lg font-semibold text-gray-700 mb-4">Customer Growth</h3>
    <div class="flex justify-end mb-4">
        <div class="form-group mb-0">
            <select id="customer-period" class="form-control">
                <option value="12">Last 12 Months</option>
                <option value="6">Last 6 Months</option>
                <option value="3">Last 3 Months</option>
            </select>
        </div>
    </div>
    <canvas id="customerChart" height="300"></canvas>
</div>

<!-- Subscription Distribution -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
    <div class="card">
        <h3 class="text-lg font-semibold text-gray-700 mb-4">Subscription Distribution by Plan</h3>
        <canvas id="planDistributionChart" height="300"></canvas>
    </div>
    
    <div class="card">
        <h3 class="text-lg font-semibold text-gray-700 mb-4">Subscription Status Distribution</h3>
        <canvas id="statusDistributionChart" height="300"></canvas>
    </div>
</div>

<!-- Export Reports Section -->
<div class="card mb-8">
    <h3 class="text-lg font-semibold text-gray-700 mb-4">Export Reports</h3>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        <div class="p-4 border rounded-lg bg-gray-50">
            <h4 class="font-semibold mb-2">Customer Report</h4>
            <p class="text-sm text-gray-600 mb-4">Export a detailed report of all customers and their subscription status.</p>
            <form action="/admin/export-report.php" method="POST">
                <input type="hidden" name="report_type" value="customers">
                <button type="submit" class="btn btn-sm btn-primary">
                    <i class="fas fa-download mr-1"></i> Export
                </button>
            </form>
        </div>
        
        <div class="p-4 border rounded-lg bg-gray-50">
            <h4 class="font-semibold mb-2">Revenue Report</h4>
            <p class="text-sm text-gray-600 mb-4">Export a detailed report of revenue by month, quarter, or year.</p>
            <form action="/admin/export-report.php" method="POST">
                <input type="hidden" name="report_type" value="revenue">
                <button type="submit" class="btn btn-sm btn-primary">
                    <i class="fas fa-download mr-1"></i> Export
                </button>
            </form>
        </div>
        
        <div class="p-4 border rounded-lg bg-gray-50">
            <h4 class="font-semibold mb-2">Subscription Report</h4>
            <p class="text-sm text-gray-600 mb-4">Export a detailed report of all subscriptions and their status.</p>
            <form action="/admin/export-report.php" method="POST">
                <input type="hidden" name="report_type" value="subscriptions">
                <button type="submit" class="btn btn-sm btn-primary">
                    <i class="fas fa-download mr-1"></i> Export
                </button>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Chart.js configuration
        Chart.defaults.font.family = "'Poppins', 'Helvetica', 'Arial', sans-serif";
        Chart.defaults.color = '#6B7280';
        
        // Revenue Chart
        const revenueCtx = document.getElementById('revenueChart').getContext('2d');
        const revenueData = <?= json_encode(array_column($revenueByMonth, 'amount')) ?>;
        const revenueLabels = <?= json_encode(array_column($revenueByMonth, 'month')) ?>;
        
        const revenueChart = new Chart(revenueCtx, {
            type: 'line',
            data: {
                labels: revenueLabels,
                datasets: [{
                    label: 'Monthly Revenue (€)',
                    data: revenueData,
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
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return '€' + context.raw.toFixed(2);
                            }
                        }
                    }
                },
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
        
        // Customer Growth Chart
        const customerCtx = document.getElementById('customerChart').getContext('2d');
        const customerData = <?= json_encode(array_column($customerGrowthByMonth, 'count')) ?>;
        const customerLabels = <?= json_encode(array_column($customerGrowthByMonth, 'month')) ?>;
        
        const customerChart = new Chart(customerCtx, {
            type: 'line',
            data: {
                labels: customerLabels,
                datasets: [{
                    label: 'New Customers',
                    data: customerData,
                    backgroundColor: 'rgba(79, 70, 229, 0.1)',
                    borderColor: 'rgba(79, 70, 229, 1)',
                    borderWidth: 2,
                    tension: 0.4,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            precision: 0
                        }
                    }
                }
            }
        });
        
        // Plan Distribution Chart
        const planCtx = document.getElementById('planDistributionChart').getContext('2d');
        const planData = <?= json_encode(array_column($subscriptionsByPlan, 'count')) ?>;
        const planLabels = <?= json_encode(array_column($subscriptionsByPlan, 'plan_type')) ?>;
        
        const planChart = new Chart(planCtx, {
            type: 'doughnut',
            data: {
                labels: planLabels.map(label => label.charAt(0).toUpperCase() + label.slice(1)),
                datasets: [{
                    data: planData,
                    backgroundColor: [
                        'rgba(59, 130, 246, 0.8)',
                        'rgba(79, 70, 229, 0.8)',
                        'rgba(236, 72, 153, 0.8)'
                    ],
                    borderColor: [
                        'rgba(59, 130, 246, 1)',
                        'rgba(79, 70, 229, 1)',
                        'rgba(236, 72, 153, 1)'
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
        
        // Status Distribution Chart
        const statusCtx = document.getElementById('statusDistributionChart').getContext('2d');
        const statusData = [
            <?= $subscriptionModel->countByStatus('active') ?>,
            <?= $subscriptionModel->countByStatus('past_due') ?>,
            <?= $subscriptionModel->countByStatus('canceled') ?>,
            <?= $subscriptionModel->countByStatus('trialing') ?>
        ];
        const statusLabels = ['Active', 'Past Due', 'Canceled', 'Trialing'];
        
        const statusChart = new Chart(statusCtx, {
            type: 'doughnut',
            data: {
                labels: statusLabels,
                datasets: [{
                    data: statusData,
                    backgroundColor: [
                        'rgba(16, 185, 129, 0.8)',
                        'rgba(245, 158, 11, 0.8)',
                        'rgba(239, 68, 68, 0.8)',
                        'rgba(59, 130, 246, 0.8)'
                    ],
                    borderColor: [
                        'rgba(16, 185, 129, 1)',
                        'rgba(245, 158, 11, 1)',
                        'rgba(239, 68, 68, 1)',
                        'rgba(59, 130, 246, 1)'
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
        
        // Period selectors
        document.getElementById('revenue-period').addEventListener('change', function() {
            const period = parseInt(this.value);
            // TODO: Implement API call to get data for selected period
            // For now, we'll just update the chart with the existing data
            revenueChart.data.labels = revenueLabels.slice(-period);
            revenueChart.data.datasets[0].data = revenueData.slice(-period);
            revenueChart.update();
        });
        
        document.getElementById('customer-period').addEventListener('change', function() {
            const period = parseInt(this.value);
            // TODO: Implement API call to get data for selected period
            // For now, we'll just update the chart with the existing data
            customerChart.data.labels = customerLabels.slice(-period);
            customerChart.data.datasets[0].data = customerData.slice(-period);
            customerChart.update();
        });
    });
</script>

<?php require_once '../../template/admin-footer.php'; ?>

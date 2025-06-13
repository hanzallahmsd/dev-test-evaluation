<?php
/**
 * Admin Subscriptions Management
 */
require_once '../../config/init.php';

use Controllers\AuthController;
use Models\User;
use Models\Subscription;
use Models\Product;

// Check if user is logged in and is admin
$authController = new AuthController();
if (!$authController->isAdmin()) {
    redirect('/login.php');
}

// Initialize models
$userModel = new User();
$subscriptionModel = new Subscription();
$productModel = new Product();

// Handle subscription actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'update_status' && isset($_POST['subscription_id']) && isset($_POST['status'])) {
        $subscriptionId = $_POST['subscription_id'];
        $status = $_POST['status'];
        
        // Update subscription status
        if ($subscriptionModel->updateStatus($subscriptionId, $status)) {
            flash('success', 'Subscription status updated successfully');
        } else {
            flash('error', 'Failed to update subscription status');
        }
    }
}

// Get all subscriptions with user data
$subscriptions = $subscriptionModel->getAllWithUserData();

// Get all products
$products = $productModel->all();

// Include admin layout
require_once '../../template/admin-layout.php';
?>

<div class="mb-6 flex justify-between items-center">
    <div>
        <h1 class="text-2xl font-bold text-gray-800">Subscription Management</h1>
        <p class="text-gray-600">Manage customer subscriptions</p>
    </div>
    <div>
        <a href="/admin/customers.php" class="btn btn-primary">
            <i class="fas fa-user-plus"></i> Add Customer
        </a>
    </div>
</div>

<!-- Subscription Filters -->
<div class="card mb-6">
    <div class="flex flex-wrap gap-4">
        <div class="form-group mb-0">
            <label for="status-filter">Status</label>
            <select id="status-filter" class="form-control">
                <option value="">All Statuses</option>
                <option value="active">Active</option>
                <option value="past_due">Past Due</option>
                <option value="canceled">Canceled</option>
                <option value="trialing">Trialing</option>
            </select>
        </div>
        
        <div class="form-group mb-0">
            <label for="plan-filter">Plan Type</label>
            <select id="plan-filter" class="form-control">
                <option value="">All Plans</option>
                <option value="small">Small</option>
                <option value="medium">Medium</option>
                <option value="large">Large</option>
            </select>
        </div>
        
        <div class="form-group mb-0">
            <label for="search-filter">Search</label>
            <input type="text" id="search-filter" class="form-control" placeholder="Search by name or email">
        </div>
    </div>
</div>

<!-- Subscriptions Table -->
<div class="card mb-6">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200" id="subscriptions-table">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Customer</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Plan</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Billing</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Next Payment</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php if (empty($subscriptions)): ?>
                    <tr>
                        <td colspan="6" class="px-6 py-4 text-center text-gray-500">No subscriptions found</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($subscriptions as $subscription): ?>
                        <?php 
                        // Determine status class
                        $statusClass = 'bg-gray-100 text-gray-800';
                        
                        switch ($subscription['status']) {
                            case 'active':
                                $statusClass = 'bg-green-100 text-green-800';
                                break;
                            case 'past_due':
                                $statusClass = 'bg-yellow-100 text-yellow-800';
                                break;
                            case 'canceled':
                                $statusClass = 'bg-red-100 text-red-800';
                                break;
                            case 'trialing':
                                $statusClass = 'bg-blue-100 text-blue-800';
                                break;
                        }
                        
                        // Format plan type
                        $planType = ucfirst($subscription['plan_type']);
                        
                        // Format next payment date
                        $nextPaymentDate = $subscription['current_period_end'] ? date('M d, Y', strtotime($subscription['current_period_end'])) : 'N/A';
                        ?>
                        <tr data-status="<?= $subscription['status'] ?>" data-plan="<?= $subscription['plan_type'] ?>" data-search="<?= strtolower($subscription['first_name'] . ' ' . $subscription['last_name'] . ' ' . $subscription['email']) ?>">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-10 w-10 rounded-full bg-primary text-white flex items-center justify-center">
                                        <span class="font-bold"><?= substr($subscription['first_name'], 0, 1) . substr($subscription['last_name'], 0, 1) ?></span>
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-gray-900">
                                            <?= htmlspecialchars($subscription['first_name'] . ' ' . $subscription['last_name']) ?>
                                        </div>
                                        <div class="text-sm text-gray-500">
                                            <?= htmlspecialchars($subscription['email']) ?>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900"><?= $planType ?></div>
                                <div class="text-xs text-gray-500"><?= $subscription['stripe_subscription_id'] ?></div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?= $statusClass ?>">
                                    <?= ucfirst($subscription['status']) ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <?= date('M d, Y', strtotime($subscription['created_at'])) ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <?= $nextPaymentDate ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex space-x-2">
                                    <a href="/admin/subscription-details.php?id=<?= $subscription['id'] ?>" class="text-primary hover:text-blue-700">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    
                                    <button class="text-secondary hover:text-indigo-700 status-toggle" data-id="<?= $subscription['id'] ?>" data-status="<?= $subscription['status'] ?>">
                                        <i class="fas fa-sync-alt"></i>
                                    </button>
                                    
                                    <?php if ($subscription['status'] === 'canceled'): ?>
                                        <form action="/admin/subscriptions.php" method="POST" class="inline">
                                            <input type="hidden" name="action" value="update_status">
                                            <input type="hidden" name="subscription_id" value="<?= $subscription['id'] ?>">
                                            <input type="hidden" name="status" value="active">
                                            <button type="submit" class="text-green-600 hover:text-green-800" title="Reactivate Subscription">
                                                <i class="fas fa-redo"></i>
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Status Update Modal -->
<div id="status-modal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title">Update Subscription Status</h3>
            <button class="modal-close">&times;</button>
        </div>
        <div class="modal-body">
            <form action="/admin/subscriptions.php" method="POST" id="status-form">
                <input type="hidden" name="action" value="update_status">
                <input type="hidden" name="subscription_id" id="subscription_id">
                
                <div class="form-group">
                    <label for="status">Status</label>
                    <select id="status" name="status" class="form-control">
                        <option value="active">Active</option>
                        <option value="past_due">Past Due</option>
                        <option value="canceled">Canceled</option>
                        <option value="trialing">Trialing</option>
                    </select>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Update Status</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Status update modal
        const statusModal = document.getElementById('status-modal');
        const statusForm = document.getElementById('status-form');
        const statusButtons = document.querySelectorAll('.status-toggle');
        const statusModalClose = statusModal.querySelector('.modal-close');
        
        statusButtons.forEach(button => {
            button.addEventListener('click', function() {
                const subscriptionId = this.getAttribute('data-id');
                const currentStatus = this.getAttribute('data-status');
                
                document.getElementById('subscription_id').value = subscriptionId;
                document.getElementById('status').value = currentStatus;
                
                statusModal.classList.add('show');
            });
        });
        
        statusModalClose.addEventListener('click', function() {
            statusModal.classList.remove('show');
        });
        
        // Close modal when clicking outside
        window.addEventListener('click', function(event) {
            if (event.target === statusModal) {
                statusModal.classList.remove('show');
            }
        });
        
        // Filtering functionality
        const statusFilter = document.getElementById('status-filter');
        const planFilter = document.getElementById('plan-filter');
        const searchFilter = document.getElementById('search-filter');
        const rows = document.querySelectorAll('#subscriptions-table tbody tr');
        
        function applyFilters() {
            const statusValue = statusFilter.value.toLowerCase();
            const planValue = planFilter.value.toLowerCase();
            const searchValue = searchFilter.value.toLowerCase();
            
            rows.forEach(row => {
                const status = row.getAttribute('data-status');
                const plan = row.getAttribute('data-plan');
                const searchText = row.getAttribute('data-search');
                
                const statusMatch = !statusValue || status === statusValue;
                const planMatch = !planValue || plan === planValue;
                const searchMatch = !searchValue || searchText.includes(searchValue);
                
                if (statusMatch && planMatch && searchMatch) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        }
        
        statusFilter.addEventListener('change', applyFilters);
        planFilter.addEventListener('change', applyFilters);
        searchFilter.addEventListener('input', applyFilters);
    });
</script>

<?php require_once '../../template/admin-footer.php'; ?>

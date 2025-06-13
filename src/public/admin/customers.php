<?php
/**
 * Admin Customers Management
 */
require_once '../../config/init.php';

use Controllers\AuthController;
use Models\User;
use Models\Subscription;

// Check if user is logged in and is admin
$authController = new AuthController();
if (!$authController->isAdmin()) {
    redirect('/login.php');
}

// Initialize models
$userModel = new User();
$subscriptionModel = new Subscription();

// Handle customer creation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'create_customer') {
        // Validate form data
        if (empty($_POST['email']) || empty($_POST['first_name']) || empty($_POST['last_name'])) {
            flash('error', 'Please fill in all required fields');
        } else {
            // Check if email already exists
            $existingUser = $userModel->findOneBy('email', $_POST['email']);
            if ($existingUser) {
                flash('error', 'Email already in use');
            } else {
                // Generate a random password
                $password = bin2hex(random_bytes(8));
                
                // Create user
                $userId = $userModel->createWithHashedPassword([
                    'email' => $_POST['email'],
                    'password' => $password,
                    'first_name' => $_POST['first_name'],
                    'last_name' => $_POST['last_name'],
                    'role' => 'customer',
                    'stripe_customer_id' => isset($_POST['stripe_customer_id']) ? $_POST['stripe_customer_id'] : null
                ]);
                
                if ($userId) {
                    // TODO: Send email with Stripe checkout link
                    flash('success', 'Customer created successfully. An email will be sent to the customer to complete registration.');
                } else {
                    flash('error', 'Failed to create customer');
                }
            }
        }
    } elseif ($_POST['action'] === 'import_customers') {
        // Handle CSV import
        if (isset($_FILES['csv_file']) && $_FILES['csv_file']['error'] === UPLOAD_ERR_OK) {
            $csvFile = $_FILES['csv_file']['tmp_name'];
            $handle = fopen($csvFile, 'r');
            
            if ($handle !== false) {
                // Skip header row
                fgetcsv($handle);
                
                $importCount = 0;
                $errorCount = 0;
                
                while (($data = fgetcsv($handle)) !== false) {
                    // Assuming CSV format: email, first_name, last_name
                    if (count($data) >= 3) {
                        $email = trim($data[0]);
                        $firstName = trim($data[1]);
                        $lastName = trim($data[2]);
                        
                        // Validate email
                        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                            // Check if email already exists
                            $existingUser = $userModel->findOneBy('email', $email);
                            if (!$existingUser) {
                                // Generate a random password
                                $password = bin2hex(random_bytes(8));
                                
                                // Create user
                                $userId = $userModel->createWithHashedPassword([
                                    'email' => $email,
                                    'password' => $password,
                                    'first_name' => $firstName,
                                    'last_name' => $lastName,
                                    'role' => 'customer'
                                ]);
                                
                                if ($userId) {
                                    // TODO: Send email with Stripe checkout link
                                    $importCount++;
                                } else {
                                    $errorCount++;
                                }
                            } else {
                                $errorCount++;
                            }
                        } else {
                            $errorCount++;
                        }
                    }
                }
                
                fclose($handle);
                
                if ($importCount > 0) {
                    flash('success', "Successfully imported {$importCount} customers. Emails will be sent to complete registration.");
                }
                
                if ($errorCount > 0) {
                    flash('warning', "{$errorCount} customers could not be imported due to errors or duplicate emails.");
                }
            } else {
                flash('error', 'Failed to open CSV file');
            }
        } else {
            flash('error', 'Please upload a valid CSV file');
        }
    }
}

// Get all customers
$customers = $userModel->getAllCustomers();

// Include admin layout
require_once '../../template/admin-layout.php';
?>

<div class="admin-header mb-6 flex justify-between items-center">
    <div>
        <h1 class="admin-title">Customer Management</h1>
        <p class="text-gray-600">Manage your subscription customers</p>
    </div>
    <div class="flex space-x-2">
        <button id="create-customer-btn" class="admin-btn admin-btn-primary">
            <i class="fas fa-user-plus admin-btn-icon"></i> Add Customer
        </button>
        <button id="import-customers-btn" class="admin-btn admin-btn-secondary">
            <i class="fas fa-file-import admin-btn-icon"></i> Import CSV
        </button>
    </div>
</div>

<!-- Customer Table -->
<div class="admin-card mb-6">
    <div class="admin-card-header">
        <h3 class="admin-card-title">All Customers</h3>
    </div>
    <div class="admin-card-body overflow-x-auto">
        <table class="admin-table w-full">
            <thead>
                <tr>
                    <th>Customer</th>
                    <th>Email</th>
                    <th>Stripe ID</th>
                    <th>Status</th>
                    <th>Created</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($customers)): ?>
                    <tr>
                        <td colspan="6" class="text-center py-4">No customers found</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($customers as $customer): ?>
                        <?php 
                        // Get customer's subscription
                        $subscription = $subscriptionModel->getActiveSubscriptionForUser($customer['id']);
                        $status = $subscription ? $subscription['status'] : 'no-subscription';
                        $statusText = $subscription ? ucfirst($subscription['status']) : 'No Subscription';
                        ?>
                        <tr>
                            <td>
                                <div class="flex items-center">
                                    <div class="user-avatar">
                                        <span><?= substr($customer['first_name'], 0, 1) . substr($customer['last_name'], 0, 1) ?></span>
                                    </div>
                                    <div class="ml-3">
                                        <div class="font-medium">
                                            <?= htmlspecialchars($customer['first_name'] . ' ' . $customer['last_name']) ?>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <?= htmlspecialchars($customer['email']) ?>
                            </td>
                            <td>
                                <?= $customer['stripe_customer_id'] ? htmlspecialchars($customer['stripe_customer_id']) : '<span class="text-muted">Not linked</span>' ?>
                            </td>
                            <td>
                                <span class="status-badge <?= $status ?>">
                                    <?= $statusText ?>
                                </span>
                            </td>
                            <td>
                                <?= date('M d, Y', strtotime($customer['created_at'])) ?>
                            </td>
                            <td>
                                <div class="action-buttons">
                                    <a href="/admin/customer-details.php?id=<?= $customer['id'] ?>" class="admin-btn admin-btn-icon admin-btn-primary" title="View Details">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="/admin/edit-customer.php?id=<?= $customer['id'] ?>" class="admin-btn admin-btn-icon admin-btn-secondary" title="Edit Customer">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <?php if (!$subscription || $subscription['status'] !== 'active'): ?>
                                        <a href="/admin/send-checkout.php?id=<?= $customer['id'] ?>" class="admin-btn admin-btn-icon admin-btn-accent" title="Send Checkout Link">
                                            <i class="fas fa-paper-plane"></i>
                                        </a>
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

<!-- Create Customer Modal -->
<div id="create-customer-modal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title">Add New Customer</h3>
            <button class="modal-close">&times;</button>
        </div>
        <div class="modal-body">
            <form action="/admin/customers.php" method="POST" id="create-customer-form">
                <input type="hidden" name="action" value="create_customer">
                
                <div class="form-group">
                    <label for="email">Email Address <span class="text-red-500">*</span></label>
                    <input type="email" id="email" name="email" required class="form-control">
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="first_name">First Name <span class="text-red-500">*</span></label>
                        <input type="text" id="first_name" name="first_name" required class="form-control">
                    </div>
                    
                    <div class="form-group">
                        <label for="last_name">Last Name <span class="text-red-500">*</span></label>
                        <input type="text" id="last_name" name="last_name" required class="form-control">
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="stripe_customer_id">Stripe Customer ID (Optional)</label>
                    <input type="text" id="stripe_customer_id" name="stripe_customer_id" class="form-control">
                    <small class="form-text">If the customer already exists in Stripe, enter their ID here</small>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Create Customer</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Import CSV Modal -->
<div id="import-customers-modal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title">Import Customers from CSV</h3>
            <button class="modal-close">&times;</button>
        </div>
        <div class="modal-body">
            <form action="/admin/customers.php" method="POST" enctype="multipart/form-data" id="import-customers-form">
                <input type="hidden" name="action" value="import_customers">
                
                <div class="form-group">
                    <label for="csv_file">CSV File <span class="text-red-500">*</span></label>
                    <input type="file" id="csv_file" name="csv_file" required class="form-control" accept=".csv">
                    <small class="form-text">CSV should have columns: email, first_name, last_name</small>
                </div>
                
                <div class="form-group">
                    <div class="bg-blue-50 border-l-4 border-blue-400 p-4 mb-4">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <i class="fas fa-info-circle text-blue-400"></i>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm text-blue-700">
                                    Imported customers will receive an email with a link to set up their payment method via Stripe Checkout.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Import Customers</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    // Modal functionality
    document.addEventListener('DOMContentLoaded', function() {
        // Create customer modal
        const createCustomerBtn = document.getElementById('create-customer-btn');
        const createCustomerModal = document.getElementById('create-customer-modal');
        const createCustomerClose = createCustomerModal.querySelector('.modal-close');
        
        createCustomerBtn.addEventListener('click', function() {
            createCustomerModal.classList.add('show');
        });
        
        createCustomerClose.addEventListener('click', function() {
            createCustomerModal.classList.remove('show');
        });
        
        // Import customers modal
        const importCustomersBtn = document.getElementById('import-customers-btn');
        const importCustomersModal = document.getElementById('import-customers-modal');
        const importCustomersClose = importCustomersModal.querySelector('.modal-close');
        
        importCustomersBtn.addEventListener('click', function() {
            importCustomersModal.classList.add('show');
        });
        
        importCustomersClose.addEventListener('click', function() {
            importCustomersModal.classList.remove('show');
        });
        
        // Close modals when clicking outside
        window.addEventListener('click', function(event) {
            if (event.target === createCustomerModal) {
                createCustomerModal.classList.remove('show');
            }
            
            if (event.target === importCustomersModal) {
                importCustomersModal.classList.remove('show');
            }
        });
    });
</script>

<?php require_once '../../template/admin-footer.php'; ?>

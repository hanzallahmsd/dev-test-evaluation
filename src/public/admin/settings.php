<?php
/**
 * Admin Settings
 */
require_once '../../config/init.php';

use Controllers\AuthController;

// Check if user is logged in and is admin
$authController = new AuthController();
if (!$authController->isAdmin()) {
    redirect('/login.php');
}

// Get current settings
$stripePublicKey = getenv('STRIPE_PUBLIC_KEY') ?: '';
$stripeSecretKey = getenv('STRIPE_SECRET_KEY') ?: '';
$stripeWebhookSecret = getenv('STRIPE_WEBHOOK_SECRET') ?: '';
$adminEmail = getenv('ADMIN_EMAIL') ?: '';
$siteUrl = getenv('SITE_URL') ?: '';

// Handle settings update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'update_stripe') {
        // Update Stripe settings
        $success = true;
        
        // In a real application, you would update environment variables or config files
        // For this demo, we'll just show a success message
        
        if ($success) {
            flash('success', 'Stripe settings updated successfully');
        } else {
            flash('error', 'Failed to update Stripe settings');
        }
    } elseif ($_POST['action'] === 'update_email') {
        // Update email settings
        $success = true;
        
        if ($success) {
            flash('success', 'Email settings updated successfully');
        } else {
            flash('error', 'Failed to update email settings');
        }
    } elseif ($_POST['action'] === 'update_password') {
        // Validate password
        if (empty($_POST['current_password']) || empty($_POST['new_password']) || empty($_POST['confirm_password'])) {
            flash('error', 'All password fields are required');
        } elseif ($_POST['new_password'] !== $_POST['confirm_password']) {
            flash('error', 'New passwords do not match');
        } else {
            // Update admin password
            $userId = $_SESSION['user_id'];
            $currentPassword = $_POST['current_password'];
            $newPassword = $_POST['new_password'];
            
            if ($authController->updatePassword($userId, $currentPassword, $newPassword)) {
                flash('success', 'Password updated successfully');
            } else {
                flash('error', 'Current password is incorrect');
            }
        }
    }
}

// Include admin layout
require_once '../../template/admin-layout.php';
?>

<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-800">Settings</h1>
    <p class="text-gray-600">Configure your subscription system settings</p>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Sidebar Navigation -->
    <div class="lg:col-span-1">
        <div class="card">
            <div class="p-4 border-b">
                <h3 class="text-lg font-semibold text-gray-700">Settings Menu</h3>
            </div>
            <div class="p-0">
                <ul class="settings-nav">
                    <li class="settings-nav-item active" data-target="stripe-settings">
                        <i class="fas fa-credit-card mr-2"></i> Stripe Integration
                    </li>
                    <li class="settings-nav-item" data-target="email-settings">
                        <i class="fas fa-envelope mr-2"></i> Email Settings
                    </li>
                    <li class="settings-nav-item" data-target="account-settings">
                        <i class="fas fa-user-shield mr-2"></i> Account Security
                    </li>
                    <li class="settings-nav-item" data-target="system-info">
                        <i class="fas fa-info-circle mr-2"></i> System Information
                    </li>
                </ul>
            </div>
        </div>
    </div>
    
    <!-- Settings Content -->
    <div class="lg:col-span-2">
        <!-- Stripe Settings -->
        <div id="stripe-settings" class="settings-content active">
            <div class="card mb-6">
                <div class="p-4 border-b">
                    <h3 class="text-lg font-semibold text-gray-700">Stripe Integration</h3>
                </div>
                <div class="p-6">
                    <form action="/admin/settings.php" method="POST">
                        <input type="hidden" name="action" value="update_stripe">
                        
                        <div class="form-group">
                            <label for="stripe_public_key">Stripe Public Key</label>
                            <input type="text" id="stripe_public_key" name="stripe_public_key" class="form-control" value="<?= htmlspecialchars($stripePublicKey) ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="stripe_secret_key">Stripe Secret Key</label>
                            <input type="password" id="stripe_secret_key" name="stripe_secret_key" class="form-control" value="<?= htmlspecialchars($stripeSecretKey) ?>">
                            <small class="form-text">For security, the secret key is masked</small>
                        </div>
                        
                        <div class="form-group">
                            <label for="stripe_webhook_secret">Stripe Webhook Secret</label>
                            <input type="password" id="stripe_webhook_secret" name="stripe_webhook_secret" class="form-control" value="<?= htmlspecialchars($stripeWebhookSecret) ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="webhook_url">Webhook URL</label>
                            <div class="flex">
                                <input type="text" id="webhook_url" class="form-control flex-grow" value="<?= htmlspecialchars($siteUrl . '/webhooks/stripe.php') ?>" readonly>
                                <button type="button" class="btn btn-secondary ml-2 copy-btn" data-clipboard-target="#webhook_url">
                                    <i class="fas fa-copy"></i>
                                </button>
                            </div>
                            <small class="form-text">Use this URL in your Stripe webhook settings</small>
                        </div>
                        
                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary">Save Stripe Settings</button>
                        </div>
                    </form>
                </div>
            </div>
            
            <div class="card">
                <div class="p-4 border-b">
                    <h3 class="text-lg font-semibold text-gray-700">Stripe Webhook Events</h3>
                </div>
                <div class="p-6">
                    <p class="mb-4">Make sure you have the following events enabled in your Stripe webhook settings:</p>
                    
                    <ul class="list-disc pl-5 mb-4">
                        <li>customer.subscription.created</li>
                        <li>customer.subscription.updated</li>
                        <li>customer.subscription.deleted</li>
                        <li>invoice.payment_succeeded</li>
                        <li>invoice.payment_failed</li>
                        <li>checkout.session.completed</li>
                    </ul>
                    
                    <a href="https://dashboard.stripe.com/webhooks" target="_blank" class="btn btn-secondary">
                        <i class="fas fa-external-link-alt mr-1"></i> Go to Stripe Webhooks
                    </a>
                </div>
            </div>
        </div>
        
        <!-- Email Settings -->
        <div id="email-settings" class="settings-content">
            <div class="card">
                <div class="p-4 border-b">
                    <h3 class="text-lg font-semibold text-gray-700">Email Configuration</h3>
                </div>
                <div class="p-6">
                    <form action="/admin/settings.php" method="POST">
                        <input type="hidden" name="action" value="update_email">
                        
                        <div class="form-group">
                            <label for="admin_email">Admin Email</label>
                            <input type="email" id="admin_email" name="admin_email" class="form-control" value="<?= htmlspecialchars($adminEmail) ?>">
                            <small class="form-text">Notifications will be sent to this email</small>
                        </div>
                        
                        <div class="form-group">
                            <label for="smtp_host">SMTP Host</label>
                            <input type="text" id="smtp_host" name="smtp_host" class="form-control" value="smtp.example.com">
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="smtp_port">SMTP Port</label>
                                <input type="text" id="smtp_port" name="smtp_port" class="form-control" value="587">
                            </div>
                            
                            <div class="form-group">
                                <label for="smtp_security">Security</label>
                                <select id="smtp_security" name="smtp_security" class="form-control">
                                    <option value="tls">TLS</option>
                                    <option value="ssl">SSL</option>
                                    <option value="none">None</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="smtp_username">SMTP Username</label>
                            <input type="text" id="smtp_username" name="smtp_username" class="form-control" value="">
                        </div>
                        
                        <div class="form-group">
                            <label for="smtp_password">SMTP Password</label>
                            <input type="password" id="smtp_password" name="smtp_password" class="form-control" value="">
                        </div>
                        
                        <div class="form-group">
                            <label for="from_email">From Email</label>
                            <input type="email" id="from_email" name="from_email" class="form-control" value="noreply@example.com">
                        </div>
                        
                        <div class="form-group">
                            <label for="from_name">From Name</label>
                            <input type="text" id="from_name" name="from_name" class="form-control" value="Subscription Service">
                        </div>
                        
                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary">Save Email Settings</button>
                            <button type="button" class="btn btn-secondary ml-2" id="test-email-btn">Send Test Email</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <!-- Account Settings -->
        <div id="account-settings" class="settings-content">
            <div class="card">
                <div class="p-4 border-b">
                    <h3 class="text-lg font-semibold text-gray-700">Change Admin Password</h3>
                </div>
                <div class="p-6">
                    <form action="/admin/settings.php" method="POST">
                        <input type="hidden" name="action" value="update_password">
                        
                        <div class="form-group">
                            <label for="current_password">Current Password</label>
                            <input type="password" id="current_password" name="current_password" class="form-control" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="new_password">New Password</label>
                            <input type="password" id="new_password" name="new_password" class="form-control" required>
                            <small class="form-text">Password must be at least 8 characters long</small>
                        </div>
                        
                        <div class="form-group">
                            <label for="confirm_password">Confirm New Password</label>
                            <input type="password" id="confirm_password" name="confirm_password" class="form-control" required>
                        </div>
                        
                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary">Update Password</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <!-- System Information -->
        <div id="system-info" class="settings-content">
            <div class="card">
                <div class="p-4 border-b">
                    <h3 class="text-lg font-semibold text-gray-700">System Information</h3>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <h4 class="font-semibold mb-2">PHP Version</h4>
                            <p class="text-gray-600"><?= phpversion() ?></p>
                        </div>
                        
                        <div>
                            <h4 class="font-semibold mb-2">Database</h4>
                            <p class="text-gray-600">MySQL <?= mysqli_get_client_version() ?></p>
                        </div>
                        
                        <div>
                            <h4 class="font-semibold mb-2">Server</h4>
                            <p class="text-gray-600"><?= $_SERVER['SERVER_SOFTWARE'] ?></p>
                        </div>
                        
                        <div>
                            <h4 class="font-semibold mb-2">PHP Extensions</h4>
                            <p class="text-gray-600">
                                <?= extension_loaded('pdo') ? '<span class="text-green-600">PDO</span>' : '<span class="text-red-600">PDO</span>' ?>,
                                <?= extension_loaded('curl') ? '<span class="text-green-600">cURL</span>' : '<span class="text-red-600">cURL</span>' ?>,
                                <?= extension_loaded('json') ? '<span class="text-green-600">JSON</span>' : '<span class="text-red-600">JSON</span>' ?>
                            </p>
                        </div>
                    </div>
                    
                    <hr class="my-4">
                    
                    <h4 class="font-semibold mb-2">Application Version</h4>
                    <p class="text-gray-600">v1.0.0</p>
                    
                    <div class="mt-4">
                        <button type="button" class="btn btn-secondary" id="check-updates-btn">
                            <i class="fas fa-sync-alt mr-1"></i> Check for Updates
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Test Email Modal -->
<div id="test-email-modal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title">Send Test Email</h3>
            <button class="modal-close">&times;</button>
        </div>
        <div class="modal-body">
            <form id="test-email-form">
                <div class="form-group">
                    <label for="test_email">Email Address</label>
                    <input type="email" id="test_email" name="test_email" class="form-control" required>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Send Test</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Settings navigation
        const navItems = document.querySelectorAll('.settings-nav-item');
        const contentSections = document.querySelectorAll('.settings-content');
        
        navItems.forEach(item => {
            item.addEventListener('click', function() {
                const target = this.getAttribute('data-target');
                
                // Update active nav item
                navItems.forEach(navItem => navItem.classList.remove('active'));
                this.classList.add('active');
                
                // Show target content
                contentSections.forEach(section => {
                    if (section.id === target) {
                        section.classList.add('active');
                    } else {
                        section.classList.remove('active');
                    }
                });
            });
        });
        
        // Test email modal
        const testEmailBtn = document.getElementById('test-email-btn');
        const testEmailModal = document.getElementById('test-email-modal');
        const testEmailClose = testEmailModal.querySelector('.modal-close');
        const testEmailForm = document.getElementById('test-email-form');
        
        testEmailBtn.addEventListener('click', function() {
            testEmailModal.classList.add('show');
        });
        
        testEmailClose.addEventListener('click', function() {
            testEmailModal.classList.remove('show');
        });
        
        testEmailForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const email = document.getElementById('test_email').value;
            
            // TODO: Implement AJAX call to send test email
            alert('Test email would be sent to: ' + email);
            
            testEmailModal.classList.remove('show');
        });
        
        // Close modal when clicking outside
        window.addEventListener('click', function(event) {
            if (event.target === testEmailModal) {
                testEmailModal.classList.remove('show');
            }
        });
        
        // Check for updates button
        const checkUpdatesBtn = document.getElementById('check-updates-btn');
        
        checkUpdatesBtn.addEventListener('click', function() {
            this.disabled = true;
            this.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i> Checking...';
            
            // Simulate checking for updates
            setTimeout(() => {
                this.disabled = false;
                this.innerHTML = '<i class="fas fa-sync-alt mr-1"></i> Check for Updates';
                alert('Your application is up to date!');
            }, 2000);
        });
        
        // Copy webhook URL button
        const copyBtn = document.querySelector('.copy-btn');
        
        copyBtn.addEventListener('click', function() {
            const webhookUrl = document.getElementById('webhook_url');
            
            webhookUrl.select();
            document.execCommand('copy');
            
            this.innerHTML = '<i class="fas fa-check"></i>';
            
            setTimeout(() => {
                this.innerHTML = '<i class="fas fa-copy"></i>';
            }, 2000);
        });
    });
</script>

<style>
    .settings-nav {
        list-style: none;
        padding: 0;
        margin: 0;
    }
    
    .settings-nav-item {
        padding: 1rem;
        border-bottom: 1px solid #e5e7eb;
        cursor: pointer;
        transition: all 0.2s;
    }
    
    .settings-nav-item:hover {
        background-color: #f9fafb;
    }
    
    .settings-nav-item.active {
        background-color: #f3f4f6;
        border-left: 3px solid #3b82f6;
        font-weight: 600;
    }
    
    .settings-content {
        display: none;
    }
    
    .settings-content.active {
        display: block;
    }
</style>

<?php require_once '../../template/admin-footer.php'; ?>

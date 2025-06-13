<?php
/**
 * Header template
 */
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= config('app.name') ?></title>
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/style.css">
    
    <?php if (isset($additionalCss)) echo $additionalCss; ?>
    
    <!-- Poppins Font -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>

<!-- Preloader -->
<div class="preloader">
    <div class="loader">
        <div class="spinner"></div>
    </div>
</div>

<?php if (isset($_SESSION['user_id'])): ?>
    <!-- Logged in navigation -->
    <nav class="navbar" id="navbar">
        <div class="navbar-container">
            <div class="navbar-brand">
                <a href="/" class="navbar-logo">
                    <?= config('app.name') ?>
                </a>
            </div>
            
            <div class="navbar-menu" id="navbar-menu">
                <a href="/" class="navbar-link">Home</a>
                
                <?php if ($_SESSION['user_role'] === 'admin'): ?>
                    <a href="/admin/dashboard.php" class="navbar-link">Dashboard</a>
                    <a href="/admin/customers.php" class="navbar-link">Customers</a>
                    <a href="/admin/subscriptions.php" class="navbar-link">Subscriptions</a>
                    <a href="/admin/invoices.php" class="navbar-link">Invoices</a>
                    <a href="/admin/reports.php" class="navbar-link">Reports</a>
                <?php else: ?>
                    <a href="/account.php" class="navbar-link">My Account</a>
                    <a href="/invoices.php" class="navbar-link">Invoices</a>
                <?php endif; ?>
            </div>
            
            <div class="navbar-actions">
                <span class="navbar-welcome">
                    Welcome, <?= htmlspecialchars($_SESSION['user_name']) ?>
                </span>
                
                <a href="/logout.php" class="btn btn-outline">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </div>
            
            <!-- Mobile menu button -->
            <button class="navbar-toggle" id="navbar-toggle">
                <i class="fas fa-bars"></i>
            </button>
        </div>
    </nav>
<?php else: ?>
    <!-- Public navigation -->
    <nav class="navbar" id="navbar">
        <div class="navbar-container">
            <div class="navbar-brand">
                <a href="/" class="navbar-logo">
                    <?= config('app.name') ?>
                </a>
            </div>
            
            <div class="navbar-menu" id="navbar-menu">
                <a href="#features" class="navbar-link scroll-link">Features</a>
                <a href="#pricing" class="navbar-link scroll-link">Pricing</a>
                <a href="#contact" class="navbar-link scroll-link">Contact</a>
            </div>
            
            <div class="navbar-actions">
                <a href="#" class="btn btn-outline open-login-modal">
                    <i class="fas fa-sign-in-alt"></i> Login
                </a>
                <a href="#" class="btn btn-primary open-register-modal">
                    <i class="fas fa-rocket"></i> Get Started
                </a>
            </div>
            
            <!-- Mobile menu button -->
            <button class="navbar-toggle" id="navbar-toggle">
                <i class="fas fa-bars"></i>
            </button>
        </div>
    </nav>
<?php endif; ?>

<!-- Flash messages -->
<?php if (isset($_SESSION['flash'])): ?>
    <div class="flash-messages">
        <?php foreach ($_SESSION['flash'] as $type => $message): ?>
            <?php
            $flashClass = 'flash-info';
            $icon = 'fa-info-circle';
            
            if ($type === 'success') {
                $flashClass = 'flash-success';
                $icon = 'fa-check-circle';
            } elseif ($type === 'error') {
                $flashClass = 'flash-error';
                $icon = 'fa-exclamation-circle';
            } elseif ($type === 'warning') {
                $flashClass = 'flash-warning';
                $icon = 'fa-exclamation-triangle';
            }
            ?>
            
            <div class="flash-message <?= $flashClass ?>" role="alert">
                <div class="flash-content">
                    <i class="fas <?= $icon ?>"></i>
                    <p><?= htmlspecialchars($message) ?></p>
                </div>
                <button class="flash-close" onclick="this.parentElement.remove()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <?php unset($_SESSION['flash'][$type]); ?>
        <?php endforeach; ?>
    </div>
    
    <script>
    // Auto-dismiss flash messages after 5 seconds
    document.addEventListener('DOMContentLoaded', function() {
        const flashMessages = document.querySelectorAll('.flash-message');
        if (flashMessages.length > 0) {
            setTimeout(function() {
                flashMessages.forEach(function(message) {
                    message.style.opacity = '0';
                    setTimeout(function() {
                        if (message.parentNode) {
                            message.parentNode.removeChild(message);
                        }
                    }, 300); // Wait for fade out animation
                });
            }, 5000); // 5 seconds
        }
    });
    </script>
<?php endif; ?>

<main>
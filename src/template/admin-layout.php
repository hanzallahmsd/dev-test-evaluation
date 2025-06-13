<?php
/**
 * Admin layout template
 */

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    // Redirect to login page
    header('Location: /login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - <?= config('app.name') ?></title>
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="/assets/css/style.css">
    <link rel="stylesheet" href="/assets/css/admin-style.css">
    
    <!-- Inter Font -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Chart.js for analytics -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <!-- No additional styles needed as they are in style.css -->
</head>
<body class="admin-panel bg-gray-50 text-gray-800">

<div class="flex min-h-screen">
    <!-- Sidebar -->
    <aside class="w-64 bg-white shadow-md hidden md:block">
        <div class="p-4 border-b">
            <a href="/admin/dashboard.php" class="text-xl font-bold text-primary">
                <?= config('app.name') ?>
            </a>
            <p class="text-xs text-gray-500 mt-1">Admin Panel</p>
        </div>
        
        <nav class="p-4 space-y-1">
            <?php
            $current_page = basename($_SERVER['PHP_SELF']);
            $menu_items = [
                'dashboard.php' => ['icon' => 'fa-tachometer-alt', 'text' => 'Dashboard'],
                'customers.php' => ['icon' => 'fa-users', 'text' => 'Customers'],
                'subscriptions.php' => ['icon' => 'fa-credit-card', 'text' => 'Subscriptions'],
                'invoices.php' => ['icon' => 'fa-file-invoice-dollar', 'text' => 'Invoices'],
                'reports.php' => ['icon' => 'fa-chart-bar', 'text' => 'Reports'],
                'settings.php' => ['icon' => 'fa-cog', 'text' => 'Settings'],
            ];
            
            foreach ($menu_items as $page => $item) {
                $is_active = $current_page === $page;
                $active_class = $is_active ? 'active' : '';
                echo '<a href="/admin/' . $page . '" class="sidebar-link ' . $active_class . '">';
                echo '<i class="fas ' . $item['icon'] . ' w-5"></i>';
                echo '<span>' . $item['text'] . '</span>';
                echo '</a>';
            }
            ?>
            
            <div class="border-t my-4 pt-4">
                <a href="/" class="sidebar-link">
                    <i class="fas fa-globe w-5"></i>
                    <span>View Website</span>
                </a>
                <a href="/logout.php" class="sidebar-link text-red-600 hover:text-red-700">
                    <i class="fas fa-sign-out-alt w-5"></i>
                    <span>Logout</span>
                </a>
            </div>
        </nav>
    </aside>
    
    <!-- Main Content -->
    <div class="flex-1 flex flex-col overflow-hidden">
        <!-- Top Navigation -->
        <header class="bg-white shadow-sm">
            <div class="flex justify-between items-center py-4 px-6">
                <div class="flex items-center md:hidden">
                    <button id="mobile-menu-button" class="text-gray-600 hover:text-primary focus:outline-none">
                        <i class="fas fa-bars text-xl"></i>
                    </button>
                </div>
                
                <div class="flex items-center">
                    <div class="relative">
                        <button id="user-menu-button" class="flex items-center space-x-2 focus:outline-none">
                            <div class="w-8 h-8 rounded-full bg-primary text-white flex items-center justify-center">
                                <span class="text-sm font-bold"><?= substr($_SESSION['user_name'], 0, 1) ?></span>
                            </div>
                            <span class="hidden md:inline-block text-sm"><?= htmlspecialchars($_SESSION['user_name']) ?></span>
                            <i class="fas fa-chevron-down text-xs"></i>
                        </button>
                        
                        <div id="user-menu" class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 hidden z-10">
                            <a href="/admin/profile.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                <i class="fas fa-user mr-2"></i> Profile
                            </a>
                            <a href="/admin/settings.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                <i class="fas fa-cog mr-2"></i> Settings
                            </a>
                            <div class="border-t border-gray-100 my-1"></div>
                            <a href="/logout.php" class="block px-4 py-2 text-sm text-red-600 hover:bg-gray-100">
                                <i class="fas fa-sign-out-alt mr-2"></i> Logout
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </header>
        
        <!-- Mobile Sidebar -->
        <div id="mobile-sidebar" class="fixed inset-0 z-20 transition-opacity bg-black bg-opacity-50 hidden">
            <div class="absolute inset-y-0 left-0 max-w-xs w-full bg-white shadow-xl">
                <div class="flex justify-between items-center p-4 border-b">
                    <a href="/admin/dashboard.php" class="text-xl font-bold text-primary">
                        <?= config('app.name') ?>
                    </a>
                    <button id="close-sidebar-button" class="text-gray-600 hover:text-primary focus:outline-none">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
                
                <nav class="p-4 space-y-1">
                    <?php
                    foreach ($menu_items as $page => $item) {
                        $is_active = $current_page === $page;
                        $active_class = $is_active ? 'active' : '';
                        echo '<a href="/admin/' . $page . '" class="sidebar-link ' . $active_class . '">';
                        echo '<i class="fas ' . $item['icon'] . ' w-5"></i>';
                        echo '<span>' . $item['text'] . '</span>';
                        echo '</a>';
                    }
                    ?>
                    
                    <div class="border-t my-4 pt-4">
                        <a href="/" class="sidebar-link">
                            <i class="fas fa-globe w-5"></i>
                            <span>View Website</span>
                        </a>
                        <a href="/logout.php" class="sidebar-link text-red-600 hover:text-red-700">
                            <i class="fas fa-sign-out-alt w-5"></i>
                            <span>Logout</span>
                        </a>
                    </div>
                </nav>
            </div>
        </div>
        
        <!-- Flash messages -->
        <?php if (isset($_SESSION['flash'])): ?>
            <div class="p-4">
                <?php foreach ($_SESSION['flash'] as $type => $message): ?>
                    <?php
                    $bgColor = 'bg-blue-100 border-blue-500 text-blue-700';
                    $icon = 'fa-info-circle';
                    
                    if ($type === 'success') {
                        $bgColor = 'bg-green-100 border-green-500 text-green-700';
                        $icon = 'fa-check-circle';
                    } elseif ($type === 'error') {
                        $bgColor = 'bg-red-100 border-red-500 text-red-700';
                        $icon = 'fa-exclamation-circle';
                    } elseif ($type === 'warning') {
                        $bgColor = 'bg-yellow-100 border-yellow-500 text-yellow-700';
                        $icon = 'fa-exclamation-triangle';
                    }
                    ?>
                    
                    <div class="<?= $bgColor ?> border-l-4 p-4 mb-4 rounded" role="alert">
                        <div class="flex items-center">
                            <i class="fas <?= $icon ?> mr-2"></i>
                            <p><?= htmlspecialchars($message) ?></p>
                        </div>
                    </div>
                    <?php unset($_SESSION['flash'][$type]); ?>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        
        <!-- Page Content -->
        <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-50 p-6">
            <!-- Page content will be inserted here -->

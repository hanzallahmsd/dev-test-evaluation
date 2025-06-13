<?php
/**
 * Authentication pages header template (without navbar)
 */
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= config('app.name') ?> - <?= $pageTitle ?? 'Authentication' ?></title>
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/style.css">
    
    <!-- Poppins Font -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="auth-body">

<!-- Preloader -->
<div class="preloader">
    <div class="loader">
        <div class="spinner"></div>
    </div>
</div>

<?php if (isset($_SESSION['flash'])): ?>
    <div class="flash-messages">
        <?php foreach ($_SESSION['flash'] as $type => $message): ?>
            <?php
            $flashClass = 'flash-info';
            if ($type === 'success') {
                $flashClass = 'flash-success';
                $flashIcon = 'fa-check-circle';
            } elseif ($type === 'error') {
                $flashClass = 'flash-error';
                $flashIcon = 'fa-exclamation-circle';
            } elseif ($type === 'warning') {
                $flashClass = 'flash-warning';
                $flashIcon = 'fa-exclamation-triangle';
            } else {
                $flashIcon = 'fa-info-circle';
            }
            ?>
            <div class="flash-message <?= $flashClass ?>">
                <div class="flash-content">
                    <i class="fas <?= $flashIcon ?>"></i>
                    <p><?= $message ?></p>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

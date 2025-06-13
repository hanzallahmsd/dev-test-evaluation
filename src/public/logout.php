<?php
/**
 * Logout page
 */

require_once '../config/init.php';

use Controllers\AuthController;

$authController = new AuthController();
$authController->logout();

// Redirect to home page
redirect('/');
?>

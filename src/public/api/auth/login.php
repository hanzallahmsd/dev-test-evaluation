<?php
/**
 * API endpoint for login
 */
require_once '../../../bootstrap.php';

// Set content type to JSON
header('Content-Type: application/json');

// Initialize auth controller
$authController = new \Controllers\AuthController();

// Check if request method is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method'
    ]);
    exit;
}

// Get login data
$email = $_POST['email'] ?? '';
$password = $_POST['password'] ?? '';
$remember = isset($_POST['remember']) ? true : false;

// Validate login
if (empty($email) || empty($password)) {
    echo json_encode([
        'success' => false,
        'message' => 'Please enter both email and password'
    ]);
    exit;
}

// Attempt login
$user = $authController->login($email, $password, $remember);

if ($user) {
    // Always redirect to home page after login
    $redirect = '/';
    
    echo json_encode([
        'success' => true,
        'message' => 'Login successful',
        'redirect' => $redirect
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid email or password'
    ]);
}

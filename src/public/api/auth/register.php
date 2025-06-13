<?php
/**
 * API endpoint for registration
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

// Get registration data
$data = [
    'email' => $_POST['email'] ?? '',
    'password' => $_POST['password'] ?? '',
    'first_name' => $_POST['first_name'] ?? '',
    'last_name' => $_POST['last_name'] ?? ''
];

// Validate data
if (empty($data['email']) || empty($data['password']) || empty($data['first_name']) || empty($data['last_name'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Please fill in all required fields'
    ]);
    exit;
}

// Attempt registration
$userId = $authController->register($data);

if ($userId) {
    // Don't log in automatically - just show success message and return to login form
    echo json_encode([
        'success' => true,
        'message' => 'Registration successful! Please log in.',
        'showLogin' => true
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Registration failed. Email may already be in use.'
    ]);
}

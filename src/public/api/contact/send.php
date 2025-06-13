<?php
/**
 * API endpoint for contact form submission
 */
require_once '../../../bootstrap.php';

// Set content type to JSON
header('Content-Type: application/json');

// Initialize contact controller
$contactController = new \Controllers\ContactController();

// Check if request method is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method'
    ]);
    exit;
}

// Get form data
$data = [
    'name' => $_POST['name'] ?? '',
    'email' => $_POST['email'] ?? '',
    'subject' => $_POST['subject'] ?? '',
    'message' => $_POST['message'] ?? ''
];

// Validate CSRF token
if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid security token. Please refresh the page and try again.'
    ]);
    exit;
}

// Validate data
if (empty($data['name']) || empty($data['email']) || empty($data['subject']) || empty($data['message'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Please fill in all required fields'
    ]);
    exit;
}

// Validate email
if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
    echo json_encode([
        'success' => false,
        'message' => 'Please enter a valid email address'
    ]);
    exit;
}

// Send message
$result = $contactController->sendMessage($data);

if ($result) {
    echo json_encode([
        'success' => true,
        'message' => 'Your message has been sent successfully! We will get back to you soon.'
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'There was a problem sending your message. Please try again later.'
    ]);
}

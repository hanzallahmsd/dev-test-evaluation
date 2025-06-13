<?php
/**
 * Subscription purchase page
 */
require_once '../bootstrap.php';

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    flash('error', 'Please log in to subscribe to a plan');
    redirect('/');
}

// Check if price ID is provided
if (!isset($_GET['price_id']) || empty($_GET['price_id'])) {
    flash('error', 'Invalid plan selected');
    redirect('/');
}

$priceId = $_GET['price_id'];

// Get user data
$userModel = new \Models\User();
$user = $userModel->find($_SESSION['user_id']);

if (!$user) {
    flash('error', 'User not found');
    redirect('/');
}

// Create subscription controller
$subscriptionController = new \Controllers\SubscriptionController();

// Set success and cancel URLs
$successUrl = config('app.url') . '/subscription-success.php';
$cancelUrl = config('app.url') . '/subscription-cancel.php';

// Create checkout session
$checkoutUrl = $subscriptionController->createCheckoutSession(
    $priceId,
    $user['id'],
    $successUrl,
    $cancelUrl
);

if ($checkoutUrl) {
    // Redirect to Stripe checkout
    header('Location: ' . $checkoutUrl);
    exit;
} else {
    // Error creating checkout session
    flash('error', 'Error creating checkout session. Please try again.');
    redirect('/');
}
?>

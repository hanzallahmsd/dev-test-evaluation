<?php
/**
 * Stripe webhook handler
 * 
 * This file handles webhook events from Stripe to keep our database in sync
 * with subscription status changes, invoice payments, etc.
 */
require_once '../../../bootstrap.php';

// Get the raw POST data
$payload = file_get_contents('php://input');
$sigHeader = $_SERVER['HTTP_STRIPE_SIGNATURE'] ?? '';

// Initialize Stripe service
$stripeService = new \Services\StripeService();
$subscriptionController = new \Controllers\SubscriptionController();

try {
    // Verify the webhook signature
    $event = $stripeService->verifyWebhookSignature($payload, $sigHeader);
    
    // Handle the event
    $result = $subscriptionController->handleWebhookEvent($event);
    
    if ($result) {
        http_response_code(200);
        echo json_encode(['status' => 'success']);
    } else {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Failed to process webhook']);
    }
} catch (\Exception $e) {
    http_response_code(400);
    error_log('Webhook error: ' . $e->getMessage());
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>

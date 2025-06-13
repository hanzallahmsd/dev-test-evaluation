<?php
/**
 * Pay invoice page
 */
require_once '../bootstrap.php';

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    redirect('/');
}

// Check if invoice ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    flash('error', 'Invalid invoice ID');
    redirect('/invoices.php');
}

$invoiceId = (int)$_GET['id'];

// Get invoice data
$invoiceModel = new \Models\Invoice();
$invoice = $invoiceModel->find($invoiceId);

// Check if invoice exists, belongs to the current user, and is unpaid
if (!$invoice || $invoice['user_id'] != $_SESSION['user_id'] || $invoice['status'] !== 'open') {
    flash('error', 'Invoice not found, already paid, or you do not have permission to pay it');
    redirect('/invoices.php');
}

// Get user data
$userModel = new \Models\User();
$user = $userModel->find($_SESSION['user_id']);

// Initialize Stripe service
$stripeService = new \Services\StripeService();

// Get Stripe invoice data
$stripeInvoice = $stripeService->getInvoice($invoice['stripe_invoice_id']);

// Check if Stripe has a hosted invoice URL
if ($stripeInvoice && isset($stripeInvoice->hosted_invoice_url)) {
    // Redirect to Stripe's hosted invoice page for payment
    header('Location: ' . $stripeInvoice->hosted_invoice_url);
    exit;
} else {
    // If no hosted invoice URL is available, redirect back with a message
    flash('error', 'Payment link is not available for this invoice. Please contact support.');
    redirect('/invoice.php?id=' . $invoiceId);
}
?>

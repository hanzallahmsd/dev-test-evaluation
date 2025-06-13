<?php
/**
 * Download invoice as PDF
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

// Check if invoice exists and belongs to the current user
if (!$invoice || $invoice['user_id'] != $_SESSION['user_id']) {
    flash('error', 'Invoice not found or you do not have permission to view it');
    redirect('/invoices.php');
}

// Get subscription data
$subscriptionModel = new \Models\Subscription();
$subscription = $subscriptionModel->find($invoice['subscription_id']);

// Get user data
$userModel = new \Models\User();
$user = $userModel->find($_SESSION['user_id']);

// Get Stripe invoice data for PDF generation
$stripeService = new \Services\StripeService();
$stripeInvoice = $stripeService->getInvoice($invoice['stripe_invoice_id']);

// Check if Stripe has a hosted invoice URL
if ($stripeInvoice && isset($stripeInvoice->hosted_invoice_url)) {
    // Redirect to Stripe's hosted invoice page
    header('Location: ' . $stripeInvoice->hosted_invoice_url);
    exit;
} else {
    // If no hosted invoice URL is available, we would need to generate our own PDF
    // This would typically require a PDF generation library like FPDF or TCPDF
    // For this example, we'll just redirect back with a message
    flash('error', 'PDF download is not available for this invoice. Please contact support.');
    redirect('/invoice.php?id=' . $invoiceId);
}
?>

<?php
namespace Services;

use Stripe\Stripe;
use Stripe\Customer;
use Stripe\Checkout\Session;
use Stripe\Subscription;
use Stripe\Invoice;
use Stripe\Webhook;
use Stripe\Exception\SignatureVerificationException;

class StripeService
{
    private $secretKey;
    private $publishableKey;
    private $webhookSecret;
    private $mode;
    
    public function __construct()
    {
        $this->secretKey = config('stripe.secret_key');
        $this->publishableKey = config('stripe.publishable_key');
        $this->webhookSecret = config('stripe.webhook_secret');
        $this->mode = config('stripe.mode');
        
        Stripe::setApiKey($this->secretKey);
    }
    
    /**
     * Get Stripe publishable key
     * 
     * @return string
     */
    public function getPublishableKey()
    {
        return $this->publishableKey;
    }
    
    /**
     * Create a Stripe customer
     * 
     * @param string $email
     * @param string $name
     * @return \Stripe\Customer
     */
    public function createCustomer($email, $name)
    {
        return Customer::create([
            'email' => $email,
            'name' => $name,
        ]);
    }
    
    /**
     * Create a checkout session for subscription
     * 
     * @param string $priceId
     * @param string $customerId
     * @param string $successUrl
     * @param string $cancelUrl
     * @return \Stripe\Checkout\Session
     */
    public function createCheckoutSession($priceId, $customerId, $successUrl, $cancelUrl)
    {
        return Session::create([
            'payment_method_types' => ['card'],
            'line_items' => [[
                'price' => $priceId,
                'quantity' => 1,
            ]],
            'mode' => 'subscription',
            'success_url' => $successUrl,
            'cancel_url' => $cancelUrl,
            'customer' => $customerId,
        ]);
    }
    
    /**
     * Create a checkout session for subscription without existing customer
     * 
     * @param string $priceId
     * @param string $email
     * @param string $successUrl
     * @param string $cancelUrl
     * @return \Stripe\Checkout\Session
     */
    public function createCheckoutSessionWithoutCustomer($priceId, $email, $successUrl, $cancelUrl)
    {
        return Session::create([
            'payment_method_types' => ['card'],
            'line_items' => [[
                'price' => $priceId,
                'quantity' => 1,
            ]],
            'mode' => 'subscription',
            'success_url' => $successUrl,
            'cancel_url' => $cancelUrl,
            'customer_email' => $email,
        ]);
    }
    
    /**
     * Create a customer portal session
     * 
     * @param string $customerId
     * @param string $returnUrl
     * @return \Stripe\BillingPortal\Session
     */
    public function createCustomerPortalSession($customerId, $returnUrl)
    {
        return \Stripe\BillingPortal\Session::create([
            'customer' => $customerId,
            'return_url' => $returnUrl,
        ]);
    }
    
    /**
     * Get subscription details
     * 
     * @param string $subscriptionId
     * @return \Stripe\Subscription
     */
    public function getSubscription($subscriptionId)
    {
        return Subscription::retrieve($subscriptionId);
    }
    
    /**
     * Cancel subscription
     * 
     * @param string $subscriptionId
     * @return \Stripe\Subscription
     */
    public function cancelSubscription($subscriptionId)
    {
        return Subscription::update($subscriptionId, [
            'cancel_at_period_end' => true,
        ]);
    }
    
    /**
     * Reactivate subscription
     * 
     * @param string $subscriptionId
     * @return \Stripe\Subscription
     */
    public function reactivateSubscription($subscriptionId)
    {
        return Subscription::update($subscriptionId, [
            'cancel_at_period_end' => false,
        ]);
    }
    
    /**
     * Get invoice details
     * 
     * @param string $invoiceId
     * @return \Stripe\Invoice
     */
    public function getInvoice($invoiceId)
    {
        return Invoice::retrieve($invoiceId);
    }
    
    /**
     * Get customer details
     * 
     * @param string $customerId
     * @return \Stripe\Customer
     */
    public function getCustomer($customerId)
    {
        return Customer::retrieve($customerId);
    }
    
    /**
     * Get all invoices for a customer
     * 
     * @param string $customerId
     * @return \Stripe\Collection
     */
    public function getCustomerInvoices($customerId)
    {
        return Invoice::all([
            'customer' => $customerId,
            'limit' => 100,
        ]);
    }
    
    /**
     * Verify webhook signature
     * 
     * @param string $payload
     * @param string $sigHeader
     * @return \Stripe\Event
     */
    public function verifyWebhookSignature($payload, $sigHeader)
    {
        try {
            return Webhook::constructEvent(
                $payload,
                $sigHeader,
                $this->webhookSecret
            );
        } catch (SignatureVerificationException $e) {
            throw new \Exception('Webhook signature verification failed: ' . $e->getMessage());
        }
    }
}

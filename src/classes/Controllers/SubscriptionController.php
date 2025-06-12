<?php
namespace Controllers;

use Models\User;
use Models\Subscription;
use Models\Product;
use Services\StripeService;

class SubscriptionController
{
    private $userModel;
    private $subscriptionModel;
    private $productModel;
    private $stripeService;
    
    public function __construct()
    {
        $this->userModel = new User();
        $this->subscriptionModel = new Subscription();
        $this->productModel = new Product();
        $this->stripeService = new StripeService();
    }
    
    /**
     * Create checkout session for a new subscription
     * 
     * @param string $priceId
     * @param int $userId
     * @param string $successUrl
     * @param string $cancelUrl
     * @return string|bool Checkout URL or false on failure
     */
    public function createCheckoutSession($priceId, $userId, $successUrl, $cancelUrl)
    {
        try {
            $user = $this->userModel->find($userId);
            
            if (!$user) {
                flash('error', 'User not found');
                return false;
            }
            
            // Check if user already has a Stripe customer ID
            if (empty($user['stripe_customer_id'])) {
                // Create a new Stripe customer
                $customer = $this->stripeService->createCustomer(
                    $user['email'],
                    $user['first_name'] . ' ' . $user['last_name']
                );
                
                // Update user with Stripe customer ID
                $this->userModel->update($userId, ['stripe_customer_id' => $customer->id]);
                $customerId = $customer->id;
            } else {
                $customerId = $user['stripe_customer_id'];
            }
            
            // Create checkout session
            $session = $this->stripeService->createCheckoutSession(
                $priceId,
                $customerId,
                $successUrl,
                $cancelUrl
            );
            
            return $session->url;
        } catch (\Exception $e) {
            flash('error', 'Error creating checkout session: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Create checkout session for a new customer without user account
     * 
     * @param string $priceId
     * @param string $email
     * @param string $successUrl
     * @param string $cancelUrl
     * @return string|bool Checkout URL or false on failure
     */
    public function createCheckoutSessionForNewCustomer($priceId, $email, $successUrl, $cancelUrl)
    {
        try {
            // Create checkout session
            $session = $this->stripeService->createCheckoutSessionWithoutCustomer(
                $priceId,
                $email,
                $successUrl,
                $cancelUrl
            );
            
            return $session->url;
        } catch (\Exception $e) {
            flash('error', 'Error creating checkout session: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Create customer portal session
     * 
     * @param int $userId
     * @param string $returnUrl
     * @return string|bool Portal URL or false on failure
     */
    public function createCustomerPortalSession($userId, $returnUrl)
    {
        try {
            $user = $this->userModel->find($userId);
            
            if (!$user || empty($user['stripe_customer_id'])) {
                flash('error', 'User not found or no Stripe customer ID');
                return false;
            }
            
            // Create customer portal session
            $session = $this->stripeService->createCustomerPortalSession(
                $user['stripe_customer_id'],
                $returnUrl
            );
            
            return $session->url;
        } catch (\Exception $e) {
            flash('error', 'Error creating customer portal session: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Handle subscription webhook event
     * 
     * @param \Stripe\Event $event
     * @return bool
     */
    public function handleWebhookEvent($event)
    {
        try {
            switch ($event->type) {
                case 'checkout.session.completed':
                    return $this->handleCheckoutSessionCompleted($event->data->object);
                
                case 'customer.subscription.created':
                case 'customer.subscription.updated':
                    return $this->handleSubscriptionUpdated($event->data->object);
                
                case 'customer.subscription.deleted':
                    return $this->handleSubscriptionDeleted($event->data->object);
                
                case 'invoice.paid':
                case 'invoice.payment_failed':
                    return $this->handleInvoiceEvent($event->data->object);
                
                default:
                    // Unhandled event type
                    return true;
            }
        } catch (\Exception $e) {
            error_log('Error handling webhook: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Handle checkout.session.completed event
     * 
     * @param \Stripe\Checkout\Session $session
     * @return bool
     */
    private function handleCheckoutSessionCompleted($session)
    {
        if ($session->mode !== 'subscription') {
            return true; // Not a subscription checkout
        }
        
        // Get customer ID from session
        $customerId = $session->customer;
        
        // Find user by Stripe customer ID
        $user = $this->userModel->findByStripeCustomerId($customerId);
        
        // If user doesn't exist, create one
        if (!$user && isset($session->customer_details->email)) {
            $email = $session->customer_details->email;
            $name = $session->customer_details->name ?? '';
            
            // Parse name if available
            $firstName = $name;
            $lastName = '';
            if ($name && strpos($name, ' ') !== false) {
                list($firstName, $lastName) = explode(' ', $name, 2);
            }
            
            // Create user
            $userId = $this->userModel->create([
                'email' => $email,
                'first_name' => $firstName,
                'last_name' => $lastName,
                'password' => password_hash(bin2hex(random_bytes(8)), PASSWORD_DEFAULT), // Random password
                'role' => 'customer',
                'stripe_customer_id' => $customerId
            ]);
            
            $user = $this->userModel->find($userId);
        }
        
        return true;
    }
    
    /**
     * Handle subscription updated event
     * 
     * @param \Stripe\Subscription $subscription
     * @return bool
     */
    private function handleSubscriptionUpdated($subscription)
    {
        // Get customer ID from subscription
        $customerId = $subscription->customer;
        
        // Find user by Stripe customer ID
        $user = $this->userModel->findByStripeCustomerId($customerId);
        
        if (!$user) {
            error_log('User not found for Stripe customer ID: ' . $customerId);
            return false;
        }
        
        // Find existing subscription in our database
        $existingSubscription = $this->subscriptionModel->findByStripeSubscriptionId($subscription->id);
        
        // Get product from price ID
        $priceId = $subscription->items->data[0]->price->id;
        $product = $this->productModel->findByStripePriceId($priceId);
        
        $planType = $product ? $product['type'] : 'unknown';
        
        if ($existingSubscription) {
            // Update existing subscription
            $this->subscriptionModel->update($existingSubscription['id'], [
                'status' => $subscription->status,
                'current_period_start' => date('Y-m-d H:i:s', $subscription->current_period_start),
                'current_period_end' => date('Y-m-d H:i:s', $subscription->current_period_end),
                'plan_type' => $planType
            ]);
        } else {
            // Create new subscription record
            $this->subscriptionModel->create([
                'user_id' => $user['id'],
                'stripe_subscription_id' => $subscription->id,
                'plan_type' => $planType,
                'status' => $subscription->status,
                'current_period_start' => date('Y-m-d H:i:s', $subscription->current_period_start),
                'current_period_end' => date('Y-m-d H:i:s', $subscription->current_period_end)
            ]);
        }
        
        return true;
    }
    
    /**
     * Handle subscription deleted event
     * 
     * @param \Stripe\Subscription $subscription
     * @return bool
     */
    private function handleSubscriptionDeleted($subscription)
    {
        // Find existing subscription in our database
        $existingSubscription = $this->subscriptionModel->findByStripeSubscriptionId($subscription->id);
        
        if ($existingSubscription) {
            // Update subscription status to canceled
            $this->subscriptionModel->update($existingSubscription['id'], [
                'status' => 'canceled'
            ]);
        }
        
        return true;
    }
    
    /**
     * Handle invoice event
     * 
     * @param \Stripe\Invoice $invoice
     * @return bool
     */
    private function handleInvoiceEvent($invoice)
    {
        // Get customer ID from invoice
        $customerId = $invoice->customer;
        
        // Find user by Stripe customer ID
        $user = $this->userModel->findByStripeCustomerId($customerId);
        
        if (!$user) {
            error_log('User not found for Stripe customer ID: ' . $customerId);
            return false;
        }
        
        // Find subscription
        $subscription = null;
        if ($invoice->subscription) {
            $subscription = $this->subscriptionModel->findByStripeSubscriptionId($invoice->subscription);
        }
        
        if (!$subscription) {
            error_log('Subscription not found for invoice: ' . $invoice->id);
            return false;
        }
        
        // Create or update invoice record
        $invoiceModel = new \Models\Invoice();
        $existingInvoice = $invoiceModel->findByStripeInvoiceId($invoice->id);
        
        $invoiceData = [
            'user_id' => $user['id'],
            'subscription_id' => $subscription['id'],
            'stripe_invoice_id' => $invoice->id,
            'amount' => $invoice->amount_paid / 100, // Convert from cents
            'currency' => strtoupper($invoice->currency),
            'status' => $invoice->status,
            'invoice_date' => date('Y-m-d H:i:s', $invoice->created)
        ];
        
        if ($existingInvoice) {
            $invoiceModel->update($existingInvoice['id'], $invoiceData);
        } else {
            $invoiceModel->create($invoiceData);
        }
        
        return true;
    }
    
    /**
     * Cancel subscription
     * 
     * @param int $subscriptionId
     * @return bool
     */
    public function cancelSubscription($subscriptionId)
    {
        try {
            $subscription = $this->subscriptionModel->find($subscriptionId);
            
            if (!$subscription) {
                flash('error', 'Subscription not found');
                return false;
            }
            
            // Cancel subscription in Stripe
            $this->stripeService->cancelSubscription($subscription['stripe_subscription_id']);
            
            // Update subscription status in database
            $this->subscriptionModel->update($subscriptionId, [
                'status' => 'canceling'
            ]);
            
            flash('success', 'Subscription has been canceled');
            return true;
        } catch (\Exception $e) {
            flash('error', 'Error canceling subscription: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Reactivate subscription
     * 
     * @param int $subscriptionId
     * @return bool
     */
    public function reactivateSubscription($subscriptionId)
    {
        try {
            $subscription = $this->subscriptionModel->find($subscriptionId);
            
            if (!$subscription) {
                flash('error', 'Subscription not found');
                return false;
            }
            
            // Reactivate subscription in Stripe
            $this->stripeService->reactivateSubscription($subscription['stripe_subscription_id']);
            
            // Update subscription status in database
            $this->subscriptionModel->update($subscriptionId, [
                'status' => 'active'
            ]);
            
            flash('success', 'Subscription has been reactivated');
            return true;
        } catch (\Exception $e) {
            flash('error', 'Error reactivating subscription: ' . $e->getMessage());
            return false;
        }
    }
}

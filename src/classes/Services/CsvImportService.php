<?php
namespace Services;

use Models\User;

class CsvImportService
{
    private $userModel;
    private $stripeService;
    
    public function __construct()
    {
        $this->userModel = new User();
        $this->stripeService = new StripeService();
    }
    
    /**
     * Process CSV file for bulk customer import
     * 
     * @param string $filePath
     * @param string $successUrl
     * @return array Results of the import process
     */
    public function processCsvFile($filePath, $successUrl)
    {
        if (!file_exists($filePath)) {
            throw new \Exception("File not found: {$filePath}");
        }
        
        $file = fopen($filePath, 'r');
        if (!$file) {
            throw new \Exception("Could not open file: {$filePath}");
        }
        
        $headers = fgetcsv($file);
        $requiredColumns = ['email', 'first_name', 'last_name'];
        
        // Validate headers
        foreach ($requiredColumns as $column) {
            if (!in_array($column, $headers)) {
                fclose($file);
                throw new \Exception("Missing required column: {$column}");
            }
        }
        
        $results = [
            'total' => 0,
            'success' => 0,
            'failed' => 0,
            'skipped' => 0,
            'errors' => []
        ];
        
        while (($row = fgetcsv($file)) !== false) {
            $results['total']++;
            
            $data = array_combine($headers, $row);
            
            try {
                // Check if user already exists
                $existingUser = $this->userModel->findOneBy('email', $data['email']);
                
                if ($existingUser) {
                    $results['skipped']++;
                    continue;
                }
                
                // Create Stripe customer
                $stripeCustomer = $this->stripeService->createCustomer(
                    $data['email'],
                    $data['first_name'] . ' ' . $data['last_name']
                );
                
                // Create user in database
                $userId = $this->userModel->create([
                    'email' => $data['email'],
                    'first_name' => $data['first_name'],
                    'last_name' => $data['last_name'],
                    'password' => password_hash(bin2hex(random_bytes(8)), PASSWORD_DEFAULT), // Random password
                    'role' => 'customer',
                    'stripe_customer_id' => $stripeCustomer->id
                ]);
                
                // Send checkout email (this would typically be handled by a separate email service)
                $this->sendCheckoutEmail($data['email'], $stripeCustomer->id, $successUrl);
                
                $results['success']++;
            } catch (\Exception $e) {
                $results['failed']++;
                $results['errors'][] = "Error processing {$data['email']}: " . $e->getMessage();
            }
        }
        
        fclose($file);
        return $results;
    }
    
    /**
     * Send checkout email to customer
     * 
     * @param string $email
     * @param string $customerId
     * @param string $successUrl
     * @return bool
     */
    private function sendCheckoutEmail($email, $customerId, $successUrl)
    {
        // In a real application, this would send an actual email
        // For this demo, we'll just log the action
        
        $checkoutUrl = config('app.url') . '/checkout.php?customer_id=' . $customerId;
        
        $subject = 'Complete Your Subscription';
        $message = "Hello,\n\n";
        $message .= "Please complete your subscription by adding your payment details:\n";
        $message .= $checkoutUrl . "\n\n";
        $message .= "Thank you for choosing our service!\n";
        
        // Log instead of sending actual email for demo purposes
        error_log("Would send email to {$email} with checkout link: {$checkoutUrl}");
        
        return true;
    }
}

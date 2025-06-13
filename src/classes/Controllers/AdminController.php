<?php
namespace Controllers;

use Models\User;
use Models\Subscription;
use Models\Invoice;
use Models\Product;
use Services\CsvImportService;

class AdminController
{
    private $userModel;
    private $subscriptionModel;
    private $invoiceModel;
    private $productModel;
    
    public function __construct()
    {
        $this->userModel = new User();
        $this->subscriptionModel = new Subscription();
        $this->invoiceModel = new Invoice();
        $this->productModel = new Product();
    }
    
    /**
     * Get dashboard statistics
     * 
     * @return array
     */
    public function getDashboardStats()
    {
        $stats = [
            'total_customers' => count($this->userModel->getAllCustomers()),
            'active_subscriptions' => 0,
            'revenue' => 0,
            'recent_signups' => []
        ];
        
        // Get active subscriptions
        $activeSubscriptions = $this->subscriptionModel->getPaginated(1, 1000, ['status' => 'active']);
        $stats['active_subscriptions'] = $activeSubscriptions['total'];
        
        // Get revenue statistics
        $invoiceStats = $this->invoiceModel->getStatistics();
        $stats['revenue'] = $invoiceStats['total_revenue'] ?? 0;
        
        // Get recent signups
        $recentUsers = db()->query("
            SELECT u.*, s.plan_type, s.status
            FROM users u
            LEFT JOIN subscriptions s ON u.id = s.user_id AND s.status IN ('active', 'trialing')
            WHERE u.role = 'customer'
            ORDER BY u.created_at DESC
            LIMIT 5
        ")->fetchAll();
        
        $stats['recent_signups'] = $recentUsers;
        
        // Get monthly revenue data for charts
        $stats['monthly_revenue'] = $this->invoiceModel->getMonthlyRevenue();
        
        // Get subscription distribution by plan
        $planDistribution = db()->query("
            SELECT plan_type, COUNT(*) as count
            FROM subscriptions
            WHERE status IN ('active', 'trialing')
            GROUP BY plan_type
        ")->fetchAll();
        
        $stats['plan_distribution'] = $planDistribution;
        
        return $stats;
    }
    
    /**
     * Create a new customer manually
     * 
     * @param array $data
     * @param string $successUrl
     * @return array|bool
     */
    public function createCustomer($data, $successUrl)
    {
        try {
            // Validate data
            if (empty($data['email']) || empty($data['first_name']) || empty($data['last_name'])) {
                flash('error', 'Please fill in all required fields');
                return false;
            }
            
            // Check if email already exists
            $existingUser = $this->userModel->findOneBy('email', $data['email']);
            if ($existingUser) {
                flash('error', 'Email already in use');
                return false;
            }
            
            // Create Stripe customer
            $stripeService = new \Services\StripeService();
            $customer = $stripeService->createCustomer(
                $data['email'],
                $data['first_name'] . ' ' . $data['last_name']
            );
            
            // Create user in database
            $password = bin2hex(random_bytes(8)); // Random password
            $userId = $this->userModel->create([
                'email' => $data['email'],
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'password' => password_hash($password, PASSWORD_DEFAULT),
                'role' => 'customer',
                'stripe_customer_id' => $customer->id
            ]);
            
            // Send checkout email
            $this->sendCheckoutEmail($data['email'], $customer->id, $successUrl);
            
            flash('success', 'Customer created successfully and checkout email sent');
            
            return [
                'user_id' => $userId,
                'stripe_customer_id' => $customer->id,
                'password' => $password
            ];
        } catch (\Exception $e) {
            flash('error', 'Error creating customer: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Process CSV import for bulk customer creation
     * 
     * @param array $file $_FILES array
     * @param string $successUrl
     * @return array|bool
     */
    public function processCsvImport($file, $successUrl)
    {
        try {
            if (!isset($file['tmp_name']) || empty($file['tmp_name'])) {
                flash('error', 'No file uploaded');
                return false;
            }
            
            $csvService = new CsvImportService();
            $results = $csvService->processCsvFile($file['tmp_name'], $successUrl);
            
            if ($results['success'] > 0) {
                flash('success', "CSV import completed: {$results['success']} customers created, {$results['skipped']} skipped, {$results['failed']} failed");
            } else {
                flash('warning', "CSV import completed but no customers were created: {$results['skipped']} skipped, {$results['failed']} failed");
            }
            
            return $results;
        } catch (\Exception $e) {
            flash('error', 'Error processing CSV: ' . $e->getMessage());
            return false;
        }
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
    
    /**
     * Generate reports
     * 
     * @param string $reportType
     * @param array $filters
     * @param string $format 'excel' or 'pdf'
     * @return string Path to the generated report file
     */
    public function generateReport($reportType, $filters, $format)
    {
        switch ($reportType) {
            case 'new_signups':
                return $this->generateNewSignupsReport($filters, $format);
            
            case 'signoffs':
                return $this->generateSignoffsReport($filters, $format);
            
            case 'revenue':
                return $this->generateRevenueReport($filters, $format);
            
            default:
                throw new \Exception('Invalid report type');
        }
    }
    
    /**
     * Generate new signups report
     * 
     * @param array $filters
     * @param string $format
     * @return string
     */
    private function generateNewSignupsReport($filters, $format)
    {
        $whereClause = '';
        $params = [];
        
        if (!empty($filters)) {
            $conditions = [];
            
            if (isset($filters['date_from']) && $filters['date_from']) {
                $conditions[] = "u.created_at >= :date_from";
                $params['date_from'] = $filters['date_from'];
            }
            
            if (isset($filters['date_to']) && $filters['date_to']) {
                $conditions[] = "u.created_at <= :date_to";
                $params['date_to'] = $filters['date_to'];
            }
            
            if (!empty($conditions)) {
                $whereClause = "WHERE " . implode(' AND ', $conditions);
            }
        }
        
        $query = "
            SELECT 
                u.id,
                u.email,
                u.first_name,
                u.last_name,
                u.created_at,
                s.plan_type,
                s.status
            FROM users u
            LEFT JOIN subscriptions s ON u.id = s.user_id AND s.status IN ('active', 'trialing')
            {$whereClause}
            WHERE u.role = 'customer'
            ORDER BY u.created_at DESC
        ";
        
        $stmt = db()->prepare($query);
        
        foreach ($params as $key => $value) {
            $stmt->bindValue(":{$key}", $value);
        }
        
        $stmt->execute();
        $data = $stmt->fetchAll();
        
        // Generate report file
        $filename = 'new_signups_' . date('Y-m-d_H-i-s');
        
        if ($format === 'excel') {
            return $this->generateExcelReport($data, $filename, 'New Signups Report');
        } else {
            return $this->generatePdfReport($data, $filename, 'New Signups Report');
        }
    }
    
    /**
     * Generate signoffs report
     * 
     * @param array $filters
     * @param string $format
     * @return string
     */
    private function generateSignoffsReport($filters, $format)
    {
        $whereClause = "WHERE s.status = 'canceled'";
        $params = [];
        
        if (!empty($filters)) {
            if (isset($filters['date_from']) && $filters['date_from']) {
                $whereClause .= " AND s.updated_at >= :date_from";
                $params['date_from'] = $filters['date_from'];
            }
            
            if (isset($filters['date_to']) && $filters['date_to']) {
                $whereClause .= " AND s.updated_at <= :date_to";
                $params['date_to'] = $filters['date_to'];
            }
        }
        
        $query = "
            SELECT 
                u.id,
                u.email,
                u.first_name,
                u.last_name,
                s.plan_type,
                s.created_at as subscription_start,
                s.updated_at as subscription_end
            FROM subscriptions s
            JOIN users u ON s.user_id = u.id
            {$whereClause}
            ORDER BY s.updated_at DESC
        ";
        
        $stmt = db()->prepare($query);
        
        foreach ($params as $key => $value) {
            $stmt->bindValue(":{$key}", $value);
        }
        
        $stmt->execute();
        $data = $stmt->fetchAll();
        
        // Generate report file
        $filename = 'signoffs_' . date('Y-m-d_H-i-s');
        
        if ($format === 'excel') {
            return $this->generateExcelReport($data, $filename, 'Subscription Cancellations Report');
        } else {
            return $this->generatePdfReport($data, $filename, 'Subscription Cancellations Report');
        }
    }
    
    /**
     * Generate revenue report
     * 
     * @param array $filters
     * @param string $format
     * @return string
     */
    private function generateRevenueReport($filters, $format)
    {
        $whereClause = '';
        $params = [];
        
        if (!empty($filters)) {
            $conditions = [];
            
            if (isset($filters['date_from']) && $filters['date_from']) {
                $conditions[] = "i.invoice_date >= :date_from";
                $params['date_from'] = $filters['date_from'];
            }
            
            if (isset($filters['date_to']) && $filters['date_to']) {
                $conditions[] = "i.invoice_date <= :date_to";
                $params['date_to'] = $filters['date_to'];
            }
            
            if (isset($filters['status']) && $filters['status']) {
                $conditions[] = "i.status = :status";
                $params['status'] = $filters['status'];
            }
            
            if (!empty($conditions)) {
                $whereClause = "WHERE " . implode(' AND ', $conditions);
            }
        }
        
        $query = "
            SELECT 
                i.id,
                u.email,
                u.first_name,
                u.last_name,
                i.amount,
                i.currency,
                i.status,
                i.invoice_date,
                s.plan_type
            FROM invoices i
            JOIN users u ON i.user_id = u.id
            JOIN subscriptions s ON i.subscription_id = s.id
            {$whereClause}
            ORDER BY i.invoice_date DESC
        ";
        
        $stmt = db()->prepare($query);
        
        foreach ($params as $key => $value) {
            $stmt->bindValue(":{$key}", $value);
        }
        
        $stmt->execute();
        $data = $stmt->fetchAll();
        
        // Generate report file
        $filename = 'revenue_' . date('Y-m-d_H-i-s');
        
        if ($format === 'excel') {
            return $this->generateExcelReport($data, $filename, 'Revenue Report');
        } else {
            return $this->generatePdfReport($data, $filename, 'Revenue Report');
        }
    }
    
    /**
     * Generate Excel report
     * 
     * @param array $data
     * @param string $filename
     * @param string $title
     * @return string
     */
    private function generateExcelReport($data, $filename, $title)
    {
        // In a real application, this would generate an actual Excel file
        // For this demo, we'll just create a CSV file
        
        $filepath = BASE_PATH . '/src/public/reports/' . $filename . '.csv';
        
        // Create reports directory if it doesn't exist
        if (!is_dir(dirname($filepath))) {
            mkdir(dirname($filepath), 0755, true);
        }
        
        $file = fopen($filepath, 'w');
        
        // Add headers
        if (!empty($data)) {
            fputcsv($file, array_keys($data[0]));
            
            // Add data rows
            foreach ($data as $row) {
                fputcsv($file, $row);
            }
        }
        
        fclose($file);
        
        return '/reports/' . $filename . '.csv';
    }
    
    /**
     * Generate PDF report
     * 
     * @param array $data
     * @param string $filename
     * @param string $title
     * @return string
     */
    private function generatePdfReport($data, $filename, $title)
    {
        // In a real application, this would generate an actual PDF file
        // For this demo, we'll just create a text file with JSON data
        
        $filepath = BASE_PATH . '/src/public/reports/' . $filename . '.txt';
        
        // Create reports directory if it doesn't exist
        if (!is_dir(dirname($filepath))) {
            mkdir(dirname($filepath), 0755, true);
        }
        
        $content = "Title: {$title}\n";
        $content .= "Generated: " . date('Y-m-d H:i:s') . "\n\n";
        $content .= json_encode($data, JSON_PRETTY_PRINT);
        
        file_put_contents($filepath, $content);
        
        return '/reports/' . $filename . '.txt';
    }
    
    /**
     * Get recent subscriptions
     * 
     * @param int $limit Number of subscriptions to return
     * @return array
     */
    public function getRecentSubscriptions($limit = 5)
    {
        $query = "SELECT s.*, u.email, u.first_name, u.last_name, p.name as plan_name 
                 FROM subscriptions s 
                 JOIN users u ON s.user_id = u.id 
                 JOIN products p ON s.plan_type = p.type 
                 ORDER BY s.created_at DESC 
                 LIMIT :limit";
        
        $stmt = db()->prepare($query);
        $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }
    
    /**
     * Get recent invoices
     * 
     * @param int $limit Number of invoices to return
     * @return array
     */
    public function getRecentInvoices($limit = 5)
    {
        $query = "SELECT i.*, u.email, u.first_name, u.last_name 
                 FROM invoices i 
                 JOIN users u ON i.user_id = u.id 
                 ORDER BY i.invoice_date DESC 
                 LIMIT :limit";
        
        $stmt = db()->prepare($query);
        $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }
}

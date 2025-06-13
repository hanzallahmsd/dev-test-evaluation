<?php
namespace Models;

class Invoice extends BaseModel
{
    protected $table = 'invoices';
    protected $fillable = [
        'user_id', 
        'subscription_id', 
        'stripe_invoice_id', 
        'amount', 
        'currency', 
        'status', 
        'invoice_date'
    ];
    
    /**
     * Find invoice by Stripe invoice ID
     * 
     * @param string $stripeInvoiceId
     * @return array|null
     */
    public function findByStripeInvoiceId($stripeInvoiceId)
    {
        return $this->findOneBy('stripe_invoice_id', $stripeInvoiceId);
    }
    
    /**
     * Get all invoices for a user
     * 
     * @param int $userId
     * @return array
     */
    public function getAllForUser($userId)
    {
        return $this->findBy('user_id', $userId);
    }
    
    /**
     * Get invoices with pagination and filtering
     * 
     * @param int $page
     * @param int $limit
     * @param array $filters
     * @return array
     */
    public function getPaginated($page = 1, $limit = 10, $filters = [])
    {
        $offset = ($page - 1) * $limit;
        
        $whereClause = '';
        $params = [];
        
        if (!empty($filters)) {
            $conditions = [];
            
            if (isset($filters['status']) && $filters['status']) {
                $conditions[] = "i.status = :status";
                $params['status'] = $filters['status'];
            }
            
            if (isset($filters['user_id']) && $filters['user_id']) {
                $conditions[] = "i.user_id = :user_id";
                $params['user_id'] = $filters['user_id'];
            }
            
            if (isset($filters['date_from']) && $filters['date_from']) {
                $conditions[] = "i.invoice_date >= :date_from";
                $params['date_from'] = $filters['date_from'];
            }
            
            if (isset($filters['date_to']) && $filters['date_to']) {
                $conditions[] = "i.invoice_date <= :date_to";
                $params['date_to'] = $filters['date_to'];
            }
            
            if (!empty($conditions)) {
                $whereClause = "WHERE " . implode(' AND ', $conditions);
            }
        }
        
        $query = "
            SELECT i.*, u.email, u.first_name, u.last_name, s.plan_type
            FROM {$this->table} i
            JOIN users u ON i.user_id = u.id
            JOIN subscriptions s ON i.subscription_id = s.id
            {$whereClause}
            ORDER BY i.invoice_date DESC
            LIMIT :limit OFFSET :offset
        ";
        
        $countQuery = "
            SELECT COUNT(*) as total 
            FROM {$this->table} i
            JOIN users u ON i.user_id = u.id
            JOIN subscriptions s ON i.subscription_id = s.id
            {$whereClause}
        ";
        
        $stmt = db()->prepare($query);
        $stmt->bindParam(':limit', $limit, \PDO::PARAM_INT);
        $stmt->bindParam(':offset', $offset, \PDO::PARAM_INT);
        
        foreach ($params as $key => $value) {
            $stmt->bindValue(":{$key}", $value);
        }
        
        $stmt->execute();
        $invoices = $stmt->fetchAll();
        
        $countStmt = db()->prepare($countQuery);
        foreach ($params as $key => $value) {
            $countStmt->bindValue(":{$key}", $value);
        }
        $countStmt->execute();
        $total = $countStmt->fetch()['total'];
        
        return [
            'data' => $invoices,
            'total' => $total,
            'page' => $page,
            'limit' => $limit,
            'last_page' => ceil($total / $limit)
        ];
    }
    
    /**
     * Get invoice statistics for dashboard
     * 
     * @return array
     */
    public function getStatistics()
    {
        $query = "
            SELECT 
                SUM(CASE WHEN status = 'paid' THEN amount ELSE 0 END) as total_revenue,
                COUNT(*) as total_invoices,
                COUNT(CASE WHEN status = 'paid' THEN 1 END) as paid_invoices,
                COUNT(CASE WHEN status = 'unpaid' THEN 1 END) as unpaid_invoices
            FROM {$this->table}
        ";
        
        $stmt = db()->query($query);
        return $stmt->fetch();
    }
    
    /**
     * Get monthly revenue data for charts
     * 
     * @param int $months Number of months to look back
     * @return array
     */
    public function getMonthlyRevenue($months = 6)
    {
        $query = "
            SELECT 
                DATE_FORMAT(invoice_date, '%Y-%m') as month,
                SUM(CASE WHEN status = 'paid' THEN amount ELSE 0 END) as revenue,
                COUNT(*) as count
            FROM {$this->table}
            WHERE invoice_date >= DATE_SUB(CURRENT_DATE(), INTERVAL :months MONTH)
            GROUP BY DATE_FORMAT(invoice_date, '%Y-%m')
            ORDER BY month ASC
        ";
        
        $stmt = db()->prepare($query);
        $stmt->bindParam(':months', $months, \PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }
    
    /**
     * Get paginated invoices for a specific user
     * 
     * @param int $userId
     * @param int $page
     * @param int $limit
     * @return array
     */
    public function getPaginatedForUser($userId, $page = 1, $limit = 10)
    {
        $filters = ['user_id' => $userId];
        $result = $this->getPaginated($page, $limit, $filters);
        return $result['data'];
    }
    
    /**
     * Count total invoices for a specific user
     * 
     * @param int $userId
     * @return int
     */
    public function countForUser($userId)
    {
        $query = "SELECT COUNT(*) as total FROM {$this->table} WHERE user_id = :user_id";
        $stmt = db()->prepare($query);
        $stmt->bindParam(':user_id', $userId, \PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetch();
        return (int)$result['total'];
    }
    
    /**
     * Get recent invoices for a user
     * 
     * @param int $userId
     * @param int $limit
     * @return array
     */
    public function getRecentForUser($userId, $limit = 3)
    {
        $query = "SELECT * FROM {$this->table} WHERE user_id = :user_id ORDER BY invoice_date DESC LIMIT :limit";
        $stmt = db()->prepare($query);
        $stmt->bindParam(':user_id', $userId, \PDO::PARAM_INT);
        $stmt->bindParam(':limit', $limit, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}

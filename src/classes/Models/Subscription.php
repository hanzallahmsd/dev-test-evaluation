<?php
namespace Models;

class Subscription extends BaseModel
{
    protected $table = 'subscriptions';
    protected $fillable = [
        'user_id', 
        'stripe_subscription_id', 
        'plan_type', 
        'status', 
        'current_period_start', 
        'current_period_end'
    ];
    
    /**
     * Get subscription by Stripe subscription ID
     * 
     * @param string $stripeSubscriptionId
     * @return array|null
     */
    public function findByStripeSubscriptionId($stripeSubscriptionId)
    {
        return $this->findOneBy('stripe_subscription_id', $stripeSubscriptionId);
    }
    
    /**
     * Get active subscription for a user
     * 
     * @param int $userId
     * @return array|null
     */
    public function getActiveSubscriptionForUser($userId)
    {
        $stmt = db()->prepare("
            SELECT * FROM {$this->table} 
            WHERE user_id = :user_id 
            AND status IN ('active', 'trialing')
            ORDER BY created_at DESC 
            LIMIT 1
        ");
        $stmt->execute(['user_id' => $userId]);
        return $stmt->fetch();
    }
    
    /**
     * Get all subscriptions for a user
     * 
     * @param int $userId
     * @return array
     */
    public function getAllForUser($userId)
    {
        return $this->findBy('user_id', $userId);
    }
    
    /**
     * Update subscription status
     * 
     * @param string $stripeSubscriptionId
     * @param string $status
     * @return bool
     */
    public function updateStatus($stripeSubscriptionId, $status)
    {
        $subscription = $this->findByStripeSubscriptionId($stripeSubscriptionId);
        
        if ($subscription) {
            return $this->update($subscription['id'], ['status' => $status]);
        }
        
        return false;
    }
    
    /**
     * Get subscriptions with pagination and filtering
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
                $conditions[] = "status = :status";
                $params['status'] = $filters['status'];
            }
            
            if (isset($filters['plan_type']) && $filters['plan_type']) {
                $conditions[] = "plan_type = :plan_type";
                $params['plan_type'] = $filters['plan_type'];
            }
            
            if (!empty($conditions)) {
                $whereClause = "WHERE " . implode(' AND ', $conditions);
            }
        }
        
        $query = "
            SELECT s.*, u.email, u.first_name, u.last_name 
            FROM {$this->table} s
            JOIN users u ON s.user_id = u.id
            {$whereClause}
            ORDER BY s.created_at DESC
            LIMIT :limit OFFSET :offset
        ";
        
        $countQuery = "
            SELECT COUNT(*) as total 
            FROM {$this->table} s
            JOIN users u ON s.user_id = u.id
            {$whereClause}
        ";
        
        $stmt = db()->prepare($query);
        $stmt->bindParam(':limit', $limit, \PDO::PARAM_INT);
        $stmt->bindParam(':offset', $offset, \PDO::PARAM_INT);
        
        foreach ($params as $key => $value) {
            $stmt->bindValue(":{$key}", $value);
        }
        
        $stmt->execute();
        $subscriptions = $stmt->fetchAll();
        
        $countStmt = db()->prepare($countQuery);
        foreach ($params as $key => $value) {
            $countStmt->bindValue(":{$key}", $value);
        }
        $countStmt->execute();
        $total = $countStmt->fetch()['total'];
        
        return [
            'data' => $subscriptions,
            'total' => $total,
            'page' => $page,
            'limit' => $limit,
            'last_page' => ceil($total / $limit)
        ];
    }
}

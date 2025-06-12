<?php
namespace Models;

class Product extends BaseModel
{
    protected $table = 'products';
    protected $fillable = [
        'name', 
        'description', 
        'price', 
        'currency', 
        'stripe_product_id', 
        'stripe_price_id', 
        'type', 
        'billing_interval', 
        'active'
    ];
    
    /**
     * Get all active products
     * 
     * @return array
     */
    public function getAllActive()
    {
        $stmt = db()->prepare("SELECT * FROM {$this->table} WHERE active = :active ORDER BY price ASC");
        $stmt->execute(['active' => 1]);
        return $stmt->fetchAll();
    }
    
    /**
     * Get active products by billing interval
     * 
     * @param string $interval 'month' or 'year'
     * @return array
     */
    public function getActiveByInterval($interval)
    {
        $stmt = db()->prepare("
            SELECT * FROM {$this->table} 
            WHERE active = :active AND billing_interval = :interval
            ORDER BY price ASC
        ");
        $stmt->execute([
            'active' => 1,
            'interval' => $interval
        ]);
        return $stmt->fetchAll();
    }
    
    /**
     * Find product by Stripe price ID
     * 
     * @param string $stripePriceId
     * @return array|null
     */
    public function findByStripePriceId($stripePriceId)
    {
        return $this->findOneBy('stripe_price_id', $stripePriceId);
    }
    
    /**
     * Find product by type and billing interval
     * 
     * @param string $type 'small', 'medium', or 'large'
     * @param string $interval 'month' or 'year'
     * @return array|null
     */
    public function findByTypeAndInterval($type, $interval)
    {
        $stmt = db()->prepare("
            SELECT * FROM {$this->table} 
            WHERE type = :type AND billing_interval = :interval AND active = :active
            LIMIT 1
        ");
        $stmt->execute([
            'type' => $type,
            'interval' => $interval,
            'active' => 1
        ]);
        return $stmt->fetch();
    }
}

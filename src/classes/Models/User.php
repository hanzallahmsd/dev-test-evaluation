<?php
namespace Models;

class User extends BaseModel
{
    protected $table = 'users';
    protected $fillable = ['email', 'password', 'first_name', 'last_name', 'role', 'stripe_customer_id'];
    
    /**
     * Authenticate a user
     * 
     * @param string $email
     * @param string $password
     * @return array|null
     */
    public function authenticate($email, $password)
    {
        $user = $this->findOneBy('email', $email);
        
        if ($user && password_verify($password, $user['password'])) {
            return $user;
        }
        
        return null;
    }
    
    /**
     * Create a new user with hashed password
     * 
     * @param array $data
     * @return int
     */
    public function createWithHashedPassword(array $data)
    {
        if (isset($data['password'])) {
            $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        }
        
        return $this->create($data);
    }
    
    /**
     * Get all customers (non-admin users)
     * 
     * @return array
     */
    public function getAllCustomers()
    {
        $stmt = db()->prepare("SELECT * FROM {$this->table} WHERE role = :role");
        $stmt->execute(['role' => 'customer']);
        return $stmt->fetchAll();
    }
    
    /**
     * Find a user by their Stripe customer ID
     * 
     * @param string $stripeCustomerId
     * @return array|null
     */
    public function findByStripeCustomerId($stripeCustomerId)
    {
        return $this->findOneBy('stripe_customer_id', $stripeCustomerId);
    }
    
    /**
     * Update a user's Stripe customer ID
     * 
     * @param int $userId
     * @param string $stripeCustomerId
     * @return bool
     */
    public function updateStripeCustomerId($userId, $stripeCustomerId)
    {
        return $this->update($userId, ['stripe_customer_id' => $stripeCustomerId]);
    }
}

<?php
namespace Controllers;

use Models\User;

class AuthController
{
    private $userModel;
    
    public function __construct()
    {
        $this->userModel = new User();
    }
    
    /**
     * Handle login request
     * 
     * @param string $email
     * @param string $password
     * @param bool $remember
     * @return array|bool User data on success, false on failure
     */
    public function login($email, $password, $remember = false)
    {
        if (empty($email) || empty($password)) {
            flash('error', 'Please enter both email and password');
            return false;
        }
        
        $user = $this->userModel->authenticate($email, $password);
        
        if (!$user) {
            flash('error', 'Invalid email or password');
            return false;
        }
        
        // Set session data
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_name'] = $user['first_name'] . ' ' . $user['last_name'];
        $_SESSION['user_role'] = $user['role'];
        
        // Set remember me cookie if requested
        if ($remember) {
            $token = bin2hex(random_bytes(32));
            $hashedToken = password_hash($token, PASSWORD_DEFAULT);
            
            // Store token in database
            $this->userModel->update($user['id'], [
                'remember_token' => $hashedToken
            ]);
            
            // Set cookie for 30 days
            setcookie(
                'remember_token',
                $token,
                time() + (30 * 24 * 60 * 60),
                '/',
                '',
                false,
                true
            );
        }
        
        return $user;
    }
    
    /**
     * Handle logout request
     * 
     * @return void
     */
    public function logout()
    {
        // Unset all session variables
        $_SESSION = [];
        
        // Destroy the session
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params["path"],
                $params["domain"],
                $params["secure"],
                $params["httponly"]
            );
        }
        
        session_destroy();
    }
    
    /**
     * Check if user is logged in
     * 
     * @return bool
     */
    public function isLoggedIn()
    {
        return isset($_SESSION['user_id']);
    }
    
    /**
     * Check if user is admin
     * 
     * @return bool
     */
    public function isAdmin()
    {
        return $this->isLoggedIn() && $_SESSION['user_role'] === 'admin';
    }
    
    /**
     * Get current user data
     * 
     * @return array|null
     */
    public function getCurrentUser()
    {
        if (!$this->isLoggedIn()) {
            return null;
        }
        
        return $this->userModel->find($_SESSION['user_id']);
    }
    
    /**
     * Register a new user
     * 
     * @param array $data
     * @return int|bool User ID on success, false on failure
     */
    public function register($data)
    {
        // Validate data
        if (empty($data['email']) || empty($data['password']) || empty($data['first_name']) || empty($data['last_name'])) {
            flash('error', 'Please fill in all required fields');
            return false;
        }
        
        // Check if email already exists
        $existingUser = $this->userModel->findOneBy('email', $data['email']);
        if ($existingUser) {
            flash('error', 'Email already in use');
            return false;
        }
        
        // Create user
        $userId = $this->userModel->createWithHashedPassword([
            'email' => $data['email'],
            'password' => $data['password'],
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'role' => 'customer'
        ]);
        
        if ($userId) {
            flash('success', 'Registration successful! Please log in.');
            return $userId;
        }
        
        flash('error', 'Registration failed');
        return false;
    }
    
    /**
     * Update user password
     * 
     * @param int $userId
     * @param string $currentPassword
     * @param string $newPassword
     * @return bool
     */
    public function updatePassword($userId, $currentPassword, $newPassword)
    {
        // Get user
        $user = $this->userModel->find($userId);
        if (!$user) {
            return false;
        }
        
        // Verify current password
        if (!password_verify($currentPassword, $user['password'])) {
            return false;
        }
        
        // Update password
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        return $this->userModel->update($userId, ['password' => $hashedPassword]);
    }
}

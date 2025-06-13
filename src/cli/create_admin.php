<?php
/**
 * Create admin user script
 */
require_once __DIR__ . '/../bootstrap.php';

echo "Creating admin user...\n";

try {
    // Get user model
    $userModel = new \Models\User();
    
    // Check if admin user already exists
    $existingAdmin = $userModel->findOneBy('email', 'admin@example.com');
    
    if ($existingAdmin) {
        echo "Admin user already exists. Updating password...\n";
        
        // Update admin password
        $userModel->update($existingAdmin['id'], [
            'password' => password_hash('admin123', PASSWORD_DEFAULT)
        ]);
    } else {
        // Create admin user
        $userId = $userModel->createWithHashedPassword([
            'email' => 'admin@example.com',
            'password' => 'admin123',
            'first_name' => 'Admin',
            'last_name' => 'User',
            'role' => 'admin'
        ]);
        
        if (!$userId) {
            throw new Exception("Failed to create admin user");
        }
    }
    
    echo "Admin user created/updated successfully.\n";
    echo "Login with:\n";
    echo "Email: admin@example.com\n";
    echo "Password: admin123\n";
    
} catch (Exception $e) {
    echo "Error creating admin user: " . $e->getMessage() . "\n";
    exit(1);
}

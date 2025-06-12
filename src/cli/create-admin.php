<?php
/**
 * Create default admin user
 */
require_once __DIR__ . '/../bootstrap.php';

echo "Creating default admin user...\n";

try {
    // Get database connection
    $db = db();
    
    // Check if admin user already exists
    $stmt = $db->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute(['admin@example.com']);
    $existingAdmin = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($existingAdmin) {
        echo "Admin user already exists.\n";
        exit(0);
    }
    
    // Create admin user
    $hashedPassword = password_hash('admin123', PASSWORD_DEFAULT);
    
    $stmt = $db->prepare("
        INSERT INTO users (email, password, first_name, last_name, role, created_at, updated_at)
        VALUES (?, ?, ?, ?, ?, NOW(), NOW())
    ");
    
    $stmt->execute([
        'admin@example.com',
        $hashedPassword,
        'Admin',
        'User',
        'admin'
    ]);
    
    echo "Admin user created successfully.\n";
    echo "Email: admin@example.com\n";
    echo "Password: admin123\n";
    echo "IMPORTANT: Please change the default password after first login!\n";
    
} catch (Exception $e) {
    echo "Error creating admin user: " . $e->getMessage() . "\n";
    exit(1);
}

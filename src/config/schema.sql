-- Database schema for Dev Test Evaluation

-- Users table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    role ENUM('admin', 'customer') NOT NULL DEFAULT 'customer',
    stripe_customer_id VARCHAR(100) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Subscriptions table
CREATE TABLE IF NOT EXISTS subscriptions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    stripe_subscription_id VARCHAR(100) NOT NULL,
    plan_type ENUM('small', 'medium', 'large') NOT NULL,
    status VARCHAR(50) NOT NULL,
    current_period_start TIMESTAMP NULL,
    current_period_end TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Invoices table
CREATE TABLE IF NOT EXISTS invoices (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    subscription_id INT NOT NULL,
    stripe_invoice_id VARCHAR(100) NOT NULL,
    amount DECIMAL(10, 2) NOT NULL,
    currency VARCHAR(3) NOT NULL DEFAULT 'EUR',
    status VARCHAR(50) NOT NULL,
    invoice_date TIMESTAMP NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (subscription_id) REFERENCES subscriptions(id) ON DELETE CASCADE
);

-- Products table
CREATE TABLE IF NOT EXISTS products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT NOT NULL,
    price DECIMAL(10, 2) NOT NULL,
    currency VARCHAR(3) NOT NULL DEFAULT 'EUR',
    stripe_product_id VARCHAR(100) NOT NULL,
    stripe_price_id VARCHAR(100) NOT NULL,
    type ENUM('small', 'medium', 'large') NOT NULL,
    billing_interval ENUM('month', 'year') NOT NULL DEFAULT 'month',
    active BOOLEAN NOT NULL DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Insert default admin user (password: admin123)
INSERT INTO users (email, password, first_name, last_name, role)
VALUES ('admin@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Admin', 'User', 'admin')
ON DUPLICATE KEY UPDATE id = id;

-- Insert default products
INSERT INTO products (name, description, price, stripe_product_id, stripe_price_id, type, billing_interval)
VALUES 
('Small Plan', 'Basic service package for small businesses', 60.00, 'prod_small_monthly', 'price_small_monthly', 'small', 'month'),
('Medium Plan', 'Enhanced service package for growing businesses', 80.00, 'prod_medium_monthly', 'price_medium_monthly', 'medium', 'month'),
('Large Plan', 'Premium service package for established businesses', 100.00, 'prod_large_monthly', 'price_large_monthly', 'large', 'month'),
('Small Plan (Annual)', 'Basic service package for small businesses, billed annually', 648.00, 'prod_small_yearly', 'price_small_yearly', 'small', 'year'),
('Medium Plan (Annual)', 'Enhanced service package for growing businesses, billed annually', 864.00, 'prod_medium_yearly', 'price_medium_yearly', 'medium', 'year'),
('Large Plan (Annual)', 'Premium service package for established businesses, billed annually', 1080.00, 'prod_large_yearly', 'price_large_yearly', 'large', 'year')
ON DUPLICATE KEY UPDATE id = id;

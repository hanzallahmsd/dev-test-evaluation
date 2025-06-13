# Subscription & Billing Platform

A comprehensive subscription-based service platform with Stripe integration for billing and user management. This application provides a complete solution for managing subscriptions, invoices, and customers with a polished admin interface.

## Features

- User authentication and role-based access control
- Subscription management with Stripe integration
- Admin dashboard with analytics and reporting
- Customer self-onboarding and subscription management
- Invoice generation and payment processing
- Responsive design with modern UI

## Requirements

- Docker and Docker Compose
- PHP 8.1+
- MySQL 8.0+
- Stripe account for payment processing

## Quick Start Guide

### Using Docker (Recommended)

1. Clone the repository:
   ```bash
   git clone https://github.com/hanzallahmsd/dev-test-evaluation.git
   cd dev-test-evaluation
   ```

2. Start the Docker containers:
   ```bash
   docker-compose up -d
   ```

3. The application will be available at http://localhost:8080

4. Access the admin dashboard at http://localhost:8080/admin/dashboard.php
   - Default admin credentials:
     - Email: admin@example.com
     - Password: admin123

### Manual Installation

1. Clone the repository:
   ```bash
   git clone https://github.com/hanzallahmsd/dev-test-evaluation.git
   cd dev-test-evaluation
   ```

2. Install dependencies:
   ```bash
   composer install
   ```

3. Set up the database:
   ```bash
   mysql -u root -p < src/config/schema.sql
   ```

4. Configure your web server to point to the `src/public` directory.

5. Update the database configuration in `src/config/database.php` with your credentials.

6. Run database migrations:
   ```bash
   php src/cli/migrate.php
   ```

## Configuration

### Database Setup

1. Create a MySQL database for the application.
2. Import the schema from `src/config/schema.sql`:
   ```bash
   mysql -u username -p database_name < src/config/schema.sql
   ```
3. Update the database configuration in `src/config/database.php`.

### Stripe Integration

1. Create a Stripe account at https://stripe.com
2. Get your API keys from the Stripe Dashboard
3. Update the configuration in `src/config/stripe.php`:
   ```php
   return [
       'secret_key' => 'your_stripe_secret_key',
       'publishable_key' => 'your_stripe_publishable_key',
       'webhook_secret' => 'your_stripe_webhook_secret',
       'mode' => 'test'  // Change to 'live' for production
   ];
   ```

4. Set up Stripe webhooks to point to `https://yourdomain.com/api/webhooks/stripe.php`

## Application Structure

```
src/
├── classes/
│   ├── Controllers/    # Application controllers
│   ├── Models/         # Database models
│   └── Services/       # Business logic services
├── config/            # Configuration files
├── public/            # Publicly accessible files
│   ├── admin/         # Admin dashboard pages
│   ├── api/           # API endpoints
│   └── assets/        # CSS, JS, and images
└── template/          # Reusable template files
```

## Usage Guide

### Admin Dashboard

1. Access the admin dashboard at `/admin/dashboard.php`
2. Default admin credentials:
   - Email: admin@example.com
   - Password: admin123

3. Admin features include:
   - Dashboard with analytics and key metrics
   - Customer management
   - Subscription management
   - Invoice tracking and reporting
   - System settings

### Customer Management

- **View Customers**: Browse all customers with their subscription status
- **Add Customer**: Create new customer accounts manually
- **Import Customers**: Bulk import customers via CSV file
- **Customer Details**: View detailed information about each customer

### Subscription Management

- **Active Subscriptions**: View and manage all active subscriptions
- **Subscription Plans**: Configure available subscription plans
- **Cancellations**: Process and track subscription cancellations
- **Upgrades/Downgrades**: Handle plan changes

### Invoice Management

- **Recent Invoices**: View recently generated invoices
- **Invoice Status**: Track payment status of all invoices
- **Download Invoices**: Generate PDF invoices for download

## User Portal

Customers can access their own portal to:

1. View their subscription details
2. Manage their subscription (upgrade, downgrade, cancel)
3. View and pay invoices
4. Update payment methods
5. Download invoice PDFs

## Development

### Local Development Environment

1. Start the Docker environment:
   ```bash
   docker-compose up -d
   ```

2. Access the application at http://localhost:8080

3. For database access:
   ```bash
   docker-compose exec db mysql -u root -p
   ```

### Testing Stripe Integration

1. Use Stripe test mode and test cards:
   - Success: 4242 4242 4242 4242
   - Decline: 4000 0000 0000 0002

2. Test webhooks using Stripe CLI:
   ```bash
   stripe listen --forward-to http://localhost:8080/api/webhooks/stripe.php
   ```

## Troubleshooting

### Common Issues

1. **Database Connection Errors**
   - Verify database credentials in `src/config/database.php`
   - Ensure MySQL service is running

2. **Stripe Integration Issues**
   - Confirm API keys are correct
   - Check webhook endpoint configuration
   - Verify SSL is properly configured for production

3. **Docker Issues**
   - Run `docker-compose down -v` and then `docker-compose up -d` to rebuild containers
   - Check Docker logs: `docker-compose logs -f`

## Security Considerations

- All admin pages require authentication and proper role authorization
- Passwords are securely hashed using bcrypt
- Stripe webhooks are verified using the webhook secret
- Input validation is implemented throughout the application
- CSRF protection is enabled for all forms

## License

This project is licensed under the MIT License - see the LICENSE file for details.

# Dev Evaluation Test

A subscription-based service platform with Stripe integration for billing and user management.

## Features

- User authentication and role-based access control
- Subscription management with Stripe integration
- Admin dashboard with analytics
- Customer self-onboarding
- Responsive design with Tailwind CSS

## Requirements

- Docker and Docker Compose
- PHP 8.1+
- MySQL 8.0+
- Stripe account for payment processing

## Installation

### Using Docker (Recommended)

1. Clone the repository:
   ```
   git clone https://github.com/yourusername/dev-evaluation-test.git
   cd dev-evaluation-test
   ```

2. Copy the environment file and update with your settings:
   ```
   cp .env.example .env
   ```
   
3. Update the `.env` file with your Stripe API keys and other configuration.

4. Start the Docker containers:
   ```
   docker-compose up -d
   ```

5. The application will be available at http://localhost

### Manual Installation

1. Clone the repository:
   ```
   git clone https://github.com/yourusername/dev-evaluation-test.git
   cd dev-evaluation-test
   ```

2. Copy the environment file and update with your settings:
   ```
   cp .env.example .env
   ```

3. Install dependencies:
   ```
   composer install
   ```

4. Set up the database:
   ```
   mysql -u root -p < src/config/schema.sql
   ```

5. Configure your web server to point to the `src/public` directory.

## Configuration

### Stripe Integration

1. Create a Stripe account at https://stripe.com
2. Get your API keys from the Stripe Dashboard
3. Update the `.env` file with your Stripe API keys:
   ```
   STRIPE_SECRET_KEY=your_stripe_secret_key
   STRIPE_PUBLISHABLE_KEY=your_stripe_publishable_key
   STRIPE_WEBHOOK_SECRET=your_stripe_webhook_secret
   STRIPE_MODE=test  # Change to 'live' for production
   ```

4. Set up Stripe webhooks to point to `https://yourdomain.com/webhook.php`

### Email Configuration

Update the `.env` file with your email settings:
```
MAIL_HOST=smtp.yourprovider.com
MAIL_PORT=587
MAIL_USERNAME=your_username
MAIL_PASSWORD=your_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=info@yourdomain.com
MAIL_FROM_NAME="Dev Evaluation Test"
```

## Usage

### Admin Access

1. Access the admin dashboard at `/admin/dashboard.php`
2. Default admin credentials:
   - Email: admin@example.com
   - Password: admin123
   
### User Management

- Create new users from the admin panel
- Import users via CSV
- Users can self-register through the signup page

### Subscription Management

- Create and manage subscription products in the admin panel
- Users can subscribe through the pricing page
- Manage subscription status, upgrades, and cancellations

## Development

### Directory Structure

- `src/` - Application source code
  - `public/` - Publicly accessible files
  - `classes/` - PHP classes (Models, Controllers, Services)
  - `config/` - Configuration files
  - `template/` - Template files

### Testing

Run the test suite:
```
vendor/bin/phpunit
```

## Deployment

The application includes a GitHub Actions workflow for CI/CD. To use it:

1. Set up the following secrets in your GitHub repository:
   - `DOCKER_HUB_USERNAME`
   - `DOCKER_HUB_TOKEN`
   - `SSH_HOST`
   - `SSH_USERNAME`
   - `SSH_PRIVATE_KEY`

2. Push to the main branch to trigger the workflow.

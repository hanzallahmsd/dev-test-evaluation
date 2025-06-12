# Dev Test Evaluation - Technical Documentation

## System Overview

The Dev Test Evaluation is a subscription-based SaaS platform that allows customers to subscribe to different service plans. The application is built using PHP with a custom MVC-like architecture, MySQL database, and integrates with Stripe for payment processing. The system is containerized using Docker for easy deployment and development.

## System Architecture

### 1. Application Structure

The application follows a custom MVC-like pattern with the following structure:

```
dev-test-evaluation/
├── docker/                 # Docker configuration files
│   ├── mysql/              # MySQL configuration
│   ├── nginx/              # Nginx web server configuration
│   └── php/                # PHP configuration
├── src/                    # Source code
│   ├── classes/            # PHP classes (Models, Controllers, Services)
│   │   ├── Controllers/    # Application controllers
│   │   ├── Models/         # Data models
│   │   └── Services/       # Service classes
│   ├── cli/                # Command-line scripts
│   ├── config/             # Configuration files
│   ├── public/             # Public web files (entry point)
│   └── template/           # HTML templates
├── .env                    # Environment variables
├── docker-compose.yml      # Docker Compose configuration
├── Dockerfile              # Docker container definition
├── setup.sh                # Setup script
└── deploy.sh               # Deployment script
```

### 2. Technology Stack

- **Backend**: PHP (custom framework)
- **Database**: MySQL 8.0
- **Web Server**: Nginx 1.21
- **Frontend**: HTML, CSS, JavaScript
- **Payment Processing**: Stripe API
- **Containerization**: Docker & Docker Compose
- **Development Tools**: PHPMyAdmin

## Core Components

### 1. Bootstrap System

The `bootstrap.php` file initializes the application by:
- Setting error reporting
- Loading environment variables from `.env`
- Registering an autoloader for PHP classes
- Providing helper functions (config, db, csrf_token, flash messages)
- Establishing database connections
- Starting sessions and implementing CSRF protection

### 2. Database Schema

The database consists of four main tables:

1. **users**: Stores user information including authentication details and Stripe customer IDs
   - Fields: id, email, password, first_name, last_name, role, stripe_customer_id, created_at, updated_at

2. **subscriptions**: Tracks user subscriptions and their status
   - Fields: id, user_id, stripe_subscription_id, plan_type, status, current_period_start, current_period_end, created_at, updated_at

3. **invoices**: Records payment history for subscriptions
   - Fields: id, user_id, subscription_id, stripe_invoice_id, amount, currency, status, invoice_date, created_at

4. **products**: Defines available subscription plans
   - Fields: id, name, description, price, currency, stripe_product_id, stripe_price_id, type, billing_interval, active, created_at, updated_at

### 3. Models

All models extend the `BaseModel` class which provides common database operations:
- `find($id)`: Retrieve a record by ID
- `all()`: Get all records
- `create(array $data)`: Insert a new record
- `update($id, array $data)`: Update an existing record
- `delete($id)`: Delete a record
- `findBy($field, $value)`: Find records by a specific field
- `findOneBy($field, $value)`: Find a single record by a specific field

Specific models include:
- **User**: Handles user authentication and profile management
- **Product**: Manages subscription plans and pricing
- **Subscription**: Tracks user subscriptions and their status
- **Invoice**: Records payment history

### 4. Controllers

Controllers handle the application logic and request processing:
- **AuthController**: Manages user authentication (login, registration, password reset)
- **AdminController**: Handles administrative functions (user management, subscription oversight)
- **SubscriptionController**: Processes subscription-related actions and Stripe webhook events

### 5. Services

Service classes encapsulate complex business logic:
- **StripeService**: Handles interactions with the Stripe API for payment processing
- **CsvImportService**: Manages data import from CSV files

### 6. Frontend

The frontend uses HTML, CSS, and JavaScript with:
- Custom CSS for styling (recently migrated from Tailwind CSS)
- Font Awesome for icons
- Google Fonts (Poppins) for typography
- Chart.js for analytics in the admin dashboard

## Key Workflows

### 1. User Registration & Authentication

1. Users register via the registration form
2. The AuthController validates input and creates a new user record
3. Passwords are hashed using PHP's password_hash function
4. Login authentication compares hashed passwords
5. User sessions are managed via PHP sessions

### 2. Subscription Management

1. Available plans are displayed on the pricing page
2. Users select a plan and are directed to checkout
3. The SubscriptionController creates a Stripe checkout session
4. After successful payment, Stripe sends webhook events
5. The application processes these events to update subscription status
6. Users can manage their subscriptions via a customer portal

### 3. Payment Processing

1. Payments are handled entirely through Stripe
2. The StripeService class interfaces with the Stripe API
3. Webhook events from Stripe update local database records
4. Invoices are generated and stored for accounting purposes

### 4. Admin Dashboard

1. Administrators can log in with admin credentials
2. The admin dashboard provides analytics and management tools
3. Admins can view and manage users, subscriptions, and invoices
4. The dashboard includes data visualization using Chart.js

## Deployment & Infrastructure

### 1. Docker Setup

The application is containerized using Docker with four main services:
- **app**: PHP application container
- **nginx**: Web server container
- **mysql**: Database container
- **phpmyadmin**: Database administration tool (optional)

Docker Compose orchestrates these containers and manages networking between them.

### 2. Environment Configuration

The `.env` file contains environment-specific configuration:
- Database credentials
- Application settings
- Stripe API keys
- Email configuration

### 3. Setup & Deployment

- **setup.sh**: Initializes the development environment
  - Checks for Docker and Docker Compose
  - Creates the .env file if it doesn't exist
  - Builds and starts Docker containers
  - Waits for MySQL to be ready

- **deploy.sh**: Handles production deployment
  - Pulls the latest code from the repository
  - Builds and restarts containers
  - Runs database migrations if needed

## Security Considerations

1. **Authentication**: Passwords are hashed using PHP's password_hash function
2. **CSRF Protection**: Forms include CSRF tokens to prevent cross-site request forgery
3. **Input Validation**: User inputs are validated and sanitized
4. **Payment Security**: Payment processing is delegated to Stripe
5. **Environment Variables**: Sensitive information is stored in environment variables

## Development Guidelines

1. **Models**: Extend BaseModel for database operations
2. **Controllers**: Keep business logic in controllers or dedicated services
3. **Templates**: Use PHP templates for view rendering
4. **CSS**: Use the custom CSS framework in `src/public/assets/css/style.css`
5. **Configuration**: Store configuration in appropriate files under `src/config/`

## Troubleshooting

1. **Database Connection Issues**: Check MySQL container status and credentials in .env
2. **Payment Processing Errors**: Verify Stripe API keys and webhook configuration
3. **Docker Issues**: Use `docker-compose logs` to view container logs
4. **PHP Errors**: Check PHP error logs in the app container

## Conclusion

The Dev Test Evaluation system provides a complete subscription management platform with user authentication, payment processing, and administrative tools. The modular architecture allows for easy maintenance and extension of functionality.

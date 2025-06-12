#!/bin/bash

# Dev Evaluation Test Setup Script

echo "Setting up Dev Evaluation Test..."

# Check if Docker is installed
if ! command -v docker &> /dev/null; then
    echo "Docker is not installed. Please install Docker and try again."
    exit 1
fi

if ! command -v docker-compose &> /dev/null; then
    echo "Docker Compose is not installed. Please install Docker Compose and try again."
    exit 1
fi

# Copy environment file if it doesn't exist
if [ ! -f .env ]; then
    echo "Creating .env file from .env.example..."
    cp .env.example .env
    echo "Please update the .env file with your configuration."
fi

# Build and start Docker containers
echo "Building and starting Docker containers..."
docker-compose up -d --build

# Wait for MySQL to be ready
echo "Waiting for MySQL to be ready..."
sleep 10

# Run database migrations
echo "Running database migrations..."
docker-compose exec app php src/cli/migrate.php

# Create default admin user
echo "Creating default admin user..."
docker-compose exec app php src/cli/create-admin.php

echo "Setup complete! The application is now running at http://localhost"
echo "Admin dashboard is available at http://localhost/admin/dashboard.php"
echo "Default admin credentials: admin@example.com / admin123"

#!/bin/bash

# Dev Evaluation Test Deployment Script

# Check command line arguments
if [ "$#" -ne 1 ]; then
    echo "Usage: $0 <environment>"
    echo "Environments: production, staging"
    exit 1
fi

ENVIRONMENT=$1
TIMESTAMP=$(date +%Y%m%d%H%M%S)

echo "Deploying to $ENVIRONMENT environment..."

# Load environment variables
if [ -f .env ]; then
    export $(grep -v '^#' .env | xargs)
else
    echo "Error: .env file not found"
    exit 1
fi

# Build Docker images
echo "Building Docker images..."
docker-compose build

# Tag Docker images
echo "Tagging Docker images..."
docker tag monthly-service-app:latest monthly-service-app:$TIMESTAMP
docker tag monthly-service-nginx:latest monthly-service-nginx:$TIMESTAMP

# Push Docker images to registry
echo "Pushing Docker images to registry..."
docker push monthly-service-app:latest
docker push monthly-service-app:$TIMESTAMP
docker push monthly-service-nginx:latest
docker push monthly-service-nginx:$TIMESTAMP

# Deploy to environment
echo "Deploying to $ENVIRONMENT server..."
case $ENVIRONMENT in
    production)
        SSH_HOST=$PROD_SSH_HOST
        SSH_USER=$PROD_SSH_USER
        SSH_KEY=$PROD_SSH_KEY
        DEPLOY_PATH=$PROD_DEPLOY_PATH
        ;;
    staging)
        SSH_HOST=$STAGING_SSH_HOST
        SSH_USER=$STAGING_SSH_USER
        SSH_KEY=$STAGING_SSH_KEY
        DEPLOY_PATH=$STAGING_DEPLOY_PATH
        ;;
    *)
        echo "Error: Invalid environment. Use 'production' or 'staging'"
        exit 1
        ;;
esac

# Deploy using SSH
ssh -i $SSH_KEY $SSH_USER@$SSH_HOST << EOF
    cd $DEPLOY_PATH
    docker-compose pull
    docker-compose down
    docker-compose up -d
    
    # Run migrations if needed
    docker-compose exec -T app php src/cli/migrate.php
    
    # Clear cache
    docker-compose exec -T app php src/cli/clear-cache.php
    
    # Check deployment status
    docker-compose ps
EOF

echo "Deployment to $ENVIRONMENT completed successfully!"

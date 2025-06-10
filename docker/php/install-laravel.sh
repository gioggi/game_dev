#!/bin/bash

# Navigate to the project directory
cd /var/www/html

# Check if Laravel is already installed
if [ -f "artisan" ]; then
    echo "Laravel is already installed. Running composer install..."
    composer install
else
    echo "Installing Laravel..."
    # Install Laravel using the existing composer.json
    composer install
    
    # Generate application key
    php artisan key:generate
    
    # Set permissions
    chown -R www-data:www-data /var/www/html
    chmod -R 755 /var/www/html/storage
fi

# Run migrations
php artisan migrate --force

echo "Laravel installation completed!"
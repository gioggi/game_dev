#!/bin/bash

# Start cron service
service cron start

# Run Laravel installation script
/usr/local/bin/install-laravel.sh

# Check if the command is the scheduler
if [[ "$1" == "php" && "$2" == "artisan" && "$3" == "schedule:work" ]]; then
    # For scheduler, just run the command directly
    echo "Running Laravel scheduler in work mode..."
    exec "$@"
elif [ $# -eq 0 ]; then
    # If no command is passed, start PHP-FPM
    exec php-fpm
else
    # Execute any other command passed to the container
    exec "$@"
fi

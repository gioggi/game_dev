FROM php:8.2-fpm

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    cron

# Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd

# Get latest Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Copy installation script
COPY install-laravel.sh /usr/local/bin/install-laravel.sh
RUN chmod +x /usr/local/bin/install-laravel.sh

# Set up entrypoint
COPY entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

# Add crontab file
RUN echo "* * * * * cd /var/www/html && php artisan schedule:run >> /dev/null 2>&1" > /etc/cron.d/laravel-scheduler

# Give execution rights on the cron job
RUN chmod 0644 /etc/cron.d/laravel-scheduler

# Apply cron job
RUN crontab /etc/cron.d/laravel-scheduler

# Set entrypoint
ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]

# Start cron service
RUN service cron start

# Expose port 9000
EXPOSE 9000

# Start PHP-FPM
CMD ["php-fpm"]

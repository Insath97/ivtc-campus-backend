#!/bin/bash

# Install dependencies if vendor directory doesn't exist
if [ ! -d "vendor" ]; then
    composer install --no-interaction --optimize-autoloader --no-dev
fi

# Generate key if not set
if [ -z "$APP_KEY" ]; then
    php artisan key:generate
fi

# Wait for database
echo "Waiting for database..."
until php artisan db:monitor; do
  >&2 echo "MySQL is unavailable - sleeping"
  sleep 1
done

echo "MySQL is up - executing command"

# Run migrations
php artisan migrate --force

# Start PHP-FPM
php-fpm

#!/bin/sh
set -e

# Ensure writable directories exist with correct permissions
chown -R www-data:www-data /var/www/html/storage
chown -R www-data:www-data /var/www/html/bootstrap/cache

if [ "$APP_ENV" = "production" ]; then
    echo "Running production migrations..."
    php artisan migrate --force --no-ansi
else
    echo "Running local migrations..."
    php artisan migrate --no-ansi
fi

echo "Starting nginx and php-fpm..."
# Start php-fpm and nginx directly (no supervisord)
php-fpm &
nginx -g "daemon off;"

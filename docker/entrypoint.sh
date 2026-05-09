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

echo "Starting nginx, php-fpm, and Reverb WebSocket..."

# Start php-fpm in background
php-fpm &

# Start Reverb WebSocket server in background (if enabled)
if [ "$BROADCAST_CONNECTION" = "reverb" ]; then
    echo "Starting Reverb on port 8080..."
    php artisan reverb:start --host=0.0.0.0 --port=8080 &
fi

# Start nginx (blocks)
nginx -g "daemon off;"

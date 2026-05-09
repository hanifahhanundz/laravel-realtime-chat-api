#!/bin/sh
set -e

# Ensure SQLite database file exists
if [ "$DB_CONNECTION" = "sqlite" ]; then
    mkdir -p /var/www/html/database
    touch /var/www/html/database/database.sqlite
fi

if [ "$APP_ENV" = "production" ]; then
    echo "Running production migrations..."
    php artisan migrate --force --no-ansi

    if [ "$BROADCAST_CONNECTION" = "reverb" ]; then
        echo "Starting Reverb WebSocket server..."
        php artisan reverb:start --host=0.0.0.0 --port=8080 &
    fi
else
    echo "Running local migrations..."
    php artisan migrate --no-ansi
fi

echo "Starting services..."
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf

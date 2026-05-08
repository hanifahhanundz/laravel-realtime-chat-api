#!/bin/bash
set -e

# For local dev: run migrations and start services
# For production: use environment variables to determine mode

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

#!/bin/bash
set -e

echo "Running migrations..."
php artisan migrate --force --no-ansi

echo "Starting Reverb WebSocket server..."
php artisan reverb:start --host=0.0.0.0 --port=8080 &

echo "Starting supervisor services (nginx + php-fpm)..."
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf

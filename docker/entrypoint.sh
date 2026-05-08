#!/bin/bash
set -e

echo "Running migrations..."
php artisan migrate --force

# Reverb start in background
echo "Starting Reverb..."
php artisan reverb:start --host=0.0.0.0 &

echo "Starting services..."
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf

#!/bin/bash

# Optimasi Laravel
echo "Running Laravel optimizations..."

# Check if APP_KEY is set, if not, generate it if in production (optional)
if [ -z "$APP_KEY" ]; then
    echo "Warning: APP_KEY is not set. Generating one..."
    php artisan key:generate --show
fi

php artisan optimize
php artisan view:cache
php artisan config:cache
php artisan route:cache

# Jalankan migrasi database otomatis jika DB tersedia
echo "Running database migrations..."
php artisan migrate --force

# Start Apache in foreground
echo "Starting Apache..."
exec apache2-foreground

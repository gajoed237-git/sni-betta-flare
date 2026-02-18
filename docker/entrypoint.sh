#!/bin/bash

# Optimasi Laravel
echo "Running Laravel optimizations..."
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

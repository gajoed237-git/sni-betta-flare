#!/bin/bash

log() {
    echo "$(date '+%Y-%m-%d %H:%M:%S') - $1"
}

log "Starting entrypoint..."

# Pastikan folder permission aman
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Tunggu database
log "Waiting for database $DB_HOST:$DB_PORT..."

MAX_RETRIES=30
COUNT=0
until timeout 1 bash -c "cat < /dev/null > /dev/tcp/$DB_HOST/$DB_PORT" 2>/dev/null || [ $COUNT -eq $MAX_RETRIES ]; do
    sleep 2
    COUNT=$((COUNT + 1))
    log "Waiting DB... ($COUNT/$MAX_RETRIES)"
done

if [ $COUNT -eq $MAX_RETRIES ]; then
    log "Database not reachable. Continuing anyway..."
else
    log "Database connected."
fi

# Jalankan migrate
log "Running migrations..."
php artisan migrate --force || log "Migration failed."

# Clear cache dulu (aman)
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Cache ulang hanya kalau production
if [ "$APP_ENV" = "production" ]; then
    log "Caching config & routes..."
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache
fi

log "Starting Apache..."
exec apache2-foreground

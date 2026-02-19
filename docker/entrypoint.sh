#!/bin/bash

# Function to log with timestamp
log() {
    echo "$(date '+%Y-%m-%d %H:%M:%S') - $1"
}

log "Starting entrypoint script..."

# 1. Optimasi Laravel
log "Running Laravel optimizations..."

# Check if APP_KEY is set
if [ -z "$APP_KEY" ]; then
    log "Warning: APP_KEY is not set. Generating one dynamically..."
    php artisan key:generate --show --no-interaction
fi

php artisan optimize
php artisan view:cache
php artisan config:cache
php artisan route:cache

# 2. Tunggu Database (Wait for DB)
log "Waiting for database host: $DB_HOST:$DB_PORT ..."
MAX_RETRIES=30
COUNT=0
until timeout 1 bash -c "cat < /dev/null > /dev/tcp/$DB_HOST/$DB_PORT" 2>/dev/null || [ $COUNT -eq $MAX_RETRIES ]; do
    sleep 2
    COUNT=$((COUNT + 1))
    log "Still waiting for database ($COUNT/$MAX_RETRIES)..."
done

if [ $COUNT -eq $MAX_RETRIES ]; then
    log "Error: Database host $DB_HOST:$DB_PORT is not reachable after $MAX_RETRIES attempts."
    log "Proceeding anyway, but migrations might fail."
else
    log "Database host is reachable!"
fi

# 3. Jalankan migrasi database
log "Running database migrations..."
if php artisan migrate --force; then
    log "Migrations completed successfully."
else
    log "Error: Migrations failed. Check your database credentials and connectivity."
fi

# 4. Start Apache
log "Starting Apache in foreground..."
exec apache2-foreground

#!/bin/sh
set -e

# Fix permissions for storage and cache directories
# We use '|| true' to prevent failure if permissions cannot be changed (e.g. some bind mounts)
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache || true
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache || true

# Check if .env exists, if not and .env.example exists, copy it (safety fallback)
if [ ! -f .env ] && [ -f .env.example ]; then
    cp .env.example .env
fi

# If APP_KEY is missing, generate it
if grep -q "APP_KEY=" .env && [ -z "$(grep "APP_KEY=" .env | cut -d '=' -f 2)" ]; then
    php artisan key:generate
fi

# Run database migrations
# We use --force to run migrations in production environment without prompt
php artisan migrate --force

# Execute the main command
exec "$@"

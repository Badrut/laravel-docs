#!/bin/bash
set -e

# Buat storage link jika belum ada
if [ ! -L /var/www/public/storage ]; then
    php artisan storage:link --quiet || true
fi

# Fix permission folder storage dan bootstrap/cache
chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache
chmod -R 775 /var/www/storage /var/www/bootstrap/cache

exec "$@"

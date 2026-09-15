#!/bin/sh
set -e

# Pastikan direktori storage & cache lengkap dan writable
mkdir -p /var/www/html/storage/framework/sessions \
         /var/www/html/storage/framework/views \
         /var/www/html/storage/framework/cache \
         /var/www/html/storage/logs \
         /var/www/html/bootstrap/cache

chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Jalankan auto-setup hanya pada container utama (php-fpm)
if [ "$1" = "php-fpm" ]; then
    # Storage link
    php artisan storage:link || true

    # Migration database otomatis
    php artisan migrate --force || true
fi

exec "$@"

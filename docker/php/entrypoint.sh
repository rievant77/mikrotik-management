#!/bin/sh
set -e

# 1. Pastikan direktori storage & cache lengkap dan writable
mkdir -p /var/www/html/storage/framework/sessions \
         /var/www/html/storage/framework/views \
         /var/www/html/storage/framework/cache \
         /var/www/html/storage/logs \
         /var/www/html/bootstrap/cache

chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# 2. Pulihkan aset Vite jika mounted volume dari host dalam kondisi kosong
if [ ! -f /var/www/html/public/build/manifest.json ] && [ -d /opt/build ]; then
    echo "[Entrypoint] Memulihkan aset Vite ke /var/www/html/public/build..."
    mkdir -p /var/www/html/public/build
    cp -r /opt/build/* /var/www/html/public/build/
fi

# 3. Pulihkan Composer vendor jika mounted volume dari host belum memiliki vendor
if [ ! -f /var/www/html/vendor/autoload.php ] && [ -d /opt/vendor ]; then
    echo "[Entrypoint] Memulihkan vendor Composer ke /var/www/html/vendor..."
    mkdir -p /var/www/html/vendor
    cp -r /opt/vendor/* /var/www/html/vendor/
fi

# 4. Jalankan auto-setup hanya pada container utama (php-fpm)
if [ "$1" = "php-fpm" ]; then
    # Storage symlink
    php artisan storage:link || true

    # Migration database otomatis
    php artisan migrate --force || true
fi

exec "$@"

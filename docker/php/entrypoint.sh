#!/bin/sh
set -e

# 1. Pastikan direktori storage & cache lengkap dan writable
mkdir -p /app/storage/framework/sessions \
         /app/storage/framework/views \
         /app/storage/framework/cache \
         /app/storage/logs \
         /app/bootstrap/cache

chown -R www-data:www-data /app/storage /app/bootstrap/cache
chmod -R 775 /app/storage /app/bootstrap/cache

# 2. Pulihkan aset Vite jika mounted volume dari host dalam kondisi kosong
if [ ! -f /app/public/build/manifest.json ] && [ -d /opt/build ]; then
    echo "[Entrypoint] Memulihkan aset Vite ke /app/public/build..."
    mkdir -p /app/public/build
    cp -r /opt/build/* /app/public/build/
fi

# 3. Pulihkan Composer vendor jika mounted volume dari host belum memiliki vendor
if [ ! -f /app/vendor/autoload.php ] && [ -d /opt/vendor ]; then
    echo "[Entrypoint] Memulihkan vendor Composer ke /app/vendor..."
    mkdir -p /app/vendor
    cp -r /opt/vendor/* /app/vendor/
fi

# 4. Jalankan auto-setup hanya pada web server utama (frankenphp)
if [ "$1" = "frankenphp" ]; then
    # Storage symlink
    php artisan storage:link || true

    # Migration database otomatis
    php artisan migrate --force || true
fi

exec "$@"

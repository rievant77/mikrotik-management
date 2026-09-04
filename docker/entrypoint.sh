#!/bin/sh

echo "==> Starting MikroTik Management Container..."

# 1. Ensure system runtime & log directories exist (Critical for Alpine Nginx & Supervisord)
mkdir -p /run/nginx /var/log/nginx /var/lib/nginx/tmp /var/log/supervisor
chown -R www-data:www-data /run/nginx /var/log/nginx /var/lib/nginx /var/log/supervisor 2>/dev/null || true
chmod -R 775 /run/nginx /var/log/nginx /var/lib/nginx /var/log/supervisor 2>/dev/null || true

# 2. Ensure .env exists
if [ ! -f /var/www/html/.env ]; then
    echo "==> Creating .env from .env.example..."
    cp /var/www/html/.env.example /var/www/html/.env
fi

# 3. Ensure SQLite database directory & file exist
mkdir -p /var/www/html/database
if [ ! -f /var/www/html/database/database.sqlite ]; then
    echo "==> Initializing SQLite database file..."
    touch /var/www/html/database/database.sqlite
fi

# 4. Ensure upload, cache, session & log directories exist
mkdir -p /var/www/html/public/uploads/avatars
mkdir -p /var/www/html/public/uploads/branding
mkdir -p /var/www/html/storage/framework/cache/data
mkdir -p /var/www/html/storage/framework/sessions
mkdir -p /var/www/html/storage/framework/views
mkdir -p /var/www/html/storage/logs
mkdir -p /var/www/html/bootstrap/cache

# 5. Fix permissions before artisan commands
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache /var/www/html/database /var/www/html/public/uploads /var/www/html/.env 2>/dev/null || true
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache /var/www/html/database /var/www/html/public/uploads 2>/dev/null || true

# 6. Generate Application Key if not present
if [ -z "$APP_KEY" ] && ! grep -q "APP_KEY=base64:" /var/www/html/.env 2>/dev/null; then
    echo "==> Generating Application Key..."
    php /var/www/html/artisan key:generate --force || true
fi

# 7. Run Database Migrations & Seeds
if [ "$DB_CONNECTION" = "mysql" ]; then
    echo "==> Checking MySQL connection..."
    max_tries=30
    count=0
    until php -r "try { new PDO('mysql:host='.getenv('DB_HOST').';port='.(getenv('DB_PORT') ?: 3306).';dbname='.getenv('DB_DATABASE'), getenv('DB_USERNAME'), getenv('DB_PASSWORD')); exit(0); } catch(Exception \$e) { exit(1); }" 2>/dev/null || [ $count -ge $max_tries ]; do
        echo "==> Waiting for MySQL database to become available ($count/$max_tries)..."
        sleep 2
        count=$((count+1))
    done
fi

echo "==> Running database migrations..."
php /var/www/html/artisan migrate --force || true

echo "==> Seeding initial administrator account if needed..."
php /var/www/html/artisan db:seed --force 2>/dev/null || true

# 8. Ensure Storage Symlink
php /var/www/html/artisan storage:link 2>/dev/null || true

# 9. Optimize Cache in Production
if [ "$APP_ENV" = "production" ]; then
    echo "==> Optimizing configuration & route cache..."
    php /var/www/html/artisan config:cache 2>/dev/null || true
    php /var/www/html/artisan route:cache 2>/dev/null || true
    php /var/www/html/artisan view:cache 2>/dev/null || true
fi

# 10. Ensure Final Ownership & Permissions
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache /var/www/html/database /var/www/html/public/uploads
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache /var/www/html/database /var/www/html/public/uploads

echo "==> Application ready! Launching Supervisord..."
exec "$@"

#!/bin/sh

set -eu

APP_DIR=/var/www/html
PORT="${PORT:-8080}"

echo "======================================"
echo "Starting Laravel application"
echo "PHP version:"
php -v | head -n 1
echo "Railway PORT: ${PORT}"
echo "======================================"

# ============================================================
# APACHE MPM
# Pastikan hanya satu MPM yang aktif
# ============================================================

a2dismod mpm_event 2>/dev/null || true
a2dismod mpm_worker 2>/dev/null || true
a2dismod mpm_mpmt 2>/dev/null || true
a2dismod mpm_prefork 2>/dev/null || true

a2enmod mpm_prefork
a2enmod rewrite

# ============================================================
# APP KEY
# ============================================================

if [ -z "${APP_KEY:-}" ]; then
    echo "ERROR: APP_KEY belum disediakan."
    echo "Set APP_KEY pada Railway Variables."
    exit 1
fi

# ============================================================
# LARAVEL DIRECTORIES
# ============================================================

mkdir -p \
    "$APP_DIR/storage/framework/cache" \
    "$APP_DIR/storage/framework/sessions" \
    "$APP_DIR/storage/framework/views" \
    "$APP_DIR/storage/logs" \
    "$APP_DIR/bootstrap/cache"

# ============================================================
# STORAGE LINK
# ============================================================

if [ ! -L "$APP_DIR/public/storage" ]; then
    php "$APP_DIR/artisan" storage:link --force \
        >/dev/null 2>&1 || true
fi

# ============================================================
# PERMISSIONS
# ============================================================

chown -R www-data:www-data \
    "$APP_DIR/storage" \
    "$APP_DIR/bootstrap/cache"

chmod -R ug+rwX \
    "$APP_DIR/storage" \
    "$APP_DIR/bootstrap/cache"

# ============================================================
# APACHE PORT
# ============================================================

sed -i \
    "s/^Listen .*/Listen ${PORT}/" \
    /etc/apache2/ports.conf

sed -i \
    "s/<VirtualHost \*:80>/<VirtualHost *:${PORT}>/g" \
    /etc/apache2/sites-available/000-default.conf

# ============================================================
# LARAVEL CACHE
# ============================================================

if [ -f "$APP_DIR/artisan" ]; then
    php "$APP_DIR/artisan" config:clear || true
    php "$APP_DIR/artisan" route:clear || true
    php "$APP_DIR/artisan" view:clear || true
fi

# ============================================================
# APACHE CONFIG TEST
# ============================================================

echo "======================================"
echo "Checking Apache configuration..."
echo "======================================"

apache2ctl -t

echo "======================================"
echo "Apache configuration OK"
echo "Listening on port: ${PORT}"
echo "======================================"

# ============================================================
# START
# ============================================================

exec "$@"
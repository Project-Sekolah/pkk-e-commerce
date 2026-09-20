#!/bin/sh

set -eu

APP_DIR=/var/www/html
PORT="${PORT:-80}"

echo "======================================"
echo "Starting Laravel application"
echo "PHP version:"
php -v | head -n 1
echo "Railway PORT: ${PORT}"
echo "======================================"

# ============================================================
# APACHE MPM
# Pastikan hanya mpm_prefork aktif
# ============================================================

rm -f \
    /etc/apache2/mods-enabled/mpm_event.load \
    /etc/apache2/mods-enabled/mpm_event.conf \
    /etc/apache2/mods-enabled/mpm_worker.load \
    /etc/apache2/mods-enabled/mpm_worker.conf \
    /etc/apache2/mods-enabled/mpm_mpmt.load \
    /etc/apache2/mods-enabled/mpm_mpmt.conf \
    /etc/apache2/mods-enabled/mpm_prefork.load \
    /etc/apache2/mods-enabled/mpm_prefork.conf

ln -s /etc/apache2/mods-available/mpm_prefork.load \
    /etc/apache2/mods-enabled/mpm_prefork.load

ln -s /etc/apache2/mods-available/mpm_prefork.conf \
    /etc/apache2/mods-enabled/mpm_prefork.conf


a2enmod rewrite

# ============================================================
# APP KEY
# ============================================================

if [ -z "${APP_KEY:-}" ]; then
    echo "ERROR: APP_KEY belum disediakan."
    echo "Set APP_KEY pada Railway Variables."
    exit 1
fi

case "$PORT" in
    ''|*[!0-9]*)
        echo "ERROR: PORT harus berupa angka, nilai saat ini: ${PORT}"
        exit 1
        ;;
esac

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

if grep -qE '^Listen ' /etc/apache2/ports.conf; then
    sed -i -E "s/^Listen .*/Listen ${PORT}/" /etc/apache2/ports.conf
else
    printf '\nListen %s\n' "$PORT" >> /etc/apache2/ports.conf
fi

sed -i -E "s/<VirtualHost \*:[0-9]+>/<VirtualHost *:${PORT}>/g" \
    /etc/apache2/sites-available/000-default.conf

# ============================================================
# LARAVEL CACHE
# ============================================================

if [ -f "$APP_DIR/artisan" ]; then
    php "$APP_DIR/artisan" config:clear || true
    php "$APP_DIR/artisan" route:clear || true
    php "$APP_DIR/artisan" view:clear || true
    php "$APP_DIR/artisan" package:discover --ansi

    if [ "${RUN_MIGRATIONS:-true}" = "true" ]; then
        echo "Running database migrations..."
        php "$APP_DIR/artisan" migrate --force
    fi
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
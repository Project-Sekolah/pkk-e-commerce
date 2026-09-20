#!/bin/sh
set -eu

APP_DIR=/var/www/html
PORT="${PORT:-80}"

if [ -z "${APP_KEY:-}" ]; then
    echo "APP_KEY belum disediakan. Set environment APP_KEY pada platform/container; file .env tidak dibundel ke image."
    exit 1
fi

mkdir -p \
    "$APP_DIR/storage/framework/cache" \
    "$APP_DIR/storage/framework/sessions" \
    "$APP_DIR/storage/framework/views" \
    "$APP_DIR/storage/logs" \
    "$APP_DIR/bootstrap/cache"

if [ ! -L "$APP_DIR/public/storage" ]; then
    php artisan storage:link --force >/dev/null 2>&1 || true
fi

chown -R www-data:www-data "$APP_DIR/storage" "$APP_DIR/bootstrap/cache"
chmod -R ug+rwX "$APP_DIR/storage" "$APP_DIR/bootstrap/cache"

if [ "$PORT" != "80" ]; then
    sed -i "s/^Listen 80$/Listen ${PORT}/" /etc/apache2/ports.conf
    sed -i "s/<VirtualHost \*:80>/<VirtualHost *:${PORT}>/" /etc/apache2/sites-available/000-default.conf
fi

exec "$@"

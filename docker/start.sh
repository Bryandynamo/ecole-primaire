#!/bin/sh
set -e

# Ensure storage and cache dirs exist and are writable
mkdir -p /var/www/html/storage/framework/{cache,sessions,views}
mkdir -p /var/www/html/bootstrap/cache
chown -R www:www /var/www/html/storage /var/www/html/bootstrap/cache || true

# Ensure SQLite database exists when using sqlite connection
if [ "${DB_CONNECTION}" = "sqlite" ]; then
  DB_PATH="${DB_DATABASE:-/var/www/html/database/database.sqlite}"
  DB_DIR=$(dirname "$DB_PATH")
  mkdir -p "$DB_DIR"
  if [ ! -f "$DB_PATH" ]; then
    touch "$DB_PATH"
  fi
  chown -R www:www "$DB_DIR" || true
fi

# Laravel optimizations (do not fail the container if artisan isn't ready yet)
if [ -f /var/www/html/artisan ]; then
  php /var/www/html/artisan config:cache || true
  php /var/www/html/artisan route:cache || true
  php /var/www/html/artisan view:cache || true
  # Storage symlink (ignore error if already exists)
  php /var/www/html/artisan storage:link || true
fi

# Start supervisor (php-fpm + nginx)
exec /usr/bin/supervisord -c /etc/supervisord.conf

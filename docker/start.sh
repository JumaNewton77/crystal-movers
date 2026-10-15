#!/bin/sh
set -e

# Listen on the port the hosting platform assigns (Railway sets PORT); 8080 when run locally.
PORT="${PORT:-8080}"
sed -ri "s/Listen [0-9]+/Listen ${PORT}/" /etc/apache2/ports.conf
sed -ri "s/<VirtualHost \*:[0-9]+>/<VirtualHost *:${PORT}>/" /etc/apache2/sites-available/000-default.conf

# A mounted volume starts empty, so make sure the storage folders exist and are writable.
mkdir -p storage/app/public storage/app/private storage/framework/cache/data storage/framework/sessions storage/framework/views storage/logs
chown -R www-data:www-data storage bootstrap/cache

# Public uploads are served through /storage -> storage/app/public.
php artisan storage:link --force >/dev/null 2>&1 || true

exec apache2-foreground

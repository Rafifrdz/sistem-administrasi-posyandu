#!/bin/sh
set -e

cd /var/www/html

echo "[deploy] mendaftarkan paket Laravel..."
php artisan package:discover --ansi || true

echo "[deploy] menyiapkan storage..."
php artisan storage:link || true

echo "[deploy] menjalankan migrasi..."
php artisan migrate --force

echo "[deploy] memeriksa data awal..."
COUNT=$(php -r '
require "vendor/autoload.php";
$app = require "bootstrap/app.php";
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
echo \App\Models\User::count();
' 2>/dev/null | tr -dc '0-9')

if [ -z "$COUNT" ] || [ "$COUNT" = "0" ]; then
    echo "[deploy] database kosong -> mengisi data contoh"
    php artisan db:seed --force || echo "[deploy] seed gagal, lanjut saja"
else
    echo "[deploy] data sudah ada ($COUNT pengguna), seed dilewati"
fi

echo "[deploy] memanaskan cache..."
php artisan config:cache
php artisan route:cache || true
php artisan view:cache || true

# File cache dibuat oleh root, kembalikan ke www-data
chown -R www-data:www-data storage bootstrap/cache || true

# Render memberi port lewat env PORT (default 80 kalau tidak ada)
PORT="${PORT:-80}"
if [ "$PORT" != "80" ]; then
    echo "[deploy] menyesuaikan Apache ke port $PORT"
    sed -i "s/^Listen 80$/Listen ${PORT}/" /etc/apache2/ports.conf
    sed -i "s/<VirtualHost \*:80>/<VirtualHost *:${PORT}>/" /etc/apache2/sites-available/000-default.conf
fi

echo "[deploy] siap. Menjalankan Apache di port ${PORT}..."
exec apache2-foreground

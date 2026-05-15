#!/bin/sh
set -e

cd /var/www/html

# Aguarda MySQL estar pronto
echo "Aguardando banco de dados..."
until php artisan db:show > /dev/null 2>&1; do
    sleep 2
done
echo "Banco de dados pronto."

# Roda migrations
php artisan migrate --force

# Optimiza para produção
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

# Cria link simbólico de storage (se não existir)
php artisan storage:link 2>/dev/null || true

# Inicia supervisord
exec /usr/bin/supervisord -c /etc/supervisord.conf

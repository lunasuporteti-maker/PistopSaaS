#!/bin/sh
set -e

cd /var/www/html

# Aguarda PostgreSQL estar pronto
echo "Aguardando banco de dados..."
until php artisan db:show > /dev/null 2>&1; do
    echo "DB não disponível ainda, aguardando..."
    sleep 3
done
echo "Banco de dados pronto."

# Roda migrations
php artisan migrate --force

# Optimiza para produção (|| true = não falha se der erro)
php artisan config:cache
php artisan route:cache || echo "⚠️  route:cache ignorado"
php artisan view:cache  || echo "⚠️  view:cache ignorado"
php artisan event:cache || echo "⚠️  event:cache ignorado"

# Roda seeders
php artisan db:seed --force || echo "⚠️  seed ignorado (já executado)"

# Cria link simbólico de storage (se não existir)
php artisan storage:link 2>/dev/null || true

# Inicia supervisord
exec /usr/bin/supervisord -c /etc/supervisord.conf

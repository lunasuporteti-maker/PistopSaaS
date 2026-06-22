#!/bin/sh
set -e

cd /var/www/html

echo "=== PitStop entrypoint ==="
echo "DB_HOST=${DB_HOST} | DB_PORT=${DB_PORT:-5432} | DB_DATABASE=${DB_DATABASE} | DB_USERNAME=${DB_USERNAME}"
echo "APP_ENV=${APP_ENV} | APP_KEY definida: $([ -n "$APP_KEY" ] && echo SIM || echo NAO)"

# Aguarda PostgreSQL via pg_isready (teste TCP direto, sem PHP)
echo "Aguardando banco de dados em ${DB_HOST}:${DB_PORT:-5432}..."
MAX=30
COUNT=0
until pg_isready -h "${DB_HOST}" -p "${DB_PORT:-5432}" -U "${DB_USERNAME}" -d "${DB_DATABASE}" -t 3 2>/dev/null; do
    COUNT=$((COUNT + 1))
    if [ "$COUNT" -ge "$MAX" ]; then
        echo "ERRO: banco não acessível após ${MAX} tentativas."
        echo "Verifique: DB_HOST=${DB_HOST} DB_PORT=${DB_PORT:-5432}"
        exit 1
    fi
    echo "Aguardando DB... (${COUNT}/${MAX})"
    sleep 3
done
echo "Banco de dados pronto."

# Roda migrations
php artisan migrate --force

# Optimiza para produção
php artisan config:cache
php artisan route:cache || echo "⚠️  route:cache ignorado"
php artisan view:cache  || echo "⚠️  view:cache ignorado"
php artisan event:cache || echo "⚠️  event:cache ignorado"

# Roda seeders
php artisan db:seed --force || echo "⚠️  seed ignorado (já executado)"

# Cria link simbólico de storage (se não existir)
php artisan storage:link 2>/dev/null || true

# Garante que o www-data (usuario do php-fpm) seja dono das pastas graváveis.
# As migrations/seeds/cache acima rodam como root e criam arquivos (ex.: laravel.log)
# como root — sem este chown, o php-fpm nao consegue escrever no log e qualquer
# Log::* estoura erro 500 (ex.: no webhook da Asaas).
chown -R www-data:www-data storage bootstrap/cache

# Inicia supervisord
exec /usr/bin/supervisord -c /etc/supervisord.conf

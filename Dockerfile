FROM php:8.3-fpm-alpine

# Dependências do sistema
RUN apk add --no-cache \
    nginx \
    supervisor \
    nodejs \
    npm \
    curl \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    libzip-dev \
    postgresql-client \
    postgresql-dev \
    zip \
    unzip \
    git \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo pdo_pgsql pgsql gd zip opcache bcmath pcntl

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Copia arquivos do projeto
COPY . .

# Instala dependências PHP (sem dev)
RUN composer install --no-dev --optimize-autoloader --no-interaction --no-progress

# Build dos assets frontend
RUN npm ci && npm run build && rm -rf node_modules

# Permissões
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html/storage \
    && chmod -R 755 /var/www/html/bootstrap/cache

# Configuração nginx
COPY docker/nginx.conf /etc/nginx/nginx.conf

# Configuração supervisord
COPY docker/supervisord.conf /etc/supervisord.conf

# Configuração PHP
COPY docker/php.ini /usr/local/etc/php/conf.d/pitstop.ini

# Pool php-fpm (controle de memória — ondemand, limita processos)
COPY docker/php-fpm-pool.conf /usr/local/etc/php-fpm.d/zz-pitstop.conf

# Entrypoint (migrations + cache + supervisord)
COPY docker/entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

EXPOSE 80

HEALTHCHECK --interval=30s --timeout=10s --start-period=60s --retries=3 \
    CMD curl -f http://localhost/api/health || exit 1

ENTRYPOINT ["/entrypoint.sh"]

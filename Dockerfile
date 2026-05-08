FROM php:8.4-fpm-alpine

ENV DEBIAN_FRONTEND=noninteractive
ENV TZ=UTC

RUN apk add --no-cache \
    postgresql-dev \
    linux-headers \
    $PHPIZE_DEPS \
    nginx \
    supervisor \
    curl \
    fcgi \
    && docker-php-ext-install pdo pdo_pgsql pdo_mysql \
    && pecl install redis > /dev/null 2>&1 || true \
    && docker-php-ext-enable redis \
    && rm -rf /var/cache/apk/*

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Copy composer files first for caching
COPY composer.json composer.lock* ./

RUN composer install --no-dev --optimize-autoloader --no-interaction 2>&1 | tail -3

# Copy application
COPY . .

# Build Laravel
RUN php artisan config:cache 2>&1 | tail -3 \
    && php artisan route:cache 2>&1 | tail -3 \
    || true

# Permissions
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# Supervisor config
COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Nginx config
COPY docker/nginx.conf /etc/nginx/sites-available/default

# Entrypoint
COPY docker/entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

EXPOSE 80

ENTRYPOINT ["/entrypoint.sh"]

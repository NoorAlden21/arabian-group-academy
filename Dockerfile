# --- Stage 1: Composer dependencies ---
FROM composer:2 AS vendor
WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-interaction --prefer-dist --no-progress
# Copy the rest so classmap can be optimized if needed
COPY . .
RUN composer install --no-dev --no-interaction --prefer-dist --no-progress \
 && php artisan package:discover --ansi || true

# --- Stage 2: App image with Apache + PHP ---
FROM php:8.2-apache

# System deps (zip, pgsql)
RUN apt-get update && apt-get install -y --no-install-recommends \
    libpq-dev zip unzip git \
 && docker-php-ext-install pdo_pgsql pgsql \
 && a2enmod rewrite \
 && rm -rf /var/lib/apt/lists/*

# Set docroot to Laravel public
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/000-default.conf /etc/apache2/apache2.conf

WORKDIR /var/www/html

# Copy app source
COPY . .

# Copy vendor from builder
COPY --from=vendor /app/vendor ./vendor

# Permissions for cache/storage
RUN chown -R www-data:www-data storage bootstrap/cache \
 && chmod -R 775 storage bootstrap/cache

# Optimize (safe if APP_KEY exists; otherwise it’ll still run)
RUN php artisan config:clear || true \
 && php artisan route:clear || true

# Entrypoint: run quick setup then start Apache
COPY scripts/entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

EXPOSE 80
CMD ["/entrypoint.sh"]

# --- Stage 1: Composer dependencies (no PHP extensions available here) ---
FROM composer:2 AS vendor
WORKDIR /app

# Copy only the files Composer needs first (better cache)
COPY composer.json composer.lock ./

# Install without dev and ignore platform reqs (pgsql/gd) in THIS stage only
RUN composer install --no-dev --no-interaction --prefer-dist --no-progress --no-scripts --ignore-platform-reqs

# Now copy the whole source and re-run install to pick up classmaps if needed
COPY . .
RUN composer install --no-dev --no-interaction --prefer-dist --no-progress --ignore-platform-reqs

# --- Stage 2: App image with Apache + PHP ---
FROM php:8.2-apache

# System deps: PostgreSQL headers, zip, image libs for GD, git
RUN apt-get update && apt-get install -y --no-install-recommends \
    libpq-dev libzip-dev zip unzip git \
    libfreetype6-dev libjpeg62-turbo-dev libpng-dev \
 && docker-php-ext-configure gd --with-freetype --with-jpeg \
 && docker-php-ext-install pdo_pgsql pgsql gd zip \
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

# Permissions
RUN chown -R www-data:www-data storage bootstrap/cache \
 && chmod -R 775 storage bootstrap/cache

# Light optimize (cache will be rebuilt at runtime too)
RUN php artisan config:clear || true \
 && php artisan route:clear || true

# Entrypoint: prepare app then start Apache
COPY scripts/entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

RUN chown -R www-data:www-data storage bootstrap/cache \
 && chmod -R 775 storage bootstrap/cache


EXPOSE 80
CMD ["/entrypoint.sh"]

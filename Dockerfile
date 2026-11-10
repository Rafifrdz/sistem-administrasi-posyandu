FROM php:8.4-apache

# Ekstensi PHP yang dibutuhkan Laravel + PostgreSQL (Neon/Supabase)
RUN apt-get update && apt-get install -y --no-install-recommends \
        libpq-dev libzip-dev libicu-dev unzip git \
    && docker-php-ext-install pdo pdo_pgsql pdo_mysql bcmath intl opcache \
    && a2enmod rewrite headers \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Dependency dulu (biar layer cache efisien)
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist --no-interaction --no-progress

# Sisa aplikasi
COPY . .

RUN composer dump-autoload --no-dev --no-scripts --optimize \
    && mkdir -p storage/framework/sessions storage/framework/views storage/framework/cache/data storage/logs bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache

COPY docker/vhost.conf /etc/apache2/sites-available/000-default.conf
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

ENV APACHE_DOCUMENT_ROOT=/var/www/html/public

EXPOSE 80
ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]

FROM php:8.4-cli-bookworm

# Install system dependencies and PHP extensions in smaller, cacheable layers
RUN apt-get update --fix-missing \
    && apt-get install -y --no-install-recommends \
        libpng-dev \
        libjpeg62-turbo-dev \
        libwebp-dev \
        libfreetype6-dev \
        libzip-dev \
        libonig-dev \
        libxml2-dev \
        libicu-dev \
        libpq-dev \
        unzip \
        curl \
        git \
    && docker-php-ext-configure gd --with-jpeg --with-webp --with-freetype \
    && docker-php-ext-install -j$(nproc) gd pdo pdo_mysql zip intl exif \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

# Install Composer
COPY --from=composer:2.8 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Copy application code and install PHP dependencies so vendor/ exists in the image for dev mounts
COPY . .

# .env is excluded from the build context (see .dockerignore), so Laravel's APP_ENV
# defaults to "production" during composer's package:discover step, which boots the
# app and trips the production-only default-password check against a nonexistent DB.
# Both compose files set the real APP_ENV at runtime, so this only affects the build.
ENV APP_ENV=local
RUN composer install --no-interaction --optimize-autoloader

# Set permissions on the default workspace directory.
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 775 /var/www/html

# Expose the Laravel development server port
EXPOSE 8000

# Entrypoint will run migrations, seeders, then start the server
COPY docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

ENTRYPOINT ["/usr/local/bin/docker-entrypoint.sh"]
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]

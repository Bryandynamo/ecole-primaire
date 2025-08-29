# Multi-stage Dockerfile for Laravel on Fly.io

# 1) Composer dependencies
FROM composer:2 AS vendor
WORKDIR /app
COPY . /app
RUN composer install --no-dev --optimize-autoloader --no-interaction --no-progress

# 2) Runtime image: PHP-FPM + Nginx + Supervisor (Alpine)
FROM php:8.2-fpm-alpine AS runtime

# Install system packages
RUN apk add --no-cache \
    nginx \
    supervisor \
    curl \
    bash \
    libzip-dev \
    icu-dev \
    oniguruma-dev \
    freetype-dev \
    libjpeg-turbo-dev \
    libpng-dev \
    libxml2-dev \
    tzdata \
    nodejs npm \
    sqlite-libs \
    sqlite-dev

# Install PHP extensions
RUN docker-php-ext-configure gd \
    --with-freetype=/usr/include/ \
    --with-jpeg=/usr/include/
RUN docker-php-ext-install -j$(nproc) \
    pdo_mysql \
    pdo_sqlite \
    zip \
    intl \
    gd \
    mbstring \
    xml \
    opcache

# Copy app
WORKDIR /var/www/html
COPY . /var/www/html
COPY --from=vendor /app/vendor /var/www/html/vendor

# Nginx & PHP config and startup scripts
COPY docker/nginx.conf /etc/nginx/nginx.conf
COPY docker/supervisord.conf /etc/supervisord.conf
COPY docker/start.sh /start.sh
RUN chmod +x /start.sh

# Ensure storage permissions
RUN addgroup -g 1000 www && adduser -G www -g www -s /bin/sh -D www \
    && chown -R www:www /var/www/html \
    && chown -R www:www /var/www/html/storage /var/www/html/bootstrap/cache

# Expose web port (Nginx)
EXPOSE 8080

# Environment defaults
ENV APP_ENV=production \
    APP_DEBUG=false \
    PHP_OPCACHE_VALIDATE_TIMESTAMPS=0

# Health check (use curl since wget is not installed)
HEALTHCHECK --interval=30s --timeout=5s --start-period=30s --retries=3 \
  CMD curl -fsS http://127.0.0.1:8080/ >/dev/null || exit 1

# Start supervisor to run php-fpm + nginx
CMD ["/start.sh"]

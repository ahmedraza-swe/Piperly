# Piperly — production image for Render / Docker hosts
FROM php:8.3-cli-bookworm

WORKDIR /var/www/html

RUN apt-get update && apt-get install -y --no-install-recommends \
    git unzip curl \
    libzip-dev libpng-dev libjpeg-dev libfreetype6-dev \
    libicu-dev libonig-dev libxml2-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        pdo_mysql mbstring zip exif pcntl bcmath gd intl xml sockets \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
    && apt-get install -y nodejs \
    && curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

COPY . .

RUN chmod +x docker/render-entrypoint.sh

# Build-time key only — Render injects real env vars at runtime
ENV APP_KEY=base64:EcjAm1p7YpnFjru2lEGYxXoisoiBAQiWnw6csnOULxE=
ENV APP_ENV=production
ENV APP_DEBUG=false

RUN composer install --no-dev --optimize-autoloader --no-interaction \
    && npm ci \
    && npm run build

EXPOSE 10000

CMD ["docker/render-entrypoint.sh"]

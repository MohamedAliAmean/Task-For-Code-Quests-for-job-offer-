FROM php:8.4-fpm

WORKDIR /var/www/html

ENV COMPOSER_ALLOW_SUPERUSER=1
ENV APP_KEY=base64:oPJuxvURnRO4VyRdB2dkRXduKkkF7ZpmWkbVZpzGpLo=

RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        curl \
        git \
        unzip \
        libicu-dev \
        libonig-dev \
        libzip-dev \
    && docker-php-ext-install \
        bcmath \
        intl \
        mbstring \
        pcntl \
        pdo_mysql \
        zip \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

RUN curl -fsSL https://deb.nodesource.com/setup_22.x | bash - \
    && apt-get update \
    && apt-get install -y --no-install-recommends nodejs \
    && rm -rf /var/lib/apt/lists/*

COPY . .

RUN mkdir -p storage/framework/cache storage/framework/sessions storage/framework/testing storage/framework/views bootstrap/cache \
    && cp -n .env.example .env \
    && composer install --no-interaction --prefer-dist \
    && npm ci \
    && npm run build \
    && mkdir -p vendor/pestphp/pest-plugin-mutate/.temp \
    && chown -R www-data:www-data storage bootstrap/cache vendor/pestphp/pest-plugin-mutate/.temp

COPY docker/app/entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

ENTRYPOINT ["/entrypoint.sh"]
CMD ["php-fpm"]

# ============================================================
# Stage 1: base — shared PHP + extension configuration
# ============================================================
FROM php:8.4-fpm-alpine AS base

# Install system dependencies and PHP extensions needed by Symfony + Doctrine
RUN apk add --no-cache \
        git \
        unzip \
        libpq-dev \
        icu-dev \
        oniguruma-dev \
        rabbitmq-c-dev \
        autoconf \
        g++ \
        make \
    && docker-php-ext-install \
        pdo \
        pdo_pgsql \
        intl \
        opcache \
        mbstring \
    && pecl install amqp \
    && docker-php-ext-enable amqp \
    && apk del autoconf g++ make

# Install Composer (copied from the official Composer image — no extra download needed)
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app

# ============================================================
# Stage 2: dev — development image (includes dev dependencies)
# ============================================================
FROM base AS dev

# Copy composer files first for better layer caching
COPY composer.json composer.lock ./

# Install all dependencies (including require-dev)
RUN composer install --no-scripts --no-autoloader --prefer-dist

# Copy the full application source
COPY . .

# Generate optimized autoloader
RUN composer dump-autoload --optimize

EXPOSE 9000

CMD ["php-fpm"]

# ============================================================
# Stage 3: prod — production image (no dev dependencies)
# ============================================================
FROM base AS prod

ENV APP_ENV=prod

COPY composer.json composer.lock ./

RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist

COPY . .

RUN composer dump-autoload --optimize --no-dev \
    && php bin/console cache:warmup

EXPOSE 9000

CMD ["php-fpm"]

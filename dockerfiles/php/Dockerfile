FROM php:8.4-fpm-alpine

# Composer (igual você já tinha)
RUN apk --update --no-cache add \
    git \
    make \
    autoconf \
    build-base \
    linux-headers \
    librdkafka-dev \
    libzip-dev \
    postgresql-dev

RUN docker-php-ext-install pdo_pgsql exif zip 

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
# (Opcional, se for usar PIE pra outras extensões)
COPY --from=ghcr.io/php/pie:bin /pie /usr/bin/pie

RUN apk add --no-cache nodejs npm
# Dependências de sistema + libs do Postgres
WORKDIR /var/www

EXPOSE 9000
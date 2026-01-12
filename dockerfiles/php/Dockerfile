FROM php:8.4-fpm-alpine

WORKDIR /var/www
EXPOSE 9000

# 1) Dependências RUNTIME (ficam na imagem final)
RUN apk add --no-cache \
    libpq \
    libzip \

# 2) Dependências de BUILD (só pra compilar extensões) + build + remove
RUN apk add --no-cache --virtual .build-deps \
    $PHPIZE_DEPS \
    build-base \
    linux-headers \
    postgresql-dev \
    libzip-dev \
  && docker-php-ext-install pdo_pgsql exif zip bcmath \
  && apk del .build-deps

# Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# (Opcional) PIE - só mantenha se você realmente usa dentro do container
COPY --from=ghcr.io/php/pie:bin /pie /usr/bin/pie

# Node: isso pesa MUITO. Se precisar mesmo, ok:
RUN apk add --no-cache nodejs npm

USER root
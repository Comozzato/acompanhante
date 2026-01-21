FROM php:8.2-fpm-alpine

ARG UID=1000
ARG GID=1000

# Cria grupo e usuário não-root
RUN addgroup -g ${GID} app \
  && adduser -D -u ${UID} -G app app

WORKDIR /app

# Dependências do sistema
RUN apk add --no-cache \
  libpq \
  libzip \
  icu-libs

# Dependências de build
RUN apk add --no-cache --virtual .build-deps \
  ffmpeg \
  $PHPIZE_DEPS \
  build-base \
  linux-headers \
  postgresql-dev \
  libzip-dev \
  icu-dev \
  && docker-php-ext-install pdo_pgsql exif zip bcmath intl \
  && apk del .build-deps

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Ajusta permissões
RUN chown -R app:app /app

# Permissões (Laravel)
# Permissões Laravel (seguro)
RUN mkdir -p storage/logs bootstrap/cache \
  && chown -R app:app storage bootstrap/cache \
  && find storage bootstrap/cache -type d -exec chmod 775 {} \; \
  && find storage bootstrap/cache -type f -exec chmod 664 {} \;

# Troca para usuário não-root
USER app

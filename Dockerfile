# --------------------------------------------
# Etapa 1 - Build (Composer)
# --------------------------------------------
FROM composer:2.7 AS vendor

WORKDIR /app

# Copia composer.* para instalar dependências
COPY composer.json composer.lock ./

# Instala dependências de produção (sem dev)
RUN composer install --no-dev --optimize-autoloader --no-interaction --no-progress

# --------------------------------------------
# Etapa 2 - App (PHP-FPM + FFmpeg)
# --------------------------------------------
FROM php:8.2-fpm

WORKDIR /var/www

# Instala dependências do sistema
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    ffmpeg \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libzip-dev \
    libonig-dev \
    libxml2-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install \
        pdo_mysql \
        mbstring \
        exif \
        pcntl \
        bcmath \
        gd \
        zip \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Copia arquivos do Laravel
COPY . .

# Copia dependências instaladas na etapa anterior
COPY --from=vendor /app/vendor ./vendor

# Permissões
RUN chown -R www-data:www-data /var/www \
    && chmod -R 775 storage bootstrap/cache

# Expõe porta do PHP-FPM
EXPOSE 9000

# Comando de execução
CMD ["php-fpm"]

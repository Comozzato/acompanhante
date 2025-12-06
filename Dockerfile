FROM php:8.2-fpm

WORKDIR /var/www

# Dependências e extensões
RUN apt-get update && apt-get install -y \
    git unzip ffmpeg nginx libpng-dev libjpeg-dev libfreetype6-dev \
    libzip-dev libonig-dev libxml2-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Composer
COPY --from=composer:2.7 /usr/bin/composer /usr/bin/composer
ENV COMPOSER_ALLOW_SUPERUSER=1
COPY composer.json composer.lock ./
RUN composer install --no-dev --optimize-autoloader --no-interaction --no-progress --no-scripts

# Copia app
COPY . .

# Permissões
RUN chown -R www-data:www-data /var/www \
    && chmod -R 775 storage bootstrap/cache

# Configuração Nginx
COPY ./nginx.conf /etc/nginx/sites-available/default

EXPOSE 80
CMD ["sh", "-c", "php-fpm -D && nginx -g 'daemon off;'"]

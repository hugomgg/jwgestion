FROM php:8.3-fpm

RUN apt-get update && apt-get install -y \
    git \
    curl \
    zip \
    unzip \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    default-mysql-client \
    gnupg \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip

# Instalar Node 20
RUN curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
    && apt-get install -y nodejs

# Instalar Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Ocultar versión de PHP en headers HTTP (X-Powered-By)
RUN echo "expose_php = Off" > /usr/local/etc/php/conf.d/security.ini

WORKDIR /var/www

CMD ["php-fpm"]

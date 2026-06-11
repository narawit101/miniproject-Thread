# ใช้ PHP 8.2 FPM บน Alpine (เบาและเร็ว)
FROM php:8.2-fpm-alpine

# ติดตั้ง PHP extensions ที่จำเป็น
RUN apk add --no-cache \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    zip \
    libzip-dev \
    oniguruma-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install \
        pdo \
        pdo_mysql \
        mysqli \
        gd \
        zip \
        mbstring \
        exif \
        pcntl \
        bcmath

# ตั้งค่า PHP
COPY php.ini /usr/local/etc/php/conf.d/custom.ini

WORKDIR /var/www/html

# รัน PHP-FPM
CMD ["php-fpm"]

## PHP
FROM php:7.4.6-cli AS php
RUN apt-get update
RUN apt-get install -y \
    libxml2-dev \
    libonig-dev \
    libpng-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    libmagickwand-dev \
    mariadb-client \
    libzip-dev
RUN docker-php-ext-configure gd --with-freetype --with-jpeg
RUN docker-php-ext-install \
    bcmath \
    ctype \
    json \
    mbstring \
    pdo \
    pdo_mysql \
    tokenizer \
    xml \
    gd \
    zip
RUN pecl install redis
RUN docker-php-ext-enable redis
RUN pecl install imagick
RUN docker-php-ext-enable imagick
RUN docker-php-ext-install exif
RUN docker-php-ext-enable exif
RUN apt-get clean


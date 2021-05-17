## PHP
FROM php:7.4.6-apache AS php
RUN apt-get update
RUN apt-get install -y \
    libxml2-dev \
    libonig-dev \
    libpng-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    libmagickwand-dev
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
    gd
RUN pecl install redis
RUN docker-php-ext-enable redis
RUN pecl install imagick
RUN docker-php-ext-enable imagick
RUN mv "$PHP_INI_DIR/php.ini-development" "$PHP_INI_DIR/php.ini"

## APACHE
RUN a2enmod rewrite
COPY docker/000-default.conf /etc/apache2/sites-available/000-default.conf

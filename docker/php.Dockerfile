## PHP
FROM php:8.2-apache AS php
RUN apt-get update
RUN apt-get install -y \
    libxml2-dev \
    libonig-dev \
    libpng-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    libmagickwand-dev \
    mariadb-client \
    libzip-dev \
    gdal-bin \
    libgdal-dev \
    python3.11-venv

RUN docker-php-ext-configure gd --with-freetype --with-jpeg
RUN docker-php-ext-install \
    bcmath \
    ctype \
    mbstring \
    pdo \
    pdo_mysql \
    xml \
    gd \
    zip
RUN pecl install redis
RUN docker-php-ext-enable redis
RUN pecl install imagick
RUN docker-php-ext-enable imagick
RUN docker-php-ext-install pcntl
RUN docker-php-ext-enable pcntl
RUN docker-php-ext-install exif
RUN docker-php-ext-enable exif
RUN mv "$PHP_INI_DIR/php.ini-development" "$PHP_INI_DIR/php.ini"

## APACHE
RUN a2enmod rewrite
COPY docker/000-default.conf /etc/apache2/sites-available/000-default.conf
COPY docker/php.ini /usr/local/etc/php/php.ini

## Python
RUN python3 -m venv /root/python
COPY resources/python/polygon-voronoi/requirements.txt /root/voronoi-requirements.txt
ENV PATH="/root/python/bin:${PATH}"
RUN pip3 install -r /root/voronoi-requirements.txt

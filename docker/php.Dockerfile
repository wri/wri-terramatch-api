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
    python3.11-venv \
    python3-dev \
    python3-venv \
    python3-pip \
    python3-numpy \
    build-essential \
    libproj-dev \
    exiftool \
    gcc \
    g++ \
    python3-gdal \
    proj-data \
    proj-bin

# Add GDAL specific environment variables
ENV CPLUS_INCLUDE_PATH=/usr/include/gdal
ENV C_INCLUDE_PATH=/usr/include/gdal
ENV GDAL_VERSION=3.4.1

# Set GDAL configuration
RUN export CPLUS_INCLUDE_PATH=/usr/include/gdal
RUN export C_INCLUDE_PATH=/usr/include/gdal

# PHP Extensions
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
RUN python3 -m venv /opt/python
COPY resources/python/polygon-voronoi/requirements.txt /root/voronoi-requirements.txt
ENV PATH="/opt/python/bin:${PATH}"

# Install Python packages
RUN pip3 install --upgrade pip
RUN pip3 install --no-cache-dir numpy wheel setuptools

# Install GDAL and its dependencies
RUN apt-get install -y python3-gdal
RUN gdal-config --version
ENV GDAL_CONFIG=/usr/bin/gdal-config
RUN pip3 install --no-binary :all: GDAL==${GDAL_VERSION}

# Install remaining requirements
RUN pip3 install -r /root/voronoi-requirements.txt

RUN chmod -R a+rx /opt/python
USER www-data
ENV PATH="/opt/python/bin:${PATH}"
USER root
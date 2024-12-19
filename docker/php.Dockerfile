FROM php:8.2-apache AS php

# Add backports for more recent GDAL version
RUN echo "deb http://deb.debian.org/debian bullseye-backports main" >> /etc/apt/sources.list

RUN apt-get update && apt-get install -y \
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
    proj-bin \
    libgdal28 \
    python3-gdal

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

# Install remaining requirements EXCEPT GDAL (since we're using system GDAL)
RUN pip3 install pyproj==3.4.1 \
    shapely==2.0.1 \
    geopandas==1.0.1 \
    pandas==2.1.3 \
    requests==2.32.3 \
    fiona==1.10.1 \
    exactextract==0.2.0 \
    rasterio==1.4.1 \
    pyyaml==6.0.2 \
    rasterstats==0.20.0 \
    boto3==1.35.43

RUN chmod -R a+rx /opt/python
USER www-data
ENV PATH="/opt/python/bin:${PATH}"
USER root
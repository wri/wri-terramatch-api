FROM php:8.2-apache AS php

RUN apt-get update && apt-get install -y \
    libxml2-dev \
    libonig-dev \
    libpng-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    libmagickwand-dev \
    mariadb-client \
    libzip-dev \
    build-essential \
    software-properties-common \
    wget \
    curl \
    gcc \
    g++ \
    python3.11-dev \
    python3.11-venv \
    python3-pip \
    python3-numpy \
    libproj-dev \
    proj-data \
    proj-bin \
    exiftool

# Install GDAL 3.4.3 from source
RUN wget https://github.com/OSGeo/gdal/releases/download/v3.4.3/gdal-3.4.3.tar.gz && \
    tar -xvf gdal-3.4.3.tar.gz && \
    cd gdal-3.4.3 && \
    ./configure --with-python=/usr/bin/python3.11 && \
    make -j$(nproc) && \
    make install && \
    ldconfig && \
    cd .. && \
    rm -rf gdal-3.4.3 gdal-3.4.3.tar.gz

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

## Python setup
RUN python3.11 -m venv /opt/python
COPY resources/python/polygon-voronoi/requirements.txt /root/voronoi-requirements.txt
ENV PATH="/opt/python/bin:${PATH}"

# Set GDAL environment variables
ENV CPLUS_INCLUDE_PATH=/usr/local/include
ENV C_INCLUDE_PATH=/usr/local/include
ENV LD_LIBRARY_PATH=/usr/local/lib:$LD_LIBRARY_PATH
ENV GDAL_VERSION=3.4.3

# Install Python packages
RUN pip3 install --upgrade pip
RUN pip3 install --no-cache-dir numpy wheel setuptools

# Install GDAL with specific version
RUN pip3 install GDAL==${GDAL_VERSION}

# Install remaining requirements
RUN pip3 install -r /root/voronoi-requirements.txt

RUN chmod -R a+rx /opt/python
USER www-data
ENV PATH="/opt/python/bin:${PATH}"
USER root
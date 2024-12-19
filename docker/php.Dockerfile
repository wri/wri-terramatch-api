FROM php:8.2-apache AS php

# Set GDAL version
ENV GDAL_VERSION=3.4.3

# Install basic dependencies
RUN apt-get update && apt-get install -y \
    libxml2-dev \
    libonig-dev \
    libpng-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    libmagickwand-dev \
    mariadb-client \
    libzip-dev \
    python3.11-venv \
    python3.11-dev \
    exiftool \
    build-essential \
    wget \
    cmake \
    sqlite3 \
    libsqlite3-dev \
    libspatialite-dev \
    libpq-dev \
    libcurl4-gnutls-dev \
    libproj-dev \
    libgeos-dev \
    && rm -rf /var/lib/apt/lists/*

# Install GDAL 3.4.3 from source
RUN wget https://github.com/OSGeo/gdal/releases/download/v${GDAL_VERSION}/gdal-${GDAL_VERSION}.tar.gz \
    && tar xzf gdal-${GDAL_VERSION}.tar.gz \
    && cd gdal-${GDAL_VERSION} \
    && ./configure \
    && make -j$(nproc) \
    && make install \
    && ldconfig \
    && cd .. \
    && rm -rf gdal-${GDAL_VERSION} gdal-${GDAL_VERSION}.tar.gz

# Set GDAL environment variables
ENV CPLUS_INCLUDE_PATH=/usr/include/gdal
ENV C_INCLUDE_PATH=/usr/include/gdal
ENV LD_LIBRARY_PATH=/usr/local/lib:$LD_LIBRARY_PATH

# Your existing PHP extensions
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

# Apache configuration
RUN a2enmod rewrite
COPY docker/000-default.conf /etc/apache2/sites-available/000-default.conf
COPY docker/php.ini /usr/local/etc/php/php.ini

# Python virtual environment setup
RUN python3.11 -m venv /opt/python
ENV PATH="/opt/python/bin:${PATH}"

# Install Python dependencies in the correct order
COPY resources/python/polygon-voronoi/requirements.txt /root/voronoi-requirements.txt
RUN pip3 install --upgrade pip wheel setuptools
RUN pip3 install numpy==1.26.4
RUN pip3 install pyproj==3.4.1
RUN pip3 install GDAL==${GDAL_VERSION}
RUN pip3 install fiona==1.10.1
RUN pip3 install shapely==2.0.1
RUN pip3 install pandas==2.1.3
RUN pip3 install geopandas==1.0.1
RUN pip3 install rasterio==1.4.1
RUN pip3 install exactextract==0.2.0
RUN pip3 install rasterstats==0.20.0
RUN pip3 install pyyaml==6.0.2
RUN pip3 install requests==2.32.3
RUN pip3 install boto3==1.35.43

RUN chmod -R a+rx /opt/python
USER www-data
ENV PATH="/opt/python/bin:${PATH}"
USER root
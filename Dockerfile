# Use an official PHP runtime as a parent image
FROM php:7.4-fpm

# Set working directory
WORKDIR /var/www/html

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    gifsicle \
    jpegoptim \
    libaio-dev \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    libldap2-dev \
    curl \
    libgd-dev \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    libzip-dev \
    libicu-dev \
    locales \
    optipng \
    unzip \
    vim \
    zip \
    libpq-dev \
    && rm -rf /var/lib/apt/lists/* \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd

# Install Oracle Instant Client and SDK
RUN mkdir /opt/oracle \
    && curl -L https://download.oracle.com/otn_software/linux/instantclient/213000/instantclient-basiclite-linux.x64-21.3.0.0.0.zip -o /opt/oracle/instantclient-basiclite-linux.x64-21.3.0.0.0.zip \
    && curl -L https://download.oracle.com/otn_software/linux/instantclient/213000/instantclient-sdk-linux.x64-21.3.0.0.0.zip -o /opt/oracle/instantclient-sdk-linux.x64-21.3.0.0.0.zip \
    && unzip /opt/oracle/instantclient-basiclite-linux.x64-21.3.0.0.0.zip -d /opt/oracle \
    && unzip /opt/oracle/instantclient-sdk-linux.x64-21.3.0.0.0.zip -d /opt/oracle \
    && rm /opt/oracle/instantclient-basiclite-linux.x64-21.3.0.0.0.zip \
    && rm /opt/oracle/instantclient-sdk-linux.x64-21.3.0.0.0.zip \
    && ln -s /opt/oracle/instantclient_21_3 /opt/oracle/instantclient \
    && echo "/opt/oracle/instantclient" > /etc/ld.so.conf.d/oracle-instantclient.conf \
    && ldconfig

# Set environment variables
ENV LD_LIBRARY_PATH=/opt/oracle/instantclient
ENV ORACLE_HOME=/opt/oracle/instantclient

# Install PHP extensions
RUN docker-php-ext-configure pdo_oci --with-pdo_oci=instantclient,/opt/oracle/instantclient_21_3 \
    && echo 'instantclient,/opt/oracle/instantclient_21_3/' | pecl install oci8-2.2.0 \
    && docker-php-ext-install pdo_oci zip exif pcntl gd \
    && docker-php-ext-install soap intl pdo pdo_mysql pdo_pgsql ldap \
    && pecl install xdebug-2.9.8 \
    && docker-php-ext-enable xdebug \
    && docker-php-ext-enable opcache \
    && docker-php-ext-enable \
            oci8 \

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Copy project files
COPY . .

# Copy custom php.ini file
COPY ./_docker/php.ini /usr/local/etc/php/conf.d/php.ini

#Copy composer
COPY --from=composer /usr/bin/composer /usr/bin/composer

# Install project dependencies
RUN composer install --no-dev --no-interaction --no-progress

# Set permissions for the storage and bootstrap cache directories
RUN chgrp -R www-data storage bootstrap/cache \
    && chmod -R ug+rwx storage bootstrap/cache

FROM php:8.3-apache

# Set working directory
WORKDIR /var/www/html

# Copy Zscaler cert
COPY zscaler.pem /usr/local/share/ca-certificates/zscaler.crt
COPY xdebug.ini /usr/local/etc/php/conf.d/

RUN a2enmod rewrite

# Install dependencies and PHP extensions
RUN apt-get update && apt-get install -y \
    unzip \
    git \
    curl \
    libzip-dev \
    zip \
    && docker-php-ext-install pdo pdo_mysql \
    && pecl install xdebug \
    && docker-php-ext-enable xdebug

# Update CA certs
RUN update-ca-certificates

# Install Composer globally
RUN curl -sS --insecure https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

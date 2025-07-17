FROM php:8.3-apache

# Set working directory
WORKDIR /var/www/html

# Copy Zscaler cert
COPY zscaler.pem /usr/local/share/ca-certificates/zscaler.crt

RUN a2enmod rewrite

# Install dependencies and PHP extensions
RUN apt-get update && apt-get install -y \
    unzip \
    git \
    curl \
    libzip-dev \
    zip \
    && docker-php-ext-install pdo pdo_mysql \
    && pecl install redis xdebug \
    && docker-php-ext-enable redis xdebug

# Update CA certs
RUN update-ca-certificates

RUN docker-php-ext-install bcmath
 
RUN docker-php-ext-install sockets

# Install Composer globally
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

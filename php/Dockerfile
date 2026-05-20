FROM php:8.3-fpm

# Install PHP dependencies
RUN docker-php-ext-install pdo pdo_mysql mysqli

# Install system packages
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    git \
    npm \
    unzip \
    nano \
    curl \
    octave \
    octave-control \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Install composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Add Composer global bin to PATH
ENV PATH="/root/.config/composer/vendor/bin:${PATH}"

# Install Laravel installer globally
RUN composer global require laravel/installer

# Configure PHP for large file uploads
RUN echo "upload_max_filesize = 500M" >> /usr/local/etc/php/php.ini && \
    echo "post_max_size = 500M" >> /usr/local/etc/php/php.ini

WORKDIR /var/www/html

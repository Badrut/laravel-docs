# 1. Upgrade base image ke PHP 8.4
FROM php:8.4-apache

# 2. Install dependencies system, ekstensi PHP, DAN Node.js
RUN apt-get update && apt-get install -y \
    git curl libpng-dev libonig-dev libxml2-dev libzip-dev zip unzip \
    # Tambahan packages untuk build Electron/NativePHP di Linux
    fakeroot dpkg-dev \
    # Install Node.js (dibutuhkan untuk npm run build & NativePHP)
    && curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
    && apt-get install -y nodejs \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip

# Aktifkan mod_rewrite Apache
RUN a2enmod rewrite

# Arahkan DocumentRoot Apache langsung ke folder public/ milik Laravel
ENV APACHE_DOCUMENT_ROOT /var/www/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Naikan limit upload PHP (upload_max_filesize, post_max_size, dll.)
COPY docker/php.ini /usr/local/etc/php/conf.d/custom.ini

# Set working directory
WORKDIR /var/www

# Entrypoint: pastikan storage link & permission benar setiap container start
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
CMD ["apache2-foreground"]
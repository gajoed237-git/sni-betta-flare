FROM php:8.2-apache

# 1. Install dependencies dasar
RUN apt-get update && apt-get install -y \
    build-essential \
    libpng-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    locales \
    zip \
    jpegoptim optipng pngquant gifsicle \
    vim \
    unzip \
    git \
    curl \
    libzip-dev \
    libonig-dev \
    libxml2-dev \
    libicu-dev \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# 2. Install PHP extensions yang dibutuhkan Laravel
RUN docker-php-ext-install pdo_mysql mbstring zip exif pcntl bcmath gd intl

# 3. Enable Apache mod_rewrite untuk Laravel Routing
RUN a2enmod rewrite

# 4. Ubah Apache DocumentRoot ke folder public Laravel
ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/000-default.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# 5. Install Composer dari image resmi
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# 6. Install Node.js v22 untuk build frontend (Vite/Tailwind)

# 7. Set working directory
WORKDIR /var/www/html

# 8. Copy seluruh kode aplikasi
COPY . .

# 9. Install PHP dependencies (Vendor)
RUN composer install --no-dev --optimize-autoloader --no-scripts

# 10. Install NPM dependencies dan Build assets jika ada package.json

# 11. Atur permission untuk storage dan cache
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# 12. Copy entrypoint script
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

# 13. Expose port 80
EXPOSE 80

ENTRYPOINT ["entrypoint.sh"]

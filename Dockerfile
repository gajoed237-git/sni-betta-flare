# 1. Gunakan image PHP 8.2 dengan Apache
FROM php:8.2-apache

# 2. Install dependencies sistem dan tool untuk install ekstensi PHP
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    zip \
    unzip \
    git \
    && rm -rf /var/lib/apt/lists/*

# 3. Gunakan helper khusus untuk install ekstensi PHP (Jauh lebih cepat daripada manual)
ADD https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions /usr/local/bin/
RUN chmod +x /usr/local/bin/install-php-extensions && \
    install-php-extensions pdo_mysql mbstring zip exif pcntl gd bcmath intl

# 4. Aktifkan modul rewrite Apache (PENTING untuk routing Laravel)
RUN a2enmod rewrite

# 5. Ubah DocumentRoot Apache ke folder /public Laravel (Solusi 404)
ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# 6. Set working directory
WORKDIR /var/www/html

# 7. Copy kodingan aplikasi
COPY . .

# 8. Install Composer (Tool manajemen library PHP)
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
RUN composer install --no-dev --optimize-autoloader

# 9. Berikan izin akses folder (Permission)
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# 10. Expose port 80
EXPOSE 80

# 11. Jalankan Apache di foreground
CMD ["apache2-foreground"]
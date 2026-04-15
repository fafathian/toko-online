# Menggunakan image resmi PHP 8.2 dengan Apache
FROM php:8.4-apache

# 1. Install system dependencies & Node.js (TAMBAHKAN libicu-dev di sini)
RUN apt-get update && apt-get install -y \
    libzip-dev \
    libicu-dev \
    zip \
    unzip \
    git \
    curl \
    && curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
    && apt-get install -y nodejs

# 2. Enable Apache mod_rewrite untuk routing Laravel
RUN a2enmod rewrite

# 3. Install PHP extensions (TAMBAHKAN intl dan bcmath di sini)
RUN docker-php-ext-configure intl \
    && docker-php-ext-install pdo_mysql zip pcntl intl bcmath

# 4. Set working directory
WORKDIR /var/www/html

# 5. Copy seluruh file proyek ke dalam container
COPY . .

# 6. Install Composer secara global
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# 7. Install dependensi PHP
RUN composer install --no-dev --optimize-autoloader

# 8. Install dependensi Node.js dan Build aset Vue/Inertia
RUN npm install
RUN npm run build

# 9. Ubah DocumentRoot Apache agar mengarah ke folder /public Laravel
ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# 10. Buat folder secara fisik dulu sebelum di-link
RUN mkdir -p /var/www/html/public/storage
RUN mkdir -p /var/www/html/storage/app/public

# 11. Atur permission awal agar composer bisa jalan
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# 12. Install dependensi PHP (Sekarang harusnya lancar setelah config diperbaiki)
RUN composer install --no-dev --optimize-autoloader

# 13. Build Frontend
RUN npm install && npm run build

# 14. Jalankan storage link & atur permission akhir
RUN php artisan storage:link --force
RUN chown -R www-data:www-data /var/www/html/public/storage
RUN chmod -R 775 /var/www/html/storage /var/www/html/public/storage

EXPOSE 80
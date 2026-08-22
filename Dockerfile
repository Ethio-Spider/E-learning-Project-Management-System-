FROM php:8.3-apache

WORKDIR /var/www/html

RUN apt-get update && apt-get install -y --no-install-recommends \
    default-mysql-client \
    zip \
    unzip \
    libzip-dev \
    libpng-dev \
    libonig-dev \
    curl \
    && docker-php-ext-install pdo pdo_mysql zip gd \
    && a2enmod rewrite headers \
    && rm -rf /var/lib/apt/lists/*

COPY . /var/www/html

RUN chown -R www-data:www-data /var/www/html \
    && mkdir -p /var/www/html/logs /var/www/html/uploads /var/www/html/backups \
    && chmod -R 755 /var/www/html/logs /var/www/html/uploads /var/www/html/backups

EXPOSE 80

CMD ["apache2-foreground"]

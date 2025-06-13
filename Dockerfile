# Alap image: hivatalos PHP-FPM Debian alapú image
ARG PHP_VERSION=8.4
FROM php:${PHP_VERSION}-fpm

# Címkék a könnyebb azonosításhoz
LABEL maintainer="Szigeti Balazs <szigeti.developer@gmail.com>"

# Környezeti változók
ENV PHP_VERSION=${PHP_VERSION}
ENV COMPOSER_HOME=/usr/local/composer
ENV PATH="$COMPOSER_HOME/vendor/bin:$PATH"

# Telepítendő csomagok és függőségek
RUN apt-get update && apt-get install -y \
    nginx \
    git \
    unzip \
    libzip-dev \
    libicu-dev \
    libpq-dev \
    && docker-php-ext-install \
    zip \
    intl \
    pdo_pgsql \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Composer telepítése
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer \
    && chmod +x /usr/local/bin/composer

# Xdebug telepítése
RUN pecl install xdebug \
    && docker-php-ext-enable xdebug

# PHP CodeSniffer és PHPUnit telepítése Composerrel globálisan
RUN composer global require squizlabs/php_codesniffer \
    && composer global require phpunit/phpunit \
    && composer global require vimeo/psalm

# Nginx konfiguráció másolása
COPY nginx.conf /etc/nginx/nginx.conf
COPY default.conf /etc/nginx/conf.d/default.conf

# Xdebug konfiguráció
COPY xdebug.ini /usr/local/etc/php/conf.d/xdebug.ini

# PHP-FPM konfiguráció testreszabása
COPY php-fpm.conf /usr/local/etc/php-fpm.d/www.conf

# Log mappák létrehozása
RUN mkdir -p /var/www/html/system_log/php
RUN mkdir -p /var/www/html/system_log/xdebug

# PHP hibajelentés konfigurálása
RUN echo "error_log = /var/www/html/system_log/php/error.log" > /usr/local/etc/php/conf.d/error-log.ini
RUN echo "log_errors = On" >> /usr/local/etc/php/conf.d/error-log.ini
RUN echo "display_errors = Off" >> /usr/local/etc/php/conf.d/error-log.ini
RUN echo "display_startup_errors = On" >> /usr/local/etc/php/conf.d/error-log.ini
RUN echo "error_reporting = E_ALL" >> /usr/local/etc/php/conf.d/error-log.ini

# Log mappa jogosultságok beállítása
RUN chown -R www-data:www-data /var/www/html/system_log
RUN chmod -R 755 /var/www/html/system_log

# Munkakönyvtár beállítása
WORKDIR /var/www/html

# Alapértelmezett parancs: Nginx és PHP-FPM indítása
COPY entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh
ENTRYPOINT ["/entrypoint.sh"]

# Portok
EXPOSE 80

# Tárhely az alkalmazás számára
VOLUME /var/www/html
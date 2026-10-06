FROM php:8.2-apache

RUN docker-php-ext-install mysqli pdo_mysql

COPY src/ /var/www/html/
COPY images/ /var/www/html/images/

RUN chown -R www-data:www-data /var/www/html && \
    echo 'DirectoryIndex home.php' > /etc/apache2/conf-available/zz-directoryindex.conf && \
    a2enconf zz-directoryindex && \
    chmod 644 /var/www/html/*.php /var/www/html/*.css && \
    chmod 644 /var/www/html/includes/*.php /var/www/html/administrator/*.php

EXPOSE 80

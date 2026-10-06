FROM php:8.2-apache

RUN docker-php-ext-install mysqli pdo_mysql

COPY src/ /var/www/html/
COPY images/ /var/www/html/images/
COPY docker/seed_flag.php /usr/local/bin/seed_flag.php

RUN chown -R www-data:www-data /var/www/html && \
    echo 'DirectoryIndex home.php' > /etc/apache2/conf-available/zz-directoryindex.conf && \
    a2enconf zz-directoryindex && \
    chmod 644 /var/www/html/*.php /var/www/html/*.css && \
    chmod 644 /var/www/html/includes/*.php /var/www/html/administrator/*.php

# Seed flag ke tabel admin.secrets saat container start (retry sampai DB siap)
CMD ["sh", "-c", "php /usr/local/bin/seed_flag.php; exec apache2-foreground"]

EXPOSE 80

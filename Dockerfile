FROM php:8.1-apache
RUN docker-php-ext-install mysqli && docker-php-ext-enable mysqli
COPY client/public/ /var/www/html/
COPY server/ /var/www/html/server/
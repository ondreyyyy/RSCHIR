FROM php:8.1-apache

RUN a2enmod rewrite

COPY 000-default.conf /etc/apache2/sites-available/000-default.conf
COPY apache2.conf /etc/apache2/apache2.conf

RUN mkdir -p /var/www/custom_dynamic && chown -R www-data:www-data /var/www/custom_dynamic

EXPOSE 80
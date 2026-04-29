FROM php:8.2-apache

RUN apt-get update \
 && apt-get install -y --no-install-recommends \
    default-libmysqlclient-dev \
    libaprutil1-dbd-mysql \
 && docker-php-ext-install pdo pdo_mysql \
 && a2enmod proxy proxy_http headers rewrite authn_dbd dbd \
 && rm -rf /var/lib/apt/lists/*

# Provide all site config via a single conf-enabled file (no helper scripts)
COPY prac3.conf /etc/apache2/conf-enabled/prac3.conf



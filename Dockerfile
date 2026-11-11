FROM php:8.3-apache

RUN docker-php-ext-install pdo_mysql mysqli \
    && a2enmod rewrite headers

RUN apt-get update \
    && apt-get install -y libcurl4-openssl-dev \
    && docker-php-ext-install curl \
    && rm -rf /var/lib/apt/lists/*

COPY . /var/www/html/

RUN printf '%s\n' \
    '<DirectoryMatch "^/var/www/html/App(/|$)">' \
    'Require all denied' \
    '</DirectoryMatch>' \
    '<FilesMatch "^(\\.env|test_ghn_env\\.php|Dockerfile|\\.gitignore)$">' \
    'Require all denied' \
    '</FilesMatch>' \
    > /etc/apache2/conf-available/givetake-security.conf \
    && a2enconf givetake-security

RUN mkdir -p /var/www/html/uploads \
    && chown -R www-data:www-data /var/www/html/uploads

EXPOSE 80
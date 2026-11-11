
FROM php:8.3-apache

RUN apt-get update && apt-get install -y \
    curl \
    ca-certificates \
    libcurl4-openssl-dev \
    && docker-php-ext-install pdo_mysql mysqli curl \
    && a2enmod rewrite headers \
    && rm -rf /var/lib/apt/lists/*

# Cài đặt Cloudflare TCP client cho Linux AMD64
RUN curl -fsSL \
    https://github.com/cloudflare/cloudflared/releases/latest/download/cloudflared-linux-amd64 \
    -o /usr/local/bin/cloudflared \
    && chmod +x /usr/local/bin/cloudflared

WORKDIR /var/www/html
COPY . /var/www/html/

# Chặn HTTP truy cập mã nguồn và file cấu hình nội bộ
RUN printf '%s\n' \
    '<DirectoryMatch "^/var/www/html/(App|docker|\\.git)(/|$)">' \
    'Require all denied' \
    '</DirectoryMatch>' \
    '<FilesMatch "(^\\.|\\.sql$|\\.sh$|^Dockerfile$|^composer\\.(json|lock)$)">' \
    'Require all denied' \
    '</FilesMatch>' \
    > /etc/apache2/conf-available/givetake-security.conf \
    && a2enconf givetake-security

RUN mkdir -p /var/www/html/uploads \
    && chown -R www-data:www-data /var/www/html/uploads \
    && chmod +x /var/www/html/docker/start.sh

EXPOSE 80

CMD ["/var/www/html/docker/start.sh"]

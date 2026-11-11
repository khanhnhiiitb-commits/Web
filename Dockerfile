
FROM php:8.3-apache

# Cài PHP extensions
RUN apt-get update && apt-get install -y \
    curl \
    ca-certificates \
    libcurl4-openssl-dev \
    && docker-php-ext-install pdo_mysql mysqli curl \
    && a2enmod rewrite headers \
    && rm -rf /var/lib/apt/lists/*

# Cài Cloudflare TCP client
RUN curl -fsSL \
    https://github.com/cloudflare/cloudflared/releases/latest/download/cloudflared-linux-amd64 \
    -o /usr/local/bin/cloudflared \
    && chmod +x /usr/local/bin/cloudflared

WORKDIR /var/www/html

# Copy source Give & Take
COPY . /var/www/html/

# Chặn truy cập HTTP vào mã nguồn và file cấu hình nội bộ
RUN printf '%s\n' \
    '<DirectoryMatch "^/var/www/html/(App|docker|\\.git)(/|$)">' \
    'Require all denied' \
    '</DirectoryMatch>' \
    '<FilesMatch "(^\\.|\\.sql$|\\.sh$|^Dockerfile$|^composer\\.(json|lock)$)">' \
    'Require all denied' \
    '</FilesMatch>' \
    > /etc/apache2/conf-available/givetake-security.conf \
    && a2enconf givetake-security

# Tạo đúng thư mục upload ảnh sản phẩm và cấp quyền cho Apache/PHP
RUN mkdir -p /var/www/html/public/uploads/items \
    && chown -R www-data:www-data /var/www/html/public/uploads \
    && find /var/www/html/public/uploads -type d -exec chmod 775 {} \; \
    && find /var/www/html/public/uploads -type f -exec chmod 664 {} \;

# Cho phép chạy script khởi động Cloudflare + Apache
RUN chmod +x /var/www/html/docker/start.sh

EXPOSE 80

CMD ["/var/www/html/docker/start.sh"]

FROM php:8.2-fpm

# Cài đặt các system dependencies cần thiết (cho Excel, GD, v.v.)
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    libzip-dev \
    libfreetype6-dev \
    libjpeg62-turbo-dev

# Xóa cache apt
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Cài đặt PHP extensions (pdo_mysql là bắt buộc, gd và zip cho Excel)
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip

# Cài đặt Composer vào image
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Thiết lập thư mục làm việc
WORKDIR /var/www

# Copy toàn bộ code vào container
COPY . .

# Cấp quyền cho thư mục storage và bootstrap/cache
RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache

EXPOSE 9000
CMD ["php-fpm"]
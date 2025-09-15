FROM php:8.3-fpm

# Arguments for UID/GID mapping
ARG UID=1000
ARG GID=1000

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git curl libpng-dev libjpeg-dev libfreetype6-dev \
    libonig-dev libxml2-dev zip unzip && \
    docker-php-ext-install pdo pdo_mysql mbstring exif pcntl bcmath gd && \
    rm -rf /var/lib/apt/lists/*

# Create non-root user that matches host UID/GID
RUN groupadd -g ${GID} laravel && \
    useradd -u ${UID} -g laravel -m laravel

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /home/laravel/app

# Copy existing app
COPY . .

# Ensure storage and cache are writable
RUN mkdir -p storage bootstrap/cache && \
    chown -R laravel:laravel /home/laravel/app

# Switch to non-root user
USER laravel

CMD ["php-fpm"]

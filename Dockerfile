FROM php:8.3-cli

# Install system dependencies
RUN apt-get update -y && apt-get install -y \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    zip \
    unzip \
    git \
    nodejs \
    npm

# Install PHP extensions required by Laravel
RUN docker-php-ext-install pdo pdo_mysql mbstring exif pcntl bcmath gd zip xml

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /app

# Copy application files
COPY . /app

# Allow superuser for composer in docker
ENV COMPOSER_ALLOW_SUPERUSER=1

# Install PHP dependencies
RUN composer install --no-dev --optimize-autoloader

# Install Node dependencies and build Vite assets
RUN npm install
RUN npm run build

# Set permissions for Laravel storage and cache
RUN chown -R www-data:www-data /app/storage /app/bootstrap/cache
RUN chmod -R 775 /app/storage /app/bootstrap/cache

# Start the Laravel development server on the port provided by Render
CMD php artisan serve --host=0.0.0.0 --port=${PORT:-10000}

FROM php:8.2-cli

# Install system dependencies
RUN apt-get update -y && apt-get install -y \
    libmcrypt-dev \
    openssl \
    zip \
    unzip \
    git \
    libonig-dev \
    nodejs \
    npm

# Install PHP extensions required by Laravel
RUN docker-php-ext-install pdo pdo_mysql mbstring

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Set working directory
WORKDIR /app

# Copy application files
COPY . /app

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

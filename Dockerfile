# Use official PHP image with built-in server
FROM php:8.1-cli

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libssl-dev \
    pkg-config \
    zip \
    unzip

# Install MongoDB PHP extension
RUN pecl install mongodb && docker-php-ext-enable mongodb

# Get latest Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /app

# Copy application files
COPY . /app

# Install PHP dependencies
RUN composer install --no-dev --optimize-autoloader

# Create uploads directory with proper permissions
RUN mkdir -p uploads && chmod 755 uploads

# Expose port
EXPOSE $PORT

# Start PHP built-in server
CMD php -S 0.0.0.0:$PORT -t .
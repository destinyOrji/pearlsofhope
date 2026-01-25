# Use PHP with built-in server (no Apache)
FROM php:8.1-cli

# Install required extensions
RUN apt-get update && apt-get install -y \
    libssl-dev \
    pkg-config \
    && pecl install mongodb \
    && docker-php-ext-enable mongodb

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /app

# Copy files
COPY . .

# Install dependencies
RUN composer install --no-dev --optimize-autoloader

# Create uploads directory
RUN mkdir -p uploads && chmod 755 uploads

# Use PHP built-in server (NOT Apache)
CMD ["sh", "-c", "php -S 0.0.0.0:${PORT:-8080} -t ."]
# Use PHP 8.0 with Apache
FROM php:8.0-apache

# Install Python 3, pip, and other dependencies
RUN apt-get update && apt-get install -y \
    python3 \
    python3-pip \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    zip \
    unzip \
    && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-install mysqli gd

# Enable Apache mod_rewrite
RUN a2enmod rewrite

# Set working directory
WORKDIR /var/www/html

# Copy application files
# We copy the 'saoo' content to the root of Apache
COPY saoo/ .
# Copy 'timetable-generator' to a sibling folder as expected by PHP scripts
COPY timetable-generator/ /var/www/timetable-generator/
# Copy logs directory (ensure it exists)
COPY logs/ /var/www/logs/

# Create necessary directories and set permissions
RUN mkdir -p /var/www/logs /var/www/uploads \
    && chown -R www-data:www-data /var/www/html \
    && chown -R www-data:www-data /var/www/timetable-generator \
    && chown -R www-data:www-data /var/www/logs \
    && chmod -R 777 /var/www/logs \
    && chmod -R 777 /var/www/timetable-generator/input.json || true

# Environment variables for Demo Mode
ENV DEMO_MODE=true

# Start Apache in foreground
CMD ["apache2-foreground"]

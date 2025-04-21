FROM php:8.1-fpm

# Install dependencies
RUN apt-get update && apt-get install -y \
    libzip-dev \
    zip \
    unzip \
    curl \
    nginx

# Install PHP extensions
RUN docker-php-ext-install pdo_mysql mysqli zip

# Configure PHP-FPM to use a unix socket
RUN mkdir -p /run/php && \
    touch /run/php/php8.1-fpm.sock && \
    chown -R www-data:www-data /run/php && \
    mkdir -p /var/log/php-fpm && \
    chown -R www-data:www-data /var/log/php-fpm

# Copy custom PHP-FPM and nginx configs
COPY php-fpm.conf /usr/local/etc/php-fpm.d/www.conf
COPY nginx.conf /etc/nginx/sites-available/default
RUN ln -sf /etc/nginx/sites-available/default /etc/nginx/sites-enabled/
RUN rm -f /etc/nginx/sites-enabled/default.conf

# Set working directory
WORKDIR /var/www/html

# Copy application files
COPY . /var/www/html/

# Create PHP info file to test PHP processing
RUN echo "<?php phpinfo(); ?>" > /var/www/html/public/info.php
# Create a simple PHP test file
RUN echo "<?php echo '<h1>PHP is working correctly!</h1>'; ?>" > /var/www/html/public/test.php

# Set permissions
RUN chown -R www-data:www-data /var/www/html
RUN chmod -R 755 /var/www/html/public

# Create startup script to run both nginx and php-fpm
RUN echo '#!/bin/bash\necho "Starting nginx and php-fpm..."\nservice nginx start\nphp-fpm -F' > /start.sh
RUN chmod +x /start.sh

# Expose port 80
EXPOSE 80

# Start services
CMD ["/start.sh"] 
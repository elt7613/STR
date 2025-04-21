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

# Copy custom PHP-FPM and nginx configs
COPY php-fpm.conf /usr/local/etc/php-fpm.d/www.conf
COPY nginx.conf /etc/nginx/sites-available/default
RUN ln -sf /etc/nginx/sites-available/default /etc/nginx/sites-enabled/
RUN rm -f /etc/nginx/sites-enabled/default.conf

# Set working directory
WORKDIR /var/www/html

# Copy application files
COPY . /var/www/html/

# Create PHP test files
RUN echo "<?php phpinfo(); ?>" > /var/www/html/public/info.php
RUN echo "<?php echo '<h1>PHP is working correctly!</h1>'; ?>" > /var/www/html/public/test.php
RUN echo "<?php echo 'This is a text file';" > /var/www/html/public/test.txt.php

# Set permissions - very important for fixing 403 errors
RUN chown -R www-data:www-data /var/www/html
RUN find /var/www/html -type d -exec chmod 755 {} \;
RUN find /var/www/html -type f -exec chmod 644 {} \;
RUN chmod 755 /var/www/html/public

# Create and configure log directories
RUN mkdir -p /var/log/nginx /var/log/php-fpm
RUN chown -R www-data:www-data /var/log/php-fpm

# Create startup script
RUN echo '#!/bin/bash\n\
echo "Starting nginx..."\n\
nginx -t\n\
service nginx start\n\
echo "Starting PHP-FPM..."\n\
php-fpm -v\n\
php-fpm --nodaemonize' > /start.sh
RUN chmod +x /start.sh

# Expose port 80
EXPOSE 80

# Start services
CMD ["/start.sh"] 
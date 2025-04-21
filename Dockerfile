FROM php:8.1-apache

# Install dependencies and PHP extensions
RUN apt-get update && apt-get install -y \
    libzip-dev \
    zip \
    unzip \
    curl \
    && docker-php-ext-install pdo_mysql mysqli zip

# Enable Apache modules
RUN a2enmod rewrite headers

# Set document root to public directory
ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# Create directory for Apache pid file
RUN mkdir -p /var/run/apache2

# Copy application files
WORKDIR /var/www/html
COPY . /var/www/html/

# Create test files
RUN echo "<?php phpinfo(); ?>" > /var/www/html/public/info.php
RUN echo "<?php echo 'PHP is working correctly!'; ?>" > /var/www/html/public/test.php

# Set permissions - very important for fixing 403 errors
RUN chown -R www-data:www-data /var/www/html
RUN chmod -R 755 /var/www/html
RUN find /var/www/html -type f -exec chmod 644 {} \;

# Fix permissions for Apache and add write access to necessary directories
RUN chmod -R 775 /var/www/html/public
RUN chmod -R 775 /var/log/ /var/run/

# Create a modified entry point script
COPY docker-entrypoint.sh /usr/local/bin/
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

# Expose port 80
EXPOSE 80

# Set the entrypoint
ENTRYPOINT ["docker-entrypoint.sh"]
CMD ["apache2-foreground"] 
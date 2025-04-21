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

# Configure PHP to handle .php files
RUN { \
        echo '<FilesMatch \.php$>'; \
        echo '  SetHandler application/x-httpd-php'; \
        echo '</FilesMatch>'; \
        echo; \
        echo 'DirectoryIndex index.php index.html'; \
        echo; \
        echo '<Directory /var/www/html>'; \
        echo '  Options -Indexes'; \
        echo '  AllowOverride All'; \
        echo '  Require all granted'; \
        echo '</Directory>'; \
    } > /etc/apache2/conf-available/docker-php.conf && \
    a2enconf docker-php

# Set document root to public directory
ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# Create directory for Apache pid file
RUN mkdir -p /var/run/apache2

# Copy application files
WORKDIR /var/www/html
COPY . /var/www/html/

# Set up PHP configuration
RUN { \
        echo 'short_open_tag = Off'; \
        echo 'expose_php = Off'; \
        echo 'error_reporting = E_ALL & ~E_DEPRECATED & ~E_STRICT'; \
        echo 'display_errors = Off'; \
        echo 'log_errors = On'; \
        echo 'error_log = /var/log/apache2/php_errors.log'; \
        echo 'upload_max_filesize = 20M'; \
        echo 'post_max_size = 20M'; \
        echo 'memory_limit = 256M'; \
        echo 'max_execution_time = 300'; \
        echo 'date.timezone = UTC'; \
    } > /usr/local/etc/php/conf.d/custom-php.ini

# Create test files
RUN echo "<?php phpinfo(); ?>" > /var/www/html/public/info.php
RUN echo "<?php echo 'PHP is working correctly!'; ?>" > /var/www/html/public/test.php
# Create a PHP file with specific extension for testing
RUN echo "<?php echo 'This is a PHP file with .php extension: ' . date('Y-m-d H:i:s'); ?>" > /var/www/html/public/test-php-ext.php

# Set permissions - very important for fixing 403/404 errors
RUN chown -R www-data:www-data /var/www/html
RUN chmod -R 755 /var/www/html
RUN find /var/www/html -type f -exec chmod 644 {} \;
RUN chmod 755 $(find /var/www/html -type d)
RUN chmod 644 $(find /var/www/html/public -name "*.php")

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
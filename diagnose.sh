#!/bin/bash
# Diagnostic script for troubleshooting 403 Forbidden errors and PHP processing issues in Coolify

echo "======== System Information ========"
uname -a
echo ""

echo "======== PHP Version ========"
php -v
echo ""

echo "======== Directory Structure ========"
ls -la /var/www/html/
ls -la /var/www/html/public/
echo ""

echo "======== File Permissions ========"
stat /var/www/html/public/index.php 2>/dev/null || echo "index.php not found"
stat /var/www/html/public/index.html 2>/dev/null || echo "index.html not found"
echo ""

echo "======== PHP-FPM Configuration ========"
if [ -f /usr/local/etc/php-fpm.d/www.conf ]; then
    echo "PHP-FPM pool configuration:"
    grep -E "^[^;]" /usr/local/etc/php-fpm.d/www.conf | grep -E "listen|user|group|pm"
    echo ""
    echo "PHP-FPM socket status:"
    ls -la /run/php/ 2>/dev/null || echo "No sockets found in /run/php/"
    echo ""
    echo "Check if PHP-FPM is running:"
    ps aux | grep php-fpm
else
    echo "PHP-FPM configuration not found at /usr/local/etc/php-fpm.d/www.conf"
fi
echo ""

echo "======== Web Server Configuration ========"
if [ -f /etc/nginx/sites-available/default ]; then
    echo "Nginx configuration:"
    grep -E "fastcgi_pass|root|index" /etc/nginx/sites-available/default
    echo ""
    echo "Check if nginx is running:"
    ps aux | grep nginx
elif [ -f /etc/apache2/sites-available/000-default.conf ]; then
    echo "Apache configuration:"
    cat /etc/apache2/sites-available/000-default.conf
else
    echo "No web server configuration found."
fi
echo ""

echo "======== Creating Test Files ========"
echo "<?php echo 'PHP is working! Current time: ' . date('Y-m-d H:i:s'); ?>" > /var/www/html/public/test.php
echo "<html><body><h1>HTML is working!</h1></body></html>" > /var/www/html/public/test.html
chmod 755 /var/www/html/public/test.php
chmod 755 /var/www/html/public/test.html
echo "Test files created at:"
echo "- /var/www/html/public/test.php"
echo "- /var/www/html/public/test.html"
echo ""

echo "======== Creating Plain PHP File in Root ========"
echo "<?php echo '<h1>PHP Test in Root</h1>'; ?>" > /var/www/html/phptest.php
chmod 755 /var/www/html/phptest.php
echo "Created PHP test file at /var/www/html/phptest.php"
echo ""

echo "======== Creating Symbolic Links ========"
ln -sf /var/www/html/public /var/www/html/public_html
echo "Symbolic link created: /var/www/html/public_html -> /var/www/html/public"
echo ""

echo "======== Setting Permissions ========"
find /var/www/html/public -type d -exec chmod 755 {} \;
find /var/www/html/public -type f -exec chmod 644 {} \;
echo "Permissions set for public directory"
echo ""

echo "======== Testing PHP Configuration ========"
php -r "echo 'PHP CLI is working';" && echo " - PHP CLI test passed" || echo " - PHP CLI test failed"
echo ""

echo "======== Completed ========"
echo "Please try accessing these test URLs:"
echo "1. https://your-app.coolify.io/test.html - Tests static HTML"
echo "2. https://your-app.coolify.io/test.php - Tests PHP processing"
echo "3. https://your-app.coolify.io/info.php - Shows PHP configuration"
echo ""
echo "If PHP files are still being downloaded instead of executed, check:"
echo "- PHP-FPM socket configuration (/run/php/php8.1-fpm.sock)"
echo "- Nginx fastcgi_pass directive (should match the socket path)"
echo "- File permissions (PHP files should be readable by www-data)"
echo "" 
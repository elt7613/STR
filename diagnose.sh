#!/bin/bash
# Diagnostic script for troubleshooting 403 Forbidden errors in Coolify

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
stat /var/www/html/public/index.php
stat /var/www/html/public/index.html
echo ""

echo "======== Web Server Configuration ========"
if [ -f /etc/nginx/sites-available/default ]; then
    echo "Nginx configuration:"
    cat /etc/nginx/sites-available/default
elif [ -f /etc/apache2/sites-available/000-default.conf ]; then
    echo "Apache configuration:"
    cat /etc/apache2/sites-available/000-default.conf
else
    echo "No web server configuration found."
fi
echo ""

echo "======== Creating Test Files ========"
echo "<?php echo 'PHP is working!'; ?>" > /var/www/html/public/test.php
echo "<html><body>HTML is working!</body></html>" > /var/www/html/public/test.html
chmod 755 /var/www/html/public/test.php
chmod 755 /var/www/html/public/test.html
echo "Test files created at:"
echo "- /var/www/html/public/test.php"
echo "- /var/www/html/public/test.html"
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

echo "======== Completed ========"
echo "Please try accessing these test URLs:"
echo "1. https://your-app.coolify.io/test.html"
echo "2. https://your-app.coolify.io/test.php"
echo ""
echo "Also try these alternative paths:"
echo "3. https://your-app.coolify.io/public_html/test.html"
echo "4. https://your-app.coolify.io/public_html/test.php"
echo "" 
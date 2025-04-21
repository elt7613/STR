#!/bin/bash
set -e

# Print environment variables for debugging (excluding passwords)
echo "Environment:"
env | grep -v PASSWORD | grep -v password

# Ensure proper directory permissions
echo "Setting directory permissions..."
find /var/www/html -type d -exec chmod 755 {} \;
find /var/www/html -type f -exec chmod 644 {} \;
chmod -R 775 /var/www/html/public
chown -R www-data:www-data /var/www/html

# Create test file to verify PHP processing
echo "<?php echo 'PHP works - ' . date('Y-m-d H:i:s'); ?>" > /var/www/html/public/php-works.php
chmod 644 /var/www/html/public/php-works.php

# Create debug file
echo '<?php
header("Content-Type: text/plain");
echo "Debug Info:\n";
echo "Server Software: " . $_SERVER["SERVER_SOFTWARE"] . "\n";
echo "Document Root: " . $_SERVER["DOCUMENT_ROOT"] . "\n";
echo "HTTP Host: " . ($_SERVER["HTTP_HOST"] ?? "Not set") . "\n";
echo "Current Directory: " . getcwd() . "\n";
echo "PHP Version: " . phpversion() . "\n";
?>' > /var/www/html/public/debug.php
chmod 644 /var/www/html/public/debug.php

# Check Apache configuration
echo "Testing Apache configuration..."
apache2ctl configtest

# Execute the main command
echo "Starting Apache..."
exec "$@" 
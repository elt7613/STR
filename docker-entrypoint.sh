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

# Create various test files for diagnostics
echo "Creating diagnostic files..."

# Simple PHP test file
echo "<?php echo 'PHP works via Apache - ' . date('Y-m-d H:i:s'); ?>" > /var/www/html/public/php-works.php

# Direct access test
echo "<?php
// This script tests direct access to PHP files
header('Content-Type: text/html');
echo '<html><body>';
echo '<h1>Direct PHP Access Test</h1>';
echo '<p>This file (direct-test.php) is being executed directly, not through the main index.php.</p>';
echo '<p>Current time: ' . date('Y-m-d H:i:s') . '</p>';
echo '<p>PHP Version: ' . phpversion() . '</p>';
echo '</body></html>';
?>" > /var/www/html/public/direct-test.php

# Create debug file
echo '<?php
header("Content-Type: text/plain");
echo "===== Debug Info =====\n\n";
echo "Server Software: " . $_SERVER["SERVER_SOFTWARE"] . "\n";
echo "Document Root: " . $_SERVER["DOCUMENT_ROOT"] . "\n";
echo "Script Filename: " . $_SERVER["SCRIPT_FILENAME"] . "\n";
echo "Request URI: " . $_SERVER["REQUEST_URI"] . "\n";
echo "HTTP Host: " . ($_SERVER["HTTP_HOST"] ?? "Not set") . "\n";
echo "Current Directory: " . getcwd() . "\n";
echo "PHP Version: " . phpversion() . "\n\n";

echo "===== PHP Configuration =====\n\n";
echo "display_errors: " . ini_get("display_errors") . "\n";
echo "error_reporting: " . ini_get("error_reporting") . "\n";
echo "upload_max_filesize: " . ini_get("upload_max_filesize") . "\n";
echo "post_max_size: " . ini_get("post_max_size") . "\n";
echo "memory_limit: " . ini_get("memory_limit") . "\n\n";

echo "===== Apache Modules =====\n\n";
$output = shell_exec("apache2ctl -M");
echo $output . "\n\n";

echo "===== Directory Listing =====\n\n";
$files = scandir("/var/www/html/public");
foreach($files as $file) {
    if($file != "." && $file != "..") {
        echo $file . "\n";
    }
}
?>' > /var/www/html/public/debug.php

# Create index.html for nginx test
echo '<!DOCTYPE html>
<html>
<head>
    <title>Nginx Test</title>
</head>
<body>
    <h1>Nginx Static File Test</h1>
    <p>If you can see this, nginx is serving static files correctly, but might not be properly configured for PHP files.</p>
    <p>Try accessing these test files:</p>
    <ul>
        <li><a href="php-works.php">php-works.php</a> - Simple PHP test</li>
        <li><a href="direct-test.php">direct-test.php</a> - Direct PHP access test</li>
        <li><a href="debug.php">debug.php</a> - Debug information</li>
        <li><a href="vehicle.php">vehicle.php</a> - Your application page</li>
    </ul>
</body>
</html>' > /var/www/html/public/index.html

# Check Apache modules 
echo "Checking Apache modules..."
apache2ctl -M

# Check PHP configuration
echo "Checking PHP configuration..."
php -i | grep "Loaded Configuration File"

# Check Apache configuration
echo "Testing Apache configuration..."
apache2ctl configtest

# Make sure PHP is properly handling PHP files
echo "Ensuring PHP is handling .php files..."
grep -r "application/x-httpd-php" /etc/apache2/

# Execute the main command
echo "Starting Apache..."
exec "$@" 
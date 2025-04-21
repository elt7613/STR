<?php
// Test file to diagnose PHP processing issues

// Output headers to prevent caching
header('Content-Type: text/html; charset=utf-8');
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

// Start HTML
echo '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PHP Processing Test</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 40px;
            line-height: 1.6;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        h1 {
            color: #333;
        }
        .success {
            color: green;
            font-weight: bold;
        }
        .error {
            color: red;
            font-weight: bold;
        }
        code {
            background: #f4f4f4;
            padding: 2px 4px;
            border-radius: 3px;
        }
        pre {
            background: #f4f4f4;
            padding: 10px;
            border-radius: 5px;
            overflow-x: auto;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>PHP Processing Test</h1>';

// Success message
echo '<p class="success">✓ PHP is working correctly! If you can see this, PHP files are being processed properly.</p>';

// Show environment information
echo '<h2>PHP Environment Information</h2>';
echo '<ul>';
echo '<li>PHP Version: ' . phpversion() . '</li>';
echo '<li>Server Software: ' . ($_SERVER['SERVER_SOFTWARE'] ?? 'Not available') . '</li>';
echo '<li>Server Name: ' . ($_SERVER['SERVER_NAME'] ?? 'Not available') . '</li>';
echo '<li>Document Root: ' . ($_SERVER['DOCUMENT_ROOT'] ?? 'Not available') . '</li>';
echo '<li>Current Time: ' . date('Y-m-d H:i:s') . '</li>';
echo '</ul>';

// Show PHP modules
echo '<h2>PHP Modules</h2>';
echo '<pre>';
$modules = get_loaded_extensions();
sort($modules);
echo implode(", ", $modules);
echo '</pre>';

// Show important PHP-FPM configuration values
echo '<h2>PHP Configuration</h2>';
echo '<pre>';
$important_settings = [
    'display_errors', 'error_reporting', 'memory_limit', 
    'upload_max_filesize', 'post_max_size', 'max_execution_time',
    'date.timezone'
];
foreach ($important_settings as $setting) {
    echo $setting . ': ' . ini_get($setting) . "\n";
}
echo '</pre>';

// Complete the HTML
echo '
        <h2>Next Steps</h2>
        <p>Now that PHP is working, you can access these files:</p>
        <ul>
            <li><a href="index.php">Main Application</a></li>
            <li><a href="info.php">PHP Info</a></li>
        </ul>
    </div>
</body>
</html>';
?> 
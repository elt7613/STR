<?php
// Simple test PHP file to verify PHP processing

// Force content type to be text/html
header('Content-Type: text/html');
?>
<!DOCTYPE html>
<html>
<head>
    <title>PHP Test</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; line-height: 1.5; }
        h1 { color: #333; }
        .box { border: 1px solid #ddd; padding: 20px; margin: 20px 0; border-radius: 5px; }
        .success { color: green; font-weight: bold; }
    </style>
</head>
<body>
    <h1>PHP Processing Test</h1>
    
    <div class="box">
        <p class="success">PHP is working correctly!</p>
        <p>If you can see this page styled with CSS, both PHP processing and static file serving are working.</p>
        <p>Current time: <?php echo date('Y-m-d H:i:s'); ?></p>
        <p>PHP Version: <?php echo phpversion(); ?></p>
        <p>Server Software: <?php echo $_SERVER['SERVER_SOFTWARE']; ?></p>
    </div>
    
    <div class="box">
        <h2>Other Test Files</h2>
        <ul>
            <li><a href="info.php">PHP Info</a> - Detailed PHP configuration</li>
            <li><a href="php-test.php">PHP Test</a> - More detailed tests</li>
            <li><a href="debug.php">Debug Info</a> - Server information</li>
        </ul>
    </div>
</body>
</html> 
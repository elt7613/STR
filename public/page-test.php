<?php
// Test file to mimic application pages like vehicle.php

// Start output buffering to ensure headers can be sent
ob_start();

// Force content type to be text/html
header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <title>Application Page Test</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; line-height: 1.5; }
        h1 { color: #333; }
        .box { border: 1px solid #ddd; padding: 20px; margin: 20px 0; border-radius: 5px; }
        .success { color: green; font-weight: bold; }
        .error { color: red; font-weight: bold; }
    </style>
</head>
<body>
    <h1>Application Page Test</h1>
    
    <div class="box">
        <p class="success">This mimics an application page like vehicle.php!</p>
        <p>If you see this instead of being prompted to download the file, PHP processing is working correctly.</p>
        <p>This file (page-test.php) is specifically designed to test if PHP files are being processed properly.</p>
        <hr>
        <p>Current time: <?php echo date('Y-m-d H:i:s'); ?></p>
        <p>PHP Version: <?php echo phpversion(); ?></p>
        <p>Server Software: <?php echo $_SERVER['SERVER_SOFTWARE']; ?></p>
    </div>
    
    <div class="box">
        <h2>Request Information</h2>
        <ul>
            <li>Request URI: <?php echo $_SERVER['REQUEST_URI']; ?></li>
            <li>Script Name: <?php echo $_SERVER['SCRIPT_NAME']; ?></li>
            <li>HTTP Host: <?php echo $_SERVER['HTTP_HOST'] ?? 'Not available'; ?></li>
            <li>Document Root: <?php echo $_SERVER['DOCUMENT_ROOT']; ?></li>
        </ul>
    </div>
    
    <div class="box">
        <h2>Next Steps</h2>
        <p>If this page displays correctly, try accessing these other files:</p>
        <ul>
            <li><a href="vehicle.php">vehicle.php</a> - Your actual application page</li>
            <li><a href="debug.php">debug.php</a> - Technical diagnostic information</li>
            <li><a href="direct-test.php">direct-test.php</a> - Another PHP test</li>
        </ul>
    </div>
</body>
</html>
<?php
// End output buffering and send the content
ob_end_flush();
?> 
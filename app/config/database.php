<?php
// Database connection configuration

// URL-based database connection (getting parameters from URL)
$databaseUrl = 'mysql://mysql:vjKSSrMJRPWdMmsb3Kr3bgRrknx6mFOb4qtIaXAMO1ncQ4AVAjlvrKbQW60kX2Zf@106.51.142.222:7845/default';

try {
    // Parse database URL components
    $dbComponents = parse_url($databaseUrl);
    
    // Extract database connection details from URL
    $servername = $dbComponents['host'] ?? '';
    $port = $dbComponents['port'] ?? '';
    $username = $dbComponents['user'] ?? '';
    $password = $dbComponents['pass'] ?? '';
    $dbname = ltrim($dbComponents['path'] ?? '', '/');

    // $servername = "localhost";
    // $username = "u599418396_str_database";
    // $password = "STR_database@123#";
    // $dbname = "u599418396_str";
    
    // If port is specified, add it to the server name
    if (!empty($port)) {
        $servername .= ":" . $port;
    }
    
    // Create connection
    $conn = mysqli_connect($servername, $username, $password, $dbname);
    
    // Check connection
    if (!$conn) {
        die("Connection failed: " . mysqli_connect_error());
    }
    //echo "Connected successfully"; 
} catch(Exception $e) {
    echo "Connection failed: " . $e->getMessage();
}

date_default_timezone_set("Asia/Kolkata");

// Only start session if it hasn't been started already
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?> 
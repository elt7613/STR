<?php
// Database connection configuration

// URL-based database connection (getting parameters from URL)
$databaseUrl = 'mysql://mysql:vjKSSrMJRPWdMmsb3Kr3bgRrknx6mFOb4qtIaXAMO1ncQ4AVAjlvrKbQW60kX2Zf@106.51.142.222:7845/default';

// Initialize $pdo and $conn as null
$pdo = null;
$conn = null;

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
    
    // If port is specified, add it to the server name for mysqli connection
    $serverWithPort = $servername;
    if (!empty($port)) {
        $serverWithPort .= ":" . $port;
    }
    
    // Create MySQLi connection
    $conn = mysqli_connect($serverWithPort, $username, $password, $dbname);
    
    // Check connection
    if (!$conn) {
        die("MySQLi Connection failed: " . mysqli_connect_error());
    }

    // Create PDO connection
    $dsn = "mysql:host={$servername};";
    if (!empty($port)) {
        $dsn .= "port={$port};";
    }
    $dsn .= "dbname={$dbname};charset=utf8mb4";
    
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];
    
    $pdo = new PDO($dsn, $username, $password, $options);
    
    //echo "Connected successfully"; 
} catch(PDOException $e) {
    die("PDO Connection failed: " . $e->getMessage());
} catch(Exception $e) {
    die("Connection failed: " . $e->getMessage());
}

date_default_timezone_set("Asia/Kolkata");

// Only start session if it hasn't been started already
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?> 
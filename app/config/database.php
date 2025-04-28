<?php
// Database connection configuration

// Initialize $pdo and $conn as null
$pdo = null;
$conn = null;

try {
    // Get database connection details from environment variables (for Coolify)
    $servername = getenv('DB_HOST') ?: '106.51.142.222';
    $port = getenv('DB_PORT') ?: '7845';
    $username = getenv('DB_USERNAME') ?: 'mysql';
    $password = getenv('DB_PASSWORD') ?: 'vjKSSrMJRPWdMmsb3Kr3bgRrknx6mFOb4qtIaXAMO1ncQ4AVAjlvrKbQW60kX2Zf';
    $dbname = getenv('DB_DATABASE') ?: 'default';
    
    // If port is specified, add it to the server name for mysqli connection
    $serverWithPort = $servername;
    if (!empty($port)) {
        $serverWithPort .= ":" . $port;
    }
    
    // Set timeout values
    $timeout = 10; // 10 seconds connection timeout (up from default)
    
    // Create MySQLi connection with timeout
    $conn = mysqli_init();
    mysqli_options($conn, MYSQLI_OPT_CONNECT_TIMEOUT, $timeout);
    mysqli_real_connect($conn, $servername, $username, $password, $dbname, $port);
    
    // Check connection
    if (mysqli_connect_errno()) {
        die("MySQLi Connection failed: " . mysqli_connect_error());
    }

    // Create PDO connection with increased timeout
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
    
    // Add timeout settings directly to the connection string instead of as attributes
    $dsn .= ";connect_timeout={$timeout}";
    
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
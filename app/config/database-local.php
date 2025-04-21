<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    // Database credentials
    $servername = "localhost";
    $username = "u599418396_str";
    $password = "STR_database@123#";
    $dbname = "u599418396_str_database";

    // Create mysqli connection first
    $conn = new mysqli($servername, $username, $password, $dbname);
    
    // Check mysqli connection
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }
    
    // Create PDO connection
    $dsn = "mysql:host=$servername;dbname=$dbname;charset=utf8mb4";
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];
    $pdo = new PDO($dsn, $username, $password, $options);

} catch (Exception $e) {
    die("Connection failed: " . $e->getMessage());
}

date_default_timezone_set("Asia/Kolkata");

// Only start session if it hasn't been started already
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>

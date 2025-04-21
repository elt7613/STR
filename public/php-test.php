<?php
// Simple test file to diagnose PHP processing issues

// Set content type to text/plain for easier debugging
header('Content-Type: text/plain');

// Output basic information
echo "PHP is working!\n";
echo "PHP Version: " . phpversion() . "\n";
echo "Server Software: " . ($_SERVER['SERVER_SOFTWARE'] ?? 'Not available') . "\n";
echo "Document Root: " . ($_SERVER['DOCUMENT_ROOT'] ?? 'Not available') . "\n\n";

// Test database connection
echo "Testing database connection...\n";
try {
    $host = getenv('DB_HOST') ?: '127.0.0.1';
    $port = getenv('DB_PORT') ?: '3306';
    $dbname = getenv('DB_DATABASE') ?: 'default';
    $username = getenv('DB_USERNAME') ?: 'mysql';
    $password = getenv('DB_PASSWORD') ?: 'password';
    
    $dsn = "mysql:host=$host;port=$port;dbname=$dbname";
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_TIMEOUT => 3,
    ];
    
    $pdo = new PDO($dsn, $username, $password, $options);
    echo "Database connection successful!\n";
} catch (PDOException $e) {
    echo "Database connection failed: " . $e->getMessage() . "\n";
}

// Test file system
echo "\nTesting file system...\n";
$testFile = __DIR__ . '/test-write.txt';
try {
    $content = "Test write at " . date('Y-m-d H:i:s');
    file_put_contents($testFile, $content);
    echo "File write successful: $testFile\n";
    echo "Content: " . file_get_contents($testFile) . "\n";
    unlink($testFile);
    echo "File delete successful\n";
} catch (Exception $e) {
    echo "File system test failed: " . $e->getMessage() . "\n";
}

// Done
echo "\nTest completed successfully!\n";
?> 
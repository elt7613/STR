<?php
/**
 * Database Schema Update Script
 * This script will ensure all required database tables and columns exist
 */

// Include initialization script
require_once __DIR__ . '/../includes/init.php';

// Set content type to plain text
header('Content-Type: text/plain');

echo "Starting database schema update...\n\n";

// Make sure users table and columns exist
try {
    // Check if phone column exists in users table
    $stmt = $pdo->query("SHOW COLUMNS FROM users LIKE 'phone'");
    if ($stmt->rowCount() == 0) {
        echo "Adding 'phone' column to users table...\n";
        $pdo->exec("ALTER TABLE users ADD COLUMN phone VARCHAR(20) DEFAULT '' AFTER password");
        echo "Phone column added successfully.\n";
    } else {
        echo "Phone column already exists.\n";
    }
    
    echo "\nSchema update completed successfully.";
} catch (PDOException $e) {
    echo "Error updating schema: " . $e->getMessage();
    exit;
}
?> 
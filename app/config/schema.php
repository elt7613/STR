<?php
// Include database connection
require_once __DIR__ . '/database.php';

// Check if $pdo is defined
if (!isset($pdo)) {
    die("Database connection error: PDO variable is not defined in database.php");
}

// Function to check if users table exists, if not create it
function ensureUsersTableExists($pdo) {
    $sql = "CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) NOT NULL UNIQUE,
        email VARCHAR(100) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        is_admin TINYINT(1) DEFAULT 0,
        is_premium_member TINYINT(1) DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    $pdo->exec($sql);
    
    // Check if is_admin column exists, add it if not
    try {
        $stmt = $pdo->query("SHOW COLUMNS FROM users LIKE 'is_admin'");
        if ($stmt->rowCount() == 0) {
            $pdo->exec("ALTER TABLE users ADD COLUMN is_admin TINYINT(1) DEFAULT 0");
        }
    } catch (PDOException $e) {
        // Column likely already exists or table doesn't exist yet
    }
    
    // Check if is_premium_member column exists, add it if not
    try {
        $stmt = $pdo->query("SHOW COLUMNS FROM users LIKE 'is_premium_member'");
        if ($stmt->rowCount() == 0) {
            $pdo->exec("ALTER TABLE users ADD COLUMN is_premium_member TINYINT(1) DEFAULT 0");
        }
    } catch (PDOException $e) {
        // Column likely already exists or table doesn't exist yet
    }
    
    // Create at least one admin user if none exists
    try {
        $stmt = $pdo->query("SELECT COUNT(*) as admin_count FROM users WHERE is_admin = 1");
        $result = $stmt->fetch();
        
        if ($result && $result['admin_count'] == 0) {
            // Create default admin user (admin/admin123)
            $adminUsername = 'admin';
            $adminEmail = 'admin@example.com';
            $adminPassword = password_hash('admin123', PASSWORD_DEFAULT);
            
            // Check if admin user already exists
            $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? OR email = ?");
            $stmt->execute([$adminUsername, $adminEmail]);
            
            if ($stmt->rowCount() == 0) {
                $stmt = $pdo->prepare("INSERT INTO users (username, email, password, is_admin) VALUES (?, ?, ?, 1)");
                $stmt->execute([$adminUsername, $adminEmail, $adminPassword]);
            } else {
                // Update existing user to be admin
                $admin = $stmt->fetch();
                $stmt = $pdo->prepare("UPDATE users SET is_admin = 1 WHERE id = ?");
                $stmt->execute([$admin['id']]);
            }
        }
    } catch (PDOException $e) {
        // Likely table doesn't exist yet, will be created above
    }
}

// Create users table if it doesn't exist
ensureUsersTableExists($pdo);
?> 
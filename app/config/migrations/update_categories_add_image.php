<?php
/**
 * Migration script to add image_path column to categories table
 */

// Include database connection
require_once __DIR__ . '/../database.php';

// Check if the database connection is established
if (!$pdo) {
    echo "Failed to connect to the database.\n";
    exit(1);
}

echo "Starting migration to add image_path to categories...\n";

// Check if image_path column already exists in categories table
$stmt = $pdo->query("SHOW COLUMNS FROM categories LIKE 'image_path'");
if ($stmt->rowCount() > 0) {
    echo "Image_path column already exists in categories table.\n";
    exit;
}

// Add image_path column to categories table
try {
    $pdo->exec("ALTER TABLE categories ADD COLUMN image_path VARCHAR(255) NULL");
    echo "Added image_path column to categories table.\n";
    
    // Create uploads directory if it doesn't exist
    $uploadDir = dirname(__DIR__, 3) . '/public/uploads/categories/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
        echo "Created categories upload directory: $uploadDir\n";
    }
    
    echo "Migration completed successfully.\n";
} catch (PDOException $e) {
    echo "Error adding image_path column: " . $e->getMessage() . "\n";
    exit(1);
}
?> 
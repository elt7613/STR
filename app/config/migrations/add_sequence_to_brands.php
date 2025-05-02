<?php
/**
 * Migration script to add sequence field to brands table
 */

// Include database configuration
require_once __DIR__ . '/../database.php';

echo "Starting migration to add sequence field to brands table...\n";

try {
    // Ensure we have a PDO connection
    if (!isset($pdo) || !($pdo instanceof PDO)) {
        echo "Error: No valid database connection found.\n";
        exit(1);
    }
    
    // Check if sequence column already exists
    $columnExists = false;
    $columns = $pdo->query("SHOW COLUMNS FROM brands LIKE 'sequence'")->fetchAll();
    
    if (count($columns) > 0) {
        echo "Sequence column already exists in brands table.\n";
        $columnExists = true;
    }
    
    if (!$columnExists) {
        // Add sequence column with default value of 999
        $pdo->exec("ALTER TABLE brands ADD COLUMN sequence INT DEFAULT 999");
        echo "Sequence column added successfully to brands table.\n";
        
        // Create an array to store current brands
        $brands = $pdo->query("SELECT id, name FROM brands ORDER BY name")->fetchAll();
        
        // Assign sequential values starting from 1
        $sequence = 1;
        foreach ($brands as $brand) {
            $stmt = $pdo->prepare("UPDATE brands SET sequence = ? WHERE id = ?");
            $stmt->execute([$sequence, $brand['id']]);
            echo "Updated brand '{$brand['name']}' with sequence {$sequence}.\n";
            $sequence++;
        }
    }
    
    echo "Migration completed successfully.\n";
} catch (PDOException $e) {
    echo "Migration failed: " . $e->getMessage() . "\n";
} 
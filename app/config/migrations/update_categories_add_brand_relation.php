<?php
/**
 * Migration script to update categories table and associate categories with brands
 */

require_once __DIR__ . '/../database.php';

// Check if the database connection is established
if (!$pdo) {
    echo "Failed to connect to the database.\n";
    exit(1);
}

echo "Starting migration to add brand relationship to categories...\n";

try {
    // Check if brand_id column already exists in categories table
    $columns = $pdo->query("SHOW COLUMNS FROM categories LIKE 'brand_id'")->fetchAll();
    
    if (count($columns) > 0) {
        echo "Brand_id column already exists in categories table.\n";
    } else {
        // Start transaction
        $pdo->beginTransaction();

        // Get default brand (first brand in the system)
        $defaultBrand = $pdo->query("SELECT id FROM brands ORDER BY id LIMIT 1")->fetch();
        
        if (!$defaultBrand) {
            // Create a default brand if none exists
            echo "No brands found. Creating a default brand...\n";
            $pdo->exec("INSERT INTO brands (name, image, sequence) VALUES ('Default Brand', 'assets/images/default-brand.png', 1)");
            $defaultBrandId = $pdo->lastInsertId();
            echo "Created default brand with ID: $defaultBrandId\n";
        } else {
            $defaultBrandId = $defaultBrand['id'];
        }

        // Temporarily remove unique constraint on category name if it exists
        try {
            $pdo->exec("ALTER TABLE categories DROP INDEX name");
            echo "Removed unique constraint on category name.\n";
        } catch (PDOException $e) {
            echo "No unique constraint on name to remove or error: " . $e->getMessage() . "\n";
        }

        // Add brand_id column
        $pdo->exec("ALTER TABLE categories ADD COLUMN brand_id INT NOT NULL DEFAULT $defaultBrandId");
        echo "Added brand_id column to categories table with default value: $defaultBrandId\n";

        // Add foreign key constraint
        $pdo->exec("ALTER TABLE categories ADD CONSTRAINT fk_category_brand FOREIGN KEY (brand_id) REFERENCES brands(id) ON DELETE CASCADE");
        echo "Added foreign key constraint to brand_id column.\n";

        // Add unique constraint for name and brand_id
        $pdo->exec("ALTER TABLE categories ADD UNIQUE KEY unique_category_name_per_brand (name, brand_id)");
        echo "Added unique constraint on name and brand_id.\n";

        // Commit the transaction
        $pdo->commit();
        echo "Changes committed successfully.\n";
    }

    echo "Migration completed successfully.\n";
} catch (PDOException $e) {
    // Rollback the transaction if any step fails
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo "Migration failed: " . $e->getMessage() . "\n";
    exit(1);
} 
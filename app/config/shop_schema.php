<?php
// Include database connection
require_once __DIR__ . '/database.php';

// Function to check if brands table exists, if not create it
function ensureBrandsTableExists($pdo) {
    $sql = "CREATE TABLE IF NOT EXISTS brands (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL UNIQUE,
        image VARCHAR(255) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    $pdo->exec($sql);
}

// Function to check if categories table exists, if not create it
function ensureCategoriesTableExists($pdo) {
    $sql = "CREATE TABLE IF NOT EXISTS categories (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL UNIQUE,
        description TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    $pdo->exec($sql);
}

// Function to check if products table exists, if not create it
function ensureProductsTableExists($pdo) {
    $sql = "CREATE TABLE IF NOT EXISTS products (
        id INT AUTO_INCREMENT PRIMARY KEY,
        brand_id INT NOT NULL,
        title VARCHAR(100) NOT NULL,
        amount DECIMAL(10,2) NOT NULL,
        description TEXT NOT NULL,
        make_id INT NULL,
        model_id INT NULL,
        series_id INT NULL,
        direct_buying TINYINT(1) DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (brand_id) REFERENCES brands(id) ON DELETE CASCADE,
        FOREIGN KEY (make_id) REFERENCES vehicle_makes(id) ON DELETE SET NULL,
        FOREIGN KEY (model_id) REFERENCES vehicle_models(id) ON DELETE SET NULL,
        FOREIGN KEY (series_id) REFERENCES vehicle_series(id) ON DELETE SET NULL
    )";
    $pdo->exec($sql);
}

// Function to check if product_categories table exists, if not create it
function ensureProductCategoriesTableExists($pdo) {
    $sql = "CREATE TABLE IF NOT EXISTS product_categories (
        id INT AUTO_INCREMENT PRIMARY KEY,
        product_id INT NOT NULL,
        category_id INT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY unique_product_category (product_id, category_id),
        FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
        FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE
    )";
    $pdo->exec($sql);
}

// Function to check if product_images table exists, if not create it
function ensureProductImagesTableExists($pdo) {
    $sql = "CREATE TABLE IF NOT EXISTS product_images (
        id INT AUTO_INCREMENT PRIMARY KEY,
        product_id INT NOT NULL,
        image_path VARCHAR(255) NOT NULL,
        is_primary TINYINT(1) DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
    )";
    $pdo->exec($sql);
}

// Function to update the products table with vehicle fields if they don't exist
function updateProductsTableWithVehicleFields($pdo) {
    try {
        // Check if make_id column already exists
        $result = $pdo->query("SHOW COLUMNS FROM products LIKE 'make_id'");
        if ($result->rowCount() == 0) {
            // Add the make_id column
            $pdo->exec("ALTER TABLE products ADD COLUMN make_id INT NULL");
            $pdo->exec("ALTER TABLE products ADD CONSTRAINT fk_product_make FOREIGN KEY (make_id) REFERENCES vehicle_makes(id) ON DELETE SET NULL");
        }
        
        // Check if model_id column already exists
        $result = $pdo->query("SHOW COLUMNS FROM products LIKE 'model_id'");
        if ($result->rowCount() == 0) {
            // Add the model_id column
            $pdo->exec("ALTER TABLE products ADD COLUMN model_id INT NULL");
            $pdo->exec("ALTER TABLE products ADD CONSTRAINT fk_product_model FOREIGN KEY (model_id) REFERENCES vehicle_models(id) ON DELETE SET NULL");
        }
        
        // Check if series_id column already exists
        $result = $pdo->query("SHOW COLUMNS FROM products LIKE 'series_id'");
        if ($result->rowCount() == 0) {
            // Add the series_id column
            $pdo->exec("ALTER TABLE products ADD COLUMN series_id INT NULL");
            $pdo->exec("ALTER TABLE products ADD CONSTRAINT fk_product_series FOREIGN KEY (series_id) REFERENCES vehicle_series(id) ON DELETE SET NULL");
        }
        
        // Check if direct_buying column already exists
        $result = $pdo->query("SHOW COLUMNS FROM products LIKE 'direct_buying'");
        if ($result->rowCount() == 0) {
            // Add the direct_buying column
            $pdo->exec("ALTER TABLE products ADD COLUMN direct_buying TINYINT(1) DEFAULT 0");
        }
    } catch (PDOException $e) {
        echo "Error updating products table: " . $e->getMessage();
    }
}

// Create tables if they don't exist
ensureBrandsTableExists($pdo);
ensureCategoriesTableExists($pdo);
ensureProductsTableExists($pdo);
ensureProductImagesTableExists($pdo);
ensureProductCategoriesTableExists($pdo);

// Update products table with vehicle fields
updateProductsTableWithVehicleFields($pdo);
?> 
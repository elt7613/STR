<?php
/**
 * Simple Database Migration System
 * 
 * This single file creates or updates all database tables.
 * Run this file directly to execute all database schema updates.
 */

// Include database connection
require_once __DIR__ . '/database.php';

// Check if $pdo is defined
if (!isset($pdo)) {
    die("Database connection error: PDO variable is not defined in database.php");
}

// Display message to console or browser
function output($message) {
    if (function_exists('browserOutput')) {
        // If included from web runner, use its output function
        browserOutput($message);
    } else {
        // Default output to console or browser
        echo $message . (PHP_SAPI === 'cli' ? "\n" : "<br>");
        // Flush output for real-time progress in browser
        if (PHP_SAPI !== 'cli' && ob_get_level() > 0) {
            ob_flush();
            flush();
        }
    }
}

// Function to create or update users table
function createUsersTable($pdo) {
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
    output("✓ Users table created or updated");
    
    // Check if is_admin column exists, add it if not
    try {
        $stmt = $pdo->query("SHOW COLUMNS FROM users LIKE 'is_admin'");
        if ($stmt->rowCount() == 0) {
            $pdo->exec("ALTER TABLE users ADD COLUMN is_admin TINYINT(1) DEFAULT 0");
            output("  Added is_admin column to users table");
        }
    } catch (PDOException $e) {
        // Column likely already exists or table doesn't exist yet
    }
    
    // Check if is_premium_member column exists, add it if not
    try {
        $stmt = $pdo->query("SHOW COLUMNS FROM users LIKE 'is_premium_member'");
        if ($stmt->rowCount() == 0) {
            $pdo->exec("ALTER TABLE users ADD COLUMN is_premium_member TINYINT(1) DEFAULT 0");
            output("  Added is_premium_member column to users table");
        }
    } catch (PDOException $e) {
        // Column likely already exists or table doesn't exist yet
    }
}

// Function to create or update vehicle tables
function createVehicleTables($pdo) {
    // Vehicle Makes table
    $sql = "CREATE TABLE IF NOT EXISTS vehicle_makes (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(50) NOT NULL UNIQUE
    )";
    $pdo->exec($sql);
    output("✓ Vehicle makes table created or updated");
    
    // Vehicle Models table
    $sql = "CREATE TABLE IF NOT EXISTS vehicle_models (
        id INT AUTO_INCREMENT PRIMARY KEY,
        make_id INT NOT NULL,
        name VARCHAR(100) NOT NULL,
        UNIQUE KEY (make_id, name),
        FOREIGN KEY (make_id) REFERENCES vehicle_makes(id) ON DELETE CASCADE
    )";
    $pdo->exec($sql);
    output("✓ Vehicle models table created or updated");
    
    // Vehicle Series table
    $sql = "CREATE TABLE IF NOT EXISTS vehicle_series (
        id INT AUTO_INCREMENT PRIMARY KEY,
        model_id INT NOT NULL,
        name VARCHAR(100) NOT NULL,
        UNIQUE KEY (model_id, name),
        FOREIGN KEY (model_id) REFERENCES vehicle_models(id) ON DELETE CASCADE
    )";
    $pdo->exec($sql);
    output("✓ Vehicle series table created or updated");
    
    // Vehicle Submissions table
    $sql = "CREATE TABLE IF NOT EXISTS vehicle_submissions (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        make_id INT NOT NULL,
        model_id INT NOT NULL, 
        series_id INT NOT NULL,
        phone VARCHAR(20) NOT NULL,
        email VARCHAR(100) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        FOREIGN KEY (make_id) REFERENCES vehicle_makes(id),
        FOREIGN KEY (model_id) REFERENCES vehicle_models(id),
        FOREIGN KEY (series_id) REFERENCES vehicle_series(id)
    )";
    $pdo->exec($sql);
    output("✓ Vehicle submissions table created or updated");
}

// Function to create or update shop tables
function createShopTables($pdo) {
    // Brands table
    $sql = "CREATE TABLE IF NOT EXISTS brands (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL UNIQUE,
        image VARCHAR(255) NOT NULL,
        sequence INT DEFAULT 999,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    $pdo->exec($sql);
    output("✓ Brands table created or updated");
    
    // Check for sequence column
    try {
        $stmt = $pdo->query("SHOW COLUMNS FROM brands LIKE 'sequence'");
        if ($stmt->rowCount() == 0) {
            $pdo->exec("ALTER TABLE brands ADD COLUMN sequence INT DEFAULT 999");
            output("  Added sequence column to brands table");
        }
    } catch (PDOException $e) {
        // Column likely already exists or table doesn't exist yet
    }

    // Categories table
    $sql = "CREATE TABLE IF NOT EXISTS categories (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        description TEXT,
        brand_id INT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (brand_id) REFERENCES brands(id) ON DELETE CASCADE,
        UNIQUE KEY unique_category_name_per_brand (name, brand_id)
    )";
    $pdo->exec($sql);
    output("✓ Categories table created or updated");
    
    // Check for brand_id column in categories
    try {
        $stmt = $pdo->query("SHOW COLUMNS FROM categories LIKE 'brand_id'");
        if ($stmt->rowCount() == 0) {
            // Get default brand (first brand in the system)
            $defaultBrand = $pdo->query("SELECT id FROM brands ORDER BY id LIMIT 1")->fetch();
            
            if (!$defaultBrand) {
                // Create a default brand if none exists
                $pdo->exec("INSERT INTO brands (name, image, sequence) VALUES ('Default Brand', 'assets/images/default-brand.png', 1)");
                $defaultBrandId = $pdo->lastInsertId();
            } else {
                $defaultBrandId = $defaultBrand['id'];
            }
            
            // Add brand_id column
            $pdo->exec("ALTER TABLE categories ADD COLUMN brand_id INT NOT NULL DEFAULT $defaultBrandId");
            $pdo->exec("ALTER TABLE categories ADD CONSTRAINT fk_category_brand FOREIGN KEY (brand_id) REFERENCES brands(id) ON DELETE CASCADE");
            $pdo->exec("ALTER TABLE categories ADD UNIQUE KEY unique_category_name_per_brand (name, brand_id)");
            output("  Added brand relationship to categories table");
        }
    } catch (PDOException $e) {
        // Column likely already exists or table doesn't exist yet
    }
    
    // Update categories table with image_path field if it doesn't exist
    $sql = "SHOW COLUMNS FROM categories LIKE 'image_path'";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    if ($stmt->rowCount() === 0) {
        $sql = "ALTER TABLE categories ADD COLUMN image_path VARCHAR(255) NULL";
        $pdo->exec($sql);
        output("  Added image_path column to categories table");
    }
    
    // Products table
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
    output("✓ Products table created or updated");
    
    // Check for vehicle fields in products
    try {
        $columns = ['make_id', 'model_id', 'series_id'];
        foreach ($columns as $column) {
            $stmt = $pdo->query("SHOW COLUMNS FROM products LIKE '$column'");
            if ($stmt->rowCount() == 0) {
                $pdo->exec("ALTER TABLE products ADD COLUMN $column INT NULL");
                $refTable = $column == 'make_id' ? 'vehicle_makes' : ($column == 'model_id' ? 'vehicle_models' : 'vehicle_series');
                $pdo->exec("ALTER TABLE products ADD CONSTRAINT fk_product_" . str_replace('_id', '', $column) . " FOREIGN KEY ($column) REFERENCES $refTable(id) ON DELETE SET NULL");
                output("  Added $column to products table");
            }
        }
    } catch (PDOException $e) {
        // Column likely already exists or table doesn't exist yet
    }
    
    // Product Categories table
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
    output("✓ Product categories table created or updated");
    
    // Product Images table
    $sql = "CREATE TABLE IF NOT EXISTS product_images (
        id INT AUTO_INCREMENT PRIMARY KEY,
        product_id INT NOT NULL,
        image_path VARCHAR(255) NOT NULL,
        is_primary TINYINT(1) DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
    )";
    $pdo->exec($sql);
    output("✓ Product images table created or updated");
    
    // Shopping Cart Items table
    $sql = "CREATE TABLE IF NOT EXISTS shopping_cart_items (
        id INT AUTO_INCREMENT PRIMARY KEY,
        session_id VARCHAR(255) NOT NULL,
        user_id INT NULL,
        product_id INT NOT NULL,
        quantity INT NOT NULL DEFAULT 1,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )";
    $pdo->exec($sql);
    output("✓ Shopping cart items table created or updated");
    
    // Orders table
    $sql = "CREATE TABLE IF NOT EXISTS orders (
        id INT AUTO_INCREMENT PRIMARY KEY,
        order_number VARCHAR(50) NOT NULL UNIQUE,
        user_id INT NULL,
        session_id VARCHAR(255) NULL,
        status VARCHAR(50) NOT NULL DEFAULT 'pending',
        payment_method VARCHAR(50) NOT NULL,
        payment_status VARCHAR(50) NOT NULL DEFAULT 'pending',
        subtotal DECIMAL(10,2) NOT NULL,
        shipping_cost DECIMAL(10,2) DEFAULT 0.00,
        discount_code VARCHAR(50) NULL,
        discount_amount DECIMAL(10,2) DEFAULT 0.00,
        gst_amount DECIMAL(10,2) DEFAULT 0.00,
        total DECIMAL(10,2) NOT NULL,
        currency VARCHAR(10) NOT NULL DEFAULT 'INR',
        notes TEXT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
    )";
    $pdo->exec($sql);
    output("✓ Orders table created or updated");
    
    // Check for discount fields in orders
    try {
        $stmt = $pdo->query("SHOW COLUMNS FROM orders LIKE 'discount_code'");
        if ($stmt->rowCount() == 0) {
            $pdo->exec("ALTER TABLE orders ADD COLUMN discount_code VARCHAR(50) NULL AFTER shipping_cost");
            $pdo->exec("ALTER TABLE orders ADD COLUMN discount_amount DECIMAL(10,2) DEFAULT 0.00 AFTER discount_code");
            output("  Added discount fields to orders table");
        }
    } catch (PDOException $e) {
        // Column likely already exists or table doesn't exist yet
    }
    
    // Check for GST field in orders
    try {
        $stmt = $pdo->query("SHOW COLUMNS FROM orders LIKE 'gst_amount'");
        if ($stmt->rowCount() == 0) {
            $pdo->exec("ALTER TABLE orders ADD COLUMN gst_amount DECIMAL(10,2) DEFAULT 0.00 AFTER discount_amount");
            output("  Added GST amount field to orders table");
        }
    } catch (PDOException $e) {
        // Column likely already exists or table doesn't exist yet
    }
    
    // Order Items table
    $sql = "CREATE TABLE IF NOT EXISTS order_items (
        id INT AUTO_INCREMENT PRIMARY KEY,
        order_id INT NOT NULL,
        product_id INT NOT NULL,
        quantity INT NOT NULL,
        price DECIMAL(10,2) NOT NULL,
        subtotal DECIMAL(10,2) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
        FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
    )";
    $pdo->exec($sql);
    output("✓ Order items table created or updated");
    
    // Billing Details table
    $sql = "CREATE TABLE IF NOT EXISTS billing_details (
        id INT AUTO_INCREMENT PRIMARY KEY,
        order_id INT NOT NULL,
        first_name VARCHAR(100) NOT NULL,
        last_name VARCHAR(100) NOT NULL,
        company_name VARCHAR(100) NULL,
        country VARCHAR(100) NOT NULL,
        street_address_1 VARCHAR(255) NOT NULL,
        street_address_2 VARCHAR(255) NULL,
        city VARCHAR(100) NOT NULL,
        state VARCHAR(100) NOT NULL,
        postcode VARCHAR(20) NOT NULL,
        phone VARCHAR(50) NOT NULL,
        email VARCHAR(100) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
    )";
    $pdo->exec($sql);
    output("✓ Billing details table created or updated");
    
    // Payment Transactions table
    $sql = "CREATE TABLE IF NOT EXISTS payment_transactions (
        id INT AUTO_INCREMENT PRIMARY KEY,
        order_id INT NOT NULL,
        transaction_id VARCHAR(255) NULL,
        payment_method VARCHAR(50) NOT NULL,
        amount DECIMAL(10,2) NOT NULL,
        currency VARCHAR(10) NOT NULL DEFAULT 'INR',
        status VARCHAR(50) NOT NULL,
        gateway_response TEXT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
    )";
    $pdo->exec($sql);
    output("✓ Payment transactions table created or updated");
    
    // Discount Codes table
    $sql = "CREATE TABLE IF NOT EXISTS discount_codes (
        id INT AUTO_INCREMENT PRIMARY KEY,
        code VARCHAR(50) NOT NULL UNIQUE,
        description TEXT NULL,
        discount_type ENUM('percentage', 'fixed') NOT NULL,
        discount_value DECIMAL(10,2) NOT NULL,
        min_order_value DECIMAL(10,2) DEFAULT 0.00,
        max_discount_amount DECIMAL(10,2) NULL,
        start_date DATETIME NOT NULL,
        end_date DATETIME NULL,
        usage_limit INT NULL,
        usage_count INT DEFAULT 0,
        is_active TINYINT(1) DEFAULT 1,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )";
    $pdo->exec($sql);
    output("✓ Discount codes table created or updated");
}

// Main function to create or update all tables
function runAllMigrations($pdo) {
    output("=== Database Migration Started ===");
    output("Starting migrations at " . date('Y-m-d H:i:s'));
    
    // Create users table
    createUsersTable($pdo);
    
    // Create vehicle tables
    createVehicleTables($pdo);
    
    // Create shop tables
    createShopTables($pdo);
    
    output("=== Database Migration Completed ===");
    output("Finished at " . date('Y-m-d H:i:s'));
}

// When this file is the main script being executed
if (basename($_SERVER['SCRIPT_FILENAME']) === basename(__FILE__)) {
    // Start buffer for web display
    if (PHP_SAPI !== 'cli') {
        ob_start();
        echo "<pre>";
    }
    
    try {
        // Start transaction
        $pdo->beginTransaction();
        
        // Run all migrations
        runAllMigrations($pdo);
        
        // Commit changes
        $pdo->commit();
    } catch (Exception $e) {
        // Roll back on error
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        
        output("Error: " . $e->getMessage());
        output("Migration failed. Changes have been rolled back.");
        exit(1);
    }
    
    // End buffer for web display
    if (PHP_SAPI !== 'cli') {
        echo "</pre>";
    }
} else {
    // If this file is included from another script, just run the migrations
    try {
        // Don't start a transaction here since we'll manage it per table
        // This prevents "no active transaction" errors
        
        // Run all migrations
        runAllMigrations($pdo);
        
        // No commit needed here since we're not using a transaction wrapper
    } catch (Exception $e) {
        // No rollback needed here since we're not using a transaction wrapper
        
        // Re-throw the exception for the including script to handle
        throw $e;
    }
} 
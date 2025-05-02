<?php
// Include database connection
require_once __DIR__ . '/database.php';

// Function to check if brands table exists, if not create it
function ensureBrandsTableExists($pdo) {
    $sql = "CREATE TABLE IF NOT EXISTS brands (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL UNIQUE,
        image VARCHAR(255) NOT NULL,
        sequence INT DEFAULT 999,
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

// Function to check if shopping_cart_items table exists, if not create it
function ensureShoppingCartItemsTableExists($pdo) {
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
}

// Function to check if orders table exists, if not create it
function ensureOrdersTableExists($pdo) {
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
        gst_amount DECIMAL(10,2) DEFAULT 0.00,
        total DECIMAL(10,2) NOT NULL,
        currency VARCHAR(10) NOT NULL DEFAULT 'INR',
        notes TEXT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
    )";
    $pdo->exec($sql);
}

// Function to check if order_items table exists, if not create it
function ensureOrderItemsTableExists($pdo) {
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
}

// Function to check if billing_details table exists, if not create it
function ensureBillingDetailsTableExists($pdo) {
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
}

// Function to check if payment_transactions table exists, if not create it
function ensurePaymentTransactionsTableExists($pdo) {
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

// Function to update orders table with GST field if it doesn't exist
function updateOrdersTableWithGSTField($pdo) {
    try {
        // Check if gst_amount column already exists
        $result = $pdo->query("SHOW COLUMNS FROM orders LIKE 'gst_amount'");
        if ($result->rowCount() == 0) {
            // Add the gst_amount column
            $pdo->exec("ALTER TABLE orders ADD COLUMN gst_amount DECIMAL(10,2) DEFAULT 0.00 AFTER shipping_cost");
        }
    } catch (PDOException $e) {
        echo "Error updating orders table: " . $e->getMessage();
    }
}

// Create tables if they don't exist
ensureBrandsTableExists($pdo);
ensureCategoriesTableExists($pdo);
ensureProductsTableExists($pdo);
ensureProductImagesTableExists($pdo);
ensureProductCategoriesTableExists($pdo);
ensureShoppingCartItemsTableExists($pdo);
ensureOrdersTableExists($pdo);
ensureOrderItemsTableExists($pdo);
ensureBillingDetailsTableExists($pdo);
ensurePaymentTransactionsTableExists($pdo);

// Update products table with vehicle fields
updateProductsTableWithVehicleFields($pdo);

// Update orders table with GST field
updateOrdersTableWithGSTField($pdo);

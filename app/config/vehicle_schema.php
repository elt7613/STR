<?php
// Include database connection
require_once __DIR__ . '/database.php';

// Function to create vehicle-related tables
function createVehicleTables($pdo) {
    // Vehicle Makes table
    $sql = "CREATE TABLE IF NOT EXISTS vehicle_makes (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(50) NOT NULL UNIQUE
    )";
    $pdo->exec($sql);
    
    // Vehicle Models table
    $sql = "CREATE TABLE IF NOT EXISTS vehicle_models (
        id INT AUTO_INCREMENT PRIMARY KEY,
        make_id INT NOT NULL,
        name VARCHAR(100) NOT NULL,
        UNIQUE KEY (make_id, name),
        FOREIGN KEY (make_id) REFERENCES vehicle_makes(id) ON DELETE CASCADE
    )";
    $pdo->exec($sql);
    
    // Vehicle Series table
    $sql = "CREATE TABLE IF NOT EXISTS vehicle_series (
        id INT AUTO_INCREMENT PRIMARY KEY,
        model_id INT NOT NULL,
        name VARCHAR(100) NOT NULL,
        UNIQUE KEY (model_id, name),
        FOREIGN KEY (model_id) REFERENCES vehicle_models(id) ON DELETE CASCADE
    )";
    $pdo->exec($sql);
    
    // Vehicle Devices table
    $sql = "CREATE TABLE IF NOT EXISTS vehicle_devices (
        id INT AUTO_INCREMENT PRIMARY KEY,
        series_id INT NOT NULL,
        name VARCHAR(100) NOT NULL,
        description TEXT,
        UNIQUE KEY (series_id, name),
        FOREIGN KEY (series_id) REFERENCES vehicle_series(id) ON DELETE CASCADE
    )";
    $pdo->exec($sql);
    
    // Vehicle Submissions table
    $sql = "CREATE TABLE IF NOT EXISTS vehicle_submissions (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        make_id INT NOT NULL,
        model_id INT NOT NULL, 
        series_id INT NOT NULL,
        device_id INT,
        phone VARCHAR(20) NOT NULL,
        email VARCHAR(100) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        FOREIGN KEY (make_id) REFERENCES vehicle_makes(id),
        FOREIGN KEY (model_id) REFERENCES vehicle_models(id),
        FOREIGN KEY (series_id) REFERENCES vehicle_series(id),
        FOREIGN KEY (device_id) REFERENCES vehicle_devices(id)
    )";
    $pdo->exec($sql);
    
    // Modify products table to add device_id if it doesn't exist already
    $checkColumnExists = $pdo->query("SHOW COLUMNS FROM products LIKE 'device_id'");
    if ($checkColumnExists->rowCount() == 0) {
        $sql = "ALTER TABLE products ADD COLUMN device_id INT NULL, 
                ADD FOREIGN KEY (device_id) REFERENCES vehicle_devices(id)";
        $pdo->exec($sql);
    }
}

// Create the vehicle tables
createVehicleTables($pdo);

// Populate the makes table with initial data from JMAX Engineering website if empty
function populateVehicleMakes($pdo) {
    $count = $pdo->query("SELECT COUNT(*) FROM vehicle_makes")->fetchColumn();
    
    if ($count == 0) {
        $makes = [
            'Chevrolet', 'Ford', 'Holden', 'Isuzu', 'Mazda', 'Mercedes', 
            'Mitsubishi', 'Nissan', 'Suzuki', 'Toyota', 'Volkswagen'
        ];
        
        $stmt = $pdo->prepare("INSERT INTO vehicle_makes (name) VALUES (?)");
        
        foreach ($makes as $make) {
            $stmt->execute([$make]);
        }
    }
}

// Call the function to populate makes
populateVehicleMakes($pdo);

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

<?php
/**
 * Vehicle form functions
 */

// Include database configuration
require_once __DIR__ . '/../config/database.php';

/**
 * Get all vehicle makes
 * 
 * @return array List of vehicle makes
 */
function getAllVehicleMakes() {
    global $pdo;
    
    try {
        $stmt = $pdo->query("SELECT id, name FROM vehicle_makes ORDER BY name");
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        return [];
    }
}

/**
 * Get vehicle models for a specific make
 * 
 * @param int $makeId Make ID
 * @return array List of vehicle models
 */
function getVehicleModelsByMake($makeId) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("SELECT id, name FROM vehicle_models WHERE make_id = ? ORDER BY name");
        $stmt->execute([$makeId]);
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        return [];
    }
}

/**
 * Get vehicle series for a specific model
 * 
 * @param int $modelId Model ID
 * @return array List of vehicle series
 */
function getVehicleSeriesByModel($modelId) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("SELECT id, name FROM vehicle_series WHERE model_id = ? ORDER BY name");
        $stmt->execute([$modelId]);
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        return [];
    }
}

/**
 * Get vehicle devices for a specific series
 * 
 * @param int $seriesId Series ID
 * @return array List of vehicle devices
 */
function getVehicleDevicesBySeries($seriesId) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("SELECT id, name, description FROM vehicle_devices WHERE series_id = ? ORDER BY name");
        $stmt->execute([$seriesId]);
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        return [];
    }
}

/**
 * Add a new vehicle device
 * 
 * @param int $seriesId Series ID
 * @param string $name Device name
 * @param string $description Device description (optional)
 * @return array Result with status and message
 */
function addVehicleDevice($seriesId, $name, $description = '') {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("INSERT INTO vehicle_devices (series_id, name, description) VALUES (?, ?, ?)");
        $stmt->execute([$seriesId, $name, $description]);
        return [
            'success' => true, 
            'message' => 'Device added successfully',
            'id' => $pdo->lastInsertId()
        ];
    } catch (PDOException $e) {
        if ($e->getCode() == 23000) { // Duplicate entry
            return ['success' => false, 'message' => 'This device already exists for the selected series'];
        }
        return ['success' => false, 'message' => 'Failed to add device: ' . $e->getMessage()];
    }
}

/**
 * Update a vehicle device
 * 
 * @param int $deviceId Device ID
 * @param int $seriesId Series ID
 * @param string $name Device name
 * @param string $description Device description (optional)
 * @return array Result with status and message
 */
function updateVehicleDevice($deviceId, $seriesId, $name, $description = '') {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("UPDATE vehicle_devices SET series_id = ?, name = ?, description = ? WHERE id = ?");
        $stmt->execute([$seriesId, $name, $description, $deviceId]);
        return ['success' => true, 'message' => 'Device updated successfully'];
    } catch (PDOException $e) {
        if ($e->getCode() == 23000) { // Duplicate entry
            return ['success' => false, 'message' => 'This device already exists for the selected series'];
        }
        return ['success' => false, 'message' => 'Failed to update device: ' . $e->getMessage()];
    }
}

/**
 * Delete a vehicle device
 * 
 * @param int $deviceId Device ID
 * @return array Result with status and message
 */
function deleteVehicleDevice($deviceId) {
    global $pdo;
    
    try {
        // Check if device is used in products
        require_once __DIR__ . '/shop.php';
        $productCount = getDeviceProductCount($deviceId);
        
        if ($productCount > 0) {
            return ['success' => false, 'message' => 'Cannot delete device that is used in ' . $productCount . ' product(s)'];
        }
        
        // Check if device is used in vehicle submissions
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM vehicle_submissions WHERE device_id = ?");
        $stmt->execute([$deviceId]);
        if ($stmt->fetchColumn() > 0) {
            return ['success' => false, 'message' => 'Cannot delete device that is used in vehicle submissions'];
        }
        
        // Delete the device
        $stmt = $pdo->prepare("DELETE FROM vehicle_devices WHERE id = ?");
        $stmt->execute([$deviceId]);
        return ['success' => true, 'message' => 'Device deleted successfully'];
    } catch (PDOException $e) {
        return ['success' => false, 'message' => 'Failed to delete device: ' . $e->getMessage()];
    }
}

/**
 * Get vehicle device details by ID
 * 
 * @param int $deviceId Device ID
 * @return array|false Device details or false if not found
 */
function getVehicleDeviceById($deviceId) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("SELECT * FROM vehicle_devices WHERE id = ?");
        $stmt->execute([$deviceId]);
        return $stmt->fetch();
    } catch (PDOException $e) {
        return false;
    }
}

/**
 * Get vehicle device name by ID
 * 
 * @param int $deviceId Device ID
 * @return string Device name or 'Unknown' if not found
 */
function getVehicleDeviceName($deviceId) {
    global $pdo;
    
    if (!$deviceId) return 'Not specified';
    
    try {
        $stmt = $pdo->prepare("SELECT name FROM vehicle_devices WHERE id = ?");
        $stmt->execute([$deviceId]);
        $result = $stmt->fetch();
        return $result ? $result['name'] : 'Unknown';
    } catch (PDOException $e) {
        return 'Unknown';
    }
}

/**
 * Save vehicle form submission
 * 
 * @param int $userId User ID
 * @param int $makeId Make ID
 * @param int $modelId Model ID
 * @param int $seriesId Series ID
 * @param int|null $deviceId Device ID (optional)
 * @param string $phone Phone number
 * @param string $email Email address
 * @return array Result with status and message
 */
function saveVehicleSubmission($userId, $makeId, $modelId, $seriesId, $deviceId = null, $phone, $email) {
    global $pdo;
    
    // Validate input
    if (empty($makeId) || empty($modelId) || empty($seriesId) || empty($phone) || empty($email)) {
        return ['success' => false, 'message' => 'All fields are required'];
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return ['success' => false, 'message' => 'Invalid email format'];
    }
    
    try {
        $stmt = $pdo->prepare("INSERT INTO vehicle_submissions (user_id, make_id, model_id, series_id, device_id, phone, email) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$userId, $makeId, $modelId, $seriesId, $deviceId, $phone, $email]);
        
        return ['success' => true, 'message' => 'Vehicle information submitted successfully'];
    } catch (PDOException $e) {
        return ['success' => false, 'message' => 'Submission failed: ' . $e->getMessage()];
    }
}

/**
 * Get vehicle submissions for a specific user
 * 
 * @param int $userId User ID
 * @return array List of vehicle submissions
 */
function getUserVehicleSubmissions($userId) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("SELECT * FROM vehicle_submissions WHERE user_id = ? ORDER BY created_at DESC");
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        return [];
    }
}

/**
 * Get vehicle make name by ID
 * 
 * @param int $makeId Make ID
 * @return string Make name
 */
function getVehicleMakeName($makeId) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("SELECT name FROM vehicle_makes WHERE id = ?");
        $stmt->execute([$makeId]);
        $result = $stmt->fetch();
        return $result ? $result['name'] : 'Unknown';
    } catch (PDOException $e) {
        return 'Unknown';
    }
}

/**
 * Get vehicle model name by ID
 * 
 * @param int $modelId Model ID
 * @return string Model name
 */
function getVehicleModelName($modelId) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("SELECT name FROM vehicle_models WHERE id = ?");
        $stmt->execute([$modelId]);
        $result = $stmt->fetch();
        return $result ? $result['name'] : 'Unknown';
    } catch (PDOException $e) {
        return 'Unknown';
    }
}

/**
 * Get vehicle series name by ID
 * 
 * @param int $seriesId Series ID
 * @return string Series name
 */
function getVehicleSeriesName($seriesId) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("SELECT name FROM vehicle_series WHERE id = ?");
        $stmt->execute([$seriesId]);
        $result = $stmt->fetch();
        return $result ? $result['name'] : 'Unknown';
    } catch (PDOException $e) {
        return 'Unknown';
    }
}

/**
 * Get all vehicle submissions
 * 
 * @return array List of all vehicle submissions
 */
function getAllVehicleSubmissions() {
    global $pdo;
    
    try {
        $stmt = $pdo->query("SELECT * FROM vehicle_submissions ORDER BY created_at DESC");
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        return [];
    }
}
?> 
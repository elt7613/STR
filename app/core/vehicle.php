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
 * Save vehicle form submission
 * 
 * @param int $userId User ID
 * @param int $makeId Make ID
 * @param int $modelId Model ID
 * @param int $seriesId Series ID
 * @param string $phone Phone number
 * @param string $email Email address
 * @return array Result with status and message
 */
function saveVehicleSubmission($userId, $makeId, $modelId, $seriesId, $phone, $email) {
    global $pdo;
    
    // Validate input
    if (empty($makeId) || empty($modelId) || empty($seriesId) || empty($phone) || empty($email)) {
        return ['success' => false, 'message' => 'All fields are required'];
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return ['success' => false, 'message' => 'Invalid email format'];
    }
    
    try {
        $stmt = $pdo->prepare("INSERT INTO vehicle_submissions (user_id, make_id, model_id, series_id, phone, email) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$userId, $makeId, $modelId, $seriesId, $phone, $email]);
        
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
<?php
/**
 * Vehicle form functions
 */

// Include database configuration
require_once __DIR__ . '/../config/database.php';

// Include email functions
require_once __DIR__ . '/email.php';

/**
 * Get all vehicle makes
 * 
 * @return array List of vehicle makes
 */
function getAllVehicleMakes() {
    global $pdo;
    
    if (!$pdo) {
        return [];
    }
    
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
    
    if (!$pdo) {
        return [];
    }
    
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
    
    if (!$pdo) {
        return [];
    }
    
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
    
    if (!$pdo) {
        return [];
    }
    
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
    
    if (!$pdo) {
        return ['success' => false, 'message' => 'Database connection error'];
    }
    
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
    
    if (!$pdo) {
        return ['success' => false, 'message' => 'Database connection error'];
    }
    
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
    
    if (!$pdo) {
        return ['success' => false, 'message' => 'Database connection error'];
    }
    
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
    
    if (!$pdo) {
        return false;
    }
    
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
    if (!$pdo) return 'Unknown';
    
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
 * Save vehicle submission
 * 
 * @param int $userId User ID
 * @param int $makeId Make ID
 * @param int $modelId Model ID
 * @param int $seriesId Series ID
 * @param string $phone Phone number
 * @param string $email Email address
 * @param int|null $deviceId Device ID (optional)
 * @return array Result with status and message
 */
function saveVehicleSubmission($userId, $makeId, $modelId, $seriesId, $phone, $email, $deviceId = null) {
    global $pdo;
    
    if (!$pdo) {
        return ['success' => false, 'message' => 'Database connection error'];
    }
    
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
    
    if (!$pdo) {
        return [];
    }
    
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
    
    if (!$pdo) {
        return 'Unknown';
    }
    
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
    
    if (!$pdo) {
        return 'Unknown';
    }
    
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
    
    if (!$pdo) {
        return 'Unknown';
    }
    
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
    
    if (!$pdo) {
        return [];
    }
    
    try {
        $stmt = $pdo->query("SELECT * FROM vehicle_submissions ORDER BY created_at DESC");
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        return [];
    }
}

/**
 * Save user vehicle information
 * 
 * @param int $userId User ID
 * @param string $brand Vehicle brand
 * @param string $model Vehicle model
 * @param string $series Vehicle series
 * @param array $images Array of uploaded image files
 * @return array Result with status and message
 */
function saveUserVehicleInfo($userId, $brand, $model, $series, $images = []) {
    global $pdo;
    
    if (!$pdo) {
        return ['success' => false, 'message' => 'Database connection error'];
    }
    
    // Validate input
    if (empty($userId) || empty($brand) || empty($model)) {
        return ['success' => false, 'message' => 'Brand and model are required fields'];
    }
    
    try {
        // Begin transaction
        $pdo->beginTransaction();
        
        // Insert vehicle information
        $stmt = $pdo->prepare("INSERT INTO vehicle_info (user_id, brand, model, series) VALUES (?, ?, ?, ?)");
        $stmt->execute([$userId, $brand, $model, $series]);
        $vehicleId = $pdo->lastInsertId();
        
        // Process images if any
        $uploadedImages = [];
        if (!empty($images) && is_array($images)) {
            foreach ($images as $image) {
                $imageResult = uploadVehicleImage($vehicleId, $image);
                if ($imageResult['success']) {
                    $uploadedImages[] = $imageResult['path'];
                }
            }
        }
        
        // Send email notification to admin about the new vehicle submission
        $vehicleData = [
            'brand' => $brand,
            'model' => $model,
            'series' => $series
        ];
        
        // Use our updated function to send the email notification
        $emailSent = sendVehicleSubmissionEmail($userId, $vehicleData, count($uploadedImages));
        
        // Commit transaction
        $pdo->commit();
        
        return [
            'success' => true, 
            'message' => 'Vehicle information submitted successfully',
            'vehicle_id' => $vehicleId,
            'images' => $uploadedImages,
            'email_sent' => $emailSent
        ];
    } catch (PDOException $e) {
        // Rollback transaction on error
        $pdo->rollBack();
        return ['success' => false, 'message' => 'Submission failed: ' . $e->getMessage()];
    }
}

/**
 * Upload vehicle image
 * 
 * @param int $vehicleId Vehicle ID
 * @param array $imageFile Uploaded image file ($_FILES array element)
 * @return array Result with status and message
 */
function uploadVehicleImage($vehicleId, $imageFile) {
    global $pdo;
    
    if (!$pdo) {
        return ['success' => false, 'message' => 'Database connection error'];
    }
    
    // Check if image is valid
    if (!isset($imageFile['tmp_name']) || empty($imageFile['tmp_name'])) {
        return ['success' => false, 'message' => 'No image file uploaded'];
    }
    
    // Check file size (max 5MB)
    if ($imageFile['size'] > 5 * 1024 * 1024) {
        return ['success' => false, 'message' => 'Image file is too large (max 5MB)'];
    }
    
    // Check file type
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    if (!in_array($imageFile['type'], $allowedTypes)) {
        return ['success' => false, 'message' => 'Invalid image type. Allowed types: JPG, PNG, GIF, WEBP'];
    }
    
    // Generate unique filename
    $extension = pathinfo($imageFile['name'], PATHINFO_EXTENSION);
    $filename = uniqid('vehicle_') . '.' . $extension;
    $uploadPath = VEHICLE_IMAGES_DIR . $filename;
    
    // Upload the file
    if (move_uploaded_file($imageFile['tmp_name'], $uploadPath)) {
        // Save image path to database
        try {
            $stmt = $pdo->prepare("INSERT INTO vehicle_images (vehicle_id, image_path) VALUES (?, ?)");
            $stmt->execute([$vehicleId, $filename]);
            
            return [
                'success' => true, 
                'message' => 'Image uploaded successfully',
                'path' => $filename,
                'id' => $pdo->lastInsertId()
            ];
        } catch (PDOException $e) {
            // Delete the uploaded file if database insert fails
            unlink($uploadPath);
            return ['success' => false, 'message' => 'Failed to save image information: ' . $e->getMessage()];
        }
    } else {
        return ['success' => false, 'message' => 'Failed to upload image'];
    }
}

/**
 * Get vehicle information for a specific user
 * 
 * @param int $userId User ID
 * @return array Vehicle information with images
 */
function getUserVehicleInfo($userId) {
    global $pdo;
    
    if (!$pdo) {
        return null;
    }
    
    try {
        // Get vehicle information
        $stmt = $pdo->prepare("SELECT * FROM vehicle_info WHERE user_id = ? ORDER BY created_at DESC");
        $stmt->execute([$userId]);
        $vehicles = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Get images for each vehicle
        foreach ($vehicles as &$vehicle) {
            $stmt = $pdo->prepare("SELECT * FROM vehicle_images WHERE vehicle_id = ?");
            $stmt->execute([$vehicle['id']]);
            $vehicle['images'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        
        return $vehicles;
    } catch (PDOException $e) {
        return null;
    }
}

/**
 * Delete vehicle information
 * 
 * @param int $vehicleId Vehicle ID
 * @param int $userId User ID (for verification)
 * @return array Result with status and message
 */
function deleteVehicleInfo($vehicleId, $userId) {
    global $pdo;
    
    if (!$pdo) {
        return ['success' => false, 'message' => 'Database connection error'];
    }
    
    try {
        // Check if the vehicle belongs to the user
        $stmt = $pdo->prepare("SELECT * FROM vehicle_info WHERE id = ? AND user_id = ?");
        $stmt->execute([$vehicleId, $userId]);
        
        if ($stmt->rowCount() === 0) {
            return ['success' => false, 'message' => 'Vehicle not found or does not belong to the user'];
        }
        
        // Begin transaction
        $pdo->beginTransaction();
        
        // Get all image paths
        $stmt = $pdo->prepare("SELECT image_path FROM vehicle_images WHERE vehicle_id = ?");
        $stmt->execute([$vehicleId]);
        $images = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Delete physical image files
        foreach ($images as $image) {
            $imagePath = VEHICLE_IMAGES_DIR . $image['image_path'];
            if (file_exists($imagePath)) {
                unlink($imagePath);
            }
        }
        
        // Delete from database (vehicle_images will be deleted automatically via CASCADE)
        $stmt = $pdo->prepare("DELETE FROM vehicle_info WHERE id = ?");
        $stmt->execute([$vehicleId]);
        
        // Commit transaction
        $pdo->commit();
        
        return ['success' => true, 'message' => 'Vehicle information deleted successfully'];
    } catch (PDOException $e) {
        // Rollback transaction on error
        $pdo->rollBack();
        return ['success' => false, 'message' => 'Deletion failed: ' . $e->getMessage()];
    }
}
?> 
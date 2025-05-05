<?php
/**
 * Discount configuration for premium users
 */

// Default discount percentage for premium users (10%)
define('PREMIUM_DISCOUNT_PERCENTAGE', 10);

/**
 * Get the premium discount percentage
 * 
 * @return float The discount percentage for premium users
 */
function getPremiumDiscountPercentage() {
    // Check if the value is stored in the database, otherwise use default
    global $pdo;
    
    try {
        // Ensure the settings table exists
        ensureSettingsTableExists($pdo);
        
        // Get the premium discount percentage from settings
        $stmt = $pdo->prepare("SELECT value FROM settings WHERE name = 'premium_discount_percentage'");
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return floatval($result['value']);
        }
    } catch (PDOException $e) {
        // Log error but use default value
        error_log("Error retrieving premium discount percentage: " . $e->getMessage());
    }
    
    // Return default value if not found in database
    return PREMIUM_DISCOUNT_PERCENTAGE;
}

/**
 * Update the premium discount percentage in the database
 * 
 * @param float $percentage The new discount percentage
 * @return array Success status and message
 */
function updatePremiumDiscountPercentage($percentage) {
    global $pdo;
    
    // Validate percentage
    if (!is_numeric($percentage) || $percentage < 0 || $percentage > 100) {
        return [
            'success' => false,
            'message' => 'Invalid percentage value. Must be between 0 and 100.'
        ];
    }
    
    try {
        // Ensure the settings table exists
        ensureSettingsTableExists($pdo);
        
        // Check if setting already exists
        $stmt = $pdo->prepare("SELECT id FROM settings WHERE name = 'premium_discount_percentage'");
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            // Update existing setting
            $stmt = $pdo->prepare("UPDATE settings SET value = :value, updated_at = NOW() WHERE name = 'premium_discount_percentage'");
        } else {
            // Insert new setting
            $stmt = $pdo->prepare("INSERT INTO settings (name, value) VALUES ('premium_discount_percentage', :value)");
        }
        
        $stmt->bindParam(':value', $percentage);
        $stmt->execute();
        
        return [
            'success' => true,
            'message' => 'Premium discount percentage updated successfully.'
        ];
    } catch (PDOException $e) {
        return [
            'success' => false,
            'message' => 'Error updating premium discount percentage: ' . $e->getMessage()
        ];
    }
}

/**
 * Get statistics about premium members and their orders
 * 
 * @return array Statistics about premium members
 */
function getPremiumMemberStatistics() {
    global $pdo;
    
    $stats = [
        'total_premium_users' => 0,
        'premium_percentage' => 0,
        'total_discount_amount' => 0,
        'premium_orders' => 0
    ];
    
    try {
        // Get total premium users
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM users WHERE is_premium_member = 1");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $stats['total_premium_users'] = $result['count'] ?? 0;
        
        // Get total users for percentage calculation
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM users");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $totalUsers = $result['count'] ?? 0;
        
        if ($totalUsers > 0) {
            $stats['premium_percentage'] = round(($stats['total_premium_users'] / $totalUsers) * 100, 2);
        }
        
        // Get total discount amount given
        $stmt = $pdo->query("SELECT SUM(discount_amount) as total FROM orders WHERE discount_amount > 0");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $stats['total_discount_amount'] = $result['total'] ?? 0;
        
        // Get count of premium orders (orders with discount)
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM orders WHERE discount_amount > 0");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $stats['premium_orders'] = $result['count'] ?? 0;
        
    } catch (PDOException $e) {
        error_log("Error getting premium member statistics: " . $e->getMessage());
    }
    
    return $stats;
}

/**
 * Ensure settings table exists in the database
 */
function ensureSettingsTableExists($pdo) {
    $sql = "CREATE TABLE IF NOT EXISTS settings (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL UNIQUE,
        value TEXT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )";
    $pdo->exec($sql);
} 
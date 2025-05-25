<?php
/**
 * Premium Membership Schema
 * 
 * This file defines the database schema for premium membership pricing
 */

// Include database connection
require_once __DIR__ . '/database.php';

// Check if $pdo is defined
if (!isset($pdo)) {
    die("Database connection error: PDO variable is not defined in database.php");
}

/**
 * Ensure premium_pricing table exists
 * 
 * @param PDO $pdo Database connection
 */
function ensurePremiumPricingTableExists($pdo) {
    $sql = "CREATE TABLE IF NOT EXISTS premium_pricing (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        description TEXT,
        price DECIMAL(10,2) NOT NULL,
        duration_months INT NOT NULL DEFAULT 12,
        is_active TINYINT(1) DEFAULT 1,
        is_recommended TINYINT(1) DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )";
    $pdo->exec($sql);
    
    // Check if is_recommended column exists, add it if not
    try {
        $stmt = $pdo->query("SHOW COLUMNS FROM premium_pricing LIKE 'is_recommended'");
        if ($stmt->rowCount() == 0) {
            $pdo->exec("ALTER TABLE premium_pricing ADD COLUMN is_recommended TINYINT(1) DEFAULT 0");
            error_log("Added is_recommended column to premium_pricing table");
        }
    } catch (PDOException $e) {
        // Column check failed, likely table doesn't exist yet (but will be created above)
        error_log("Error checking for is_recommended column: " . $e->getMessage());
    }
    
    // Ensure premium_expiry column exists in users table
    try {
        $stmt = $pdo->query("SHOW COLUMNS FROM users LIKE 'premium_expiry'");
        if ($stmt->rowCount() == 0) {
            $pdo->exec("ALTER TABLE users ADD COLUMN premium_expiry DATETIME DEFAULT NULL AFTER is_premium_member");
            error_log("Added premium_expiry column to users table");
        }
    } catch (PDOException $e) {
        error_log("Error checking for premium_expiry column: " . $e->getMessage());
    }
    
    // Check if there's at least one active premium plan
    $stmt = $pdo->query("SELECT COUNT(*) as plan_count FROM premium_pricing WHERE is_active = 1");
    $result = $stmt->fetch();
    
    // If no active plans exist, create a default one
    if ($result && $result['plan_count'] == 0) {
        $stmt = $pdo->prepare("INSERT INTO premium_pricing (name, description, price, duration_months, is_active) 
                              VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([
            'Annual Premium Membership',
            'Enjoy exclusive benefits including discounts on all services for one full year.',
            2999.00,
            12,
            1
        ]);
    }
}

/**
 * Get all active premium pricing plans
 * 
 * @return array List of premium pricing plans
 */
function getPremiumPricingPlans() {
    global $pdo;
    
    try {
        $stmt = $pdo->query("SELECT * FROM premium_pricing WHERE is_active = 1 ORDER BY price ASC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error fetching premium pricing plans: " . $e->getMessage());
        return [];
    }
}

/**
 * Get a specific premium pricing plan by ID
 * 
 * @param int $planId The plan ID to retrieve
 * @return array|false Plan details or false if not found
 */
function getPremiumPricingPlan($planId) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("SELECT * FROM premium_pricing WHERE id = ?");
        $stmt->execute([$planId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error fetching premium pricing plan: " . $e->getMessage());
        return false;
    }
}

/**
 * Update a premium pricing plan
 * 
 * @param int $planId Plan ID to update
 * @param string $name Plan name
 * @param string $description Plan description
 * @param float $price Plan price
 * @param int $durationMonths Plan duration in months
 * @param int $isActive Whether the plan is active (1) or not (0)
 * @param int $isRecommended Whether the plan is recommended (1) or not (0)
 * @return bool Whether update was successful
 */
function updatePremiumPricingPlan($planId, $name, $description, $price, $durationMonths, $isActive, $isRecommended = 0) {
    global $pdo;
    
    try {
        // If this plan is being set as recommended, first unset any other recommended plans
        if ($isRecommended == 1) {
            $pdo->exec("UPDATE premium_pricing SET is_recommended = 0 WHERE id != $planId");
        }
        
        $stmt = $pdo->prepare("UPDATE premium_pricing 
                              SET name = ?, description = ?, price = ?, 
                                  duration_months = ?, is_active = ?, is_recommended = ? 
                              WHERE id = ?");
        $stmt->execute([$name, $description, $price, $durationMonths, $isActive, $isRecommended, $planId]);
        return $stmt->rowCount() > 0;
    } catch (PDOException $e) {
        error_log("Error updating premium pricing plan: " . $e->getMessage());
        return false;
    }
}

/**
 * Add a new premium pricing plan
 * 
 * @param string $name Plan name
 * @param string $description Plan description
 * @param float $price Plan price
 * @param int $durationMonths Plan duration in months
 * @param int $isActive Whether the plan is active (1) or not (0)
 * @param int $isRecommended Whether the plan is recommended (1) or not (0)
 * @return int|false The new plan ID or false on failure
 */
function addPremiumPricingPlan($name, $description, $price, $durationMonths, $isActive, $isRecommended = 0) {
    global $pdo;
    
    try {
        // If this plan is being set as recommended, first unset any other recommended plans
        if ($isRecommended == 1) {
            $pdo->exec("UPDATE premium_pricing SET is_recommended = 0");
        }
        
        $stmt = $pdo->prepare("INSERT INTO premium_pricing 
                              (name, description, price, duration_months, is_active, is_recommended) 
                              VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$name, $description, $price, $durationMonths, $isActive, $isRecommended]);
        return $pdo->lastInsertId();
    } catch (PDOException $e) {
        error_log("Error adding premium pricing plan: " . $e->getMessage());
        return false;
    }
}

/**
 * Update user's premium status
 * 
 * @param int $userId User ID to update
 * @param int $isPremium Whether user is premium (1) or not (0)
 * @param string $expiryDate Premium membership expiry date
 * @param int $planId Premium plan ID
 * @param string $paymentId Payment transaction ID
 * @return bool Whether update was successful
 */
function updateUserPremiumStatus($userId, $isPremium, $expiryDate, $planId, $paymentId) {
    global $pdo;
    
    try {
        // Begin transaction
        $pdo->beginTransaction();
        
        // Update user's premium status
        $stmt = $pdo->prepare("UPDATE users SET is_premium_member = ?, premium_expiry = ? WHERE id = ?");
        $stmt->execute([$isPremium, $expiryDate, $userId]);
        
        // Record the payment
        $stmt = $pdo->prepare("INSERT INTO premium_payments 
                               (user_id, plan_id, payment_id, payment_amount, payment_status, order_reference) 
                               VALUES (?, ?, ?, (SELECT price FROM premium_pricing WHERE id = ?), 'completed', ?)");
        $stmt->execute([$userId, $planId, $paymentId, $planId, 'PREMIUM_' . $userId . '_' . time()]);
        
        // Commit transaction
        $pdo->commit();
        return true;
    } catch (PDOException $e) {
        // Rollback transaction on error
        $pdo->rollBack();
        error_log("Error updating user premium status: " . $e->getMessage());
        return false;
    }
}

/**
 * Ensure premium_payments table exists
 * 
 * @param PDO $pdo Database connection
 */
function ensurePremiumPaymentsTableExists($pdo) {
    $sql = "CREATE TABLE IF NOT EXISTS premium_payments (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        plan_id INT NOT NULL,
        payment_id VARCHAR(100) NOT NULL,
        payment_amount DECIMAL(10,2) NOT NULL,
        payment_status VARCHAR(50) DEFAULT 'completed',
        order_reference VARCHAR(100) DEFAULT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        FOREIGN KEY (plan_id) REFERENCES premium_pricing(id)
    )";
    $pdo->exec($sql);
    
    // Check if amount column exists, if so migrate to payment_amount
    try {
        $stmt = $pdo->query("SHOW COLUMNS FROM premium_payments LIKE 'amount'");
        if ($stmt->rowCount() > 0) {
            // Column exists, update all records to use payment_amount
            $pdo->exec("UPDATE premium_payments SET payment_amount = amount WHERE payment_amount IS NULL");
            $pdo->exec("ALTER TABLE premium_payments DROP COLUMN amount");
            error_log("Migrated amount column to payment_amount in premium_payments table");
        }
    } catch (PDOException $e) {
        error_log("Error checking for amount column: " . $e->getMessage());
    }
    
    // Check if payment_date column exists, if so migrate to created_at
    try {
        $stmt = $pdo->query("SHOW COLUMNS FROM premium_payments LIKE 'payment_date'");
        if ($stmt->rowCount() > 0) {
            // Column exists, update all records to use created_at
            $pdo->exec("UPDATE premium_payments SET created_at = payment_date WHERE created_at IS NULL");
            $pdo->exec("ALTER TABLE premium_payments DROP COLUMN payment_date");
            error_log("Migrated payment_date column to created_at in premium_payments table");
        }
    } catch (PDOException $e) {
        error_log("Error checking for payment_date column: " . $e->getMessage());
    }
}

// Create premium_pricing table if it doesn't exist
ensurePremiumPricingTableExists($pdo);

// Create premium_payments table if it doesn't exist
ensurePremiumPaymentsTableExists($pdo);
?>
<?php
/**
 * Update User Handler
 */

// Include initialization script
require_once __DIR__ . '/../../includes/init.php';

// Check if user is admin
if (!isAdmin()) {
    // Redirect to home page or show access denied
    header('Location: /index.php');
    exit;
}

// Initialize variables
$success = '';
$error = '';

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $userId = isset($_POST['user_id']) ? intval($_POST['user_id']) : 0;
    $username = isset($_POST['username']) ? trim($_POST['username']) : '';
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $password = isset($_POST['password']) ? trim($_POST['password']) : '';
    $isPremiumMember = isset($_POST['is_premium_member']) ? 1 : 0;
    
    // Validate inputs
    if (empty($userId) || empty($username) || empty($email)) {
        $error = 'User ID, username, and email are required.';
    } else {
        // Update user information
        $result = updateUser($userId, $username, $email, $password, $isPremiumMember);
        
        if ($result['success']) {
            $success = $result['message'];
        } else {
            $error = $result['message'];
        }
    }
}

// Redirect back to dashboard with message
if (!empty($success)) {
    header('Location: /admin/dashboard.php?success=' . urlencode($success));
    exit;
} elseif (!empty($error)) {
    header('Location: /admin/dashboard.php?error=' . urlencode($error));
    exit;
} else {
    // No form submission, redirect to dashboard
    header('Location: /admin/dashboard.php');
    exit;
}

/**
 * Function to update user information
 * 
 * @param int $userId User ID
 * @param string $username New username
 * @param string $email New email
 * @param string $password New password (optional)
 * @param int $isPremiumMember Premium membership status (0 or 1)
 * @return array Result with success status and message
 */
function updateUser($userId, $username, $email, $password = '', $isPremiumMember = 0) {
    global $pdo;
    
    // Check if PDO connection exists
    if (!isset($pdo) || $pdo === null) {
        return ['success' => false, 'message' => 'Database connection error: PDO variable is not defined.'];
    }
    
    try {
        // Start transaction
        $pdo->beginTransaction();
        
        // Check if user exists
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $user = $stmt->fetch();
        
        if (!$user) {
            return ['success' => false, 'message' => 'User not found.'];
        }
        
        // Check if username already exists for another user
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? AND id != ?");
        $stmt->execute([$username, $userId]);
        if ($stmt->rowCount() > 0) {
            return ['success' => false, 'message' => 'Username already exists.'];
        }
        
        // Check if email already exists for another user
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? AND id != ?");
        $stmt->execute([$email, $userId]);
        if ($stmt->rowCount() > 0) {
            return ['success' => false, 'message' => 'Email already exists.'];
        }
        
        // Update user information
        if (!empty($password)) {
            // Hash password
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            
            // Update username, email, password, and premium status
            $stmt = $pdo->prepare("UPDATE users SET username = ?, email = ?, password = ?, is_premium_member = ? WHERE id = ?");
            $stmt->execute([$username, $email, $hashedPassword, $isPremiumMember, $userId]);
        } else {
            // Update username, email, and premium status
            $stmt = $pdo->prepare("UPDATE users SET username = ?, email = ?, is_premium_member = ? WHERE id = ?");
            $stmt->execute([$username, $email, $isPremiumMember, $userId]);
        }
        
        // Commit transaction
        $pdo->commit();
        
        return ['success' => true, 'message' => 'User information updated successfully.'];
    } catch (PDOException $e) {
        // Rollback transaction on error
        if (isset($pdo)) {
            $pdo->rollBack();
        }
        return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
    }
}
?> 
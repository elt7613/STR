<?php
/**
 * Authentication system functions
 */

// Include database configuration
require_once __DIR__ . '/../config/database.php';

/**
 * Register a new user
 * 
 * @param string $username User's username
 * @param string $email User's email
 * @param string $password User's password (will be hashed)
 * @param string $phone User's phone number
 * @param int $isPremiumMember Premium membership status (0 or 1, defaults to 0)
 * @return array Result with status and message
 */
function registerUser($username, $email, $password, $phone = '', $isPremiumMember = 0) {
    /** @var \PDO $pdo */
    global $pdo;
    
    // Validate input
    if (empty($username) || empty($email) || empty($password) || empty($phone)) {
        return ['success' => false, 'message' => 'All fields are required'];
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return ['success' => false, 'message' => 'Invalid email format'];
    }
    
    if (strlen($password) < 8) {
        return ['success' => false, 'message' => 'Password must be at least 8 characters'];
    }
    
    if (!$pdo) {
        return ['success' => false, 'message' => 'Database connection error'];
    }
    
    try {
        // Check if username or email already exists
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$username, $email]);
        
        if ($stmt->rowCount() > 0) {
            return ['success' => false, 'message' => 'Username or email already exists'];
        }
        
        // Insert new user
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO users (username, email, password, phone, is_premium_member) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$username, $email, $hashedPassword, $phone, $isPremiumMember]);
        
        return ['success' => true, 'message' => 'Registration successful'];
    } catch (PDOException $e) {
        return ['success' => false, 'message' => 'Registration failed: ' . $e->getMessage()];
    }
}

/**
 * Login a user
 * 
 * @param string $username Username or email
 * @param string $password Password to verify
 * @return array Result with status and message
 */
function loginUser($username, $password) {
    /** @var \PDO $pdo */
    global $pdo;
    
    // Validate input
    if (empty($username) || empty($password)) {
        return ['success' => false, 'message' => 'Username/email and password are required'];
    }
    
    if (!$pdo) {
        return ['success' => false, 'message' => 'Database connection error'];
    }
    
    try {
        // Check if it's an email or username
        $field = filter_var($username, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';
        
        // Get user from database
        $stmt = $pdo->prepare("SELECT * FROM users WHERE $field = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();
        
        if (!$user) {
            return ['success' => false, 'message' => 'Invalid credentials'];
        }
        
        // Verify password
        if (password_verify($password, $user['password'])) {
            // Password is correct, store user info in session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['phone'] = $user['phone'];
            $_SESSION['profile_image'] = $user['profile_image'];
            $_SESSION['is_premium_member'] = $user['is_premium_member'];
            $_SESSION['is_admin'] = $user['is_admin'];
            $_SESSION['loggedin'] = true;
            
            return ['success' => true, 'message' => 'Login successful'];
        } else {
            return ['success' => false, 'message' => 'Invalid credentials'];
        }
    } catch (PDOException $e) {
        return ['success' => false, 'message' => 'Login failed: ' . $e->getMessage()];
    }
}

/**
 * Check if user is logged in
 * 
 * @return bool Whether user is logged in
 */
function isLoggedIn() {
    return isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true;
}

/**
 * Check if user is an admin
 * 
 * @return bool Whether user is an admin
 */
function isAdmin() {
    // If not logged in, not admin
    if (!isLoggedIn()) {
        return false;
    }
    
    // If the session already has admin status, trust it
    if (isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1) {
        return true;
    }
    
    /** @var \PDO $pdo */
    global $pdo;
    
    if (!$pdo) {
        return false;
    }
    
    try {
        // Double-check from database
        $stmt = $pdo->prepare("SELECT is_admin FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch();
        
        $isAdmin = $user && isset($user['is_admin']) && $user['is_admin'] == 1;
        
        // Update the session with the correct value
        $_SESSION['is_admin'] = $isAdmin ? 1 : 0;
        
        return $isAdmin;
    } catch (PDOException $e) {
        error_log("Error verifying admin status: " . $e->getMessage());
        return false;
    }
}

/**
 * Get all users from the database
 * 
 * @return array List of all users
 */
function getAllUsers() {
    /** @var \PDO $pdo */
    global $pdo;
    
    if (!$pdo) {
        return [];
    }
    
    try {
        $stmt = $pdo->query("SELECT id, username, email, created_at, is_admin, is_premium_member FROM users ORDER BY id");
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        return [];
    }
}

/**
 * Get username by user ID
 * 
 * @param int $userId User ID
 * @return string Username or 'Unknown'
 */
function getUsernameById($userId) {
    /** @var \PDO $pdo */
    global $pdo;
    
    if (!$pdo) {
        return 'Unknown';
    }
    
    try {
        $stmt = $pdo->prepare("SELECT username FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $result = $stmt->fetch();
        return $result ? $result['username'] : 'Unknown';
    } catch (PDOException $e) {
        return 'Unknown';
    }
}

/**
 * Get user details by ID
 * 
 * @param int $userId User ID
 * @return array|false User data or false if not found
 */
function getUserById($userId) {
    /** @var \PDO $pdo */
    global $pdo;
    
    if (!$pdo) {
        return false;
    }
    
    try {
        $stmt = $pdo->prepare("SELECT id, username, email, phone, profile_image, is_admin, is_premium_member, created_at FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        return $stmt->fetch();
    } catch (PDOException $e) {
        return false;
    }
}

/**
 * Check if user is a premium member
 * 
 * @return bool Whether user is a premium member
 */
function isPremiumMember() {
    /** @var \PDO $pdo */
    global $pdo;
    
    // If not logged in, not a premium member
    if (!isLoggedIn()) {
        return false;
    }
    
    if (!$pdo) {
        return false;
    }
    
    try {
        // Check if user has premium member status
        $stmt = $pdo->prepare("SELECT is_premium_member FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch();
        
        return $user && isset($user['is_premium_member']) && $user['is_premium_member'] == 1;
    } catch (PDOException $e) {
        return false;
    }
}

/**
 * Log out current user
 */
function logoutUser() {
    // Unset all session variables
    $_SESSION = [];
    
    // Destroy the session
    session_destroy();
}

/**
 * Update user profile
 * 
 * @param int $userId User ID
 * @param array $data Profile data to update (username, email, phone, etc.)
 * @param string|null $newPassword New password if changing password
 * @param array|null $profileImage The uploaded profile image file data ($_FILES['profile_image'])
 * @return array Result with status and message
 */
function updateUserProfile($userId, $data, $newPassword = null, $profileImage = null) {
    /** @var \PDO $pdo */
    global $pdo;
    
    if (!$pdo) {
        return ['success' => false, 'message' => 'Database connection error'];
    }
    
    try {
        // Start building the SQL query and parameters
        $updateFields = [];
        $params = [];
        
        // Add data fields to update
        if (isset($data['username']) && !empty($data['username'])) {
            // Check if username is taken by another user
            $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? AND id != ?");
            $stmt->execute([$data['username'], $userId]);
            if ($stmt->rowCount() > 0) {
                return ['success' => false, 'message' => 'Username is already taken'];
            }
            
            $updateFields[] = "username = :username";
            $params[':username'] = $data['username'];
        }
        
        if (isset($data['email']) && !empty($data['email'])) {
            // Validate email format
            if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                return ['success' => false, 'message' => 'Invalid email format'];
            }
            
            // Check if email is taken by another user
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
            $stmt->execute([$data['email'], $userId]);
            if ($stmt->rowCount() > 0) {
                return ['success' => false, 'message' => 'Email is already taken'];
            }
            
            $updateFields[] = "email = :email";
            $params[':email'] = $data['email'];
        }
        
        if (isset($data['phone'])) {
            $updateFields[] = "phone = :phone";
            $params[':phone'] = $data['phone'];
        }
        
        // Handle profile image upload
        if ($profileImage && is_array($profileImage) && isset($profileImage['tmp_name']) && !empty($profileImage['tmp_name'])) {
            // Validate file type
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
            $fileType = mime_content_type($profileImage['tmp_name']);
            
            if (!in_array($fileType, $allowedTypes)) {
                return ['success' => false, 'message' => 'Invalid file type. Please upload a JPEG, PNG, or GIF image.'];
            }
            
            // Validate file size (max 5MB)
            if ($profileImage['size'] > 5 * 1024 * 1024) {
                return ['success' => false, 'message' => 'File size too large. Maximum size is 5MB.'];
            }
            
            // Create upload directory if it doesn't exist
            // Use DOCUMENT_ROOT to ensure the path works in both local and hosting environments
            $uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/uploads/profile_images/';
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            
            // Generate unique filename
            $extension = pathinfo($profileImage['name'], PATHINFO_EXTENSION);
            $fileName = 'profile_' . $userId . '_' . time() . '.' . $extension;
            $filePath = $uploadDir . $fileName;
            
            // Move uploaded file
            if (move_uploaded_file($profileImage['tmp_name'], $filePath)) {
                // Delete old profile image if it exists
                $stmt = $pdo->prepare("SELECT profile_image FROM users WHERE id = ?");
                $stmt->execute([$userId]);
                $oldImage = $stmt->fetchColumn();
                
                if ($oldImage && file_exists($_SERVER['DOCUMENT_ROOT'] . '/uploads/profile_images/' . $oldImage)) {
                    unlink($_SERVER['DOCUMENT_ROOT'] . '/uploads/profile_images/' . $oldImage);
                }
                
                $updateFields[] = "profile_image = :profile_image";
                $params[':profile_image'] = $fileName;
            } else {
                return ['success' => false, 'message' => 'Failed to upload profile image. Please try again.'];
            }
        }
        
        // Handle password change
        if ($newPassword !== null) {
            if (strlen($newPassword) < 8) {
                return ['success' => false, 'message' => 'Password must be at least 8 characters'];
            }
            
            $updateFields[] = "password = :password";
            $params[':password'] = password_hash($newPassword, PASSWORD_DEFAULT);
        }
        
        // If no fields to update, return success
        if (empty($updateFields)) {
            return ['success' => true, 'message' => 'No changes to update'];
        }
        
        // Build the SQL query
        $sql = "UPDATE users SET " . implode(", ", $updateFields) . " WHERE id = :userId";
        $params[':userId'] = $userId;
        
        // Execute the update
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        
        // Update session data if it's the current user
        if (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $userId) {
            if (isset($data['username'])) {
                $_SESSION['username'] = $data['username'];
            }
            if (isset($data['email'])) {
                $_SESSION['email'] = $data['email'];
            }
            if (isset($data['phone'])) {
                $_SESSION['phone'] = $data['phone'];
            }
            if (isset($params[':profile_image'])) {
                $_SESSION['profile_image'] = $params[':profile_image'];
            }
        }
        
        return ['success' => true, 'message' => 'Profile updated successfully'];
    } catch (PDOException $e) {
        return ['success' => false, 'message' => 'Update failed: ' . $e->getMessage()];
    }
}

/**
 * Get user orders
 * 
 * @param int $userId User ID
 * @return array User orders
 */
function getUserOrders($userId) {
    /** @var \PDO $pdo */
    global $pdo;
    
    if (!$pdo) {
        return [];
    }
    
    try {
        $stmt = $pdo->prepare("
            SELECT o.*, 
                   (SELECT COUNT(*) FROM order_items WHERE order_id = o.id) as item_count
            FROM orders o
            WHERE o.user_id = :user_id
            ORDER BY o.created_at DESC
        ");
        $stmt->execute([':user_id' => $userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log('Error getting user orders: ' . $e->getMessage());
        return [];
    }
}
?> 
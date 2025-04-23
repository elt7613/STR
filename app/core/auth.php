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
 * @return array Result with status and message
 */
function registerUser($username, $email, $password, $phone = '') {
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
        $stmt = $pdo->prepare("INSERT INTO users (username, email, password, phone) VALUES (?, ?, ?, ?)");
        $stmt->execute([$username, $email, $hashedPassword, $phone]);
        
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
    /** @var \PDO $pdo */
    global $pdo;
    
    // If not logged in, not admin
    if (!isLoggedIn()) {
        return false;
    }
    
    if (!$pdo) {
        return false;
    }
    
    try {
        // Check if user has admin role
        $stmt = $pdo->prepare("SELECT is_admin FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch();
        
        return $user && isset($user['is_admin']) && $user['is_admin'] == 1;
    } catch (PDOException $e) {
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
        $stmt = $pdo->query("SELECT id, username, email, created_at, is_admin FROM users ORDER BY id");
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
        $stmt = $pdo->prepare("SELECT id, username, email, phone, is_admin, created_at FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        return $stmt->fetch();
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
?> 
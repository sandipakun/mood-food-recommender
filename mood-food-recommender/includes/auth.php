<?php
/**
 * Authentication Helper Functions
 */

/**
 * Register a new user
 */
function registerUser($username, $email, $password) {
    $db = getDB();
    
    // Validate inputs
    if (empty($username) || empty($email) || empty($password)) {
        return ['success' => false, 'message' => 'All fields are required'];
    }
    
    if (!isValidEmail($email)) {
        return ['success' => false, 'message' => 'Invalid email format'];
    }
    
    if (strlen($password) < 6) {
        return ['success' => false, 'message' => 'Password must be at least 6 characters'];
    }
    
    // Check if username or email exists
    $stmt = $db->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
    $stmt->execute([$username, $email]);
    if ($stmt->fetch()) {
        return ['success' => false, 'message' => 'Username or email already exists'];
    }
    
    // Hash password
    $passwordHash = password_hash($password, PASSWORD_BCRYPT);
    
    // Insert user
    try {
        $stmt = $db->prepare("INSERT INTO users (username, email, password_hash) VALUES (?, ?, ?)");
        $stmt->execute([$username, $email, $passwordHash]);
        
        return ['success' => true, 'user_id' => $db->lastInsertId()];
    } catch (PDOException $e) {
        error_log("Registration error: " . $e->getMessage());
        return ['success' => false, 'message' => 'Registration failed'];
    }
}

/**
 * Login user
 */
function loginUser($username, $password) {
    $db = getDB();
    
    $stmt = $db->prepare("SELECT id, username, email, password_hash, is_premium FROM users WHERE username = ? OR email = ?");
    $stmt->execute([$username, $username]);
    $user = $stmt->fetch();
    
    if (!$user || !password_verify($password, $user['password_hash'])) {
        return ['success' => false, 'message' => 'Invalid credentials'];
    }
    
    // Set session
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['email'] = $user['email'];
    $_SESSION['is_premium'] = $user['is_premium'];
    
    return ['success' => true, 'user' => [
        'id' => $user['id'],
        'username' => $user['username'],
        'email' => $user['email'],
        'is_premium' => $user['is_premium']
    ]];
}

/**
 * Logout user
 */
function logoutUser() {
    $_SESSION = [];
    if (isset($_COOKIE[session_name()])) {
        setcookie(session_name(), '', time() - 3600, '/');
    }
    session_destroy();
    return ['success' => true];
}

/**
 * Get current user
 */
function getCurrentUser() {
    if (!isLoggedIn()) {
        return null;
    }
    
    return [
        'id' => $_SESSION['user_id'],
        'username' => $_SESSION['username'],
        'email' => $_SESSION['email'],
        'is_premium' => $_SESSION['is_premium'] ?? 0
    ];
}


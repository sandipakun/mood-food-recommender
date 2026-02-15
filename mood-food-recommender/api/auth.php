<?php
/**
 * Authentication API Endpoints
 * POST /api/auth.php?action=register
 * POST /api/auth.php?action=login
 * POST /api/auth.php?action=logout
 * GET  /api/auth.php?action=me
 */

require_once dirname(__DIR__) . '/config/config.php';

$action = $_GET['action'] ?? $_POST['action'] ?? '';

switch ($action) {
    case 'register':
        handleRegister();
        break;
    
    case 'login':
        handleLogin();
        break;
    
    case 'logout':
        handleLogout();
        break;
    
    case 'me':
        handleGetCurrentUser();
        break;
    
    default:
        errorResponse('Invalid action', 400);
}

function handleRegister() {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        errorResponse('Method not allowed', 405);
    }
    
    $username = sanitize($_POST['username'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    
    if ($password !== $confirmPassword) {
        errorResponse('Passwords do not match', 400);
    }
    
    $result = registerUser($username, $email, $password);
    
    if ($result['success']) {
        // Auto-login after registration
        loginUser($username, $password);
        successResponse(getCurrentUser(), 'Registration successful');
    } else {
        errorResponse($result['message'], 400);
    }
}

function handleLogin() {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        errorResponse('Method not allowed', 405);
    }
    
    $username = sanitize($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    $result = loginUser($username, $password);
    
    if ($result['success']) {
        successResponse($result['user'], 'Login successful');
    } else {
        errorResponse($result['message'], 401);
    }
}

function handleLogout() {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        errorResponse('Method not allowed', 405);
    }
    
    $result = logoutUser();
    successResponse(null, 'Logout successful');
}

function handleGetCurrentUser() {
    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        errorResponse('Method not allowed', 405);
    }
    
    $user = getCurrentUser();
    if ($user) {
        successResponse($user);
    } else {
        errorResponse('Not authenticated', 401);
    }
}


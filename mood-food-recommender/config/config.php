<?php
/**
 * Mood-Based Recipe Recommender - Configuration
 * Production-ready configuration with security best practices
 */

// Error reporting (disable in production)
error_reporting(E_ALL);
ini_set("display_errors", 1); // Set to 1 for debugging
ini_set('log_errors', 1);

// Session security
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', 0); // Set to 1 if using HTTPS
ini_set('session.use_strict_mode', 1);
ini_set('session.cookie_samesite', 'Strict');

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Application constants
define('APP_NAME', 'Mood Food Recommender');
define('APP_VERSION', '1.0.0');
define('BASE_URL', 'http://localhost/mood-food-recommender'); // Update for production

// Paths
define('ROOT_PATH', dirname(__DIR__));
define('API_PATH', ROOT_PATH . '/api');
define('ASSETS_PATH', ROOT_PATH . '/assets');

// Database configuration
require_once ROOT_PATH . '/config/database.php';

// Utility functions
require_once ROOT_PATH . '/includes/utils.php';

// Authentication helper
require_once ROOT_PATH . '/includes/auth.php';

// CORS headers (if needed for API)
header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');


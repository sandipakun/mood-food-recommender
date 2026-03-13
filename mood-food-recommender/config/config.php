<?php
/**
 * Global application configuration for Mood Food Recommender.
 *
 * - Centralizes error reporting
 * - Applies session ini settings BEFORE starting session
 * - Starts PHP session exactly once for the whole app
 * - Defines global constants (BASE_URL, APP_NAME, APP_VERSION, paths)
 * - Bootstraps database + shared helpers
 */

// ---------- Error reporting (development) ----------
error_reporting(E_ALL);
ini_set('display_errors', '1');   // Set to '0' in production
ini_set('log_errors', '1');

// ---------- Session configuration (must be BEFORE session_start) ----------
ini_set('session.cookie_httponly', '1');
ini_set('session.cookie_secure', '0'); // Set to '1' when using HTTPS
ini_set('session.use_strict_mode', '1');
ini_set('session.cookie_samesite', 'Strict');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ---------- Application constants ----------
define('APP_NAME', 'Mood Food Recommender');
define('APP_VERSION', '1.0.0');
define('BASE_URL', 'http://localhost/mood-food-recommender'); // Update for production

// ---------- Paths ----------
define('ROOT_PATH', dirname(__DIR__));
define('API_PATH', ROOT_PATH . '/api');
define('ASSETS_PATH', ROOT_PATH . '/assets');

// ---------- Core bootstrap ----------
// Database (PDO, utf8mb4, port 3307)
require_once ROOT_PATH . '/config/database.php';

// Shared utilities (sanitize, JSON helpers, etc.)
require_once ROOT_PATH . '/includes/utils.php';

// Frontend authentication helpers (user auth, not admin)
require_once ROOT_PATH . '/includes/auth.php';

// ---------- Security headers ----------
// NOTE: We intentionally do NOT set Content-Type here so this config
// can be used by both JSON APIs and HTML pages.
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');


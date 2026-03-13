<?php
/**
 * Admin-specific helpers.
 *
 * NOTE:
 * - Global session configuration and session_start() live in config/config.php.
 * - This file is loaded AFTER config/config.php from admin/includes/header.php.
 */

define('ADMIN_APP_NAME', 'Mood Food Admin');

// Paths
define('ADMIN_ROOT', dirname(__DIR__));                 // .../admin
define('PROJECT_ROOT', dirname(ADMIN_ROOT));            // project root

// Database + utilities are already loaded via config/config.php.
/**
 * HTML escape helper.
 */
function e(string $value): string {
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

/**
 * Admin URL helper (absolute, based on BASE_URL).
 */
function admin_url(string $path): string {
    $path = ltrim($path, '/');
    if (defined('BASE_URL')) {
        return rtrim(BASE_URL, '/') . '/admin/' . $path;
    }
    // Fallback if BASE_URL is not defined for some reason
    return '/admin/' . $path;
}

/**
 * Redirect and exit.
 */
function redirect(string $to): void {
    header('Location: ' . $to);
    exit;
}

/**
 * CSRF token helpers.
 */
function csrf_token(): string {
    if (empty($_SESSION['admin_csrf'])) {
        $_SESSION['admin_csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['admin_csrf'];
}

function csrf_field(): string {
    return '<input type="hidden" name="csrf_token" value="' . e(csrf_token()) . '">';
}

function csrf_verify(): void {
    $token = $_POST['csrf_token'] ?? '';
    if (!$token || empty($_SESSION['admin_csrf']) || !hash_equals($_SESSION['admin_csrf'], $token)) {
        http_response_code(419);
        die('Invalid CSRF token.');
    }
}

/**
 * Admin auth session helpers.
 */
function admin_is_logged_in(): bool {
    return !empty($_SESSION['admin_user']['id']);
}

function admin_user(): ?array {
    return admin_is_logged_in() ? $_SESSION['admin_user'] : null;
}

function admin_require_login(): void {
    if (!admin_is_logged_in()) {
        redirect(admin_url('login.php'));
    }
}

/**
 * Flash messages.
 */
function flash_set(string $type, string $message): void {
    $_SESSION['admin_flash'] = ['type' => $type, 'message' => $message];
}

function flash_get(): ?array {
    if (empty($_SESSION['admin_flash'])) return null;
    $f = $_SESSION['admin_flash'];
    unset($_SESSION['admin_flash']);
    return $f;
}


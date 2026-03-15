<?php
/**
 * Admin authentication: login, registration, logout.
 * Depends on admin/includes/config.php (which loads app config and getDB()).
 */
require_once __DIR__ . '/config.php';

/**
 * Attempt admin login by email or username. Uses password_verify().
 * On success sets $_SESSION['admin_user'] and updates last_login_at.
 * @return bool True if login succeeded
 */
function admin_login(string $login, string $password): bool
{
    $login = trim($login);
    $password = (string) $password;
    if ($login === '' || $password === '') {
        return false;
    }

    $db = getDB();
    $stmt = $db->prepare("SELECT id, username, email, password_hash, is_active FROM admin_users WHERE username = ? OR email = ? LIMIT 1");
    $stmt->execute([$login, $login]);
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$admin || (int) $admin['is_active'] !== 1 || !password_verify($password, $admin['password_hash'])) {
        return false;
    }

    session_regenerate_id(true);
    $_SESSION['admin_id']       = (int) $admin['id'];
    $_SESSION['admin_username'] = (string) $admin['username'];
    $_SESSION['admin_user']     = [
        'id'       => (int) $admin['id'],
        'username' => (string) $admin['username'],
        'email'    => (string) $admin['email'],
    ];

    $upd = $db->prepare("UPDATE admin_users SET last_login_at = NOW() WHERE id = ?");
    $upd->execute([(int) $admin['id']]);

    return true;
}

/**
 * Register a new admin. Validates: unique username/email, password >= 8 chars, confirm match.
 * Uses password_hash(PASSWORD_DEFAULT). Prepared statements for all DB.
 * @return array Empty on success, list of error messages otherwise
 */
function admin_register(string $username, string $email, string $password, string $confirm): array
{
    $errors = [];
    $username = trim($username);
    $email    = trim($email);

    if ($username === '' || $email === '' || $password === '' || $confirm === '') {
        $errors[] = 'All fields are required.';
        return $errors;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Invalid email address.';
    }

    if (strlen($password) < 8) {
        $errors[] = 'Password must be at least 8 characters.';
    }

    if ($password !== $confirm) {
        $errors[] = 'Passwords do not match.';
    }

    if (!empty($errors)) {
        return $errors;
    }

    $db = getDB();
    $stmt = $db->prepare("SELECT 1 FROM admin_users WHERE username = ? OR email = ? LIMIT 1");
    $stmt->execute([$username, $email]);
    if ($stmt->fetch()) {
        $errors[] = 'Username or email already in use.';
        return $errors;
    }

    $hash = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $db->prepare("INSERT INTO admin_users (username, email, password_hash) VALUES (?, ?, ?)");
    $stmt->execute([$username, $email, $hash]);

    return $errors;
}

/**
 * End admin session and regenerate session ID.
 */
function admin_logout(): void
{
    unset($_SESSION['admin_id'], $_SESSION['admin_username'], $_SESSION['admin_user']);
    session_regenerate_id(true);
}

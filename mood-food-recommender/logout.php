<?php
require_once 'config/database.php';
require_once 'includes/utils.php';
require_once 'includes/auth.php';

// Ensure session is started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Logout user
logoutUser();

// Redirect to index page
header('Location: index.php');
exit;
?>


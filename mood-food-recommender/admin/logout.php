<?php
require_once __DIR__ . '/includes/config.php';

$_SESSION['admin_user'] = null;
unset($_SESSION['admin_user']);
session_regenerate_id(true);

flash_set('success', 'Logged out.');
redirect(admin_url('login.php'));


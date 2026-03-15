<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/auth.php';

admin_logout();
flash_set('success', 'You have been logged out.');
redirect('admin/login.php');


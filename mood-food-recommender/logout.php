<?php
require_once __DIR__ . '/config/config.php';

// Logout user
logoutUser();

// Redirect to index page
redirect('index.php');
?>


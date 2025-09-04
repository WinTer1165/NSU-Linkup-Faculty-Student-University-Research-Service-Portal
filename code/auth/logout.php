<?php
// auth/logout.php
require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once '../includes/db_connect.php';

// Log the logout action before destroying session
if (is_logged_in()) {
    log_audit('LOGOUT', 'users', get_user_id());
}

// Destroy the session
session_destroy();

// Clear remember me cookie if exists
if (isset($_COOKIE['remember_token'])) {
    setcookie('remember_token', '', time() - 3600, '/');
}

// Redirect to home page
redirect('index.php');

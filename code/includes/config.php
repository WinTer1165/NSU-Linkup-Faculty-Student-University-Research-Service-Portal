<?php
// includes/config.php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'nsu_linkup');

define('SITE_URL', 'http://localhost/nsu-linkup');
define('UPLOAD_PATH', __DIR__ . '/../assets/uploads/');
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB upload limit
// Add after existing constants
define('BASE_URL', SITE_URL);
define('SESSION_TIMEOUT', 3600); // 1 hour
define('SITE_NAME', 'NSU LinkUp');
define('ASSETS_URL', SITE_URL . '/assets');
define('ADMIN_SECRET_KEY', 'NSU_ADMIN_2025'); // For admin signup


session_start();

// Set timezone
date_default_timezone_set('Asia/Dhaka');

// Error reporting for development
error_reporting(E_ALL);
ini_set('display_errors', 1);


<?php
// api/test.php - Simple test file
session_start();
header('Content-Type: application/json');

// Basic connectivity test
echo json_encode([
    'success' => true,
    'message' => 'API is reachable',
    'session' => [
        'user_id' => $_SESSION['user_id'] ?? 'not set',
        'user_type' => $_SESSION['user_type'] ?? 'not set'
    ],
    'php_version' => phpversion(),
    'method' => $_SERVER['REQUEST_METHOD']
]);

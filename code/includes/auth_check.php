<?php
// includes/auth_check.php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/db_connect.php';

// Check if user is logged in
if (!is_logged_in()) {
    redirect('index.php');
    exit();
}

// Get current page
$current_page = basename($_SERVER['PHP_SELF']);
$user_type = get_user_type();

// Define allowed pages for each user type
$allowed_pages = [
    'admin' => [
        'dashboard.php',
        'announcements.php',
        'verify-users.php',
        'manage-users.php',
        'contact-queries.php',
        'audit-logs.php'
    ],
    'student' => [
        'dashboard.php',
        'profile.php',
        'research.php',
        'chatbot.php',
        'students.php',
        'faculty.php',
        'events.php',
        'announcements.php'
    ],
    'faculty' => [
        'dashboard.php',
        'profile.php',
        'create-research.php',
        'manage-posts.php',
        'chatbot.php',
        'applications.php',
        'research.php',
        'students.php',
        'faculty.php',
        'events.php',
        'announcements.php'
    ],
    'organizer' => [
        'dashboard.php',
        'create-event.php',
        'all-events.php'
    ]
];

// Check if user has access to current page
$user_allowed_pages = $allowed_pages[$user_type] ?? [];
if (!in_array($current_page, $user_allowed_pages)) {
    show_alert('Access denied. You do not have permission to view this page.', 'error');
    redirect($user_type . '/dashboard.php');
    exit();
}

// Check if user is verified (except for admin)
if ($user_type !== 'admin') {
    global $db;
    $user_id = get_user_id();

    $stmt = $db->prepare("SELECT is_verified, is_banned FROM users WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if ($user['is_banned']) {
        session_destroy();
        redirect('index.php?banned=1');
        exit();
    }

    if (!$user['is_verified'] && $user_type !== 'student') {
        show_alert('Your account is pending verification. Please wait for admin approval.', 'warning');
        session_destroy();
        redirect('index.php');
        exit();
    }
}

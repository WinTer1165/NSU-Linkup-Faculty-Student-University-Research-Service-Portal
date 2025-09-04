<?php
// api/get-faculty-profile.php

require_once 'init.php';

// Include required files
require_once '../includes/config.php';
require_once '../includes/db_connect.php';

// Check if session is already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_type'], ['student', 'faculty'])) {
    send_json_response(['success' => false, 'message' => 'Unauthorized access']);
}

// Check if faculty ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    send_json_response(['success' => false, 'message' => 'Faculty ID is required']);
}

$faculty_id = intval($_GET['id']);

try {
    $query = "SELECT f.*, u.email 
              FROM faculty f 
              JOIN users u ON f.user_id = u.user_id 
              WHERE f.faculty_id = ? AND u.is_verified = TRUE AND u.is_banned = FALSE";

    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $faculty_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        send_json_response(['success' => false, 'message' => 'Faculty not found']);
    }

    $faculty = $result->fetch_assoc();

    // Get active research posts
    $research_query = "SELECT research_id, title, department, salary, duration, 
                             apply_deadline, description, min_cgpa, tags,
                             student_roles, number_required
                      FROM research_posts 
                      WHERE faculty_id = ? AND is_active = TRUE 
                      ORDER BY created_at DESC";

    $stmt_research = $conn->prepare($research_query);
    $stmt_research->bind_param("i", $faculty_id);
    $stmt_research->execute();
    $research_result = $stmt_research->get_result();

    $research_posts = [];
    while ($post = $research_result->fetch_assoc()) {
        $research_posts[] = $post;
    }

    $stmt->close();
    $stmt_research->close();
    $conn->close();

    $faculty['research_posts'] = $research_posts;

    // Send success response
    send_json_response([
        'success' => true,
        'faculty' => $faculty
    ]);
} catch (Exception $e) {
    error_log("Error in get-faculty-profile.php: " . $e->getMessage());
    send_json_response([
        'success' => false,
        'message' => 'Error fetching faculty profile'
    ]);
}

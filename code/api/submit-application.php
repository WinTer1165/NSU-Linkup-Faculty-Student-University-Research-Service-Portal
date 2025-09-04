<?php
require_once '../includes/config.php';
require_once '../includes/db_connect.php';
require_once '../includes/functions.php';

header('Content-Type: application/json');

// Check if user is logged in and is a student
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'student') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

// Check if it's a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit();
}

$user_id = $_SESSION['user_id'];
$research_id = isset($_POST['research_id']) ? (int)$_POST['research_id'] : 0;
$cover_letter = isset($_POST['cover_letter']) ? sanitize_input($_POST['cover_letter']) : '';

// Validate inputs
if (!$research_id || empty($cover_letter)) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit();
}

try {
    // Get student_id
    $student_query = "SELECT student_id, cgpa FROM students WHERE user_id = ?";
    $stmt = $conn->prepare($student_query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $student_result = $stmt->get_result();

    if ($student_result->num_rows == 0) {
        echo json_encode(['success' => false, 'message' => 'Student profile not found']);
        exit();
    }

    $student = $student_result->fetch_assoc();
    $student_id = $student['student_id'];
    $student_cgpa = $student['cgpa'];

    // Check if research post exists and is active
    $research_query = "SELECT min_cgpa, apply_deadline FROM research_posts WHERE research_id = ? AND is_active = TRUE";
    $stmt2 = $conn->prepare($research_query);
    $stmt2->bind_param("i", $research_id);
    $stmt2->execute();
    $research_result = $stmt2->get_result();

    if ($research_result->num_rows == 0) {
        echo json_encode(['success' => false, 'message' => 'Research post not found or inactive']);
        exit();
    }

    $research = $research_result->fetch_assoc();

    // Check if deadline has passed
    if (strtotime($research['apply_deadline']) < time()) {
        echo json_encode(['success' => false, 'message' => 'Application deadline has passed']);
        exit();
    }

    // Check CGPA requirement
    if ($research['min_cgpa'] && $student_cgpa && $student_cgpa < $research['min_cgpa']) {
        echo json_encode(['success' => false, 'message' => 'Your CGPA does not meet the minimum requirement']);
        exit();
    }

    // Check if already applied
    $check_query = "SELECT application_id FROM research_applications WHERE research_id = ? AND student_id = ?";
    $stmt3 = $conn->prepare($check_query);
    $stmt3->bind_param("ii", $research_id, $student_id);
    $stmt3->execute();
    $check_result = $stmt3->get_result();

    if ($check_result->num_rows > 0) {
        echo json_encode(['success' => false, 'message' => 'You have already applied for this research position']);
        exit();
    }

    // Submit application
    $insert_query = "INSERT INTO research_applications (research_id, student_id, cover_letter) VALUES (?, ?, ?)";
    $stmt4 = $conn->prepare($insert_query);
    $stmt4->bind_param("iis", $research_id, $student_id, $cover_letter);

    if ($stmt4->execute()) {
        // Log the action
        log_audit('APPLY_RESEARCH', 'research_applications', $conn->insert_id);
        echo json_encode(['success' => true, 'message' => 'Application submitted successfully!']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to submit application']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}

$conn->close();

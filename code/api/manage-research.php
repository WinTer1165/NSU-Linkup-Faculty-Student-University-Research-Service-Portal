<?php
// api/submit-application.php
require_once '../includes/config.php';
require_once '../includes/db_connect.php';
require_once '../includes/functions.php';

// Check if user is logged in and is a student
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'student') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit();
}

$user_id = $_SESSION['user_id'];
$research_id = isset($_POST['research_id']) ? (int)$_POST['research_id'] : 0;
$cover_letter = isset($_POST['cover_letter']) ? sanitize_input($_POST['cover_letter']) : '';

if (!$research_id || !$cover_letter) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit();
}

try {
    // Get student_id
    $stmt = $conn->prepare("SELECT student_id, cgpa FROM students WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'Student profile not found']);
        exit();
    }

    $student = $result->fetch_assoc();
    $student_id = $student['student_id'];
    $student_cgpa = $student['cgpa'];
    $stmt->close();

    // Check if research post exists and is active
    $stmt = $conn->prepare("SELECT min_cgpa, apply_deadline FROM research_posts WHERE research_id = ? AND is_active = TRUE");
    $stmt->bind_param("i", $research_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'Research post not found or inactive']);
        exit();
    }

    $research = $result->fetch_assoc();
    $stmt->close();

    // Check deadline
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
    $stmt = $conn->prepare("SELECT application_id FROM research_applications WHERE research_id = ? AND student_id = ?");
    $stmt->bind_param("ii", $research_id, $student_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        echo json_encode(['success' => false, 'message' => 'You have already applied for this research position']);
        exit();
    }
    $stmt->close();

    // Submit application
    $stmt = $conn->prepare("INSERT INTO research_applications (research_id, student_id, cover_letter) VALUES (?, ?, ?)");
    $stmt->bind_param("iis", $research_id, $student_id, $cover_letter);

    if ($stmt->execute()) {
        // Log the action
        log_audit('APPLY_RESEARCH', 'research_applications', $conn->insert_id);

        echo json_encode(['success' => true, 'message' => 'Application submitted successfully!']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to submit application']);
    }
    $stmt->close();
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error submitting application']);
}

$conn->close();

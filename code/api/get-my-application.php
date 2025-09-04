<?php
require_once '../includes/config.php';
require_once '../includes/db_connect.php';

header('Content-Type: application/json');

// Check if user is logged in and is a student
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'student') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$user_id = $_SESSION['user_id'];
$research_id = isset($_GET['research_id']) ? (int)$_GET['research_id'] : 0;

if (!$research_id) {
    echo json_encode(['success' => false, 'message' => 'Invalid research ID']);
    exit();
}

try {
    // Get student_id
    $student_query = "SELECT student_id FROM students WHERE user_id = ?";
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

    // Get application details
    $app_query = "SELECT ra.*, r.title as research_title, 
                         f.full_name as faculty_name, u.email as faculty_email
                  FROM research_applications ra
                  JOIN research_posts r ON ra.research_id = r.research_id
                  JOIN faculty f ON r.faculty_id = f.faculty_id
                  JOIN users u ON f.user_id = u.user_id
                  WHERE ra.research_id = ? AND ra.student_id = ?";

    $stmt2 = $conn->prepare($app_query);
    $stmt2->bind_param("ii", $research_id, $student_id);
    $stmt2->execute();
    $result = $stmt2->get_result();

    if ($result->num_rows == 0) {
        echo json_encode(['success' => false, 'message' => 'Application not found']);
        exit();
    }

    $application = $result->fetch_assoc();

    echo json_encode(['success' => true, 'application' => $application]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Database error']);
}

$conn->close();

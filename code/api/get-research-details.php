<?php
require_once '../includes/config.php';
require_once '../includes/db_connect.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$user_id = $_SESSION['user_id'];
$user_type = $_SESSION['user_type'];

// Get research ID from request
$research_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$research_id) {
    echo json_encode(['success' => false, 'message' => 'Invalid research ID']);
    exit();
}

try {
    // Get research details with faculty info
    $query = "SELECT r.*, 
                     f.full_name as faculty_name, 
                     f.profile_image as faculty_image,
                     f.office, 
                     f.title as faculty_title,
                     u.email as faculty_email,
                     (SELECT COUNT(*) FROM research_applications WHERE research_id = r.research_id) as application_count
              FROM research_posts r
              JOIN faculty f ON r.faculty_id = f.faculty_id
              JOIN users u ON f.user_id = u.user_id
              WHERE r.research_id = ? AND r.is_active = TRUE";

    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $research_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 0) {
        echo json_encode(['success' => false, 'message' => 'Research post not found']);
        exit();
    }

    $research = $result->fetch_assoc();

    // Check if student has already applied (for students only)
    if ($user_type == 'student') {
        $check_query = "SELECT ra.status 
                       FROM research_applications ra
                       JOIN students s ON ra.student_id = s.student_id
                       WHERE ra.research_id = ? AND s.user_id = ?";

        $stmt2 = $conn->prepare($check_query);
        $stmt2->bind_param("ii", $research_id, $user_id);
        $stmt2->execute();
        $check_result = $stmt2->get_result();

        if ($check_result->num_rows > 0) {
            $application = $check_result->fetch_assoc();
            $research['has_applied'] = true;
            $research['application_status'] = $application['status'];
        } else {
            $research['has_applied'] = false;
        }
        $stmt2->close();
    }

    echo json_encode(['success' => true, 'research' => $research]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}

$stmt->close();
$conn->close();

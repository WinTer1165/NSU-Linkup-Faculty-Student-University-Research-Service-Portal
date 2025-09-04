<?php
// api/get-application-details.php
require_once '../includes/config.php';
require_once '../includes/db_connect.php';
require_once '../includes/functions.php';

header('Content-Type: application/json');

// Check if user is logged in and is faculty
if (!is_logged_in() || get_user_type() !== 'faculty') {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

// Get application ID
$application_id = intval($_GET['application_id'] ?? 0);

if (!$application_id) {
    echo json_encode(['success' => false, 'message' => 'Invalid application ID']);
    exit;
}

$faculty_id = $_SESSION['faculty_id'];

// Get application details
$stmt = $db->prepare("
    SELECT 
        ra.*,
        s.full_name as student_name,
        s.email as student_email,
        s.phone as student_phone,
        s.cgpa as student_cgpa,
        s.degree as student_degree,
        s.research_interest as student_research_interest,
        s.linkedin as student_linkedin,
        s.github as student_github,
        s.profile_image as student_profile_image,
        r.title as research_title,
        r.department as research_department
    FROM research_applications ra
    JOIN students s ON ra.student_id = s.student_id
    JOIN users u ON s.user_id = u.user_id
    JOIN research_posts r ON ra.research_id = r.research_id
    WHERE ra.application_id = ? 
    AND r.faculty_id = ?
");
$stmt->bind_param("ii", $application_id, $faculty_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Application not found']);
    exit;
}

$application = $result->fetch_assoc();
$stmt->close();

// Get student's skills
$skills = [];
$stmt = $db->prepare("
    SELECT skill_name 
    FROM student_skills 
    WHERE student_id = ?
");
$stmt->bind_param("i", $application['student_id']);
$stmt->execute();
$skill_result = $stmt->get_result();
while ($skill = $skill_result->fetch_assoc()) {
    $skills[] = $skill['skill_name'];
}
$stmt->close();

// Format response
$response = [
    'success' => true,
    'data' => [
        'application' => [
            'application_id' => $application['application_id'],
            'cover_letter' => $application['cover_letter'],
            'status' => $application['status'],
            'applied_at' => $application['applied_at'],
            'reviewed_at' => $application['reviewed_at']
        ],
        'student' => [
            'student_id' => $application['student_id'],
            'full_name' => $application['student_name'],
            'email' => $application['student_email'],
            'phone' => $application['student_phone'],
            'cgpa' => $application['student_cgpa'],
            'degree' => $application['student_degree'],
            'research_interest' => $application['student_research_interest'],
            'linkedin' => $application['student_linkedin'],
            'github' => $application['student_github'],
            'profile_image' => $application['student_profile_image'] ?
                SITE_URL . '/assets/uploads/profiles/' . $application['student_profile_image'] : null,
            'skills' => $skills
        ],
        'research' => [
            'title' => $application['research_title'],
            'department' => $application['research_department']
        ]
    ]
];

echo json_encode($response);

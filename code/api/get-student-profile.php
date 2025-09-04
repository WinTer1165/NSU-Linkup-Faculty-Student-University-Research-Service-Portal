<?php
// api/get-student-profile.php


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

// Check if student ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    send_json_response(['success' => false, 'message' => 'Student ID is required']);
}

$student_id = intval($_GET['id']);

try {
    // Get student details
    $query = "SELECT s.*, u.email 
              FROM students s 
              JOIN users u ON s.user_id = u.user_id 
              WHERE s.student_id = ? AND u.is_verified = TRUE AND u.is_banned = FALSE";

    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $student_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        send_json_response(['success' => false, 'message' => 'Student not found']);
    }

    $student = $result->fetch_assoc();

    // Get skills
    $skills_query = "SELECT skill_name FROM student_skills WHERE student_id = ? ORDER BY skill_name";
    $stmt_skills = $conn->prepare($skills_query);
    $stmt_skills->bind_param("i", $student_id);
    $stmt_skills->execute();
    $skills_result = $stmt_skills->get_result();

    $skills = [];
    while ($skill = $skills_result->fetch_assoc()) {
        $skills[] = $skill['skill_name'];
    }

    // Get experience
    $exp_query = "SELECT * FROM student_experience 
                  WHERE student_id = ? 
                  ORDER BY end_date IS NULL DESC, end_date DESC, start_date DESC";
    $stmt_exp = $conn->prepare($exp_query);
    $stmt_exp->bind_param("i", $student_id);
    $stmt_exp->execute();
    $exp_result = $stmt_exp->get_result();

    $experience = [];
    while ($exp = $exp_result->fetch_assoc()) {
        $experience[] = $exp;
    }

    // Get achievements
    $ach_query = "SELECT * FROM student_achievements 
                  WHERE student_id = ? 
                  ORDER BY date DESC";
    $stmt_ach = $conn->prepare($ach_query);
    $stmt_ach->bind_param("i", $student_id);
    $stmt_ach->execute();
    $ach_result = $stmt_ach->get_result();

    $achievements = [];
    while ($ach = $ach_result->fetch_assoc()) {
        $achievements[] = $ach;
    }

    // Get publications
    $pub_query = "SELECT * FROM student_publications 
                  WHERE student_id = ? 
                  ORDER BY year DESC, title ASC";
    $stmt_pub = $conn->prepare($pub_query);
    $stmt_pub->bind_param("i", $student_id);
    $stmt_pub->execute();
    $pub_result = $stmt_pub->get_result();

    $publications = [];
    while ($pub = $pub_result->fetch_assoc()) {
        $publications[] = $pub;
    }

    // Close statements
    $stmt->close();
    $stmt_skills->close();
    $stmt_exp->close();
    $stmt_ach->close();
    $stmt_pub->close();
    $conn->close();

    // Send success response with all data
    send_json_response([
        'success' => true,
        'full_name' => $student['full_name'],
        'profile_image' => $student['profile_image'],
        'bio' => $student['bio'],
        'research_interest' => $student['research_interest'],
        'phone' => $student['phone'],
        'linkedin' => $student['linkedin'],
        'github' => $student['github'],
        'address' => $student['address'],
        'degree' => $student['degree'],
        'university' => $student['university'],
        'start_date' => $student['start_date'],
        'end_date' => $student['end_date'],
        'cgpa' => $student['cgpa'],
        'email' => $student['email'],
        'skills' => $skills,
        'experience' => $experience,
        'achievements' => $achievements,
        'publications' => $publications
    ]);
} catch (Exception $e) {
    error_log("Error in get-student-profile.php: " . $e->getMessage());
    send_json_response([
        'success' => false,
        'message' => 'Error fetching student profile'
    ]);
}

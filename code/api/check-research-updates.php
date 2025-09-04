<?php
require_once '../includes/config.php';
require_once '../includes/db_connect.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'hasUpdates' => false]);
    exit();
}

// Check for new research posts in the last hour
$query = "SELECT COUNT(*) as new_count FROM research_posts 
          WHERE is_active = TRUE AND created_at > DATE_SUB(NOW(), INTERVAL 1 HOUR)";

$result = $conn->query($query);
$row = $result->fetch_assoc();

$hasUpdates = $row['new_count'] > 0;

echo json_encode(['success' => true, 'hasUpdates' => $hasUpdates]);

$conn->close();

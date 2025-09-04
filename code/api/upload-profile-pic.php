<?php
// api/upload-profile-pic.php
require_once '../includes/config.php';
require_once '../includes/db_connect.php';
require_once '../includes/functions.php';

// Check if user is logged in
if (!is_logged_in()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

// Check if file was uploaded
if (!isset($_FILES['profile_picture']) || $_FILES['profile_picture']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['success' => false, 'message' => 'No file uploaded']);
    exit;
}

$file = $_FILES['profile_picture'];
$user_type = get_user_type();
$user_id = get_user_id();

// Upload the image
$result = upload_image($file, 'profiles');

if ($result['success']) {
    // Update database based on user type
    $table = '';
    $id_field = '';
    $id_value = 0;

    switch ($user_type) {
        case 'student':
            $table = 'students';
            $id_field = 'user_id';
            $id_value = $user_id;
            break;
        case 'faculty':
            $table = 'faculty';
            $id_field = 'user_id';
            $id_value = $user_id;
            break;
        case 'organizer':
            $table = 'organizers';
            $id_field = 'user_id';
            $id_value = $user_id;
            break;
        default:
            echo json_encode(['success' => false, 'message' => 'Invalid user type']);
            exit;
    }

    // Get old image to delete
    $stmt = $db->prepare("SELECT profile_image FROM $table WHERE $id_field = ?");
    $stmt->bind_param("i", $id_value);
    $stmt->execute();
    $result_query = $stmt->get_result();
    $old_data = $result_query->fetch_assoc();
    $old_image = $old_data['profile_image'] ?? null;
    $stmt->close();

    // Update with new image
    $stmt = $db->prepare("UPDATE $table SET profile_image = ? WHERE $id_field = ?");
    $stmt->bind_param("si", $result['filename'], $id_value);

    if ($stmt->execute()) {
        // Delete old image if exists
        if ($old_image && file_exists(UPLOAD_PATH . 'profiles/' . $old_image)) {
            unlink(UPLOAD_PATH . 'profiles/' . $old_image);
        }

        // Log the update
        log_audit('UPDATE_PROFILE_PICTURE', $table, $id_value, ['profile_image' => $old_image], ['profile_image' => $result['filename']]);

        echo json_encode([
            'success' => true,
            'filename' => $result['filename'],
            'url' => SITE_URL . '/assets/uploads/profiles/' . $result['filename']
        ]);
    } else {
        // Delete uploaded file if database update failed
        if (file_exists(UPLOAD_PATH . 'profiles/' . $result['filename'])) {
            unlink(UPLOAD_PATH . 'profiles/' . $result['filename']);
        }
        echo json_encode(['success' => false, 'message' => 'Failed to update profile picture']);
    }
    $stmt->close();
} else {
    echo json_encode(['success' => false, 'message' => $result['message']]);
}

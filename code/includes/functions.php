<?php
// includes/functions.php

require_once __DIR__ . '/db_connect.php';
// Sanitize input
function sanitize_input($data)
{
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Validate email
function validate_email($email)
{
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

// Hash password
function hash_password($password)
{
    return password_hash($password, PASSWORD_BCRYPT);
}

// Verify password
function verify_password($password, $hash)
{
    return password_verify($password, $hash);
}

// Check if user is logged in
function is_logged_in()
{
    return isset($_SESSION['user_id']) && isset($_SESSION['user_type']);
}

// Get user type
function get_user_type()
{
    return $_SESSION['user_type'] ?? null;
}

// Get user ID
function get_user_id()
{
    return $_SESSION['user_id'] ?? null;
}

// Redirect function
function redirect($url)
{
    header("Location: " . SITE_URL . "/" . $url);
    exit();
}

// Show alert message
function show_alert($message, $type = 'info')
{
    $_SESSION['alert'] = [
        'message' => $message,
        'type' => $type
    ];
}

// Display alert
function display_alert()
{
    if (isset($_SESSION['alert'])) {
        $alert = $_SESSION['alert'];
        unset($_SESSION['alert']);

        $class = 'alert-info';
        switch ($alert['type']) {
            case 'success':
                $class = 'alert-success';
                break;
            case 'error':
                $class = 'alert-danger';
                break;
            case 'warning':
                $class = 'alert-warning';
                break;
        }

        echo '<div class="alert ' . $class . ' alert-dismissible fade show" role="alert">';
        echo htmlspecialchars($alert['message']);
        echo '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>';
        echo '</div>';
    }
}

// Upload image
function upload_image($file, $folder = 'profiles')
{
    $allowed = ['jpg', 'jpeg', 'png', 'gif'];
    $filename = $file['name'];
    $fileTmp = $file['tmp_name'];
    $fileSize = $file['size'];

    $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

    if (!in_array($ext, $allowed)) {
        return ['success' => false, 'message' => 'Invalid file type'];
    }

    if ($fileSize > MAX_FILE_SIZE) {
        return ['success' => false, 'message' => 'File size too large'];
    }

    $newName = uniqid() . '.' . $ext;
    $uploadPath = UPLOAD_PATH . $folder . '/' . $newName;

    if (move_uploaded_file($fileTmp, $uploadPath)) {
        return ['success' => true, 'filename' => $newName];
    }

    return ['success' => false, 'message' => 'Upload failed'];
}

// Log audit trail
function log_audit($action, $table, $record_id, $old_values = null, $new_values = null)
{
    global $db;

    $user_id = get_user_id();
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';

    $stmt = $db->prepare("INSERT INTO audit_logs (user_id, action, table_affected, record_id, old_values, new_values, ip_address) VALUES (?, ?, ?, ?, ?, ?, ?)");

    $old_json = $old_values ? json_encode($old_values) : null;
    $new_json = $new_values ? json_encode($new_values) : null;

    $stmt->bind_param("issssss", $user_id, $action, $table, $record_id, $old_json, $new_json, $ip);
    $stmt->execute();
    $stmt->close();
}

// Pagination function
function paginate($total_records, $current_page, $records_per_page = 10)
{
    $total_pages = ceil($total_records / $records_per_page);
    $offset = ($current_page - 1) * $records_per_page;

    return [
        'total_pages' => $total_pages,
        'current_page' => $current_page,
        'offset' => $offset,
        'limit' => $records_per_page,
        'total_records' => $total_records
    ];
}

// Format date
function format_date($date, $format = 'M d, Y')
{
    return date($format, strtotime($date));
}

// Check user role
function has_role($role)
{
    return get_user_type() === $role;
}

// Generate CSRF token
function generate_csrf_token()
{
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// Verify CSRF token
function verify_csrf_token($token)
{
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// Get user details
function get_user_details($user_id, $user_type)
{
    global $db;

    $table = '';
    $id_field = '';

    switch ($user_type) {
        case 'student':
            $table = 'students';
            $id_field = 'student_id';
            break;
        case 'faculty':
            $table = 'faculty';
            $id_field = 'faculty_id';
            break;
        case 'organizer':
            $table = 'organizers';
            $id_field = 'organizer_id';
            break;
        case 'admin':
            $table = 'admins';
            $id_field = 'admin_id';
            break;
    }

    if ($table) {
        $stmt = $db->prepare("SELECT * FROM $table WHERE user_id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }

    return null;
}

// Fetch combined user info (email + role-specific fields) for headers, etc.
function getUserInfo(mysqli $conn, int $user_id, string $user_type): array
{
    // Safe defaults
    $defaults = [
        'full_name'     => 'User',
        'profile_image' => null,
        'email'         => null,
    ];

    // Map role to its table & extra columns you want
    $map = [
        'student'   => ['table' => 'students',   'cols' => 'full_name, profile_image, degree, cgpa, linkedin, github'],
        'faculty' => ['table' => 'faculty', 'cols' => 'full_name, profile_image, title, office, linkedin, github'],
        'organizer' => ['table' => 'organizers', 'cols' => 'full_name, profile_image'],
        'admin'     => ['table' => 'admins',     'cols' => 'full_name, profile_image'],
    ];

    if (!isset($map[$user_type])) {
        return $defaults;
    }

    $tbl  = $map[$user_type]['table'];
    $cols = $map[$user_type]['cols'];

    $sql = "SELECT u.email, $cols
            FROM users u
            JOIN $tbl r ON u.user_id = r.user_id
            WHERE u.user_id = ?
            LIMIT 1";

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        error_log('getUserInfo prepare failed: ' . $conn->error);
        return $defaults;
    }

    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $res = $stmt->get_result();
    $row = $res ? $res->fetch_assoc() : null;
    $stmt->close();

    return $row ?: $defaults;
}

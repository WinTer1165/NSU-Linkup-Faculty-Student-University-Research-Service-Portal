<?php
require_once '../includes/config.php';
require_once '../includes/db_connect.php';
require_once '../includes/functions.php';
require_once '../includes/auth_check.php';

// Check if user is logged in and is a student
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'student') {
    header("Location: ../index.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Handle AJAX requests
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');

    $action = $_POST['action'];
    $response = ['success' => false, 'message' => ''];

    try {
        switch ($action) {
            case 'upload_profile_picture':
                // Handle profile picture upload
                if (!isset($_FILES['profile_picture']) || $_FILES['profile_picture']['error'] !== UPLOAD_ERR_OK) {
                    $response['message'] = 'No file uploaded or upload error.';
                    break;
                }

                $file = $_FILES['profile_picture'];

                // Validate file type
                $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
                if (!in_array($file['type'], $allowed_types)) {
                    $response['message'] = 'Invalid file type. Please upload JPEG, PNG, or GIF files only.';
                    break;
                }

                // Validate file size (5MB max)
                $max_size = 5 * 1024 * 1024; // 5MB
                if ($file['size'] > $max_size) {
                    $response['message'] = 'File size too large. Maximum size is 5MB.';
                    break;
                }

                // Create upload directory if it doesn't exist
                $upload_dir = '../assets/uploads/profiles/';
                if (!file_exists($upload_dir)) {
                    mkdir($upload_dir, 0755, true);
                }

                // Generate unique filename
                $file_extension = pathinfo($file['name'], PATHINFO_EXTENSION);
                $filename = 'profile_' . $user_id . '_' . uniqid() . '.' . $file_extension;
                $filepath = $upload_dir . $filename;

                // Get current profile image to delete later
                $stmt = $conn->prepare("SELECT profile_image FROM students WHERE user_id = ?");
                $stmt->bind_param("i", $user_id);
                $stmt->execute();
                $current_image = $stmt->get_result()->fetch_assoc()['profile_image'];
                $stmt->close();

                // Move uploaded file
                if (move_uploaded_file($file['tmp_name'], $filepath)) {
                    // Update database
                    $stmt = $conn->prepare("UPDATE students SET profile_image = ? WHERE user_id = ?");
                    $stmt->bind_param("si", $filename, $user_id);

                    if ($stmt->execute()) {
                        // Delete old profile image if it exists
                        if ($current_image && file_exists($upload_dir . $current_image)) {
                            unlink($upload_dir . $current_image);
                        }

                        $response['success'] = true;
                        $response['message'] = 'Profile picture updated successfully!';
                        $response['filename'] = $filename;

                        // Log the action
                        log_audit('UPDATE_PROFILE_PICTURE', 'students', $user_id);
                    } else {
                        // Delete uploaded file if database update failed
                        unlink($filepath);
                        $response['message'] = 'Failed to update database.';
                    }
                    $stmt->close();
                } else {
                    $response['message'] = 'Failed to upload file.';
                }
                break;

            case 'update_basic':
                $full_name = sanitize_input($_POST['full_name']);
                $phone = sanitize_input($_POST['phone']);
                $bio = sanitize_input($_POST['bio']);
                $research_interest = sanitize_input($_POST['research_interest']);
                $linkedin = sanitize_input($_POST['linkedin']);
                $github = sanitize_input($_POST['github']);
                $address = sanitize_input($_POST['address']);

                $stmt = $conn->prepare("UPDATE students SET full_name = ?, phone = ?, bio = ?, research_interest = ?, linkedin = ?, github = ?, address = ? WHERE user_id = ?");
                $stmt->bind_param("sssssssi", $full_name, $phone, $bio, $research_interest, $linkedin, $github, $address, $user_id);

                if ($stmt->execute()) {
                    $_SESSION['full_name'] = $full_name;
                    $response['success'] = true;
                    $response['message'] = 'Basic information updated successfully!';
                    log_audit('UPDATE_BASIC_INFO', 'students', $user_id);
                } else {
                    $response['message'] = 'Failed to update basic information.';
                }
                $stmt->close();
                break;

            case 'update_education':
                $degree = sanitize_input($_POST['degree']);
                $university = sanitize_input($_POST['university']);
                $start_date = sanitize_input($_POST['start_date']);
                $end_date = sanitize_input($_POST['end_date']);
                $cgpa = $_POST['cgpa'] ? floatval($_POST['cgpa']) : null;

                $stmt = $conn->prepare("UPDATE students SET degree = ?, university = ?, start_date = ?, end_date = ?, cgpa = ? WHERE user_id = ?");
                $stmt->bind_param("ssssdi", $degree, $university, $start_date, $end_date, $cgpa, $user_id);

                if ($stmt->execute()) {
                    $response['success'] = true;
                    $response['message'] = 'Education details updated successfully!';
                    log_audit('UPDATE_EDUCATION', 'students', $user_id);
                } else {
                    $response['message'] = 'Failed to update education details.';
                }
                $stmt->close();
                break;

            case 'add_experience':
                $student_id = getStudentId($user_id);
                $position = sanitize_input($_POST['position']);
                $company = sanitize_input($_POST['company']);
                $start_date = sanitize_input($_POST['start_date']);
                $end_date = $_POST['end_date'] ? sanitize_input($_POST['end_date']) : null;
                $description = sanitize_input($_POST['description']);

                $stmt = $conn->prepare("INSERT INTO student_experience (student_id, position, company, start_date, end_date, description) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("isssss", $student_id, $position, $company, $start_date, $end_date, $description);

                if ($stmt->execute()) {
                    $response['success'] = true;
                    $response['message'] = 'Experience added successfully!';
                    $response['experience_id'] = $conn->insert_id;
                    log_audit('ADD_EXPERIENCE', 'student_experience', $conn->insert_id);
                } else {
                    $response['message'] = 'Failed to add experience.';
                }
                $stmt->close();
                break;

            case 'delete_experience':
                $exp_id = intval($_POST['exp_id']);
                $student_id = getStudentId($user_id);

                $stmt = $conn->prepare("DELETE FROM student_experience WHERE exp_id = ? AND student_id = ?");
                $stmt->bind_param("ii", $exp_id, $student_id);

                if ($stmt->execute()) {
                    $response['success'] = true;
                    $response['message'] = 'Experience deleted successfully!';
                    log_audit('DELETE_EXPERIENCE', 'student_experience', $exp_id);
                } else {
                    $response['message'] = 'Failed to delete experience.';
                }
                $stmt->close();
                break;

            case 'add_skill':
                $student_id = getStudentId($user_id);
                $skill_name = sanitize_input($_POST['skill_name']);

                // Check if skill already exists
                $stmt = $conn->prepare("SELECT skill_id FROM student_skills WHERE student_id = ? AND skill_name = ?");
                $stmt->bind_param("is", $student_id, $skill_name);
                $stmt->execute();
                if ($stmt->get_result()->num_rows > 0) {
                    $response['message'] = 'Skill already exists.';
                    $stmt->close();
                    break;
                }
                $stmt->close();

                $stmt = $conn->prepare("INSERT INTO student_skills (student_id, skill_name) VALUES (?, ?)");
                $stmt->bind_param("is", $student_id, $skill_name);

                if ($stmt->execute()) {
                    $response['success'] = true;
                    $response['message'] = 'Skill added successfully!';
                    $response['skill_id'] = $conn->insert_id;
                    log_audit('ADD_SKILL', 'student_skills', $conn->insert_id);
                } else {
                    $response['message'] = 'Failed to add skill.';
                }
                $stmt->close();
                break;

            case 'delete_skill':
                $skill_id = intval($_POST['skill_id']);
                $student_id = getStudentId($user_id);

                $stmt = $conn->prepare("DELETE FROM student_skills WHERE skill_id = ? AND student_id = ?");
                $stmt->bind_param("ii", $skill_id, $student_id);

                if ($stmt->execute()) {
                    $response['success'] = true;
                    $response['message'] = 'Skill deleted successfully!';
                    log_audit('DELETE_SKILL', 'student_skills', $skill_id);
                } else {
                    $response['message'] = 'Failed to delete skill.';
                }
                $stmt->close();
                break;

            case 'add_achievement':
                $student_id = getStudentId($user_id);
                $title = sanitize_input($_POST['title']);
                $type = sanitize_input($_POST['type']);
                $description = sanitize_input($_POST['description']);
                $date = sanitize_input($_POST['date']);

                $stmt = $conn->prepare("INSERT INTO student_achievements (student_id, title, type, description, date) VALUES (?, ?, ?, ?, ?)");
                $stmt->bind_param("issss", $student_id, $title, $type, $description, $date);

                if ($stmt->execute()) {
                    $response['success'] = true;
                    $response['message'] = 'Achievement added successfully!';
                    $response['achievement_id'] = $conn->insert_id;
                    log_audit('ADD_ACHIEVEMENT', 'student_achievements', $conn->insert_id);
                } else {
                    $response['message'] = 'Failed to add achievement.';
                }
                $stmt->close();
                break;

            case 'delete_achievement':
                $achievement_id = intval($_POST['achievement_id']);
                $student_id = getStudentId($user_id);

                $stmt = $conn->prepare("DELETE FROM student_achievements WHERE achievement_id = ? AND student_id = ?");
                $stmt->bind_param("ii", $achievement_id, $student_id);

                if ($stmt->execute()) {
                    $response['success'] = true;
                    $response['message'] = 'Achievement deleted successfully!';
                    log_audit('DELETE_ACHIEVEMENT', 'student_achievements', $achievement_id);
                } else {
                    $response['message'] = 'Failed to delete achievement.';
                }
                $stmt->close();
                break;

            case 'add_publication':
                $student_id = getStudentId($user_id);
                $title = sanitize_input($_POST['title']);
                $journal = sanitize_input($_POST['journal']);
                $year = intval($_POST['year']);
                $url = $_POST['url'] ? sanitize_input($_POST['url']) : null;

                $stmt = $conn->prepare("INSERT INTO student_publications (student_id, title, journal, year, url) VALUES (?, ?, ?, ?, ?)");
                $stmt->bind_param("issis", $student_id, $title, $journal, $year, $url);

                if ($stmt->execute()) {
                    $response['success'] = true;
                    $response['message'] = 'Publication added successfully!';
                    $response['publication_id'] = $conn->insert_id;
                    log_audit('ADD_PUBLICATION', 'student_publications', $conn->insert_id);
                } else {
                    $response['message'] = 'Failed to add publication.';
                }
                $stmt->close();
                break;

            case 'delete_publication':
                $publication_id = intval($_POST['publication_id']);
                $student_id = getStudentId($user_id);

                $stmt = $conn->prepare("DELETE FROM student_publications WHERE publication_id = ? AND student_id = ?");
                $stmt->bind_param("ii", $publication_id, $student_id);

                if ($stmt->execute()) {
                    $response['success'] = true;
                    $response['message'] = 'Publication deleted successfully!';
                    log_audit('DELETE_PUBLICATION', 'student_publications', $publication_id);
                } else {
                    $response['message'] = 'Failed to delete publication.';
                }
                $stmt->close();
                break;

            default:
                $response['message'] = 'Invalid action.';
        }
    } catch (Exception $e) {
        $response['message'] = 'An error occurred: ' . $e->getMessage();
    }

    echo json_encode($response);
    exit();
}

// Helper function to get student ID
function getStudentId($user_id)
{
    global $conn;
    $stmt = $conn->prepare("SELECT student_id FROM students WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();
    return $row['student_id'];
}

// Get student data
$stmt = $conn->prepare("SELECT s.*, u.email FROM students s JOIN users u ON s.user_id = u.user_id WHERE s.user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$student = $stmt->get_result()->fetch_assoc();
$stmt->close();

$student_id = $student['student_id'];

// Get experience
$stmt = $conn->prepare("SELECT * FROM student_experience WHERE student_id = ? ORDER BY start_date DESC");
$stmt->bind_param("i", $student_id);
$stmt->execute();
$experiences = $stmt->get_result();
$stmt->close();

// Get skills
$stmt = $conn->prepare("SELECT * FROM student_skills WHERE student_id = ?");
$stmt->bind_param("i", $student_id);
$stmt->execute();
$skills = $stmt->get_result();
$stmt->close();

// Get achievements
$stmt = $conn->prepare("SELECT * FROM student_achievements WHERE student_id = ? ORDER BY date DESC");
$stmt->bind_param("i", $student_id);
$stmt->execute();
$achievements = $stmt->get_result();
$stmt->close();

// Get publications
$stmt = $conn->prepare("SELECT * FROM student_publications WHERE student_id = ? ORDER BY year DESC");
$stmt->bind_param("i", $student_id);
$stmt->execute();
$publications = $stmt->get_result();
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - NSU LinkUp</title>
    <link rel="stylesheet" href="../assets/css/common.css">
    <link rel="stylesheet" href="../assets/css/profile.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>

<body>
    <div class="dashboard-container">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <h2>NSU LinkUp</h2>
                <p>Student Portal</p>
            </div>

            <nav class="sidebar-nav">
                <a href="dashboard.php">
                    <i class="fas fa-home"></i>
                    Dashboard
                </a>
                <a href="profile.php" class="active">
                    <i class="fas fa-user"></i>
                    My Profile
                </a>
                <a href="../student/chatbot.php">
                    <i class="fas fa-robot"></i>
                    AI Assistant
                </a>
                <a href="../student/research.php">
                    <i class="fas fa-microscope"></i>
                    Research Posts
                </a>
                <a href="../student/students.php">
                    <i class="fas fa-graduation-cap"></i>
                    Students
                </a>
                <a href="../student/faculty.php">
                    <i class="fas fa-chalkboard-teacher"></i>
                    Faculty
                </a>
                <!-- <a href="../student/events.php">
                    <i class="fas fa-calendar-alt"></i>
                    Events
                </a> -->
                <a href="../student/announcements.php">
                    <i class="fas fa-bullhorn"></i>
                    Announcements
                </a>
                <a href="../auth/logout.php" class="logout">
                    <i class="fas fa-sign-out-alt"></i>
                    Logout
                </a>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <!-- Profile Header -->
            <div class="profile-header">
                <div class="profile-cover"></div>
                <div class="profile-info">
                    <div class="profile-picture-wrapper">
                        <?php if ($student['profile_image']): ?>
                            <img src="../assets/uploads/profiles/<?php echo $student['profile_image']; ?>" alt="Profile" class="profile-picture">
                        <?php else: ?>
                            <div class="profile-picture-placeholder">
                                <?php echo strtoupper(substr($student['full_name'], 0, 1)); ?>
                            </div>
                        <?php endif; ?>
                        <button class="btn-change-photo" onclick="document.getElementById('profilePicture').click()">
                            <i class="fas fa-camera"></i>
                        </button>
                        <input type="file" id="profilePicture" accept="image/*" style="display: none;" onchange="uploadProfilePicture(this)">
                    </div>
                    <div class="profile-details">
                        <h1><?php echo htmlspecialchars($student['full_name']); ?></h1>
                        <p class="profile-degree"><?php echo htmlspecialchars($student['degree'] ?? 'Student'); ?> at <?php echo htmlspecialchars($student['university']); ?></p>
                        <p class="profile-email"><?php echo htmlspecialchars($student['email']); ?></p>
                    </div>
                </div>
            </div>

            <!-- Profile Navigation -->
            <div class="profile-nav">
                <button class="profile-nav-item active" data-tab="basic">Basic Info</button>
                <button class="profile-nav-item" data-tab="education">Education</button>
                <button class="profile-nav-item" data-tab="experience">Experience</button>
                <button class="profile-nav-item" data-tab="skills">Skills</button>
                <button class="profile-nav-item" data-tab="achievements">Achievements</button>
                <button class="profile-nav-item" data-tab="publications">Publications</button>
            </div>

            <!-- Profile Content -->
            <div class="profile-content">
                <!-- Basic Info Tab -->
                <div class="profile-tab active" id="basic-tab">
                    <div class="card">
                        <div class="card-header">
                            <h2>Basic Information</h2>
                            <button class="btn btn-sm btn-primary" onclick="editBasicInfo()">Edit</button>
                        </div>

                        <form id="basicInfoForm" style="display: none;">
                            <div class="form-grid">
                                <div class="form-group">
                                    <label for="full_name">Full Name</label>
                                    <input type="text" name="full_name" id="full_name" class="form-control" value="<?php echo htmlspecialchars($student['full_name']); ?>" required>
                                </div>
                                <div class="form-group">
                                    <label for="phone">Phone Number</label>
                                    <input type="tel" name="phone" id="phone" class="form-control" value="<?php echo htmlspecialchars($student['phone'] ?? ''); ?>">
                                </div>
                                <div class="form-group form-full">
                                    <label for="bio">Bio</label>
                                    <textarea name="bio" id="bio" class="form-control" rows="3"><?php echo htmlspecialchars($student['bio'] ?? ''); ?></textarea>
                                </div>
                                <div class="form-group form-full">
                                    <label for="research_interest">Research Interests</label>
                                    <textarea name="research_interest" id="research_interest" class="form-control" rows="3"><?php echo htmlspecialchars($student['research_interest'] ?? ''); ?></textarea>
                                </div>
                                <div class="form-group">
                                    <label for="linkedin">LinkedIn Profile</label>
                                    <input type="url" name="linkedin" id="linkedin" class="form-control" value="<?php echo htmlspecialchars($student['linkedin'] ?? ''); ?>" placeholder="https://linkedin.com/in/username">
                                </div>
                                <div class="form-group">
                                    <label for="github">GitHub Profile</label>
                                    <input type="url" name="github" id="github" class="form-control" value="<?php echo htmlspecialchars($student['github'] ?? ''); ?>" placeholder="https://github.com/username">
                                </div>
                                <div class="form-group form-full">
                                    <label for="address">Address</label>
                                    <textarea name="address" id="address" class="form-control" rows="2"><?php echo htmlspecialchars($student['address'] ?? ''); ?></textarea>
                                </div>
                            </div>
                            <div class="form-actions">
                                <button type="submit" class="btn btn-primary">Save Changes</button>
                                <button type="button" class="btn btn-outline" onclick="cancelBasicEdit()">Cancel</button>
                            </div>
                        </form>

                        <div id="basicInfoView">
                            <div class="info-grid">
                                <div class="info-item">
                                    <label>Phone</label>
                                    <p><?php echo htmlspecialchars($student['phone'] ?? 'Not provided'); ?></p>
                                </div>
                                <div class="info-item">
                                    <label>Email</label>
                                    <p><?php echo htmlspecialchars($student['email']); ?></p>
                                </div>
                                <div class="info-item full-width">
                                    <label>Bio</label>
                                    <p><?php echo nl2br(htmlspecialchars($student['bio'] ?? 'No bio provided')); ?></p>
                                </div>
                                <div class="info-item full-width">
                                    <label>Research Interests</label>
                                    <p><?php echo nl2br(htmlspecialchars($student['research_interest'] ?? 'No research interests specified')); ?></p>
                                </div>
                                <div class="info-item">
                                    <label>LinkedIn</label>
                                    <?php if ($student['linkedin']): ?>
                                        <a href="<?php echo htmlspecialchars($student['linkedin']); ?>" target="_blank" class="social-link">
                                            View Profile →
                                        </a>
                                    <?php else: ?>
                                        <p>Not provided</p>
                                    <?php endif; ?>
                                </div>
                                <div class="info-item">
                                    <label>GitHub</label>
                                    <?php if ($student['github']): ?>
                                        <a href="<?php echo htmlspecialchars($student['github']); ?>" target="_blank" class="social-link">
                                            View Profile →
                                        </a>
                                    <?php else: ?>
                                        <p>Not provided</p>
                                    <?php endif; ?>
                                </div>
                                <div class="info-item full-width">
                                    <label>Address</label>
                                    <p><?php echo nl2br(htmlspecialchars($student['address'] ?? 'Not provided')); ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Education Tab -->
                <div class="profile-tab" id="education-tab">
                    <div class="card">
                        <div class="card-header">
                            <h2>Education Details</h2>
                            <button class="btn btn-sm btn-primary" onclick="editEducation()">Edit</button>
                        </div>

                        <form id="educationForm" style="display: none;">
                            <div class="form-grid">
                                <div class="form-group">
                                    <label for="degree">Degree Program</label>
                                    <select name="degree" id="degree" class="form-control" required>
                                        <option value="">Select degree</option>
                                        <option value="BSc in CSE" <?php echo $student['degree'] == 'BSc in CSE' ? 'selected' : ''; ?>>BSc in Computer Science & Engineering</option>
                                        <option value="BSc in EEE" <?php echo $student['degree'] == 'BSc in EEE' ? 'selected' : ''; ?>>BSc in Electrical & Electronic Engineering</option>
                                        <option value="BBA" <?php echo $student['degree'] == 'BBA' ? 'selected' : ''; ?>>Bachelor of Business Administration</option>
                                        <option value="BA in English" <?php echo $student['degree'] == 'BA in English' ? 'selected' : ''; ?>>BA in English</option>
                                        <option value="BSc in Mathematics" <?php echo $student['degree'] == 'BSc in Mathematics' ? 'selected' : ''; ?>>BSc in Mathematics</option>
                                        <option value="BSc in Physics" <?php echo $student['degree'] == 'BSc in Physics' ? 'selected' : ''; ?>>BSc in Physics</option>
                                        <option value="BSc in Biochemistry" <?php echo $student['degree'] == 'BSc in Biochemistry' ? 'selected' : ''; ?>>BSc in Biochemistry</option>
                                        <option value="BArch" <?php echo $student['degree'] == 'BArch' ? 'selected' : ''; ?>>Bachelor of Architecture</option>
                                        <option value="LLB" <?php echo $student['degree'] == 'LLB' ? 'selected' : ''; ?>>Bachelor of Laws</option>
                                        <option value="Other" <?php echo $student['degree'] == 'Other' ? 'selected' : ''; ?>>Other</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="university">University</label>
                                    <input type="text" name="university" id="university" class="form-control" value="<?php echo htmlspecialchars($student['university']); ?>" required>
                                </div>
                                <div class="form-group">
                                    <label for="start_date">Start Date</label>
                                    <input type="date" name="start_date" id="start_date" class="form-control" value="<?php echo $student['start_date']; ?>">
                                </div>
                                <div class="form-group">
                                    <label for="end_date">Expected End Date</label>
                                    <input type="date" name="end_date" id="end_date" class="form-control" value="<?php echo $student['end_date']; ?>">
                                </div>
                                <div class="form-group">
                                    <label for="cgpa">CGPA</label>
                                    <input type="number" name="cgpa" id="cgpa" class="form-control" step="0.01" min="0" max="4" value="<?php echo $student['cgpa']; ?>">
                                </div>
                            </div>
                            <div class="form-actions">
                                <button type="submit" class="btn btn-primary">Save Changes</button>
                                <button type="button" class="btn btn-outline" onclick="cancelEducationEdit()">Cancel</button>
                            </div>
                        </form>

                        <div id="educationView">
                            <div class="education-card">
                                <h3><?php echo htmlspecialchars($student['degree'] ?? 'Degree not specified'); ?></h3>
                                <p class="university"><?php echo htmlspecialchars($student['university']); ?></p>
                                <div class="education-details">
                                    <span><?php echo $student['start_date'] ? date('M Y', strtotime($student['start_date'])) : 'Start date not set'; ?></span>
                                    <span>-</span>
                                    <span><?php echo $student['end_date'] ? date('M Y', strtotime($student['end_date'])) : 'Present'; ?></span>
                                </div>
                                <?php if ($student['cgpa']): ?>
                                    <p class="cgpa">CGPA: <?php echo number_format($student['cgpa'], 2); ?>/4.00</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Experience Tab -->
                <div class="profile-tab" id="experience-tab">
                    <div class="card">
                        <div class="card-header">
                            <h2>Experience</h2>
                            <button class="btn btn-sm btn-primary" onclick="addExperience()">Add Experience</button>
                        </div>
                        <div id="experienceList">
                            <?php if ($experiences->num_rows > 0): ?>
                                <?php while ($exp = $experiences->fetch_assoc()): ?>
                                    <div class="experience-item" data-id="<?php echo $exp['exp_id']; ?>">
                                        <h3><?php echo htmlspecialchars($exp['position']); ?></h3>
                                        <p class="company"><?php echo htmlspecialchars($exp['company']); ?></p>
                                        <p class="duration">
                                            <?php echo date('M Y', strtotime($exp['start_date'])); ?> -
                                            <?php echo $exp['end_date'] ? date('M Y', strtotime($exp['end_date'])) : 'Present'; ?>
                                        </p>
                                        <p class="description"><?php echo nl2br(htmlspecialchars($exp['description'])); ?></p>
                                        <div class="item-actions">
                                            <button class="btn btn-sm btn-danger" onclick="deleteExperience(<?php echo $exp['exp_id']; ?>)">Delete</button>
                                        </div>
                                    </div>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <p class="empty-message">No experience added yet.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Skills Tab -->
                <div class="profile-tab" id="skills-tab">
                    <div class="card">
                        <div class="card-header">
                            <h2>Skills</h2>
                            <button class="btn btn-sm btn-primary" onclick="manageSkills()">Add Skills</button>
                        </div>
                        <div id="skillsList">
                            <?php if ($skills->num_rows > 0): ?>
                                <div class="skills-container">
                                    <?php while ($skill = $skills->fetch_assoc()): ?>
                                        <span class="skill-tag" data-id="<?php echo $skill['skill_id']; ?>">
                                            <?php echo htmlspecialchars($skill['skill_name']); ?>
                                            <button class="skill-remove" onclick="removeSkill(<?php echo $skill['skill_id']; ?>)">&times;</button>
                                        </span>
                                    <?php endwhile; ?>
                                </div>
                            <?php else: ?>
                                <p class="empty-message">No skills added yet.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Achievements Tab -->
                <div class="profile-tab" id="achievements-tab">
                    <div class="card">
                        <div class="card-header">
                            <h2>Achievements & Certifications</h2>
                            <button class="btn btn-sm btn-primary" onclick="addAchievement()">Add Achievement</button>
                        </div>
                        <div id="achievementsList">
                            <?php if ($achievements->num_rows > 0): ?>
                                <?php while ($achievement = $achievements->fetch_assoc()): ?>
                                    <div class="achievement-item" data-id="<?php echo $achievement['achievement_id']; ?>">
                                        <div class="achievement-icon">
                                            <?php if ($achievement['type'] == 'certification'): ?>
                                                🎓
                                            <?php elseif ($achievement['type'] == 'award'): ?>
                                                🏆
                                            <?php else: ?>
                                                ⭐
                                            <?php endif; ?>
                                        </div>
                                        <div class="achievement-content">
                                            <h3><?php echo htmlspecialchars($achievement['title']); ?></h3>
                                            <p><?php echo nl2br(htmlspecialchars($achievement['description'])); ?></p>
                                            <p class="achievement-date"><?php echo date('M d, Y', strtotime($achievement['date'])); ?></p>
                                        </div>
                                        <div class="item-actions">
                                            <button class="btn btn-sm btn-danger" onclick="deleteAchievement(<?php echo $achievement['achievement_id']; ?>)">Delete</button>
                                        </div>
                                    </div>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <p class="empty-message">No achievements added yet.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Publications Tab -->
                <div class="profile-tab" id="publications-tab">
                    <div class="card">
                        <div class="card-header">
                            <h2>Publications</h2>
                            <button class="btn btn-sm btn-primary" onclick="addPublication()">Add Publication</button>
                        </div>
                        <div id="publicationsList">
                            <?php if ($publications->num_rows > 0): ?>
                                <?php while ($pub = $publications->fetch_assoc()): ?>
                                    <div class="publication-item" data-id="<?php echo $pub['publication_id']; ?>">
                                        <h3><?php echo htmlspecialchars($pub['title']); ?></h3>
                                        <p class="journal"><?php echo htmlspecialchars($pub['journal']); ?>, <?php echo $pub['year']; ?></p>
                                        <?php if ($pub['url']): ?>
                                            <a href="<?php echo htmlspecialchars($pub['url']); ?>" target="_blank" class="publication-link">View Publication →</a>
                                        <?php endif; ?>
                                        <div class="item-actions">
                                            <button class="btn btn-sm btn-danger" onclick="deletePublication(<?php echo $pub['publication_id']; ?>)">Delete</button>
                                        </div>
                                    </div>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <p class="empty-message">No publications added yet.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Modals -->
    <!-- Experience Modal -->
    <div id="experienceModal" class="modal">
        <div class="modal-content">
            <span class="modal-close" onclick="closeModal('experienceModal')">&times;</span>
            <h2 id="experienceModalTitle">Add Experience</h2>
            <form id="experienceForm">
                <div class="form-group">
                    <label for="exp_position">Position</label>
                    <input type="text" id="exp_position" name="position" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="exp_company">Company</label>
                    <input type="text" id="exp_company" name="company" class="form-control" required>
                </div>
                <div class="form-grid">
                    <div class="form-group">
                        <label for="exp_start_date">Start Date</label>
                        <input type="date" id="exp_start_date" name="start_date" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="exp_end_date">End Date</label>
                        <input type="date" id="exp_end_date" name="end_date" class="form-control">
                        <label class="checkbox-label">
                            <input type="checkbox" id="exp_current" onchange="toggleEndDate(this)">
                            <span>Currently working here</span>
                        </label>
                    </div>
                </div>
                <div class="form-group">
                    <label for="exp_description">Description</label>
                    <textarea id="exp_description" name="description" class="form-control" rows="4"></textarea>
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Save Experience</button>
                    <button type="button" class="btn btn-outline" onclick="closeModal('experienceModal')">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Skills Modal -->
    <div id="skillsModal" class="modal">
        <div class="modal-content">
            <span class="modal-close" onclick="closeModal('skillsModal')">&times;</span>
            <h2>Add Skills</h2>
            <div class="form-group">
                <label for="new_skill">Add New Skill</label>
                <div class="input-group">
                    <input type="text" id="new_skill" class="form-control" placeholder="Enter skill name">
                    <button class="btn btn-primary" onclick="addSkill()">Add</button>
                </div>
            </div>
            <div class="skills-suggestions">
                <p>Popular skills:</p>
                <div class="suggestion-tags">
                    <span onclick="quickAddSkill('JavaScript')">JavaScript</span>
                    <span onclick="quickAddSkill('Python')">Python</span>
                    <span onclick="quickAddSkill('Java')">Java</span>
                    <span onclick="quickAddSkill('React')">React</span>
                    <span onclick="quickAddSkill('Machine Learning')">Machine Learning</span>
                    <span onclick="quickAddSkill('Data Analysis')">Data Analysis</span>
                    <span onclick="quickAddSkill('SQL')">SQL</span>
                    <span onclick="quickAddSkill('Git')">Git</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Achievement Modal -->
    <div id="achievementModal" class="modal">
        <div class="modal-content">
            <span class="modal-close" onclick="closeModal('achievementModal')">&times;</span>
            <h2 id="achievementModalTitle">Add Achievement</h2>
            <form id="achievementForm">
                <div class="form-group">
                    <label for="achievement_title">Title</label>
                    <input type="text" id="achievement_title" name="title" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="achievement_type">Type</label>
                    <select id="achievement_type" name="type" class="form-control" required>
                        <option value="certification">Certification</option>
                        <option value="award">Award</option>
                        <option value="achievement">Achievement</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="achievement_description">Description</label>
                    <textarea id="achievement_description" name="description" class="form-control" rows="3"></textarea>
                </div>
                <div class="form-group">
                    <label for="achievement_date">Date</label>
                    <input type="date" id="achievement_date" name="date" class="form-control" required>
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Save Achievement</button>
                    <button type="button" class="btn btn-outline" onclick="closeModal('achievementModal')">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Publication Modal -->
    <div id="publicationModal" class="modal">
        <div class="modal-content">
            <span class="modal-close" onclick="closeModal('publicationModal')">&times;</span>
            <h2 id="publicationModalTitle">Add Publication</h2>
            <form id="publicationForm">
                <div class="form-group">
                    <label for="publication_title">Title</label>
                    <input type="text" id="publication_title" name="title" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="publication_journal">Journal/Conference</label>
                    <input type="text" id="publication_journal" name="journal" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="publication_year">Year</label>
                    <input type="number" id="publication_year" name="year" class="form-control" min="2000" max="<?php echo date('Y'); ?>" required>
                </div>
                <div class="form-group">
                    <label for="publication_url">URL (optional)</label>
                    <input type="url" id="publication_url" name="url" class="form-control" placeholder="https://example.com/publication">
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Save Publication</button>
                    <button type="button" class="btn btn-outline" onclick="closeModal('publicationModal')">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Mobile Menu Toggle -->
    <button class="mobile-menu-toggle" id="mobileMenuToggle">
        <i class="fas fa-bars"></i>
    </button>

    <script src="../assets/js/common.js"></script>
    <script src="../assets/js/profile.js"></script>
    <script>
        // Global function for profile picture upload
        function uploadProfilePicture(input) {
            if (input.files && input.files[0]) {
                const file = input.files[0];

                // Validate file type
                const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
                if (!allowedTypes.includes(file.type)) {
                    alert('Please select a valid image file (JPEG, PNG, or GIF).');
                    return;
                }

                // Validate file size (max 5MB)
                if (file.size > 5 * 1024 * 1024) {
                    alert('File size must be less than 5MB.');
                    return;
                }

                const formData = new FormData();
                formData.append('action', 'upload_profile_picture');
                formData.append('profile_picture', file);

                // Show loading
                const loadingOverlay = document.createElement('div');
                loadingOverlay.id = 'loadingOverlay';
                loadingOverlay.innerHTML = '<div style="background: white; padding: 2rem; border-radius: 1rem; text-align: center;"><p>Uploading profile picture...</p></div>';
                loadingOverlay.style.cssText = 'position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); display: flex; align-items: center; justify-content: center; z-index: 10000;';
                document.body.appendChild(loadingOverlay);

                fetch('profile.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        document.body.removeChild(loadingOverlay);

                        if (data.success) {
                            // Update profile picture display
                            const pictureElement = document.querySelector('.profile-picture');
                            const placeholderElement = document.querySelector('.profile-picture-placeholder');

                            if (pictureElement) {
                                pictureElement.src = '../assets/uploads/profiles/' + data.filename + '?t=' + Date.now();
                            } else if (placeholderElement) {
                                const img = document.createElement('img');
                                img.src = '../assets/uploads/profiles/' + data.filename + '?t=' + Date.now();
                                img.alt = 'Profile';
                                img.className = 'profile-picture';
                                placeholderElement.parentNode.replaceChild(img, placeholderElement);
                            }

                            alert('Profile picture updated successfully!');
                        } else {
                            alert('Error: ' + data.message);
                        }
                    })
                    .catch(error => {
                        document.body.removeChild(loadingOverlay);
                        alert('An error occurred while uploading profile picture.');
                        console.error('Error:', error);
                    });
            }
        }
    </script>
</body>

</html>

<?php $conn->close(); ?>
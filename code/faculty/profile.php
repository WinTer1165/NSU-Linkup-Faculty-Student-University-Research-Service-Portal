<?php
require_once '../includes/config.php';
require_once '../includes/db_connect.php';
require_once '../includes/functions.php';
require_once '../includes/auth_check.php';

// Check if user is logged in and is faculty
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'faculty') {
    header("Location: ../index.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Get faculty details
$stmt = $conn->prepare("SELECT f.*, u.email FROM faculty f JOIN users u ON f.user_id = u.user_id WHERE f.user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$faculty = $stmt->get_result()->fetch_assoc();
$faculty_id = $faculty['faculty_id'];
$stmt->close();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] == 'update_profile') {
        // Get form data
        $full_name = sanitize_input($_POST['full_name']);
        $title = sanitize_input($_POST['title']);
        $prefix = sanitize_input($_POST['prefix']);
        $office = sanitize_input($_POST['office']);
        $phone = sanitize_input($_POST['phone']);
        $office_hours = sanitize_input($_POST['office_hours']);
        $education = sanitize_input($_POST['education']);
        $research_interests = sanitize_input($_POST['research_interests']);
        $courses_taught = sanitize_input($_POST['courses_taught']);
        $biography = sanitize_input($_POST['biography']);
        $about = sanitize_input($_POST['about']);
        $linkedin = sanitize_input($_POST['linkedin']);
        $google_scholar = sanitize_input($_POST['google_scholar']);
        $github = sanitize_input($_POST['github']);
        $website = sanitize_input($_POST['website']);

        // Handle profile image upload
        $profile_image = null;
        if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] == 0) {
            $upload_result = upload_image($_FILES['profile_image'], 'profiles');
            if ($upload_result['success']) {
                $profile_image = $upload_result['filename'];
            }
        }

        // Update faculty profile
        if ($profile_image) {
            $stmt = $conn->prepare("UPDATE faculty SET full_name = ?, title = ?, prefix = ?, office = ?, phone = ?, office_hours = ?, education = ?, research_interests = ?, courses_taught = ?, biography = ?, about = ?, linkedin = ?, google_scholar = ?, github = ?, website = ?, profile_image = ? WHERE faculty_id = ?");
            $stmt->bind_param("ssssssssssssssssi", $full_name, $title, $prefix, $office, $phone, $office_hours, $education, $research_interests, $courses_taught, $biography, $about, $linkedin, $google_scholar, $github, $website, $profile_image, $faculty_id);
        } else {
            $stmt = $conn->prepare("UPDATE faculty SET full_name = ?, title = ?, prefix = ?, office = ?, phone = ?, office_hours = ?, education = ?, research_interests = ?, courses_taught = ?, biography = ?, about = ?, linkedin = ?, google_scholar = ?, github = ?, website = ? WHERE faculty_id = ?");
            $stmt->bind_param("sssssssssssssssi", $full_name, $title, $prefix, $office, $phone, $office_hours, $education, $research_interests, $courses_taught, $biography, $about, $linkedin, $google_scholar, $github, $website, $faculty_id);
        }

        if ($stmt->execute()) {
            $_SESSION['success'] = "Profile updated successfully!";
            header("Location: profile.php");
            exit();
        } else {
            $_SESSION['error'] = "Failed to update profile. Please try again.";
        }
        $stmt->close();
    }
}

// Calculate profile completion
$profile_fields = ['title', 'office', 'phone', 'office_hours', 'education', 'research_interests', 'courses_taught', 'biography', 'about', 'linkedin', 'google_scholar', 'github', 'website', 'profile_image'];
$completed_fields = 0;
foreach ($profile_fields as $field) {
    if (!empty($faculty[$field])) {
        $completed_fields++;
    }
}
$profile_completion = round(($completed_fields / count($profile_fields)) * 100);

// Get research statistics
$stmt = $conn->prepare("SELECT COUNT(*) as total_posts, SUM(CASE WHEN is_active = 1 THEN 1 ELSE 0 END) as active_posts FROM research_posts WHERE faculty_id = ?");
$stmt->bind_param("i", $faculty_id);
$stmt->execute();
$research_stats = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Get total applications received
$stmt = $conn->prepare("
    SELECT COUNT(*) as total_applications 
    FROM research_applications ra
    JOIN research_posts r ON ra.research_id = r.research_id
    WHERE r.faculty_id = ?
");
$stmt->bind_param("i", $faculty_id);
$stmt->execute();
$total_applications = $stmt->get_result()->fetch_assoc()['total_applications'];
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Faculty Profile - NSU LinkUp</title>
    <link rel="stylesheet" href="../assets/css/common.css">
    <link rel="stylesheet" href="../assets/css/faculty-profile.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>

<body>
    <div class="dashboard-container">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <h2>NSU LinkUp</h2>
                <p>Faculty Portal</p>
            </div>

            <nav class="sidebar-nav">
                <a href="dashboard.php">
                    <i class="fas fa-home"></i>
                    Dashboard
                </a>
                <a href="../faculty/profile.php" class="active">
                    <i class="fas fa-user"></i>
                    My Profile
                </a>
                <a href="../faculty/create-research.php">
                    <i class="fas fa-plus-circle"></i>
                    Create Research
                </a>
                <a href="../faculty/manage-posts.php">
                    <i class="fas fa-microscope"></i>
                    Manage Posts
                </a>
                <a href="../faculty/applications.php">
                    <i class="fas fa-file-alt"></i>
                    Applications
                </a>
                <a href="research.php">
                    <i class="fas fa-microscope"></i>
                    Research Posts
                </a>
                <a href="../faculty/students.php">
                    <i class="fas fa-graduation-cap"></i>
                    Students
                </a>
                <a href="../faculty/faculty.php">
                    <i class="fas fa-chalkboard-teacher"></i>
                    Faculty
                </a>
                <!-- <a href="../faculty/events.php">
                    <i class="fas fa-calendar-alt"></i>
                    Events
                </a> -->
                <a href="chatbot.php">
                    <i class="fas fa-robot"></i>
                    AI Assistant
                </a>
                <a href="announcements.php">
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
            <!-- Header -->
            <header class="content-header">
                <div class="header-left">
                    <h1>My Profile</h1>
                    <p>Manage your faculty profile information</p>
                </div>
                <div class="header-right">
                    <button class="btn btn-primary" onclick="toggleEditMode()">
                        <i class="fas fa-edit"></i>
                        Edit Profile
                    </button>
                </div>
            </header>

            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success">
                    <?php echo $_SESSION['success'];
                    unset($_SESSION['success']); ?>
                </div>
            <?php endif; ?>

            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger">
                    <?php echo $_SESSION['error'];
                    unset($_SESSION['error']); ?>
                </div>
            <?php endif; ?>

            <!-- Profile Stats -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon profile">
                        <i class="fas fa-user-check"></i>
                    </div>
                    <div class="stat-content">
                        <h3>Profile Completion</h3>
                        <p class="stat-value"><?php echo $profile_completion; ?>%</p>
                        <div class="progress-bar">
                            <div class="progress-fill" style="width: <?php echo $profile_completion; ?>%"></div>
                        </div>
                        <?php if ($profile_completion < 100): ?>
                            <span class="stat-link">Complete your profile</span>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon posts">
                        <i class="fas fa-microscope"></i>
                    </div>
                    <div class="stat-content">
                        <h3>Total Research Posts</h3>
                        <p class="stat-value"><?php echo $research_stats['total_posts'] ?? 0; ?></p>
                        <a href="manage-posts.php" class="stat-link">Manage Posts →</a>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon active">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="stat-content">
                        <h3>Active Posts</h3>
                        <p class="stat-value"><?php echo $research_stats['active_posts'] ?? 0; ?></p>
                        <a href="create-research.php" class="stat-link">Create New →</a>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon applications">
                        <i class="fas fa-file-alt"></i>
                    </div>
                    <div class="stat-content">
                        <h3>Applications Received</h3>
                        <p class="stat-value"><?php echo $total_applications; ?></p>
                        <a href="applications.php" class="stat-link">View All →</a>
                    </div>
                </div>
            </div>

            <!-- Profile Content Grid -->
            <div class="profile-grid">
                <!-- Profile Information -->
                <div class="profile-main">
                    <!-- View Mode -->
                    <div id="viewMode" class="profile-view">
                        <!-- Profile Header Card -->
                        <div class="profile-card">
                            <div class="profile-header-section">
                                <div class="profile-avatar-section">
                                    <img src="<?php echo $faculty['profile_image'] ? '../assets/uploads/profiles/' . $faculty['profile_image'] : '../assets/images/default-avatar.png'; ?>"
                                        alt="Profile" class="profile-avatar-large">
                                </div>
                                <div class="profile-info-section">
                                    <h2><?php echo htmlspecialchars($faculty['prefix'] . ' ' . $faculty['full_name']); ?></h2>
                                    <p class="profile-title"><?php echo htmlspecialchars($faculty['title'] ?? 'Faculty Member'); ?></p>
                                    <p class="profile-email">
                                        <i class="fas fa-envelope"></i>
                                        <?php echo htmlspecialchars($faculty['email']); ?>
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Basic Information -->
                        <div class="profile-card">
                            <h3><i class="fas fa-user-circle"></i> Basic Information</h3>
                            <div class="info-grid">
                                <div class="info-item">
                                    <label>Full Name</label>
                                    <span><?php echo htmlspecialchars($faculty['full_name']); ?></span>
                                </div>
                                <div class="info-item">
                                    <label>Title</label>
                                    <span><?php echo htmlspecialchars($faculty['title'] ?? 'Not specified'); ?></span>
                                </div>
                                <div class="info-item">
                                    <label>Office</label>
                                    <span><?php echo htmlspecialchars($faculty['office'] ?? 'Not specified'); ?></span>
                                </div>
                                <div class="info-item">
                                    <label>Phone</label>
                                    <span><?php echo htmlspecialchars($faculty['phone'] ?? 'Not specified'); ?></span>
                                </div>
                                <div class="info-item">
                                    <label>Office Hours</label>
                                    <span><?php echo htmlspecialchars($faculty['office_hours'] ?? 'Not specified'); ?></span>
                                </div>
                            </div>
                        </div>

                        <!-- Academic Information -->
                        <div class="profile-card">
                            <h3><i class="fas fa-graduation-cap"></i> Academic Information</h3>
                            <div class="content-section">
                                <h4>Education</h4>
                                <p><?php echo nl2br(htmlspecialchars($faculty['education'] ?? 'No education information provided.')); ?></p>
                            </div>
                            <div class="content-section">
                                <h4>Research Interests</h4>
                                <p><?php echo nl2br(htmlspecialchars($faculty['research_interests'] ?? 'No research interests specified.')); ?></p>
                            </div>
                            <div class="content-section">
                                <h4>Courses Taught</h4>
                                <p><?php echo nl2br(htmlspecialchars($faculty['courses_taught'] ?? 'No courses information available.')); ?></p>
                            </div>
                        </div>

                        <!-- Biography & About -->
                        <div class="profile-card">
                            <h3><i class="fas fa-info-circle"></i> Biography & About</h3>
                            <div class="content-section">
                                <h4>Biography</h4>
                                <p><?php echo nl2br(htmlspecialchars($faculty['biography'] ?? 'No biography provided.')); ?></p>
                            </div>
                            <div class="content-section">
                                <h4>About</h4>
                                <p><?php echo nl2br(htmlspecialchars($faculty['about'] ?? 'No additional information provided.')); ?></p>
                            </div>
                        </div>
                    </div>

                    <!-- Edit Mode -->
                    <div id="editMode" class="profile-edit" style="display: none;">
                        <form method="POST" action="" enctype="multipart/form-data">
                            <input type="hidden" name="action" value="update_profile">

                            <!-- Basic Information -->
                            <div class="profile-card">
                                <h3><i class="fas fa-user-circle"></i> Basic Information</h3>
                                <div class="form-grid">
                                    <div class="form-group">
                                        <label for="prefix">Prefix</label>
                                        <input type="text" id="prefix" name="prefix"
                                            value="<?php echo htmlspecialchars($faculty['prefix'] ?? ''); ?>"
                                            placeholder="e.g., Dr., Prof.">
                                    </div>
                                    <div class="form-group">
                                        <label for="full_name" class="required">Full Name</label>
                                        <input type="text" id="full_name" name="full_name"
                                            value="<?php echo htmlspecialchars($faculty['full_name']); ?>" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="title">Title</label>
                                        <input type="text" id="title" name="title"
                                            value="<?php echo htmlspecialchars($faculty['title'] ?? ''); ?>"
                                            placeholder="e.g., Assistant Professor">
                                    </div>
                                    <div class="form-group">
                                        <label for="office">Office</label>
                                        <input type="text" id="office" name="office"
                                            value="<?php echo htmlspecialchars($faculty['office'] ?? ''); ?>"
                                            placeholder="e.g., Room 301, Building A">
                                    </div>
                                    <div class="form-group">
                                        <label for="phone">Phone</label>
                                        <input type="tel" id="phone" name="phone"
                                            value="<?php echo htmlspecialchars($faculty['phone'] ?? ''); ?>"
                                            placeholder="+880 1xxx-xxxxxx">
                                    </div>
                                    <div class="form-group">
                                        <label for="office_hours">Office Hours</label>
                                        <input type="text" id="office_hours" name="office_hours"
                                            value="<?php echo htmlspecialchars($faculty['office_hours'] ?? ''); ?>"
                                            placeholder="e.g., Mon-Wed: 2:00 PM - 4:00 PM">
                                    </div>
                                </div>
                            </div>

                            <!-- Academic Information -->
                            <div class="profile-card">
                                <h3><i class="fas fa-graduation-cap"></i> Academic Information</h3>
                                <div class="form-group">
                                    <label for="education">Education</label>
                                    <textarea id="education" name="education" rows="4"
                                        placeholder="List your educational qualifications..."><?php echo htmlspecialchars($faculty['education'] ?? ''); ?></textarea>
                                </div>
                                <div class="form-group">
                                    <label for="research_interests">Research Interests</label>
                                    <textarea id="research_interests" name="research_interests" rows="4"
                                        placeholder="Describe your research interests..."><?php echo htmlspecialchars($faculty['research_interests'] ?? ''); ?></textarea>
                                </div>
                                <div class="form-group">
                                    <label for="courses_taught">Courses Taught</label>
                                    <textarea id="courses_taught" name="courses_taught" rows="4"
                                        placeholder="List the courses you teach..."><?php echo htmlspecialchars($faculty['courses_taught'] ?? ''); ?></textarea>
                                </div>
                            </div>

                            <!-- Biography & About -->
                            <div class="profile-card">
                                <h3><i class="fas fa-info-circle"></i> Biography & About</h3>
                                <div class="form-group">
                                    <label for="biography">Biography</label>
                                    <textarea id="biography" name="biography" rows="5"
                                        placeholder="Write your professional biography..."><?php echo htmlspecialchars($faculty['biography'] ?? ''); ?></textarea>
                                </div>
                                <div class="form-group">
                                    <label for="about">About</label>
                                    <textarea id="about" name="about" rows="5"
                                        placeholder="Additional information about yourself..."><?php echo htmlspecialchars($faculty['about'] ?? ''); ?></textarea>
                                </div>
                            </div>

                            <!-- Social Links -->
                            <div class="profile-card">
                                <h3><i class="fas fa-link"></i> Social & Professional Links</h3>
                                <div class="form-grid">
                                    <div class="form-group">
                                        <label for="linkedin">LinkedIn</label>
                                        <input type="url" id="linkedin" name="linkedin"
                                            value="<?php echo htmlspecialchars($faculty['linkedin'] ?? ''); ?>"
                                            placeholder="https://linkedin.com/in/username">
                                    </div>
                                    <div class="form-group">
                                        <label for="google_scholar">Google Scholar</label>
                                        <input type="url" id="google_scholar" name="google_scholar"
                                            value="<?php echo htmlspecialchars($faculty['google_scholar'] ?? ''); ?>"
                                            placeholder="https://scholar.google.com/...">
                                    </div>
                                    <div class="form-group">
                                        <label for="github">GitHub</label>
                                        <input type="url" id="github" name="github"
                                            value="<?php echo htmlspecialchars($faculty['github'] ?? ''); ?>"
                                            placeholder="https://github.com/username">
                                    </div>
                                    <div class="form-group">
                                        <label for="website">Personal Website</label>
                                        <input type="url" id="website" name="website"
                                            value="<?php echo htmlspecialchars($faculty['website'] ?? ''); ?>"
                                            placeholder="https://yourwebsite.com">
                                    </div>
                                </div>
                            </div>

                            <!-- Profile Picture -->
                            <div class="profile-card">
                                <h3><i class="fas fa-camera"></i> Profile Picture</h3>
                                <div class="form-group">
                                    <label for="profile_image">Upload New Profile Picture</label>
                                    <input type="file" id="profile_image" name="profile_image" accept="image/*">
                                    <small class="form-help">Maximum file size: 5MB. Supported formats: JPG, PNG, GIF</small>
                                </div>
                            </div>

                            <!-- Form Actions -->
                            <div class="form-actions">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="fas fa-save"></i>
                                    Save Changes
                                </button>
                                <button type="button" class="btn btn-outline btn-lg" onclick="toggleEditMode()">
                                    Cancel
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Sidebar -->
                <div class="profile-sidebar">
                    <!-- Social Links -->
                    <div class="sidebar-section">
                        <h3>Social Links</h3>
                        <div class="social-links">
                            <?php if ($faculty['linkedin']): ?>
                                <a href="<?php echo htmlspecialchars($faculty['linkedin']); ?>" target="_blank" class="social-link linkedin">
                                    <i class="fab fa-linkedin"></i>
                                    LinkedIn
                                </a>
                            <?php endif; ?>
                            <?php if ($faculty['google_scholar']): ?>
                                <a href="<?php echo htmlspecialchars($faculty['google_scholar']); ?>" target="_blank" class="social-link scholar">
                                    <i class="fas fa-graduation-cap"></i>
                                    Google Scholar
                                </a>
                            <?php endif; ?>
                            <?php if ($faculty['github']): ?>
                                <a href="<?php echo htmlspecialchars($faculty['github']); ?>" target="_blank" class="social-link github">
                                    <i class="fab fa-github"></i>
                                    GitHub
                                </a>
                            <?php endif; ?>
                            <?php if ($faculty['website']): ?>
                                <a href="<?php echo htmlspecialchars($faculty['website']); ?>" target="_blank" class="social-link website">
                                    <i class="fas fa-globe"></i>
                                    Website
                                </a>
                            <?php endif; ?>
                            <?php if (!$faculty['linkedin'] && !$faculty['google_scholar'] && !$faculty['github'] && !$faculty['website']): ?>
                                <p class="text-muted">No social links added yet.</p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Quick Actions -->
                    <div class="sidebar-section">
                        <h3>Quick Actions</h3>
                        <div class="quick-actions">
                            <a href="create-research.php" class="quick-action-btn">
                                <i class="fas fa-plus"></i>
                                Create Research Post
                            </a>
                            <a href="manage-posts.php" class="quick-action-btn">
                                <i class="fas fa-microscope"></i>
                                Manage Posts
                            </a>
                            <a href="applications.php" class="quick-action-btn">
                                <i class="fas fa-file-alt"></i>
                                View Applications
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Mobile Menu Toggle -->
    <button class="mobile-menu-toggle" id="mobileMenuToggle">
        <i class="fas fa-bars"></i>
    </button>

    <script src="../assets/js/common.js"></script>
    <script src="../assets/js/faculty-profile.js"></script>
    <script>
        function toggleEditMode() {
            const viewMode = document.getElementById('viewMode');
            const editMode = document.getElementById('editMode');

            if (viewMode.style.display === 'none') {
                viewMode.style.display = 'block';
                editMode.style.display = 'none';
            } else {
                viewMode.style.display = 'none';
                editMode.style.display = 'block';
            }
        }
    </script>
</body>

</html>

<?php $conn->close(); ?>
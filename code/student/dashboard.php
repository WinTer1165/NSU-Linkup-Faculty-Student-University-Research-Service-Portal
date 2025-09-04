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

// Get student details
$stmt = $conn->prepare("SELECT s.*, u.email FROM students s JOIN users u ON s.user_id = u.user_id WHERE s.user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$student = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Calculate profile completion
$profile_fields = ['bio', 'research_interest', 'phone', 'linkedin', 'github', 'address', 'cgpa', 'profile_image'];
$completed_fields = 0;
foreach ($profile_fields as $field) {
    if (!empty($student[$field])) {
        $completed_fields++;
    }
}
$profile_completion = round(($completed_fields / count($profile_fields)) * 100);

// Get student's research applications
$stmt = $conn->prepare("
    SELECT ra.*, r.title, r.department, r.apply_deadline, r.salary, r.is_active,
           f.full_name as faculty_name, f.profile_image as faculty_image
    FROM research_applications ra
    JOIN research_posts r ON ra.research_id = r.research_id
    JOIN faculty f ON r.faculty_id = f.faculty_id
    WHERE ra.student_id = (SELECT student_id FROM students WHERE user_id = ?)
    ORDER BY ra.applied_at DESC
    LIMIT 5
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$applications = $stmt->get_result();
$stmt->close();

// Get available research posts (not applied)
$stmt = $conn->prepare("
    SELECT r.*, f.full_name as faculty_name, f.profile_image as faculty_image,
           DATEDIFF(r.apply_deadline, CURDATE()) as days_left
    FROM research_posts r
    JOIN faculty f ON r.faculty_id = f.faculty_id
    WHERE r.is_active = 1 
    AND r.apply_deadline >= CURDATE()
    AND r.research_id NOT IN (
        SELECT research_id FROM research_applications 
        WHERE student_id = (SELECT student_id FROM students WHERE user_id = ?)
    )
    ORDER BY r.created_at DESC
    LIMIT 3
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$available_research = $stmt->get_result();
$stmt->close();

// Get upcoming events
$stmt = $conn->prepare("
    SELECT e.*, o.full_name as organizer_name
    FROM events e
    JOIN organizers o ON e.organizer_id = o.organizer_id
    WHERE e.is_published = 1 AND e.event_date >= CURDATE()
    ORDER BY e.event_date ASC
    LIMIT 3
");
$stmt->execute();
$upcoming_events = $stmt->get_result();
$stmt->close();

// Get recent announcements
$stmt = $conn->prepare("
    SELECT a.*, ad.full_name as admin_name
    FROM announcements a
    JOIN admins ad ON a.admin_id = ad.admin_id
    WHERE a.is_published = 1
    ORDER BY a.created_at DESC
    LIMIT 3
");
$stmt->execute();
$recent_announcements = $stmt->get_result();
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard - NSU LinkUp</title>
    <link rel="stylesheet" href="../assets/css/common.css">
    <link rel="stylesheet" href="../assets/css/student-dashboard.css">
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
                <a href="dashboard.php" class="active">
                    <i class="fas fa-home"></i>
                    Dashboard
                </a>
                <a href="profile.php">
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
            <!-- Header -->
            <header class="content-header">
                <div class="header-left">
                    <h1>Welcome back, <?php echo htmlspecialchars($student['full_name']); ?>!</h1>
                    <p>Here's what's happening at NSU LinkUp</p>
                </div>
                <div class="header-right">
                    <div class="user-info">
                        <img src="<?php echo $student['profile_image'] ? '../assets/uploads/profiles/' . $student['profile_image'] : '../assets/images/default-avatar.png'; ?>"
                            alt="Profile" class="user-avatar">
                        <span><?php echo htmlspecialchars($student['full_name']); ?></span>
                    </div>
                </div>
            </header>

            <!-- Quick Stats -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon profile">
                        <i class="fas fa-user"></i>
                    </div>
                    <div class="stat-content">
                        <h3>Profile Completion</h3>
                        <p class="stat-value"><?php echo $profile_completion; ?>%</p>
                        <div class="progress-bar">
                            <div class="progress-fill" style="width: <?php echo $profile_completion; ?>%"></div>
                        </div>
                        <?php if ($profile_completion < 100): ?>
                            <a href="profile.php" class="stat-link">Complete Profile →</a>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon applications">
                        <i class="fas fa-file-alt"></i>
                    </div>
                    <div class="stat-content">
                        <h3>My Applications</h3>
                        <p class="stat-value"><?php echo $applications->num_rows; ?></p>
                        <a href="#applications" class="stat-link">View Applications →</a>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon research">
                        <i class="fas fa-microscope"></i>
                    </div>
                    <div class="stat-content">
                        <h3>Available Research</h3>
                        <p class="stat-value"><?php echo $available_research->num_rows; ?></p>
                        <a href="../student/research.php" class="stat-link">Browse All →</a>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon events">
                        <i class="fas fa-calendar-alt"></i>
                    </div>
                    <div class="stat-content">
                        <h3>Upcoming Events</h3>
                        <p class="stat-value"><?php echo $upcoming_events->num_rows; ?></p>
                        <a href="../student/events.php" class="stat-link">View All →</a>
                    </div>
                </div>
            </div>

            <!-- Main Dashboard Content -->
            <div class="dashboard-grid">
                <!-- My Applications -->
                <div class="dashboard-section" id="applications">
                    <div class="section-header">
                        <h2>My Research Applications</h2>
                        <a href="../student/research.php" class="btn btn-sm btn-outline">Apply to More</a>
                    </div>

                    <?php if ($applications->num_rows > 0): ?>
                        <div class="applications-list">
                            <?php
                            $applications->data_seek(0); // Reset result pointer
                            while ($app = $applications->fetch_assoc()):
                            ?>
                                <div class="application-item status-<?php echo $app['status']; ?>">
                                    <div class="application-header">
                                        <div class="faculty-info">
                                            <img src="<?php echo $app['faculty_image'] ? '../assets/uploads/profiles/' . $app['faculty_image'] : '../assets/images/default-avatar.png'; ?>"
                                                alt="Faculty" class="faculty-avatar">
                                            <div>
                                                <h3><?php echo htmlspecialchars($app['title']); ?></h3>
                                                <p class="faculty-name"><?php echo htmlspecialchars($app['faculty_name']); ?></p>
                                            </div>
                                        </div>
                                        <span class="status-badge status-<?php echo $app['status']; ?>">
                                            <?php echo ucfirst($app['status']); ?>
                                        </span>
                                    </div>

                                    <div class="application-details">
                                        <div class="detail-item">
                                            <i class="fas fa-building"></i>
                                            <?php echo htmlspecialchars($app['department']); ?>
                                        </div>
                                        <?php if ($app['salary']): ?>
                                            <div class="detail-item">
                                                <i class="fas fa-dollar-sign"></i>
                                                <?php echo htmlspecialchars($app['salary']); ?>
                                            </div>
                                        <?php endif; ?>
                                        <div class="detail-item">
                                            <i class="far fa-calendar"></i>
                                            Applied: <?php echo date('M d, Y', strtotime($app['applied_at'])); ?>
                                        </div>
                                        <div class="detail-item">
                                            <i class="fas fa-clock"></i>
                                            Deadline: <?php echo date('M d, Y', strtotime($app['apply_deadline'])); ?>
                                        </div>
                                    </div>

                                    <?php if ($app['status'] === 'pending'): ?>
                                        <div class="application-actions">
                                            <!-- <button class="btn btn-sm btn-outline" onclick="viewApplication(<?php echo $app['application_id']; ?>)">
                                                View Details
                                            </button> -->
                                        </div>
                                    <?php elseif ($app['status'] === 'accepted'): ?>
                                        <div class="application-success">
                                            <i class="fas fa-check-circle"></i>
                                            Congratulations! Your application has been accepted. Contact your faculty via email for further details.
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    <?php else: ?>
                        <div class="empty-state">
                            <i class="fas fa-file-alt"></i>
                            <h3>No Applications Yet</h3>
                            <p>Start applying to research opportunities to see them here.</p>
                            <a href="../research.php" class="btn btn-primary">Browse Research Posts</a>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Sidebar Content -->
                <div class="dashboard-sidebar">
                    <!-- Available Research -->
                    <!-- <div class="sidebar-section">
                        <h3>Available Research</h3>
                        <?php if ($available_research->num_rows > 0): ?>
                            <div class="research-list">
                                <?php while ($research = $available_research->fetch_assoc()): ?>
                                    <div class="research-item">
                                        <h4><?php echo htmlspecialchars($research['title']); ?></h4>
                                        <p class="research-faculty"><?php echo htmlspecialchars($research['faculty_name']); ?></p>
                                        <p class="research-department"><?php echo htmlspecialchars($research['department']); ?></p>
                                        <?php if ($research['days_left'] <= 7): ?>
                                            <span class="deadline-warning">Deadline: <?php echo $research['days_left']; ?> days left</span>
                                        <?php endif; ?>
                                        <a href="../research.php?id=<?php echo $research['research_id']; ?>" class="btn btn-sm btn-primary">Apply Now</a>
                                    </div>
                                <?php endwhile; ?>
                            </div>
                        <?php else: ?>
                            <p class="text-muted">No available research posts.</p>
                        <?php endif; ?>
                    </div> -->

                    <!-- Recent Announcements -->
                    <div class="sidebar-section">
                        <h3>Recent Announcements</h3>
                        <?php if ($recent_announcements->num_rows > 0): ?>
                            <div class="announcement-list">
                                <?php while ($announcement = $recent_announcements->fetch_assoc()): ?>
                                    <div class="announcement-item">
                                        <h4><?php echo htmlspecialchars($announcement['title']); ?></h4>
                                        <p><?php echo htmlspecialchars(substr($announcement['content'], 0, 100)) . '...'; ?></p>
                                        <span class="announcement-date"><?php echo date('M d, Y', strtotime($announcement['created_at'])); ?></span>
                                    </div>
                                <?php endwhile; ?>
                            </div>
                            <a href="../student/announcements.php" class="btn btn-sm btn-outline btn-block">View All</a>
                        <?php else: ?>
                            <p class="text-muted">No announcements.</p>
                        <?php endif; ?>
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
    <script src="../assets/js/student-dashboard.js"></script>
</body>

</html>

<?php $conn->close(); ?>
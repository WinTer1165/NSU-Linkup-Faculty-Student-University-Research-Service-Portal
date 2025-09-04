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

// Get statistics
// Total research posts
$stmt = $conn->prepare("SELECT COUNT(*) as total FROM research_posts WHERE faculty_id = ?");
$stmt->bind_param("i", $faculty_id);
$stmt->execute();
$total_posts = $stmt->get_result()->fetch_assoc()['total'];
$stmt->close();

// Active research posts
$stmt = $conn->prepare("SELECT COUNT(*) as total FROM research_posts WHERE faculty_id = ? AND is_active = 1 AND apply_deadline >= CURDATE()");
$stmt->bind_param("i", $faculty_id);
$stmt->execute();
$active_posts = $stmt->get_result()->fetch_assoc()['total'];
$stmt->close();

// Total applications
$stmt = $conn->prepare("
    SELECT COUNT(*) as total 
    FROM research_applications ra
    JOIN research_posts r ON ra.research_id = r.research_id
    WHERE r.faculty_id = ?
");
$stmt->bind_param("i", $faculty_id);
$stmt->execute();
$total_applications = $stmt->get_result()->fetch_assoc()['total'];
$stmt->close();

// Pending applications
$stmt = $conn->prepare("
    SELECT COUNT(*) as total 
    FROM research_applications ra
    JOIN research_posts r ON ra.research_id = r.research_id
    WHERE r.faculty_id = ? AND ra.status = 'pending'
");
$stmt->bind_param("i", $faculty_id);
$stmt->execute();
$pending_applications = $stmt->get_result()->fetch_assoc()['total'];
$stmt->close();

// Get recent applications with student details
$stmt = $conn->prepare("
    SELECT ra.*, r.title as research_title, s.full_name as student_name, 
           s.cgpa, s.degree, s.profile_image, u.email as student_email,
           DATEDIFF(r.apply_deadline, CURDATE()) as days_left
    FROM research_applications ra
    JOIN research_posts r ON ra.research_id = r.research_id
    JOIN students s ON ra.student_id = s.student_id
    JOIN users u ON s.user_id = u.user_id
    WHERE r.faculty_id = ? 
    ORDER BY ra.applied_at DESC
    LIMIT 5
");
$stmt->bind_param("i", $faculty_id);
$stmt->execute();
$applications = $stmt->get_result();
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
    <title>Faculty Dashboard - NSU LinkUp</title>
    <link rel="stylesheet" href="../assets/css/common.css">
    <link rel="stylesheet" href="../assets/css/faculty-dashboard.css">
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
                <a href="dashboard.php" class="active">
                    <i class="fas fa-home"></i>
                    Dashboard
                </a>
                <a href="../faculty/profile.php">
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
                    <h1>Welcome back, <?php echo htmlspecialchars($faculty['prefix'] . ' ' . $faculty['full_name']); ?>!</h1>
                    <p>Here's what's happening at NSU LinkUp</p>
                </div>
                <div class="header-right">
                    <div class="user-info">
                        <img src="<?php echo $faculty['profile_image'] ? '../assets/uploads/profiles/' . $faculty['profile_image'] : '../assets/images/default-avatar.png'; ?>"
                            alt="Profile" class="user-avatar">
                        <span><?php echo htmlspecialchars($faculty['full_name']); ?></span>
                    </div>
                </div>
            </header>

            <!-- Quick Stats -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon posts">
                        <i class="fas fa-microscope"></i>
                    </div>
                    <div class="stat-content">
                        <h3>Total Research Posts</h3>
                        <p class="stat-value"><?php echo $total_posts; ?></p>
                        <a href="manage-posts.php" class="stat-link">Manage Posts →</a>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon active">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="stat-content">
                        <h3>Active Posts</h3>
                        <p class="stat-value"><?php echo $active_posts; ?></p>
                        <a href="create-research.php" class="stat-link">Create New →</a>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon applications">
                        <i class="fas fa-file-alt"></i>
                    </div>
                    <div class="stat-content">
                        <h3>Total Applications</h3>
                        <p class="stat-value"><?php echo $total_applications; ?></p>
                        <a href="applications.php" class="stat-link">View All →</a>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon pending">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="stat-content">
                        <h3>Pending Review</h3>
                        <p class="stat-value"><?php echo $pending_applications; ?></p>
                        <?php if ($pending_applications > 0): ?>
                            <a href="applications.php?status=pending" class="stat-link">Review Now →</a>
                        <?php else: ?>
                            <span style="color: #10b981; font-size: 0.875rem;">All caught up!</span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Main Dashboard Content -->
            <div class="dashboard-grid">
                <!-- Recent Applications -->
                <div class="dashboard-section" id="applications">
                    <div class="section-header">
                        <h2>Recent Applications</h2>
                        <a href="applications.php" class="btn btn-sm btn-outline">View All</a>
                    </div>

                    <?php if ($applications->num_rows > 0): ?>
                        <div class="applications-list">
                            <?php
                            $applications->data_seek(0);
                            while ($app = $applications->fetch_assoc()):
                            ?>
                                <div class="application-item status-<?php echo $app['status']; ?>">
                                    <div class="application-header">
                                        <div class="student-info">
                                            <img src="<?php echo $app['profile_image'] ? '../assets/uploads/profiles/' . $app['profile_image'] : '../assets/images/default-avatar.png'; ?>"
                                                alt="Student" class="student-avatar">
                                            <div>
                                                <h3><?php echo htmlspecialchars($app['student_name']); ?></h3>
                                                <p class="student-details">
                                                    <?php echo htmlspecialchars($app['degree']); ?>
                                                    <?php if ($app['cgpa']): ?>
                                                        • CGPA: <?php echo number_format($app['cgpa'], 2); ?>
                                                    <?php endif; ?>
                                                </p>
                                            </div>
                                        </div>
                                        <span class="status-badge status-<?php echo $app['status']; ?>">
                                            <?php echo ucfirst($app['status']); ?>
                                        </span>
                                    </div>

                                    <div class="application-details">
                                        <div class="detail-item">
                                            <i class="fas fa-microscope"></i>
                                            <strong>Applied for:</strong> <?php echo htmlspecialchars($app['research_title']); ?>
                                        </div>
                                        <div class="detail-item">
                                            <i class="fas fa-calendar"></i>
                                            Applied: <?php echo date('M d, Y', strtotime($app['applied_at'])); ?>
                                        </div>
                                        <?php if ($app['days_left'] > 0): ?>
                                            <div class="detail-item">
                                                <i class="fas fa-clock"></i>
                                                Deadline in <?php echo $app['days_left']; ?> days
                                            </div>
                                        <?php endif; ?>
                                    </div>

                                    <?php if ($app['status'] === 'pending'): ?>
                                        <div class="application-actions">
                                            <!-- <button class="btn btn-sm btn-primary" onclick="reviewApplication(<?php echo $app['application_id']; ?>)">
                                                Review
                                            </button>
                                            <button class="btn btn-sm btn-success" onclick="acceptApplication(<?php echo $app['application_id']; ?>)">
                                                Accept
                                            </button>
                                            <button class="btn btn-sm btn-danger" onclick="rejectApplication(<?php echo $app['application_id']; ?>)">
                                                Reject
                                            </button> -->
                                            <a href="mailto:<?php echo $app['student_email']; ?>" class="btn btn-sm btn-outline">
                                                Contact
                                            </a>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    <?php else: ?>
                        <div class="empty-state">
                            <i class="fas fa-inbox"></i>
                            <h3>No Applications Yet</h3>
                            <p>Applications will appear here when students apply to your research posts.</p>
                            <a href="create-research.php" class="btn btn-primary">Create Research Post</a>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Sidebar Content -->
                <div class="dashboard-sidebar">
                    <!-- Upcoming Events -->

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
                            <a href="../faculty/announcements.php" class="btn btn-sm btn-outline btn-block">View All</a>
                        <?php else: ?>
                            <p class="text-muted">No announcements.</p>
                        <?php endif; ?>
                    </div>

                    <!-- Quick Actions -->
                    <div class="sidebar-section">
                        <h3>Quick Actions</h3>
                        <div class="quick-actions">
                            <a href="create-research.php" class="quick-action-btn">
                                <i class="fas fa-plus"></i>
                                Create Research Post
                            </a>
                            <a href="profile.php" class="quick-action-btn">
                                <i class="fas fa-edit"></i>
                                Update Profile
                            </a>
                            <a href="../faculty/students.php" class="quick-action-btn">
                                <i class="fas fa-users"></i>
                                Browse Students
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
    <script src="../assets/js/faculty-dashboard.js"></script>
</body>

</html>

<?php $conn->close(); ?>
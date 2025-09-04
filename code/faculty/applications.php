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

// Handle approve/reject actions
if (isset($_POST['action']) && isset($_POST['application_id'])) {
    $application_id = intval($_POST['application_id']);
    $action = $_POST['action'];

    // Verify ownership of the research post
    $stmt = $conn->prepare("
        SELECT ra.*, r.faculty_id 
        FROM research_applications ra
        JOIN research_posts r ON ra.research_id = r.research_id
        WHERE ra.application_id = ?
    ");
    $stmt->bind_param("i", $application_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $app = $result->fetch_assoc();
        if ($app['faculty_id'] == $faculty_id) {
            $new_status = ($action == 'approve') ? 'accepted' : 'rejected';

            $stmt = $conn->prepare("UPDATE research_applications SET status = ? WHERE application_id = ?");
            $stmt->bind_param("si", $new_status, $application_id);

            if ($stmt->execute()) {
                $_SESSION['success'] = "Application " . $new_status . " successfully!";
            } else {
                $_SESSION['error'] = "Failed to update application status.";
            }
        } else {
            $_SESSION['error'] = "Unauthorized action.";
        }
    }
    $stmt->close();

    header("Location: applications.php" . (isset($_GET['research_id']) ? "?research_id=" . $_GET['research_id'] : ""));
    exit();
}

// Build query based on filters
$query = "
    SELECT ra.*, r.title as research_title, r.department, r.apply_deadline,
           s.full_name as student_name, s.cgpa, s.degree, s.profile_image, 
           s.phone as student_phone, s.address, s.research_interest,
           s.linkedin, s.github, u.email as student_email
    FROM research_applications ra
    JOIN research_posts r ON ra.research_id = r.research_id
    JOIN students s ON ra.student_id = s.student_id
    JOIN users u ON s.user_id = u.user_id
    WHERE r.faculty_id = ?
";

$params = [$faculty_id];
$types = "i";

// Filter by research post if specified
if (isset($_GET['research_id']) && !empty($_GET['research_id'])) {
    $query .= " AND r.research_id = ?";
    $params[] = intval($_GET['research_id']);
    $types .= "i";
}

// Filter by status if specified
if (isset($_GET['status']) && !empty($_GET['status'])) {
    $query .= " AND ra.status = ?";
    $params[] = $_GET['status'];
    $types .= "s";
}

$query .= " ORDER BY ra.applied_at DESC";

$stmt = $conn->prepare($query);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$applications = $stmt->get_result();
$stmt->close();

// Get research posts for filter dropdown
$stmt = $conn->prepare("SELECT research_id, title FROM research_posts WHERE faculty_id = ? ORDER BY created_at DESC");
$stmt->bind_param("i", $faculty_id);
$stmt->execute();
$research_posts = $stmt->get_result();
$stmt->close();

// Get statistics
$stmt = $conn->prepare("
    SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN ra.status = 'pending' THEN 1 ELSE 0 END) as pending,
        SUM(CASE WHEN ra.status = 'accepted' THEN 1 ELSE 0 END) as accepted,
        SUM(CASE WHEN ra.status = 'rejected' THEN 1 ELSE 0 END) as rejected
    FROM research_applications ra
    JOIN research_posts r ON ra.research_id = r.research_id
    WHERE r.faculty_id = ?
");
$stmt->bind_param("i", $faculty_id);
$stmt->execute();
$stats = $stmt->get_result()->fetch_assoc();
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Applications - NSU LinkUp</title>
    <link rel="stylesheet" href="../assets/css/common.css">
    <link rel="stylesheet" href="../assets/css/faculty-dashboard.css">
    <link rel="stylesheet" href="../assets/css/applications.css">
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
                <a href="../faculty/applications.php" class="active">
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
                    <h1>Student Applications</h1>
                    <p>Review and manage applications for your research posts</p>
                </div>
            </header>

            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    <?php echo $_SESSION['success'];
                    unset($_SESSION['success']); ?>
                </div>
            <?php endif; ?>

            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i>
                    <?php echo $_SESSION['error'];
                    unset($_SESSION['error']); ?>
                </div>
            <?php endif; ?>

            <!-- Statistics Cards -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon total">
                        <i class="fas fa-file-alt"></i>
                    </div>
                    <div class="stat-content">
                        <h3>Total Applications</h3>
                        <p class="stat-value"><?php echo $stats['total']; ?></p>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon pending">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="stat-content">
                        <h3>Pending Review</h3>
                        <p class="stat-value"><?php echo $stats['pending']; ?></p>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon accepted">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="stat-content">
                        <h3>Accepted</h3>
                        <p class="stat-value"><?php echo $stats['accepted']; ?></p>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon rejected">
                        <i class="fas fa-times-circle"></i>
                    </div>
                    <div class="stat-content">
                        <h3>Rejected</h3>
                        <p class="stat-value"><?php echo $stats['rejected']; ?></p>
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <div class="filter-section">
                <form method="GET" action="" class="filter-form">
                    <div class="filter-group">
                        <label for="research_id">Filter by Research Post:</label>
                        <select name="research_id" id="research_id" class="filter-select" onchange="this.form.submit()">
                            <option value="">All Posts</option>
                            <?php
                            $research_posts->data_seek(0);
                            while ($post = $research_posts->fetch_assoc()):
                            ?>
                                <option value="<?php echo $post['research_id']; ?>"
                                    <?php echo (isset($_GET['research_id']) && $_GET['research_id'] == $post['research_id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($post['title']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <div class="filter-group">
                        <label for="status">Filter by Status:</label>
                        <select name="status" id="status" class="filter-select" onchange="this.form.submit()">
                            <option value="">All Status</option>
                            <option value="pending" <?php echo (isset($_GET['status']) && $_GET['status'] == 'pending') ? 'selected' : ''; ?>>Pending</option>
                            <option value="accepted" <?php echo (isset($_GET['status']) && $_GET['status'] == 'accepted') ? 'selected' : ''; ?>>Accepted</option>
                            <option value="rejected" <?php echo (isset($_GET['status']) && $_GET['status'] == 'rejected') ? 'selected' : ''; ?>>Rejected</option>
                        </select>
                    </div>

                    <div class="filter-group">
                        <a href="applications.php" class="btn btn-outline btn-sm">Clear Filters</a>
                    </div>
                </form>
            </div>

            <!-- Applications List -->
            <div class="applications-container">
                <?php if ($applications->num_rows > 0): ?>
                    <div class="applications-list">
                        <?php while ($app = $applications->fetch_assoc()): ?>
                            <div class="application-card" data-status="<?php echo $app['status']; ?>">
                                <div class="application-header">
                                    <div class="student-info">
                                        <img src="<?php echo $app['profile_image'] ? '../assets/uploads/profiles/' . $app['profile_image'] : '../assets/images/default-avatar.png'; ?>"
                                            alt="Student" class="student-avatar">
                                        <div class="student-details">
                                            <h3><?php echo htmlspecialchars($app['student_name']); ?></h3>
                                            <p><?php echo htmlspecialchars($app['degree']); ?></p>
                                            <?php if ($app['cgpa']): ?>
                                                <span class="cgpa-badge">CGPA: <?php echo number_format($app['cgpa'], 2); ?></span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <div class="status-badge status-<?php echo $app['status']; ?>">
                                        <?php echo ucfirst($app['status']); ?>
                                    </div>
                                </div>

                                <div class="application-body">
                                    <div class="research-info">
                                        <h4>Applied for: <?php echo htmlspecialchars($app['research_title']); ?></h4>
                                        <p class="department"><?php echo htmlspecialchars($app['department']); ?></p>
                                    </div>

                                    <div class="application-meta">
                                        <div class="meta-item">
                                            <i class="fas fa-calendar"></i>
                                            Applied: <?php echo date('M d, Y', strtotime($app['applied_at'])); ?>
                                        </div>
                                        <div class="meta-item">
                                            <i class="fas fa-envelope"></i>
                                            <?php echo htmlspecialchars($app['student_email']); ?>
                                        </div>
                                        <?php if ($app['student_phone']): ?>
                                            <div class="meta-item">
                                                <i class="fas fa-phone"></i>
                                                <?php echo htmlspecialchars($app['student_phone']); ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>

                                    <?php if ($app['cover_letter']): ?>
                                        <div class="cover-letter">
                                            <h5>Cover Letter:</h5>
                                            <p><?php echo nl2br(htmlspecialchars($app['cover_letter'])); ?></p>
                                        </div>
                                    <?php endif; ?>

                                    <?php if ($app['research_interest']): ?>
                                        <div class="research-interests">
                                            <h5>Research Interests:</h5>
                                            <p><?php echo htmlspecialchars($app['research_interest']); ?></p>
                                        </div>
                                    <?php endif; ?>

                                    <div class="student-links">
                                        <?php if ($app['linkedin']): ?>
                                            <a href="<?php echo htmlspecialchars($app['linkedin']); ?>" target="_blank" class="link-btn">
                                                <i class="fab fa-linkedin"></i> LinkedIn
                                            </a>
                                        <?php endif; ?>
                                        <?php if ($app['github']): ?>
                                            <a href="<?php echo htmlspecialchars($app['github']); ?>" target="_blank" class="link-btn">
                                                <i class="fab fa-github"></i> GitHub
                                            </a>
                                        <?php endif; ?>
                                        <a href="mailto:<?php echo $app['student_email']; ?>" class="link-btn">
                                            <i class="fas fa-envelope"></i> Email
                                        </a>
                                    </div>
                                </div>

                                <div class="application-actions">
                                    <?php if ($app['status'] == 'pending'): ?>
                                        <button class="btn btn-success"
                                            onclick="confirmAction(<?php echo $app['application_id']; ?>, 'approve', '<?php echo addslashes($app['student_name']); ?>')">
                                            <i class="fas fa-check"></i> Approve
                                        </button>
                                        <button class="btn btn-danger"
                                            onclick="confirmAction(<?php echo $app['application_id']; ?>, 'reject', '<?php echo addslashes($app['student_name']); ?>')">
                                            <i class="fas fa-times"></i> Reject
                                        </button>
                                    <?php elseif ($app['status'] == 'accepted'): ?>
                                        <span class="status-message success">
                                            <i class="fas fa-check-circle"></i> Application Approved
                                        </span>
                                    <?php else: ?>
                                        <span class="status-message danger">
                                            <i class="fas fa-times-circle"></i> Application Rejected
                                        </span>
                                    <?php endif; ?>
                                    <!-- <button class="btn btn-outline" onclick="viewDetails(<?php echo $app['application_id']; ?>)">
                                        <i class="fas fa-eye"></i> View Details
                                    </button> -->
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-inbox"></i>
                        <h3>No Applications Found</h3>
                        <p>There are no applications matching your filters.</p>
                        <?php if (isset($_GET['research_id']) || isset($_GET['status'])): ?>
                            <a href="applications.php" class="btn btn-primary">View All Applications</a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <!-- Action Confirmation Modal -->
    <div id="actionModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="modalTitle">Confirm Action</h2>
                <span class="modal-close" onclick="closeModal()">&times;</span>
            </div>
            <div class="modal-body">
                <p id="modalMessage"></p>
            </div>
            <div class="modal-footer">
                <form method="POST" action="" id="actionForm">
                    <input type="hidden" name="application_id" id="applicationId">
                    <input type="hidden" name="action" id="actionType">
                    <button type="button" class="btn btn-outline" onclick="closeModal()">Cancel</button>
                    <button type="submit" class="btn" id="confirmBtn">Confirm</button>
                </form>
            </div>
        </div>
    </div>

    <!-- Mobile Menu Toggle -->
    <button class="mobile-menu-toggle" id="mobileMenuToggle">
        <i class="fas fa-bars"></i>
    </button>

    <script src="../assets/js/common.js"></script>
    <script src="../assets/js/applications.js"></script>
</body>

</html>

<?php $conn->close(); ?>
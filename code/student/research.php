<?php
require_once '../includes/config.php';
require_once '../includes/db_connect.php';
require_once '../includes/functions.php';
require_once '../includes/auth_check.php';

// Check if user is logged in and is either student or faculty
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_type'], ['student', 'faculty'])) {
    header("Location: ../index.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$user_type = $_SESSION['user_type'];

// Get student info if user is a student (for CGPA check)
$student_cgpa = null;
if ($user_type == 'student') {
    $stmt = $conn->prepare("SELECT cgpa FROM students WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $student_cgpa = $row['cgpa'];
    }
    $stmt->close();
}

// Handle application submission for students
if ($_SERVER['REQUEST_METHOD'] == 'POST' && $user_type == 'student' && isset($_POST['apply_research'])) {
    $research_id = (int)$_POST['research_id'];
    $cover_letter = sanitize_input($_POST['cover_letter']);

    // Get student_id
    $stmt = $conn->prepare("SELECT student_id FROM students WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $student_data = $stmt->get_result()->fetch_assoc();
    $student_id = $student_data['student_id'];
    $stmt->close();

    // Check if already applied
    $stmt = $conn->prepare("SELECT application_id FROM research_applications WHERE research_id = ? AND student_id = ?");
    $stmt->bind_param("ii", $research_id, $student_id);
    $stmt->execute();
    $existing = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if ($existing) {
        show_alert('You have already applied for this research position.', 'warning');
    } else {
        // Submit application
        $stmt = $conn->prepare("INSERT INTO research_applications (research_id, student_id, cover_letter) VALUES (?, ?, ?)");
        $stmt->bind_param("iis", $research_id, $student_id, $cover_letter);

        if ($stmt->execute()) {
            show_alert('Application submitted successfully!', 'success');
            log_audit('APPLY_RESEARCH', 'research_applications', $conn->insert_id);
        } else {
            show_alert('Failed to submit application. Please try again.', 'error');
        }
        $stmt->close();
    }
}

// Filter parameters
$department = isset($_GET['department']) ? $_GET['department'] : '';
$search = isset($_GET['search']) ? $_GET['search'] : '';
$min_salary = isset($_GET['min_salary']) ? (int)$_GET['min_salary'] : 0;

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 9;
$offset = ($page - 1) * $limit;

// Build query
$where_conditions = ["r.is_active = TRUE"];
$params = [];
$types = "";

if ($department) {
    $where_conditions[] = "r.department = ?";
    $params[] = $department;
    $types .= "s";
}

if ($search) {
    $where_conditions[] = "(r.title LIKE ? OR r.description LIKE ? OR r.tags LIKE ?)";
    $search_param = "%$search%";
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
    $types .= "sss";
}

if ($min_salary > 0) {
    $where_conditions[] = "CAST(REPLACE(REPLACE(r.salary, '$', ''), ',', '') AS UNSIGNED) >= ?";
    $params[] = $min_salary;
    $types .= "i";
}

$where_clause = implode(" AND ", $where_conditions);

// Get total count
$count_sql = "SELECT COUNT(*) AS total FROM research_posts r WHERE $where_clause";
$count_types = $types;
$count_params = $params;

$stmt = $conn->prepare($count_sql);
if ($count_params) $stmt->bind_param($count_types, ...$count_params);
$stmt->execute();
$total_posts = $stmt->get_result()->fetch_assoc()['total'];
$stmt->close();

$total_pages = (int)ceil($total_posts / $limit);

// Get research posts with faculty info
$main_sql = "
  SELECT
    r.research_id, r.title, r.min_cgpa, r.department, r.apply_deadline,
    r.duration, r.tags, r.salary, r.number_required, r.is_active, r.created_at,
    r.description, r.student_roles,
    f.full_name AS faculty_name,
    f.profile_image AS faculty_image,
    f.office, f.title as faculty_title,
    u.email AS faculty_email,
    (
      SELECT COUNT(*) 
      FROM research_applications ra 
      WHERE ra.research_id = r.research_id
    ) AS application_count";

// Add application status for students
if ($user_type == 'student') {
    $main_sql .= ",
    (
      SELECT status 
      FROM research_applications ra 
      JOIN students s ON ra.student_id = s.student_id 
      WHERE ra.research_id = r.research_id AND s.user_id = ?
    ) AS application_status";
    $params = array_merge([$user_id], $params);
    $types = "i" . $types;
}

$main_sql .= "
  FROM research_posts r
  JOIN faculty f ON r.faculty_id = f.faculty_id
  JOIN users u ON u.user_id = f.user_id
  WHERE $where_clause
  ORDER BY r.created_at DESC
  LIMIT ? OFFSET ?
";

$main_types = $types . "ii";
$main_params = array_merge($params, [$limit, $offset]);

$stmt = $conn->prepare($main_sql);
$stmt->bind_param($main_types, ...$main_params);
$stmt->execute();
$result = $stmt->get_result();

// Get unique departments for filter
$dept_query = "SELECT DISTINCT department FROM research_posts WHERE is_active = TRUE AND department IS NOT NULL ORDER BY department";
$dept_result = $conn->query($dept_query);

// Get user info for header
$user_info = getUserInfo($conn, $user_id, $user_type);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Research Opportunities - NSU LinkUp</title>
    <link rel="stylesheet" href="../assets/css/common.css">
    <link rel="stylesheet" href="../assets/css/research.css">
    <link rel="stylesheet" href="../assets/css/research-view.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>

<body>
    <div class="dashboard-container">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <h2>NSU LinkUp</h2>
                <p><?php echo ucfirst($user_type); ?> Portal</p>
            </div>

            <nav class="sidebar-nav">
                <a href="dashboard.php">
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
                <a href="research.php" class="active">
                    <i class="fas fa-microscope"></i>
                    Research Posts
                </a>
                <a href="students.php">
                    <i class="fas fa-graduation-cap"></i>
                    Students
                </a>
                <a href="faculty.php">
                    <i class="fas fa-chalkboard-teacher"></i>
                    Faculty
                </a>
                <!-- <a href="events.php">
                    <i class="fas fa-calendar-alt"></i>
                    Events
                </a> -->
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
                    <h1>
                        <i class="fas fa-microscope"></i>
                        Research Opportunities
                    </h1>
                    <p>
                        <?php if ($user_type == 'student'): ?>
                            Find and apply for research positions with faculty members
                        <?php else: ?>
                            Browse research opportunities and view applications
                        <?php endif; ?>
                    </p>
                </div>
                <div class="header-right">
                    <div class="user-info">
                        <span>Welcome, <?php echo htmlspecialchars($user_info['full_name']); ?></span>
                        <img src="<?php echo $user_info['profile_image'] ? '../assets/uploads/profiles/' . $user_info['profile_image'] : '../assets/images/default-avatar.png'; ?>"
                            alt="Profile" class="user-avatar">
                    </div>
                </div>
            </header>

            <!-- Filters Section -->
            <div class="filters-section">
                <form method="GET" class="filters-form">
                    <div class="search-box">
                        <i class="fas fa-search"></i>
                        <input type="text" name="search" placeholder="Search research opportunities..."
                            value="<?php echo htmlspecialchars($search); ?>">
                    </div>

                    <select name="department" class="filter-select">
                        <option value="">All Departments</option>
                        <?php while ($dept = $dept_result->fetch_assoc()): ?>
                            <option value="<?php echo htmlspecialchars($dept['department']); ?>"
                                <?php echo $department == $dept['department'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($dept['department']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>

                    <select name="min_salary" class="filter-select">
                        <option value="0">Any Salary</option>
                        <option value="5000" <?php echo $min_salary == 5000 ? 'selected' : ''; ?>>$5,000+</option>
                        <option value="10000" <?php echo $min_salary == 10000 ? 'selected' : ''; ?>>$10,000+</option>
                        <option value="15000" <?php echo $min_salary == 15000 ? 'selected' : ''; ?>>$15,000+</option>
                        <option value="20000" <?php echo $min_salary == 20000 ? 'selected' : ''; ?>>$20,000+</option>
                    </select>

                    <button type="submit" class="filter-submit">
                        <i class="fas fa-filter"></i>
                        Apply Filters
                    </button>

                    <?php if ($search || $department || $min_salary > 0): ?>
                        <a href="research.php" class="clear-filters">
                            <i class="fas fa-times"></i>
                            Clear
                        </a>
                    <?php endif; ?>
                </form>

                <div class="results-info">
                    Showing <?php echo $total_posts; ?> research opportunities
                </div>
            </div>

            <!-- Research Posts Grid -->
            <div class="research-container">
                <?php if ($result->num_rows > 0): ?>
                    <div class="research-grid">
                        <?php while ($post = $result->fetch_assoc()): ?>
                            <?php
                            $is_eligible = true;
                            $application_status = $post['application_status'] ?? null;

                            if ($user_type == 'student' && $post['min_cgpa'] && $student_cgpa) {
                                $is_eligible = $student_cgpa >= $post['min_cgpa'];
                            }
                            $deadline_passed = strtotime($post['apply_deadline']) < time();
                            ?>

                            <div class="research-card <?php echo (!$is_eligible || $deadline_passed) ? 'ineligible' : ''; ?>">
                                <?php if ($deadline_passed): ?>
                                    <div class="deadline-badge expired">Deadline Passed</div>
                                <?php elseif (strtotime($post['apply_deadline']) <= strtotime('+3 days')): ?>
                                    <div class="deadline-badge urgent">Deadline Soon</div>
                                <?php endif; ?>

                                <?php if ($user_type == 'student' && $application_status): ?>
                                    <div class="application-status-badge status-<?php echo $application_status; ?>">
                                        <?php echo ucfirst($application_status); ?>
                                    </div>
                                <?php endif; ?>

                                <div class="research-header">
                                    <h3 class="research-title">
                                        <?php echo htmlspecialchars($post['title']); ?>
                                    </h3>
                                    <div class="research-meta">
                                        <span class="positions-count">
                                            <i class="fas fa-users"></i>
                                            <?php echo $post['number_required']; ?> positions
                                        </span>
                                        <?php if ($post['application_count'] > 0): ?>
                                            <span class="applications-count">
                                                <i class="fas fa-file-alt"></i>
                                                <?php echo $post['application_count']; ?> applications
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <div class="faculty-info">
                                    <img src="<?php echo $post['faculty_image'] ? '../assets/uploads/profiles/' . $post['faculty_image'] : '../assets/images/default-avatar.png'; ?>"
                                        alt="Faculty" class="faculty-avatar">
                                    <div class="faculty-details">
                                        <h4><?php echo htmlspecialchars($post['faculty_title'] . ' ' . $post['faculty_name']); ?></h4>
                                        <p><?php echo htmlspecialchars($post['office'] ?? 'Office not specified'); ?></p>
                                    </div>
                                </div>

                                <div class="research-details">
                                    <?php if ($post['department']): ?>
                                        <div class="detail-item">
                                            <i class="fas fa-building"></i>
                                            <?php echo htmlspecialchars($post['department']); ?>
                                        </div>
                                    <?php endif; ?>

                                    <?php if ($post['salary']): ?>
                                        <div class="detail-item">
                                            <i class="fas fa-dollar-sign"></i>
                                            <?php echo htmlspecialchars($post['salary']); ?>
                                        </div>
                                    <?php endif; ?>

                                    <?php if ($post['duration']): ?>
                                        <div class="detail-item">
                                            <i class="far fa-clock"></i>
                                            <?php echo htmlspecialchars($post['duration']); ?>
                                        </div>
                                    <?php endif; ?>

                                    <?php if ($post['min_cgpa']): ?>
                                        <div class="detail-item <?php echo !$is_eligible ? 'requirement-not-met' : ''; ?>">
                                            <i class="fas fa-graduation-cap"></i>
                                            Min CGPA: <?php echo $post['min_cgpa']; ?>
                                            <?php if ($user_type == 'student' && $student_cgpa): ?>
                                                (Your CGPA: <?php echo number_format($student_cgpa, 2); ?>)
                                            <?php endif; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <?php if ($post['tags']): ?>
                                    <div class="research-tags">
                                        <?php
                                        $tags = explode(',', $post['tags']);
                                        foreach (array_slice($tags, 0, 3) as $tag):
                                        ?>
                                            <span class="tag"><?php echo htmlspecialchars(trim($tag)); ?></span>
                                        <?php endforeach; ?>
                                        <?php if (count($tags) > 3): ?>
                                            <span class="tag">+<?php echo count($tags) - 3; ?></span>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>

                                <div class="research-description">
                                    <?php echo nl2br(htmlspecialchars(substr($post['description'], 0, 150))); ?>
                                    <?php if (strlen($post['description']) > 150): ?>...<?php endif; ?>
                                </div>

                                <div class="research-footer">
                                    <div class="deadline-info">
                                        <i class="far fa-calendar-alt"></i>
                                        Apply by <?php echo date('M d, Y', strtotime($post['apply_deadline'])); ?>
                                        <?php
                                        $days_left = ceil((strtotime($post['apply_deadline']) - time()) / (60 * 60 * 24));
                                        if ($days_left > 0) {
                                            echo " ($days_left days left)";
                                        }
                                        ?>
                                    </div>

                                    <div class="research-actions">
                                        <button class="view-details-btn"
                                            data-research-id="<?php echo $post['research_id']; ?>">
                                            View Details
                                        </button>

                                        <?php if ($user_type == 'student' && !$deadline_passed && $is_eligible && !$application_status): ?>
                                            <button class="apply-btn btn-primary"
                                                data-research-id="<?php echo $post['research_id']; ?>"
                                                data-research-title="<?php echo htmlspecialchars($post['title']); ?>">
                                                Apply Now
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>

                    <!-- Pagination -->
                    <?php if ($total_pages > 1): ?>
                        <div class="pagination">
                            <?php
                            $url_params = [];
                            if ($department) $url_params[] = "department=" . urlencode($department);
                            if ($search) $url_params[] = "search=" . urlencode($search);
                            if ($min_salary) $url_params[] = "min_salary=$min_salary";
                            $url_suffix = !empty($url_params) ? '&' . implode('&', $url_params) : '';
                            ?>

                            <a href="?page=1<?php echo $url_suffix; ?>"
                                class="page-link <?php echo $page == 1 ? 'disabled' : ''; ?>">
                                <i class="fas fa-angle-double-left"></i>
                            </a>
                            <a href="?page=<?php echo max(1, $page - 1); ?><?php echo $url_suffix; ?>"
                                class="page-link <?php echo $page == 1 ? 'disabled' : ''; ?>">
                                <i class="fas fa-angle-left"></i>
                            </a>

                            <?php
                            $start = max(1, $page - 2);
                            $end = min($total_pages, $page + 2);

                            for ($i = $start; $i <= $end; $i++):
                            ?>
                                <a href="?page=<?php echo $i; ?><?php echo $url_suffix; ?>"
                                    class="page-link <?php echo $i == $page ? 'active' : ''; ?>">
                                    <?php echo $i; ?>
                                </a>
                            <?php endfor; ?>

                            <a href="?page=<?php echo min($total_pages, $page + 1); ?><?php echo $url_suffix; ?>"
                                class="page-link <?php echo $page == $total_pages ? 'disabled' : ''; ?>">
                                <i class="fas fa-angle-right"></i>
                            </a>
                            <a href="?page=<?php echo $total_pages; ?><?php echo $url_suffix; ?>"
                                class="page-link <?php echo $page == $total_pages ? 'disabled' : ''; ?>">
                                <i class="fas fa-angle-double-right"></i>
                            </a>
                        </div>
                    <?php endif; ?>

                <?php else: ?>
                    <div class="no-data">
                        <div class="no-data-icon">
                            <i class="fas fa-microscope"></i>
                        </div>
                        <h2>No Research Opportunities Found</h2>
                        <p>
                            <?php if ($search || $department || $min_salary > 0): ?>
                                Try adjusting your filters or search terms.
                            <?php else: ?>
                                Check back later for new research opportunities.
                            <?php endif; ?>
                        </p>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <!-- Research Details Modal -->
    <div id="researchModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <div id="modalBody">
                <!-- Research details will be loaded here -->
            </div>
        </div>
    </div>

    <!-- Application Modal -->
    <?php if ($user_type == 'student'): ?>
        <div id="applicationModal" class="modal">
            <div class="modal-content">
                <span class="close">&times;</span>
                <div id="applicationModalBody">
                    <h2>Apply for Research Position</h2>
                    <form method="POST" id="applicationForm">
                        <input type="hidden" name="apply_research" value="1">
                        <input type="hidden" name="research_id" id="apply_research_id">

                        <div class="form-group">
                            <label for="research_title_display">Research Position:</label>
                            <input type="text" id="research_title_display" class="form-control" readonly>
                        </div>

                        <div class="form-group">
                            <label for="cover_letter">Cover Letter</label>
                            <textarea name="cover_letter" id="cover_letter" class="form-control" rows="8"
                                placeholder="Explain why you're interested in this research position and what qualifications you bring..." required></textarea>
                            <small class="form-text">Tell the faculty member why you're the right fit for this research position.</small>
                        </div>

                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary">Submit Application</button>
                            <button type="button" class="btn btn-outline" onclick="closeModal('applicationModal')">Cancel</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Mobile Menu Toggle -->
    <button class="mobile-menu-toggle" id="mobileMenuToggle">
        <i class="fas fa-bars"></i>
    </button>

    <script src="../assets/js/common2.js"></script>
    <script src="../assets/js/research.js"></script>
</body>

</html>

<?php
$stmt->close();
$conn->close();
?>
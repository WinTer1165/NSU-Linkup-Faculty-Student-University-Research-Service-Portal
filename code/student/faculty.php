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

// Filter parameters
$search = isset($_GET['search']) ? $_GET['search'] : '';
$department = isset($_GET['department']) ? $_GET['department'] : '';

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 12;
$offset = ($page - 1) * $limit;

// Build query
$where_conditions = ["u.is_verified = TRUE AND u.is_banned = FALSE"];
$params = [];
$types = "";

if ($search) {
    $where_conditions[] = "(f.full_name LIKE ? OR f.title LIKE ? OR f.research_interests LIKE ? OR f.biography LIKE ?)";
    $search_param = "%$search%";
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
    $types .= "ssss";
}

if ($department) {
    // Check research posts for department since faculty table doesn't have department
    $where_conditions[] = "f.faculty_id IN (SELECT DISTINCT faculty_id FROM research_posts WHERE department = ?)";
    $params[] = $department;
    $types .= "s";
}

$where_clause = implode(" AND ", $where_conditions);

// Get total count
$count_query = "SELECT COUNT(*) as total FROM faculty f 
                JOIN users u ON f.user_id = u.user_id 
                WHERE $where_clause";
$stmt = $conn->prepare($count_query);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$total_faculty = $stmt->get_result()->fetch_assoc()['total'];
$total_pages = ceil($total_faculty / $limit);

// Get faculty with their active research posts count
$query = "SELECT f.*, u.email,
          (SELECT COUNT(*) FROM research_posts WHERE faculty_id = f.faculty_id AND is_active = TRUE) as active_research_count,
          (SELECT GROUP_CONCAT(DISTINCT department) FROM research_posts WHERE faculty_id = f.faculty_id) as departments
          FROM faculty f 
          JOIN users u ON f.user_id = u.user_id 
          WHERE $where_clause 
          ORDER BY f.full_name ASC 
          LIMIT ? OFFSET ?";

$params[] = $limit;
$params[] = $offset;
$types .= "ii";

$stmt = $conn->prepare($query);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();

// Get unique departments for filter
$dept_query = "SELECT DISTINCT department FROM research_posts WHERE department IS NOT NULL ORDER BY department";
$dept_result = $conn->query($dept_query);

// Get user info for header
$user_info = getUserInfo($conn, $user_id, $user_type);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Faculty - NSU LinkUp</title>
    <link rel="stylesheet" href="../assets/css/common.css">
    <link rel="stylesheet" href="../assets/css/faculty.css">
    <link rel="stylesheet" href="../assets/css/profile-view.css">
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
                <a href="research.php">
                    <i class="fas fa-microscope"></i>
                    Research Posts
                </a>
                <a href="students.php">
                    <i class="fas fa-graduation-cap"></i>
                    Students
                </a>
                <a href="faculty.php" class="active">
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
                        <i class="fas fa-chalkboard-teacher"></i>
                        Faculty Directory
                    </h1>
                    <p>Connect with faculty members and explore research opportunities</p>
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
                        <input type="text" name="search" placeholder="Search faculty by name, title, or research interests..."
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

                    <button type="submit" class="filter-submit">
                        <i class="fas fa-filter"></i>
                        Apply Filters
                    </button>

                    <?php if ($search || $department): ?>
                        <a href="faculty.php" class="clear-filters">
                            <i class="fas fa-times"></i>
                            Clear
                        </a>
                    <?php endif; ?>
                </form>

                <div class="results-info">
                    Showing <?php echo $total_faculty; ?> faculty members
                </div>
            </div>

            <!-- Faculty Grid -->
            <div class="faculty-container">
                <?php if ($result->num_rows > 0): ?>
                    <div class="faculty-grid">
                        <?php while ($faculty = $result->fetch_assoc()): ?>
                            <div class="faculty-card">
                                <div class="faculty-header">
                                    <img src="<?php echo $faculty['profile_image'] ? '../assets/uploads/profiles/' . $faculty['profile_image'] : '../assets/images/default-avatar.png'; ?>"
                                        alt="<?php echo htmlspecialchars($faculty['full_name']); ?>"
                                        class="faculty-avatar">
                                    <div class="faculty-basic">
                                        <h3 class="faculty-name">
                                            <?php if ($faculty['prefix']): ?>
                                                <?php echo htmlspecialchars($faculty['prefix']); ?>
                                            <?php endif; ?>
                                            <?php echo htmlspecialchars($faculty['full_name']); ?>
                                        </h3>
                                        <?php if ($faculty['title']): ?>
                                            <p class="faculty-title"><?php echo htmlspecialchars($faculty['title']); ?></p>
                                        <?php endif; ?>
                                        <?php if ($faculty['office']): ?>
                                            <p class="faculty-office">
                                                <i class="fas fa-door-open"></i>
                                                <?php echo htmlspecialchars($faculty['office']); ?>
                                            </p>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <?php if ($faculty['departments']): ?>
                                    <div class="faculty-departments">
                                        <?php
                                        $depts = explode(',', $faculty['departments']);
                                        foreach (array_unique($depts) as $dept):
                                        ?>
                                            <span class="dept-tag"><?php echo htmlspecialchars(trim($dept)); ?></span>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>

                                <?php if ($faculty['research_interests']): ?>
                                    <div class="faculty-interests">
                                        <i class="fas fa-flask"></i>
                                        <span><?php echo htmlspecialchars(substr($faculty['research_interests'], 0, 150)); ?>
                                            <?php if (strlen($faculty['research_interests']) > 150): ?>...<?php endif; ?></span>
                                    </div>
                                <?php endif; ?>

                                <?php if ($faculty['courses_taught']): ?>
                                    <div class="faculty-courses">
                                        <i class="fas fa-book"></i>
                                        <span><?php echo htmlspecialchars(substr($faculty['courses_taught'], 0, 100)); ?>
                                            <?php if (strlen($faculty['courses_taught']) > 100): ?>...<?php endif; ?></span>
                                    </div>
                                <?php endif; ?>

                                <div class="faculty-stats">
                                    <?php if ($faculty['active_research_count'] > 0): ?>
                                        <div class="stat-item">
                                            <i class="fas fa-microscope"></i>
                                            <span><?php echo $faculty['active_research_count']; ?> Active Research Post<?php echo $faculty['active_research_count'] > 1 ? 's' : ''; ?></span>
                                        </div>
                                    <?php endif; ?>
                                    <?php if ($faculty['office_hours']): ?>
                                        <div class="stat-item">
                                            <i class="far fa-clock"></i>
                                            <span><?php echo htmlspecialchars($faculty['office_hours']); ?></span>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <div class="faculty-footer">
                                    <div class="faculty-links">
                                        <a href="mailto:<?php echo htmlspecialchars($faculty['email']); ?>"
                                            class="social-link" title="Email">
                                            <i class="fas fa-envelope"></i>
                                        </a>
                                        <?php if ($faculty['linkedin']): ?>
                                            <a href="<?php echo htmlspecialchars($faculty['linkedin']); ?>"
                                                target="_blank" class="social-link" title="LinkedIn">
                                                <i class="fab fa-linkedin"></i>
                                            </a>
                                        <?php endif; ?>
                                        <?php if ($faculty['google_scholar']): ?>
                                            <a href="<?php echo htmlspecialchars($faculty['google_scholar']); ?>"
                                                target="_blank" class="social-link" title="Google Scholar">
                                                <i class="fas fa-graduation-cap"></i>
                                            </a>
                                        <?php endif; ?>
                                        <?php if ($faculty['github']): ?>
                                            <a href="<?php echo htmlspecialchars($faculty['github']); ?>"
                                                target="_blank" class="social-link" title="GitHub">
                                                <i class="fab fa-github"></i>
                                            </a>
                                        <?php endif; ?>
                                        <?php if ($faculty['website']): ?>
                                            <a href="<?php echo htmlspecialchars($faculty['website']); ?>"
                                                target="_blank" class="social-link" title="Website">
                                                <i class="fas fa-globe"></i>
                                            </a>
                                        <?php endif; ?>
                                    </div>

                                    <button class="view-profile-btn"
                                        data-faculty-id="<?php echo $faculty['faculty_id']; ?>">
                                        View Profile
                                    </button>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>

                    <!-- Pagination -->
                    <?php if ($total_pages > 1): ?>
                        <div class="pagination">
                            <?php
                            $url_params = [];
                            if ($search) $url_params[] = "search=" . urlencode($search);
                            if ($department) $url_params[] = "department=" . urlencode($department);
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
                            <i class="fas fa-chalkboard-teacher"></i>
                        </div>
                        <h2>No Faculty Found</h2>
                        <p>
                            <?php if ($search || $department): ?>
                                Try adjusting your filters or search terms.
                            <?php else: ?>
                                No faculty members have registered yet.
                            <?php endif; ?>
                        </p>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <!-- Faculty Profile Modal -->
    <div id="facultyModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <div id="modalBody">
                <!-- Faculty details will be loaded here -->
            </div>
        </div>
    </div>

    <!-- Mobile Menu Toggle -->
    <button class="mobile-menu-toggle" id="mobileMenuToggle">
        <i class="fas fa-bars"></i>
    </button>

    <script src="../assets/js/common.js"></script>
    <script src="../assets/js/faculty.js"></script>
</body>

</html>

<?php
$stmt->close();
$conn->close();
?>
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
$degree = isset($_GET['degree']) ? $_GET['degree'] : '';
$skills = isset($_GET['skills']) ? $_GET['skills'] : '';

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 12;
$offset = ($page - 1) * $limit;

// Build query
$where_conditions = ["u.is_verified = TRUE AND u.is_banned = FALSE"];
$params = [];
$types = "";

if ($search) {
    $where_conditions[] = "(s.full_name LIKE ? OR s.bio LIKE ? OR s.research_interest LIKE ?)";
    $search_param = "%$search%";
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
    $types .= "sss";
}

if ($degree) {
    $where_conditions[] = "s.degree = ?";
    $params[] = $degree;
    $types .= "s";
}

if ($skills) {
    $where_conditions[] = "s.student_id IN (SELECT student_id FROM student_skills WHERE skill_name LIKE ?)";
    $params[] = "%$skills%";
    $types .= "s";
}

$where_clause = implode(" AND ", $where_conditions);

// Get total count
$count_query = "SELECT COUNT(*) as total FROM students s 
                JOIN users u ON s.user_id = u.user_id 
                WHERE $where_clause";
$stmt = $conn->prepare($count_query);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$total_students = $stmt->get_result()->fetch_assoc()['total'];
$total_pages = ceil($total_students / $limit);

// Get students with their skills
$query = "SELECT s.*, u.email,
          GROUP_CONCAT(DISTINCT sk.skill_name) as skills_list
          FROM students s 
          JOIN users u ON s.user_id = u.user_id 
          LEFT JOIN student_skills sk ON s.student_id = sk.student_id
          WHERE $where_clause 
          GROUP BY s.student_id
          ORDER BY s.full_name ASC 
          LIMIT ? OFFSET ?";

$params[] = $limit;
$params[] = $offset;
$types .= "ii";

$stmt = $conn->prepare($query);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();

// Get unique degrees for filter
$degree_query = "SELECT DISTINCT degree FROM students WHERE degree IS NOT NULL ORDER BY degree";
$degree_result = $conn->query($degree_query);

// Get user info for header
$user_info = getUserInfo($conn, $user_id, $user_type);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Students - NSU LinkUp</title>
    <link rel="stylesheet" href="../assets/css/common.css">
    <link rel="stylesheet" href="../assets/css/students.css">
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
                <a href="students.php" class="active">
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
                        <i class="fas fa-graduation-cap"></i>
                        Student Directory
                    </h1>
                    <p>Connect with talented students at NSU</p>
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
                        <input type="text" name="search" placeholder="Search students by name, bio, or interests..."
                            value="<?php echo htmlspecialchars($search); ?>">
                    </div>

                    <select name="degree" class="filter-select">
                        <option value="">All Degrees</option>
                        <?php while ($deg = $degree_result->fetch_assoc()): ?>
                            <option value="<?php echo htmlspecialchars($deg['degree']); ?>"
                                <?php echo $degree == $deg['degree'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($deg['degree']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>

                    <input type="text" name="skills" class="skills-input"
                        placeholder="Filter by skills..."
                        value="<?php echo htmlspecialchars($skills); ?>">

                    <button type="submit" class="filter-submit">
                        <i class="fas fa-filter"></i>
                        Apply Filters
                    </button>

                    <?php if ($search || $degree || $skills): ?>
                        <a href="students.php" class="clear-filters">
                            <i class="fas fa-times"></i>
                            Clear
                        </a>
                    <?php endif; ?>
                </form>

                <div class="results-info">
                    Showing <?php echo $total_students; ?> students
                </div>
            </div>

            <!-- Students Grid -->
            <div class="students-container">
                <?php if ($result->num_rows > 0): ?>
                    <div class="students-grid">
                        <?php while ($student = $result->fetch_assoc()): ?>
                            <div class="student-card">
                                <div class="student-header">
                                    <img src="<?php echo $student['profile_image'] ? '../assets/uploads/profiles/' . $student['profile_image'] : '../assets/images/default-avatar.png'; ?>"
                                        alt="<?php echo htmlspecialchars($student['full_name']); ?>"
                                        class="student-avatar">
                                    <div class="student-basic">
                                        <h3 class="student-name"><?php echo htmlspecialchars($student['full_name']); ?></h3>
                                        <?php if ($student['degree']): ?>
                                            <p class="student-degree"><?php echo htmlspecialchars($student['degree']); ?></p>
                                        <?php endif; ?>
                                        <?php if ($student['cgpa']): ?>
                                            <div class="student-cgpa">
                                                <i class="fas fa-star"></i>
                                                CGPA: <?php echo number_format($student['cgpa'], 2); ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <?php if ($student['bio']): ?>
                                    <div class="student-bio">
                                        <?php echo htmlspecialchars(substr($student['bio'], 0, 150)); ?>
                                        <?php if (strlen($student['bio']) > 150): ?>...<?php endif; ?>
                                    </div>
                                <?php endif; ?>

                                <?php if ($student['research_interest']): ?>
                                    <div class="student-interests">
                                        <i class="fas fa-microscope"></i>
                                        <span><?php echo htmlspecialchars(substr($student['research_interest'], 0, 100)); ?>
                                            <?php if (strlen($student['research_interest']) > 100): ?>...<?php endif; ?></span>
                                    </div>
                                <?php endif; ?>

                                <?php if ($student['skills_list']): ?>
                                    <div class="student-skills">
                                        <?php
                                        $student_skills = explode(',', $student['skills_list']);
                                        foreach (array_slice($student_skills, 0, 5) as $skill):
                                        ?>
                                            <span class="skill-tag"><?php echo htmlspecialchars(trim($skill)); ?></span>
                                        <?php endforeach; ?>
                                        <?php if (count($student_skills) > 5): ?>
                                            <span class="skill-tag more">+<?php echo count($student_skills) - 5; ?></span>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>

                                <div class="student-footer">
                                    <div class="student-links">
                                        <?php if ($student['linkedin']): ?>
                                            <a href="<?php echo htmlspecialchars($student['linkedin']); ?>"
                                                target="_blank" class="social-link" title="LinkedIn">
                                                <i class="fab fa-linkedin"></i>
                                            </a>
                                        <?php endif; ?>
                                        <?php if ($student['github']): ?>
                                            <a href="<?php echo htmlspecialchars($student['github']); ?>"
                                                target="_blank" class="social-link" title="GitHub">
                                                <i class="fab fa-github"></i>
                                            </a>
                                        <?php endif; ?>
                                        <a href="mailto:<?php echo htmlspecialchars($student['email']); ?>"
                                            class="social-link" title="Email">
                                            <i class="fas fa-envelope"></i>
                                        </a>
                                    </div>

                                    <button class="view-profile-btn"
                                        data-student-id="<?php echo $student['student_id']; ?>">
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
                            if ($degree) $url_params[] = "degree=" . urlencode($degree);
                            if ($skills) $url_params[] = "skills=" . urlencode($skills);
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
                            <i class="fas fa-user-graduate"></i>
                        </div>
                        <h2>No Students Found</h2>
                        <p>
                            <?php if ($search || $degree || $skills): ?>
                                Try adjusting your filters or search terms.
                            <?php else: ?>
                                No students have registered yet.
                            <?php endif; ?>
                        </p>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <!-- Student Profile Modal -->
    <div id="studentModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <div id="modalBody">
                <!-- Student details will be loaded here -->
            </div>
        </div>
    </div>

    <!-- Mobile Menu Toggle -->
    <button class="mobile-menu-toggle" id="mobileMenuToggle">
        <i class="fas fa-bars"></i>
    </button>

    <script src="../assets/js/common.js"></script>
    <script src="../assets/js/students.js"></script>
</body>

</html>

<?php
$stmt->close();
$conn->close();
?>
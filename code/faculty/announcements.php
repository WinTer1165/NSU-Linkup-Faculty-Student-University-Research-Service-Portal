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

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

// Get total count
$count_query = "SELECT COUNT(*) as total FROM announcements WHERE is_published = TRUE";
$count_result = $conn->query($count_query);
$total_announcements = $count_result->fetch_assoc()['total'];
$total_pages = ceil($total_announcements / $limit);

// Get announcements with admin info
$query = "SELECT a.*, ad.full_name as admin_name 
          FROM announcements a 
          JOIN admins ad ON a.admin_id = ad.admin_id 
          WHERE a.is_published = TRUE 
          ORDER BY a.created_at DESC 
          LIMIT ? OFFSET ?";

$stmt = $conn->prepare($query);
$stmt->bind_param("ii", $limit, $offset);
$stmt->execute();
$result = $stmt->get_result();

// Get user info for header
$user_info = getUserInfo($conn, $user_id, $user_type);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Announcements - NSU LinkUp</title>
    <link rel="stylesheet" href="../assets/css/common.css">
    <link rel="stylesheet" href="../assets/css/announcements.css">
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
                <a href="announcements.php" class="active">
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
                        <i class="fas fa-bullhorn"></i>
                        Announcements
                    </h1>
                    <p>Stay updated with the latest news and information</p>
                </div>
                <div class="header-right">
                    <div class="user-info">
                        <span>Welcome, <?php echo htmlspecialchars($user_info['full_name']); ?></span>
                        <img src="<?php echo $user_info['profile_image'] ? '../assets/uploads/profiles/' . $user_info['profile_image'] : '../assets/images/default-avatar.png'; ?>"
                            alt="Profile" class="user-avatar">
                    </div>
                </div>
            </header>

            <!-- Announcements List -->
            <div class="announcements-container">
                <?php if ($result->num_rows > 0): ?>
                    <div class="announcements-grid">
                        <?php while ($announcement = $result->fetch_assoc()): ?>
                            <article class="announcement-card" data-id="<?php echo $announcement['announcement_id']; ?>">
                                <div class="announcement-header">
                                    <h2 class="announcement-title">
                                        <?php echo htmlspecialchars($announcement['title']); ?>
                                    </h2>
                                    <span class="announcement-date">
                                        <i class="far fa-calendar"></i>
                                        <?php echo date('M d, Y', strtotime($announcement['created_at'])); ?>
                                    </span>
                                </div>

                                <div class="announcement-content">
                                    <?php echo nl2br(htmlspecialchars($announcement['content'])); ?>
                                </div>

                                <div class="announcement-footer">
                                    <span class="announcement-author">
                                        <i class="fas fa-user-shield"></i>
                                        Posted by <?php echo htmlspecialchars($announcement['admin_name']); ?>
                                    </span>
                                    <span class="announcement-time">
                                        <i class="far fa-clock"></i>
                                        <?php echo date('h:i A', strtotime($announcement['created_at'])); ?>
                                    </span>
                                </div>
                            </article>
                        <?php endwhile; ?>
                    </div>

                    <!-- Pagination -->
                    <?php if ($total_pages > 1): ?>
                        <div class="pagination">
                            <a href="?page=1" class="page-link <?php echo $page == 1 ? 'disabled' : ''; ?>">
                                <i class="fas fa-angle-double-left"></i>
                            </a>
                            <a href="?page=<?php echo max(1, $page - 1); ?>"
                                class="page-link <?php echo $page == 1 ? 'disabled' : ''; ?>">
                                <i class="fas fa-angle-left"></i>
                            </a>

                            <?php
                            $start = max(1, $page - 2);
                            $end = min($total_pages, $page + 2);

                            for ($i = $start; $i <= $end; $i++):
                            ?>
                                <a href="?page=<?php echo $i; ?>"
                                    class="page-link <?php echo $i == $page ? 'active' : ''; ?>">
                                    <?php echo $i; ?>
                                </a>
                            <?php endfor; ?>

                            <a href="?page=<?php echo min($total_pages, $page + 1); ?>"
                                class="page-link <?php echo $page == $total_pages ? 'disabled' : ''; ?>">
                                <i class="fas fa-angle-right"></i>
                            </a>
                            <a href="?page=<?php echo $total_pages; ?>"
                                class="page-link <?php echo $page == $total_pages ? 'disabled' : ''; ?>">
                                <i class="fas fa-angle-double-right"></i>
                            </a>
                        </div>
                    <?php endif; ?>

                <?php else: ?>
                    <div class="no-data">
                        <div class="no-data-icon">
                            <i class="fas fa-bullhorn"></i>
                        </div>
                        <h2>No Announcements Found</h2>
                        <p>There are currently no announcements to display.</p>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <!-- Mobile Menu Toggle -->
    <button class="mobile-menu-toggle" id="mobileMenuToggle">
        <i class="fas fa-bars"></i>
    </button>

    <script src="../assets/js/common.js"></script>
    <script src="../assets/js/announcements.js"></script>
</body>

</html>

<?php
$stmt->close();
$conn->close();
?>
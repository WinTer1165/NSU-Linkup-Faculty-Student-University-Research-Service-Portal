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

// Handle delete action
if (isset($_POST['action']) && $_POST['action'] == 'delete' && isset($_POST['research_id'])) {
    $research_id = intval($_POST['research_id']);

    // Verify ownership
    $stmt = $conn->prepare("SELECT faculty_id FROM research_posts WHERE research_id = ?");
    $stmt->bind_param("i", $research_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $post = $result->fetch_assoc();
        if ($post['faculty_id'] == $faculty_id) {
            // Delete related applications first
            $stmt = $conn->prepare("DELETE FROM research_applications WHERE research_id = ?");
            $stmt->bind_param("i", $research_id);
            $stmt->execute();

            // Delete the research post
            $stmt = $conn->prepare("DELETE FROM research_posts WHERE research_id = ?");
            $stmt->bind_param("i", $research_id);
            if ($stmt->execute()) {
                $_SESSION['success'] = "Research post deleted successfully!";
            } else {
                $_SESSION['error'] = "Failed to delete research post.";
            }
        } else {
            $_SESSION['error'] = "Unauthorized action.";
        }
    }
    $stmt->close();

    header("Location: manage-posts.php");
    exit();
}

// Get all research posts
$stmt = $conn->prepare("
    SELECT r.*, 
           (SELECT COUNT(*) FROM research_applications WHERE research_id = r.research_id) as application_count,
           (SELECT COUNT(*) FROM research_applications WHERE research_id = r.research_id AND status = 'pending') as pending_count,
           (SELECT COUNT(*) FROM research_applications WHERE research_id = r.research_id AND status = 'accepted') as accepted_count,
           DATEDIFF(r.apply_deadline, CURDATE()) as days_left
    FROM research_posts r
    WHERE r.faculty_id = ?
    ORDER BY r.created_at DESC
");
$stmt->bind_param("i", $faculty_id);
$stmt->execute();
$research_posts = $stmt->get_result();
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Research Posts - NSU LinkUp</title>
    <link rel="stylesheet" href="../assets/css/common.css">
    <link rel="stylesheet" href="../assets/css/faculty-dashboard.css">
    <link rel="stylesheet" href="../assets/css/manage-posts.css">
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
                <a href="../faculty/manage-posts.php" class="active">
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
                    <h1>Manage Research Posts</h1>
                    <p>View and manage all your research opportunities</p>
                </div>
                <div class="header-right">
                    <a href="create-research.php" class="btn btn-primary">
                        <i class="fas fa-plus"></i>
                        Create New Post
                    </a>
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

            <!-- Filter Section -->
            <div class="filter-section">
                <div class="filter-group">
                    <label for="statusFilter">Filter by Status:</label>
                    <select id="statusFilter" class="filter-select">
                        <option value="all">All Posts</option>
                        <option value="active">Active</option>
                        <option value="expired">Expired</option>
                    </select>
                </div>
                <div class="filter-group">
                    <label for="sortBy">Sort by:</label>
                    <select id="sortBy" class="filter-select">
                        <option value="newest">Newest First</option>
                        <option value="oldest">Oldest First</option>
                        <option value="applications">Most Applications</option>
                        <option value="deadline">Deadline</option>
                    </select>
                </div>
                <div class="filter-group">
                    <div class="search-box">
                        <i class="fas fa-search"></i>
                        <input type="text" id="searchPosts" placeholder="Search posts...">
                    </div>
                </div>
            </div>

            <!-- Research Posts Grid -->
            <div class="posts-container">
                <?php if ($research_posts->num_rows > 0): ?>
                    <div class="posts-grid">
                        <?php while ($post = $research_posts->fetch_assoc()): ?>
                            <div class="post-card" data-status="<?php echo ($post['days_left'] >= 0 && $post['is_active']) ? 'active' : 'expired'; ?>"
                                data-created="<?php echo $post['created_at']; ?>"
                                data-applications="<?php echo $post['application_count']; ?>"
                                data-deadline="<?php echo $post['apply_deadline']; ?>">
                                <div class="post-header">
                                    <h3><?php echo htmlspecialchars($post['title']); ?></h3>
                                    <div class="post-status">
                                        <?php if ($post['is_active'] && $post['days_left'] >= 0): ?>
                                            <span class="badge badge-success">Active</span>
                                        <?php elseif ($post['days_left'] < 0): ?>
                                            <span class="badge badge-danger">Expired</span>
                                        <?php else: ?>
                                            <span class="badge badge-warning">Inactive</span>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <div class="post-details">
                                    <div class="detail-item">
                                        <i class="fas fa-building"></i>
                                        <span><?php echo htmlspecialchars($post['department']); ?></span>
                                    </div>
                                    <div class="detail-item">
                                        <i class="fas fa-calendar"></i>
                                        <span>Deadline: <?php echo date('M d, Y', strtotime($post['apply_deadline'])); ?></span>
                                    </div>
                                    <?php if ($post['days_left'] >= 0): ?>
                                        <div class="detail-item">
                                            <i class="fas fa-clock"></i>
                                            <span><?php echo $post['days_left']; ?> days left</span>
                                        </div>
                                    <?php endif; ?>
                                    <div class="detail-item">
                                        <i class="fas fa-hourglass-half"></i>
                                        <span>Duration: <?php echo htmlspecialchars($post['duration']); ?></span>
                                    </div>
                                    <?php if ($post['salary']): ?>
                                        <div class="detail-item">
                                            <i class="fas fa-dollar-sign"></i>
                                            <span><?php echo htmlspecialchars($post['salary']); ?></span>
                                        </div>
                                    <?php endif; ?>
                                    <?php if ($post['min_cgpa']): ?>
                                        <div class="detail-item">
                                            <i class="fas fa-graduation-cap"></i>
                                            <span>Min CGPA: <?php echo number_format($post['min_cgpa'], 2); ?></span>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <div class="post-stats">
                                    <div class="stat">
                                        <span class="stat-number"><?php echo $post['application_count']; ?></span>
                                        <span class="stat-label">Total Applications</span>
                                    </div>
                                    <div class="stat">
                                        <span class="stat-number"><?php echo $post['pending_count']; ?></span>
                                        <span class="stat-label">Pending</span>
                                    </div>
                                    <div class="stat">
                                        <span class="stat-number"><?php echo $post['accepted_count']; ?></span>
                                        <span class="stat-label">Accepted</span>
                                    </div>
                                </div>

                                <div class="post-description">
                                    <?php echo nl2br(htmlspecialchars(substr($post['description'], 0, 150))); ?>
                                    <?php if (strlen($post['description']) > 150): ?>...<?php endif; ?>
                                </div>

                                <?php if ($post['tags']): ?>
                                    <div class="post-tags">
                                        <?php
                                        $tags = explode(',', $post['tags']);
                                        foreach (array_slice($tags, 0, 3) as $tag):
                                        ?>
                                            <span class="tag"><?php echo htmlspecialchars(trim($tag)); ?></span>
                                        <?php endforeach; ?>
                                        <?php if (count($tags) > 3): ?>
                                            <span class="tag">+<?php echo count($tags) - 3; ?> more</span>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>

                                <div class="post-actions">
                                    <!-- <a href="../shared/research-detail.php?id=<?php echo $post['research_id']; ?>"
                                        class="btn btn-sm btn-outline" target="_blank">
                                        <i class="fas fa-eye"></i>
                                        View
                                    </a> -->
                                    <a href="applications.php?research_id=<?php echo $post['research_id']; ?>"
                                        class="btn btn-sm btn-primary">
                                        <i class="fas fa-file-alt"></i>
                                        Applications (<?php echo $post['application_count']; ?>)
                                    </a>
                                    <button class="btn btn-sm btn-danger"
                                        onclick="confirmDelete(<?php echo $post['research_id']; ?>, '<?php echo addslashes($post['title']); ?>')">
                                        <i class="fas fa-trash"></i>
                                        Delete
                                    </button>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-microscope"></i>
                        <h3>No Research Posts Yet</h3>
                        <p>You haven't created any research opportunities.</p>
                        <a href="create-research.php" class="btn btn-primary">
                            <i class="fas fa-plus"></i>
                            Create Your First Post
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <!-- Delete Confirmation Modal -->
    <div id="deleteModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Confirm Delete</h2>
                <span class="modal-close" onclick="closeDeleteModal()">&times;</span>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this research post?</p>
                <p class="post-title-confirm" id="postTitle"></p>
                <p class="warning-text">
                    <i class="fas fa-exclamation-triangle"></i>
                    This action cannot be undone. All applications for this post will also be deleted.
                </p>
            </div>
            <div class="modal-footer">
                <form method="POST" action="" id="deleteForm">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="research_id" id="deleteResearchId">
                    <button type="button" class="btn btn-outline" onclick="closeDeleteModal()">Cancel</button>
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-trash"></i>
                        Delete Post
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Mobile Menu Toggle -->
    <button class="mobile-menu-toggle" id="mobileMenuToggle">
        <i class="fas fa-bars"></i>
    </button>

    <script src="../assets/js/common.js"></script>
    <script src="../assets/js/manage-posts.js"></script>
</body>

</html>

<?php $conn->close(); ?>
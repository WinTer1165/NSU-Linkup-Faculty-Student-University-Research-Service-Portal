<?php
// admin/manage-users.php
require_once '../includes/config.php';
require_once '../includes/db_connect.php';
require_once '../includes/functions.php';
require_once '../includes/auth_check.php';

// Check if user is admin
if (get_user_type() !== 'admin') {
    redirect('index.php');
    exit();
}

// Handle AJAX requests
if (isset($_GET['ajax'])) {
    header('Content-Type: application/json');

    $action = $_GET['action'] ?? '';

    if ($action === 'toggle_ban') {
        $user_id = intval($_POST['user_id']);

        // Get current ban status
        $stmt = $db->prepare("SELECT is_banned FROM users WHERE user_id = ? AND user_type != 'admin'");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        $stmt->close();

        if ($user) {
            $new_status = $user['is_banned'] ? 0 : 1;

            $stmt = $db->prepare("UPDATE users SET is_banned = ? WHERE user_id = ?");
            $stmt->bind_param("ii", $new_status, $user_id);

            if ($stmt->execute()) {
                log_audit(
                    $new_status ? 'BAN_USER' : 'UNBAN_USER',
                    'users',
                    $user_id,
                    ['is_banned' => $user['is_banned']],
                    ['is_banned' => $new_status]
                );
                echo json_encode(['success' => true, 'is_banned' => $new_status]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to update ban status']);
            }
            $stmt->close();
        } else {
            echo json_encode(['success' => false, 'message' => 'User not found']);
        }
    } elseif ($action === 'delete_user') {
        $user_id = intval($_POST['user_id']);

        // Check if user exists and is not admin
        $stmt = $db->prepare("SELECT user_type FROM users WHERE user_id = ? AND user_type != 'admin'");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $stmt->close();

            // Delete user (cascade will handle related records)
            $stmt = $db->prepare("DELETE FROM users WHERE user_id = ?");
            $stmt->bind_param("i", $user_id);

            if ($stmt->execute()) {
                log_audit('DELETE_USER', 'users', $user_id);
                echo json_encode(['success' => true]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to delete user']);
            }
            $stmt->close();
        } else {
            echo json_encode(['success' => false, 'message' => 'User not found or is admin']);
        }
    } elseif ($action === 'assign_organizer') {
        $user_id = intval($_POST['user_id']);

        // Get user details
        $stmt = $db->prepare("
            SELECT u.*, 
                   CASE 
                       WHEN u.user_type = 'student' THEN s.full_name
                       WHEN u.user_type = 'faculty' THEN f.full_name
                   END as full_name
            FROM users u
            LEFT JOIN students s ON u.user_id = s.user_id
            LEFT JOIN faculty f ON u.user_id = f.user_id
            WHERE u.user_id = ? AND u.user_type IN ('student', 'faculty')
        ");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        $stmt->close();

        if ($user) {
            // Start transaction
            $db->begin_transaction();

            try {
                // Update user type
                $stmt = $db->prepare("UPDATE users SET user_type = 'organizer' WHERE user_id = ?");
                $stmt->bind_param("i", $user_id);
                $stmt->execute();
                $stmt->close();

                // Insert into organizers table
                $stmt = $db->prepare("INSERT INTO organizers (user_id, full_name) VALUES (?, ?)");
                $stmt->bind_param("is", $user_id, $user['full_name']);
                $stmt->execute();
                $stmt->close();

                $db->commit();

                log_audit(
                    'ASSIGN_ORGANIZER_ROLE',
                    'users',
                    $user_id,
                    ['user_type' => $user['user_type']],
                    ['user_type' => 'organizer']
                );

                echo json_encode(['success' => true]);
            } catch (Exception $e) {
                $db->rollback();
                echo json_encode(['success' => false, 'message' => 'Failed to assign organizer role']);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Invalid user']);
        }
    }
    exit();
}

// Filters
$filter_type = $_GET['type'] ?? '';
$filter_status = $_GET['status'] ?? '';
$search = $_GET['search'] ?? '';

// Pagination
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$per_page = 20;
$offset = ($page - 1) * $per_page;

// Build query conditions
$conditions = ["u.user_type != 'admin'"];
$params = [];
$types = '';

if ($filter_type) {
    $conditions[] = "u.user_type = ?";
    $params[] = $filter_type;
    $types .= 's';
}

if ($filter_status === 'active') {
    $conditions[] = "u.is_banned = 0 AND u.is_verified = 1";
} elseif ($filter_status === 'banned') {
    $conditions[] = "u.is_banned = 1";
} elseif ($filter_status === 'unverified') {
    $conditions[] = "u.is_verified = 0";
}

if ($search) {
    $conditions[] = "(u.email LIKE ? OR 
                     s.full_name LIKE ? OR 
                     f.full_name LIKE ? OR 
                     o.full_name LIKE ?)";
    $search_param = "%$search%";
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
    $types .= 'ssss';
}

$where_clause = 'WHERE ' . implode(' AND ', $conditions);

// Get total users count
$count_query = "
    SELECT COUNT(DISTINCT u.user_id) as total 
    FROM users u
    LEFT JOIN students s ON u.user_id = s.user_id
    LEFT JOIN faculty f ON u.user_id = f.user_id
    LEFT JOIN organizers o ON u.user_id = o.user_id
    $where_clause
";

$stmt = $db->prepare($count_query);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$total_users = $stmt->get_result()->fetch_assoc()['total'];
$stmt->close();

$total_pages = ceil($total_users / $per_page);

// Get users
$users_query = "
    SELECT u.*,
           CASE 
               WHEN u.user_type = 'student' THEN s.full_name
               WHEN u.user_type = 'faculty' THEN f.full_name
               WHEN u.user_type = 'organizer' THEN o.full_name
           END as full_name,
           CASE 
               WHEN u.user_type = 'student' THEN s.profile_image
               WHEN u.user_type = 'faculty' THEN f.profile_image
               WHEN u.user_type = 'organizer' THEN NULL
           END as profile_image,
           s.degree, s.cgpa, s.university,
           f.title as faculty_title, f.office,
           o.organization
    FROM users u
    LEFT JOIN students s ON u.user_id = s.user_id
    LEFT JOIN faculty f ON u.user_id = f.user_id
    LEFT JOIN organizers o ON u.user_id = o.user_id
    $where_clause
    ORDER BY u.created_at DESC
    LIMIT ? OFFSET ?
";

// Add pagination params
$params[] = $per_page;
$params[] = $offset;
$types .= 'ii';

$stmt = $db->prepare($users_query);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$users = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Get statistics
$stmt = $db->prepare("
    SELECT 
        COUNT(CASE WHEN user_type = 'student' THEN 1 END) as students,
        COUNT(CASE WHEN user_type = 'faculty' THEN 1 END) as faculty,
        COUNT(CASE WHEN user_type = 'organizer' THEN 1 END) as organizers,
        COUNT(CASE WHEN is_banned = 1 THEN 1 END) as banned,
        COUNT(CASE WHEN is_verified = 0 AND user_type != 'student' THEN 1 END) as unverified
    FROM users
    WHERE user_type != 'admin'
");
$stmt->execute();
$stats = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Page settings
$page_title = 'Manage Users';
$page_css = ['manage-users.css'];
$page_js = ['manage-users.js'];

// Include header
require_once '../includes/header.php';
?>

<div class="manage-users-container">
    <div class="page-header">
        <h1 class="page-title">Manage Users</h1>
        <div class="header-stats">
            <div class="stat-item">
                <span class="stat-icon">👨‍🎓</span>
                <div class="stat-content">
                    <span class="stat-value"><?php echo $stats['students']; ?></span>
                    <span class="stat-label">Students</span>
                </div>
            </div>
            <div class="stat-item">
                <span class="stat-icon">👨‍🏫</span>
                <div class="stat-content">
                    <span class="stat-value"><?php echo $stats['faculty']; ?></span>
                    <span class="stat-label">Faculty</span>
                </div>
            </div>
            <div class="stat-item">
                <span class="stat-icon">🎯</span>
                <div class="stat-content">
                    <span class="stat-value"><?php echo $stats['organizers']; ?></span>
                    <span class="stat-label">Organizers</span>
                </div>
            </div>
            <div class="stat-item danger">
                <span class="stat-icon">🚫</span>
                <div class="stat-content">
                    <span class="stat-value"><?php echo $stats['banned']; ?></span>
                    <span class="stat-label">Banned</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="filters-section">
        <form method="GET" class="filters-form">
            <div class="filter-group">
                <label class="filter-label">User Type</label>
                <select name="type" class="filter-select">
                    <option value="">All Types</option>
                    <option value="student" <?php echo $filter_type === 'student' ? 'selected' : ''; ?>>Students</option>
                    <option value="faculty" <?php echo $filter_type === 'faculty' ? 'selected' : ''; ?>>Faculty</option>
                    <!-- <option value="organizer" <?php echo $filter_type === 'organizer' ? 'selected' : ''; ?>>Organizers</option> -->
                </select>
            </div>

            <div class="filter-group">
                <label class="filter-label">Status</label>
                <select name="status" class="filter-select">
                    <option value="">All Status</option>
                    <option value="active" <?php echo $filter_status === 'active' ? 'selected' : ''; ?>>Active</option>
                    <option value="banned" <?php echo $filter_status === 'banned' ? 'selected' : ''; ?>>Banned</option>
                    <option value="unverified" <?php echo $filter_status === 'unverified' ? 'selected' : ''; ?>>Unverified</option>
                </select>
            </div>

            <div class="filter-group flex-grow">
                <label class="filter-label">Search</label>
                <input type="text"
                    name="search"
                    class="filter-input"
                    placeholder="Search by name or email..."
                    value="<?php echo htmlspecialchars($search); ?>">
            </div>

            <div class="filter-actions">
                <button type="submit" class="btn btn-primary">Search</button>
                <a href="manage-users.php" class="btn btn-secondary">Clear</a>
            </div>
        </form>
    </div>

    <!-- Users Table -->
    <div class="users-table-container">
        <?php if (empty($users)): ?>
            <div class="empty-state">
                <div class="empty-icon">👥</div>
                <h3>No users found</h3>
                <p>Try adjusting your filters.</p>
            </div>
        <?php else: ?>
            <table class="users-table">
                <thead>
                    <tr>
                        <th>User</th>
                        <th>Type</th>
                        <th>Details</th>
                        <th>Status</th>
                        <th>Joined</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                        <tr class="user-row" data-user-id="<?php echo $user['user_id']; ?>">
                            <td class="user-info-cell">
                                <div class="user-info">
                                    <div class="user-avatar">
                                        <?php if ($user['profile_image']): ?>
                                            <img src="<?php echo SITE_URL; ?>/assets/uploads/profiles/<?php echo $user['profile_image']; ?>"
                                                alt="Profile">
                                        <?php else: ?>
                                            <div class="avatar-placeholder">
                                                <?php echo strtoupper(substr($user['full_name'] ?? 'U', 0, 1)); ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="user-details">
                                        <h4 class="user-name"><?php echo htmlspecialchars($user['full_name'] ?? 'Unknown'); ?></h4>
                                        <p class="user-email"><?php echo htmlspecialchars($user['email']); ?></p>
                                    </div>
                                </div>
                            </td>
                            <td class="user-type">
                                <span class="type-badge type-<?php echo $user['user_type']; ?>">
                                    <?php echo ucfirst($user['user_type']); ?>
                                </span>
                            </td>
                            <td class="user-meta">
                                <?php if ($user['user_type'] === 'student'): ?>
                                    <div class="meta-item"><?php echo htmlspecialchars($user['degree'] ?? 'N/A'); ?></div>
                                    <?php if ($user['cgpa']): ?>
                                        <div class="meta-item">CGPA: <?php echo $user['cgpa']; ?></div>
                                    <?php endif; ?>
                                <?php elseif ($user['user_type'] === 'faculty'): ?>
                                    <div class="meta-item"><?php echo htmlspecialchars($user['faculty_title'] ?? 'N/A'); ?></div>
                                    <?php if ($user['office']): ?>
                                        <div class="meta-item">Office: <?php echo htmlspecialchars($user['office']); ?></div>
                                    <?php endif; ?>
                                <?php elseif ($user['user_type'] === 'organizer'): ?>
                                    <div class="meta-item"><?php echo htmlspecialchars($user['organization'] ?? 'N/A'); ?></div>
                                <?php endif; ?>
                            </td>
                            <td class="user-status">
                                <?php if ($user['is_banned']): ?>
                                    <span class="status-badge banned">Banned</span>
                                <?php elseif (!$user['is_verified'] && $user['user_type'] !== 'student'): ?>
                                    <span class="status-badge unverified">Unverified</span>
                                <?php else: ?>
                                    <span class="status-badge active">Active</span>
                                <?php endif; ?>
                            </td>
                            <td class="user-joined">
                                <?php echo format_date($user['created_at'], 'M d, Y'); ?>
                            </td>
                            <td class="user-actions">
                                <div class="action-buttons">

                                    <?php if ($user['is_banned']): ?>
                                        <button class="btn-icon btn-unban"
                                            onclick="toggleBan(<?php echo $user['user_id']; ?>, false)"
                                            title="Unban User">
                                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <circle cx="12" cy="12" r="10"></circle>
                                                <path d="M8 12h8"></path>
                                            </svg>
                                        </button>
                                    <?php else: ?>
                                        <button class="btn-icon btn-ban"
                                            onclick="toggleBan(<?php echo $user['user_id']; ?>, true)"
                                            title="Ban User">
                                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <circle cx="12" cy="12" r="10"></circle>
                                                <line x1="4.93" y1="4.93" x2="19.07" y2="19.07"></line>
                                            </svg>
                                        </button>
                                    <?php endif; ?>

                                    <!-- <?php if ($user['user_type'] === 'student' || $user['user_type'] === 'faculty'): ?>
                                        <button class="btn-icon btn-assign"
                                            onclick="assignOrganizerRole(<?php echo $user['user_id']; ?>, '<?php echo htmlspecialchars($user['full_name']); ?>')"
                                            title="Assign Organizer Role">
                                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                                                <circle cx="8.5" cy="7" r="4"></circle>
                                                <polyline points="17 11 19 13 23 9"></polyline>
                                            </svg>
                                        </button>
                                    <?php endif; ?> -->

                                    <button class="btn-icon btn-delete"
                                        onclick="deleteUser(<?php echo $user['user_id']; ?>, '<?php echo htmlspecialchars($user['full_name']); ?>')"
                                        title="Delete User">
                                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <polyline points="3 6 5 6 21 6"></polyline>
                                            <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>

    <!-- Pagination -->
    <?php if ($total_pages > 1): ?>
        <div class="pagination">
            <?php if ($page > 1): ?>
                <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page - 1])); ?>"
                    class="pagination-link">Previous</a>
            <?php endif; ?>

            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <?php if ($i == 1 || $i == $total_pages || ($i >= $page - 2 && $i <= $page + 2)): ?>
                    <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $i])); ?>"
                        class="pagination-link <?php echo $i == $page ? 'active' : ''; ?>">
                        <?php echo $i; ?>
                    </a>
                <?php elseif ($i == $page - 3 || $i == $page + 3): ?>
                    <span class="pagination-dots">...</span>
                <?php endif; ?>
            <?php endfor; ?>

            <?php if ($page < $total_pages): ?>
                <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page + 1])); ?>"
                    class="pagination-link">Next</a>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

<?php require_once '../includes/footer.php'; ?>
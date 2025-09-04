<?php
// admin/contact-queries.php
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

    if ($action === 'mark_read') {
        $query_id = intval($_POST['query_id']);

        $stmt = $db->prepare("UPDATE contact_queries SET is_read = 1 WHERE query_id = ?");
        $stmt->bind_param("i", $query_id);

        if ($stmt->execute()) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to mark as read']);
        }
        $stmt->close();
    } elseif ($action === 'mark_unread') {
        $query_id = intval($_POST['query_id']);

        $stmt = $db->prepare("UPDATE contact_queries SET is_read = 0 WHERE query_id = ?");
        $stmt->bind_param("i", $query_id);

        if ($stmt->execute()) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to mark as unread']);
        }
        $stmt->close();
    } elseif ($action === 'delete') {
        $query_id = intval($_POST['query_id']);

        $stmt = $db->prepare("DELETE FROM contact_queries WHERE query_id = ?");
        $stmt->bind_param("i", $query_id);

        if ($stmt->execute()) {
            log_audit('DELETE_CONTACT_QUERY', 'contact_queries', $query_id);
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to delete query']);
        }
        $stmt->close();
    } elseif ($action === 'get_details') {
        $query_id = intval($_GET['query_id']);

        $stmt = $db->prepare("SELECT * FROM contact_queries WHERE query_id = ?");
        $stmt->bind_param("i", $query_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $query = $result->fetch_assoc();
        $stmt->close();

        if ($query) {
            // Mark as read when viewed
            if (!$query['is_read']) {
                $stmt = $db->prepare("UPDATE contact_queries SET is_read = 1 WHERE query_id = ?");
                $stmt->bind_param("i", $query_id);
                $stmt->execute();
                $stmt->close();
            }

            echo json_encode(['success' => true, 'data' => $query]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Query not found']);
        }
    }
    exit();
}

// Filters
$filter_status = $_GET['status'] ?? 'all';
$filter_subject = $_GET['subject'] ?? '';
$search = $_GET['search'] ?? '';

// Pagination
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$per_page = 20;
$offset = ($page - 1) * $per_page;

// Build query conditions
$conditions = [];
$params = [];
$types = '';

if ($filter_status === 'read') {
    $conditions[] = "is_read = 1";
} elseif ($filter_status === 'unread') {
    $conditions[] = "is_read = 0";
}

if ($filter_subject && $filter_subject !== 'all') {
    $conditions[] = "subject = ?";
    $params[] = $filter_subject;
    $types .= 's';
}

if ($search) {
    $conditions[] = "(name LIKE ? OR email LIKE ? OR message LIKE ?)";
    $search_param = "%$search%";
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
    $types .= 'sss';
}

$where_clause = !empty($conditions) ? 'WHERE ' . implode(' AND ', $conditions) : '';

// Get total queries count
$count_query = "SELECT COUNT(*) as total FROM contact_queries $where_clause";
$stmt = $db->prepare($count_query);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$total_queries = $stmt->get_result()->fetch_assoc()['total'];
$stmt->close();

$total_pages = ceil($total_queries / $per_page);

// Get queries
$queries_query = "
    SELECT * FROM contact_queries 
    $where_clause
    ORDER BY is_read ASC, created_at DESC
    LIMIT ? OFFSET ?
";

// Add pagination params
$params[] = $per_page;
$params[] = $offset;
$types .= 'ii';

$stmt = $db->prepare($queries_query);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$queries = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Get statistics
$stmt = $db->prepare("
    SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN is_read = 0 THEN 1 ELSE 0 END) as unread,
        SUM(CASE WHEN is_read = 1 THEN 1 ELSE 0 END) as read_count
    FROM contact_queries
");
$stmt->execute();
$stats = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Get unique subjects for filter
$stmt = $db->prepare("SELECT DISTINCT subject FROM contact_queries ORDER BY subject");
$stmt->execute();
$subjects = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Page settings
$page_title = 'Contact Queries';
$page_css = ['contact-queries.css'];
$page_js = ['contact-queries.js'];

// Include header
require_once '../includes/header.php';
?>

<div class="contact-queries-container">
    <div class="page-header">
        <h1 class="page-title">Contact Queries</h1>
        <div class="header-stats">
            <div class="stat-badge unread">
                <span class="stat-number"><?php echo $stats['unread']; ?></span>
                <span class="stat-label">Unread</span>
            </div>
            <div class="stat-badge total">
                <span class="stat-number"><?php echo $stats['total']; ?></span>
                <span class="stat-label">Total</span>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="filters-section">
        <form method="GET" class="filters-form">
            <div class="filter-group">
                <label class="filter-label">Status</label>
                <select name="status" class="filter-select">
                    <option value="all" <?php echo $filter_status === 'all' ? 'selected' : ''; ?>>All Messages</option>
                    <option value="unread" <?php echo $filter_status === 'unread' ? 'selected' : ''; ?>>Unread Only</option>
                    <option value="read" <?php echo $filter_status === 'read' ? 'selected' : ''; ?>>Read Only</option>
                </select>
            </div>

            <div class="filter-group">
                <label class="filter-label">Subject</label>
                <select name="subject" class="filter-select">
                    <option value="">All Subjects</option>
                    <?php foreach ($subjects as $subject): ?>
                        <option value="<?php echo htmlspecialchars($subject['subject']); ?>"
                            <?php echo $filter_subject === $subject['subject'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($subject['subject']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="filter-group">
                <label class="filter-label">Search</label>
                <input type="text"
                    name="search"
                    class="filter-input"
                    placeholder="Search by name, email, or message..."
                    value="<?php echo htmlspecialchars($search); ?>">
            </div>

            <div class="filter-actions">
                <button type="submit" class="btn btn-primary">Apply Filters</button>
                <a href="contact-queries.php" class="btn btn-secondary">Clear</a>
            </div>
        </form>
    </div>

    <!-- Queries List -->
    <div class="queries-list">
        <?php if (empty($queries)): ?>
            <div class="empty-state">
                <div class="empty-icon">📧</div>
                <h3>No messages found</h3>
                <p>Try adjusting your filters or check back later.</p>
            </div>
        <?php else: ?>
            <?php foreach ($queries as $query): ?>
                <div class="query-card <?php echo !$query['is_read'] ? 'unread' : ''; ?>"
                    data-query-id="<?php echo $query['query_id']; ?>">
                    <div class="query-header">
                        <div class="query-sender">
                            <div class="sender-avatar">
                                <?php echo strtoupper(substr($query['name'], 0, 1)); ?>
                            </div>
                            <div class="sender-info">
                                <h3 class="sender-name"><?php echo htmlspecialchars($query['name']); ?></h3>
                                <p class="sender-email"><?php echo htmlspecialchars($query['email']); ?></p>
                            </div>
                        </div>
                        <div class="query-meta">
                            <span class="query-subject"><?php echo htmlspecialchars($query['subject']); ?></span>
                            <span class="query-date"><?php echo format_date($query['created_at'], 'M d, Y'); ?></span>
                        </div>
                    </div>

                    <div class="query-content">
                        <p class="query-message"><?php echo nl2br(htmlspecialchars($query['message'])); ?></p>
                    </div>

                    <div class="query-actions">
                        <button class="btn-action btn-view" onclick="viewQuery(<?php echo $query['query_id']; ?>)">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                <circle cx="12" cy="12" r="3"></circle>
                            </svg>
                            View Details
                        </button>

                        <?php if (!$query['is_read']): ?>
                            <button class="btn-action btn-mark-read" onclick="markAsRead(<?php echo $query['query_id']; ?>)">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                                    <polyline points="22 4 12 14.01 9 11.01"></polyline>
                                </svg>
                                Mark as Read
                            </button>
                        <?php else: ?>
                            <button class="btn-action btn-mark-unread" onclick="markAsUnread(<?php echo $query['query_id']; ?>)">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <circle cx="12" cy="12" r="10"></circle>
                                    <line x1="8" y1="12" x2="16" y2="12"></line>
                                </svg>
                                Mark as Unread
                            </button>
                        <?php endif; ?>

                        <button class="btn-action btn-reply" onclick="replyToQuery('<?php echo htmlspecialchars($query['email']); ?>', '<?php echo htmlspecialchars($query['subject']); ?>')">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polyline points="9 17 4 12 9 7"></polyline>
                                <path d="M20 18v-2a4 4 0 0 0-4-4H4"></path>
                            </svg>
                            Reply
                        </button>

                        <button class="btn-action btn-delete" onclick="deleteQuery(<?php echo $query['query_id']; ?>)">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polyline points="3 6 5 6 21 6"></polyline>
                                <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                            </svg>
                            Delete
                        </button>
                    </div>
                </div>
            <?php endforeach; ?>
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

<!-- Query Details Modal -->
<div id="queryDetailsModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2 class="modal-title">Message Details</h2>
            <span class="modal-close" onclick="closeDetailsModal()">&times;</span>
        </div>
        <div id="queryDetailsContent" class="query-details-content">
            <!-- Content will be loaded here -->
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
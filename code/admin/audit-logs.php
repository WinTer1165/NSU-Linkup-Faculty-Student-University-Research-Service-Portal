<?php
// admin/audit-logs.php
require_once '../includes/config.php';
require_once '../includes/db_connect.php';
require_once '../includes/functions.php';
require_once '../includes/auth_check.php';

// Check if user is admin
if (get_user_type() !== 'admin') {
    redirect('index.php');
    exit();
}

// Filters
$filter_action = $_GET['action'] ?? '';
$filter_table = $_GET['table'] ?? '';
$filter_user = $_GET['user'] ?? '';
$filter_date = $_GET['date'] ?? '';
$search = $_GET['search'] ?? '';

// Pagination
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$per_page = 50;
$offset = ($page - 1) * $per_page;

// Build query conditions
$conditions = [];
$params = [];
$types = '';

if ($filter_action) {
    $conditions[] = "al.action = ?";
    $params[] = $filter_action;
    $types .= 's';
}

if ($filter_table) {
    $conditions[] = "al.table_affected = ?";
    $params[] = $filter_table;
    $types .= 's';
}

if ($filter_user) {
    $conditions[] = "al.user_id = ?";
    $params[] = intval($filter_user); 
    $types .= 'i';
}

if ($filter_date) {
    $conditions[] = "DATE(al.created_at) = ?";
    $params[] = $filter_date;
    $types .= 's';
}

if ($search) {
    $conditions[] = "(u.email LIKE ? OR al.action LIKE ? OR al.table_affected LIKE ? OR al.ip_address LIKE ?)";
    $search_param = "%$search%";
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
    $types .= 'ssss';
}

$where_clause = !empty($conditions) ? 'WHERE ' . implode(' AND ', $conditions) : '';

// Get total logs count
$count_query = "
    SELECT COUNT(*) as total 
    FROM audit_logs al
    LEFT JOIN users u ON al.user_id = u.user_id
    $where_clause
";

if (!empty($params)) {
    $stmt = $db->prepare($count_query);
    $stmt->bind_param($types, ...$params);
} else {
    $stmt = $db->prepare($count_query);
}
$stmt->execute();
$total_logs = $stmt->get_result()->fetch_assoc()['total'];
$stmt->close();

$total_pages = ceil($total_logs / $per_page);

// Get logs
$logs_query = "
    SELECT al.*, 
           u.email as user_email,
           CASE 
               WHEN u.user_type = 'student' THEN s.full_name
               WHEN u.user_type = 'faculty' THEN f.full_name
               WHEN u.user_type = 'organizer' THEN o.full_name
               WHEN u.user_type = 'admin' THEN a.full_name
           END as user_name,
           u.user_type
    FROM audit_logs al
    LEFT JOIN users u ON al.user_id = u.user_id
    LEFT JOIN students s ON u.user_id = s.user_id
    LEFT JOIN faculty f ON u.user_id = f.user_id
    LEFT JOIN organizers o ON u.user_id = o.user_id
    LEFT JOIN admins a ON u.user_id = a.user_id
    $where_clause
    ORDER BY al.created_at DESC
    LIMIT ? OFFSET ?
";

// Create new arrays for the full query parameters
$full_params = $params;
$full_params[] = $per_page;
$full_params[] = $offset;
$full_types = $types . 'ii';

if (!empty($full_params)) {
    $stmt = $db->prepare($logs_query);
    $stmt->bind_param($full_types, ...$full_params);
} else {
    $stmt = $db->prepare($logs_query);
    $stmt->bind_param('ii', $per_page, $offset);
}
$stmt->execute();
$logs = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Get unique actions for filter dropdown
$stmt = $db->prepare("SELECT DISTINCT action FROM audit_logs ORDER BY action");
$stmt->execute();
$actions = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Get unique tables for filter dropdown
$stmt = $db->prepare("SELECT DISTINCT table_affected FROM audit_logs WHERE table_affected IS NOT NULL ORDER BY table_affected");
$stmt->execute();
$tables = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Get users for filter dropdown
$stmt = $db->prepare("
    SELECT DISTINCT al.user_id, u.email, u.user_type
    FROM audit_logs al
    JOIN users u ON al.user_id = u.user_id
    ORDER BY u.email
");
$stmt->execute();
$users = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Page settings
$page_title = 'Audit Logs';
$page_css = ['audit-logs.css'];
$page_js = ['audit-logs.js'];

// Include header
require_once '../includes/header.php';
?>

<div class="audit-logs-container">
    <div class="page-header">
        <h1 class="page-title">Audit Logs</h1>
        <div class="header-actions">
            <!-- <button class="btn btn-secondary" onclick="exportLogs()">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                    <polyline points="7 10 12 15 17 10"></polyline>
                    <line x1="12" y1="15" x2="12" y2="3"></line>
                </svg>
                Export CSV
            </button> -->
            <button class="btn btn-primary" onclick="location.reload()">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="23 4 23 10 17 10"></polyline>
                    <polyline points="1 20 1 14 7 14"></polyline>
                    <path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"></path>
                </svg>
                Refresh
            </button>
        </div>
    </div>

    <!-- Filters -->
    <div class="filters-section">
        <form method="GET" class="filters-form" action="audit-logs.php">
            <div class="filter-group">
                <label class="filter-label">Action</label>
                <select name="action" class="filter-select">
                    <option value="">All Actions</option>
                    <?php foreach ($actions as $action): ?>
                        <option value="<?php echo htmlspecialchars($action['action']); ?>"
                            <?php echo $filter_action === $action['action'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($action['action']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="filter-group">
                <label class="filter-label">Table</label>
                <select name="table" class="filter-select">
                    <option value="">All Tables</option>
                    <?php foreach ($tables as $table): ?>
                        <option value="<?php echo htmlspecialchars($table['table_affected']); ?>"
                            <?php echo $filter_table === $table['table_affected'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($table['table_affected']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="filter-group">
                <label class="filter-label">User</label>
                <select name="user" class="filter-select">
                    <option value="">All Users</option>
                    <?php foreach ($users as $user): ?>
                        <option value="<?php echo $user['user_id']; ?>"
                            <?php echo $filter_user == $user['user_id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($user['email']); ?> (<?php echo ucfirst($user['user_type']); ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="filter-group">
                <label class="filter-label">Date</label>
                <input type="date"
                    name="date"
                    class="filter-input"
                    value="<?php echo htmlspecialchars($filter_date); ?>">
            </div>

            <div class="filter-group">
                <label class="filter-label">Search</label>
                <input type="text"
                    name="search"
                    class="filter-input"
                    placeholder="Search logs..."
                    value="<?php echo htmlspecialchars($search); ?>">
            </div>

            <div class="filter-actions">
                <button type="submit" class="btn btn-primary">Apply Filters</button>
                <a href="audit-logs.php" class="btn btn-secondary">Clear</a>
            </div>
        </form>
    </div>

    <!-- Statistics -->
    <div class="log-stats">
        <div class="stat-item">
            <span class="stat-label">Total Logs:</span>
            <span class="stat-value"><?php echo number_format($total_logs); ?></span>
        </div>
        <div class="stat-item">
            <span class="stat-label">Today's Logs:</span>
            <?php
            $stmt = $db->prepare("SELECT COUNT(*) as today FROM audit_logs WHERE DATE(created_at) = CURDATE()");
            $stmt->execute();
            $today_count = $stmt->get_result()->fetch_assoc()['today'];
            $stmt->close();
            ?>
            <span class="stat-value"><?php echo number_format($today_count); ?></span>
        </div>
        <div class="stat-item">
            <span class="stat-label">This Week:</span>
            <?php
            $stmt = $db->prepare("SELECT COUNT(*) as week FROM audit_logs WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)");
            $stmt->execute();
            $week_count = $stmt->get_result()->fetch_assoc()['week'];
            $stmt->close();
            ?>
            <span class="stat-value"><?php echo number_format($week_count); ?></span>
        </div>
    </div>

    <!-- Logs Table -->
    <div class="logs-table-container">
        <?php if (empty($logs)): ?>
            <div class="empty-state">
                <div class="empty-icon">📋</div>
                <h3>No logs found</h3>
                <p>Try adjusting your filters or check back later.</p>
            </div>
        <?php else: ?>
            <table class="logs-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Timestamp</th>
                        <th>User</th>
                        <th>Action</th>
                        <th>Table</th>
                        <th>Record ID</th>
                        <th>IP Address</th>
                        <th>Details</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($logs as $log): ?>
                        <tr class="log-row" data-log-id="<?php echo $log['log_id']; ?>">
                            <td class="log-id">#<?php echo $log['log_id']; ?></td>
                            <td class="log-timestamp">
                                <div class="timestamp-full"><?php echo format_date($log['created_at'], 'M d, Y h:i:s A'); ?></div>
                                <div class="timestamp-relative"><?php echo time_ago($log['created_at']); ?></div>
                            </td>
                            <td class="log-user">
                                <?php if ($log['user_id']): ?>
                                    <div class="user-info">
                                        <span class="user-name"><?php echo htmlspecialchars($log['user_name'] ?? 'Unknown'); ?></span>
                                        <span class="user-email"><?php echo htmlspecialchars($log['user_email']); ?></span>
                                        <span class="user-type badge badge-<?php echo $log['user_type']; ?>"><?php echo ucfirst($log['user_type']); ?></span>
                                    </div>
                                <?php else: ?>
                                    <span class="system-user">System</span>
                                <?php endif; ?>
                            </td>
                            <td class="log-action">
                                <span class="action-badge action-<?php echo strtolower($log['action']); ?>">
                                    <?php echo htmlspecialchars($log['action']); ?>
                                </span>
                            </td>
                            <td class="log-table"><?php echo htmlspecialchars($log['table_affected'] ?? '-'); ?></td>
                            <td class="log-record"><?php echo $log['record_id'] ?? '-'; ?></td>
                            <td class="log-ip"><?php echo htmlspecialchars($log['ip_address']); ?></td>
                            <td class="log-details">
                                <?php if ($log['old_values'] || $log['new_values']): ?>
                                    <button class="btn-details" onclick="showLogDetails(<?php echo $log['log_id']; ?>)">
                                        View Details
                                    </button>
                                <?php else: ?>
                                    -
                                <?php endif; ?>
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

<!-- Log Details Modal -->
<div id="logDetailsModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2 class="modal-title">Log Details</h2>
            <span class="modal-close" onclick="closeDetailsModal()">&times;</span>
        </div>
        <div id="logDetailsContent" class="log-details-content">
            <!-- Content will be loaded here -->
        </div>
    </div>
</div>

<!-- Store log data for JavaScript -->
<script>
    window.logsData = <?php echo json_encode($logs); ?>;
</script>

<?php
// Helper function for relative time
function time_ago($datetime)
{
    $time = strtotime($datetime);
    $now = time();
    $diff = $now - $time;

    if ($diff < 60) {
        return 'Just now';
    } elseif ($diff < 3600) {
        return floor($diff / 60) . ' minutes ago';
    } elseif ($diff < 86400) {
        return floor($diff / 3600) . ' hours ago';
    } elseif ($diff < 604800) {
        return floor($diff / 86400) . ' days ago';
    } else {
        return format_date($datetime);
    }
}

require_once '../includes/footer.php';
?>
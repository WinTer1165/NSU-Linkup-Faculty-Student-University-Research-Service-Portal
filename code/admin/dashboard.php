<?php
// admin/dashboard.php
require_once '../includes/auth_check.php';
require_once '../includes/db_connect.php';

// Page settings
$page_title = 'Admin Dashboard';
$page_css = ['admin-dashboard.css'];
$page_js = ['admin-dashboard.js'];

// Get statistics
$stats = [];

// Total users by type
$user_types = ['student', 'faculty', 'organizer'];
foreach ($user_types as $type) {
    $stmt = $db->prepare("SELECT COUNT(*) as count FROM users WHERE user_type = ?");
    $stmt->bind_param("s", $type);
    $stmt->execute();
    $stats[$type . '_count'] = $stmt->get_result()->fetch_assoc()['count'];
    $stmt->close();
}

// Pending verifications
$stmt = $db->prepare("SELECT COUNT(*) as count FROM users WHERE is_verified = 0 AND user_type IN ('faculty', 'organizer')");
$stmt->execute();
$stats['pending_verifications'] = $stmt->get_result()->fetch_assoc()['count'];
$stmt->close();

// Total research posts
$stmt = $db->prepare("SELECT COUNT(*) as count FROM research_posts");
$stmt->execute();
$stats['total_research'] = $stmt->get_result()->fetch_assoc()['count'];
$stmt->close();

// Total events
$stmt = $db->prepare("SELECT COUNT(*) as count FROM events");
$stmt->execute();
$stats['total_events'] = $stmt->get_result()->fetch_assoc()['count'];
$stmt->close();

// Total announcements
$stmt = $db->prepare("SELECT COUNT(*) as count FROM announcements");
$stmt->execute();
$stats['total_announcements'] = $stmt->get_result()->fetch_assoc()['count'];
$stmt->close();

// Recent activities (audit logs)
$stmt = $db->prepare("
    SELECT al.*, u.email, u.user_type 
    FROM audit_logs al 
    LEFT JOIN users u ON al.user_id = u.user_id 
    ORDER BY al.created_at DESC 
    LIMIT 10
");
$stmt->execute();
$recent_activities = $stmt->get_result();
$stmt->close();

// Pending contact queries
$stmt = $db->prepare("SELECT COUNT(*) as count FROM contact_queries WHERE is_read = 0");
$stmt->execute();
$stats['unread_queries'] = $stmt->get_result()->fetch_assoc()['count'];
$stmt->close();

// User growth data for chart (last 30 days)
$growth_data = [];
for ($i = 29; $i >= 0; $i--) {
    $date = date('Y-m-d', strtotime("-$i days"));
    $stmt = $db->prepare("SELECT COUNT(*) as count FROM users WHERE DATE(created_at) = ?");
    $stmt->bind_param("s", $date);
    $stmt->execute();
    $count = $stmt->get_result()->fetch_assoc()['count'];
    $growth_data[] = [
        'date' => date('M d', strtotime($date)),
        'count' => $count
    ];
    $stmt->close();
}

// Include header
require_once '../includes/header.php';
?>

<div class="admin-header">
    <h1>Admin Dashboard</h1>
    <p>System Overview and Management</p>
</div>

<!-- Stats Grid -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon" style="background: var(--gradient-primary);">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                <circle cx="9" cy="7" r="4"></circle>
                <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
            </svg>
        </div>
        <div class="stat-content">
            <h3>Total Students</h3>
            <p class="stat-value"><?php echo number_format($stats['student_count']); ?></p>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon" style="background: var(--gradient-secondary);">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M22 10v6M2 10l10-5 10 5-10 5z"></path>
                <path d="M6 12v5c3 3 9 3 12 0v-5"></path>
            </svg>
        </div>
        <div class="stat-content">
            <h3>Total Faculty</h3>
            <p class="stat-value"><?php echo number_format($stats['faculty_count']); ?></p>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon" style="background: var(--gradient-accent);">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path>
                <path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
            </svg>
        </div>
        <div class="stat-content">
            <h3>Pending Verifications</h3>
            <p class="stat-value"><?php echo number_format($stats['pending_verifications']); ?></p>
            <?php if ($stats['pending_verifications'] > 0): ?>
                <a href="verify-users.php" class="stat-link">Review Now →</a>
            <?php endif; ?>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon" style="background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M3 3h18v18H3zM12 8v8m-4-4h8"></path>
            </svg>
        </div>
        <div class="stat-content">
            <h3>Platform Activity</h3>
            <div class="mini-stats">
                <span><?php echo $stats['total_research']; ?> Research Posts</span>
                <span><?php echo $stats['total_events']; ?> Events</span>
                <span><?php echo $stats['total_announcements']; ?> Announcements</span>
            </div>
        </div>
    </div>
</div>

<!-- Main Content -->
<div class="dashboard-grid">
    <!-- User Growth Chart -->
    <div class="dashboard-section">
        <div class="section-header">
            <h2>User Growth (Last 30 Days)</h2>
        </div>
        <div class="chart-container">
            <canvas id="userGrowthChart"></canvas>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="dashboard-section">
        <div class="section-header">
            <h2>Quick Actions</h2>
        </div>
        <div class="quick-actions">
            <a href="announcements.php" class="action-card">
                <div class="action-icon">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M22 2L11 13"></path>
                        <path d="M22 2L15 22L11 13L2 9L22 2Z"></path>
                    </svg>
                </div>
                <div class="action-content">
                    <h3>Post Announcement</h3>
                    <p>Create a new system-wide announcement</p>
                </div>
            </a>

            <a href="verify-users.php" class="action-card">
                <div class="action-icon">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M9 11l3 3L22 4"></path>
                        <path d="M21 12v7a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2h11"></path>
                    </svg>
                </div>
                <div class="action-content">
                    <h3>Verify Users</h3>
                    <p><?php echo $stats['pending_verifications']; ?> pending verifications</p>
                </div>
            </a>

            <a href="manage-users.php" class="action-card">
                <div class="action-icon">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                        <circle cx="9" cy="7" r="4"></circle>
                        <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                        <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                    </svg>
                </div>
                <div class="action-content">
                    <h3>Manage Users</h3>
                    <p>View and manage all platform users</p>
                </div>
            </a>

            <a href="contact-queries.php" class="action-card">
                <div class="action-icon">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
                        <polyline points="22,6 12,13 2,6"></polyline>
                    </svg>
                </div>
                <div class="action-content">
                    <h3>Contact Queries</h3>
                    <p><?php echo $stats['unread_queries']; ?> unread messages</p>
                </div>
            </a>
        </div>
    </div>
</div>

<!-- Recent Activities -->
<div class="activities-section">
    <div class="section-header">
        <h2>Recent Activities</h2>
        <a href="audit-logs.php" class="btn btn-sm btn-outline">View All Logs</a>
    </div>
    <div class="activities-table">
        <table class="table">
            <thead>
                <tr>
                    <th>Time</th>
                    <th>User</th>
                    <th>Action</th>
                    <th>Details</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($activity = $recent_activities->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo format_date($activity['created_at'], 'M d, H:i'); ?></td>
                        <td>
                            <div class="user-info">
                                <span class="user-email"><?php echo htmlspecialchars($activity['email'] ?? 'System'); ?></span>
                                <span class="user-type badge badge-<?php echo $activity['user_type'] ?? 'system'; ?>">
                                    <?php echo ucfirst($activity['user_type'] ?? 'System'); ?>
                                </span>
                            </div>
                        </td>
                        <td>
                            <span class="action-badge action-<?php echo strtolower(explode('_', $activity['action'])[0]); ?>">
                                <?php echo str_replace('_', ' ', $activity['action']); ?>
                            </span>
                        </td>
                        <td>
                            <?php echo htmlspecialchars($activity['table_affected']); ?>
                            <?php if ($activity['record_id']): ?>
                                (#<?php echo $activity['record_id']; ?>)
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
    // Pass growth data to JavaScript
    const growthData = <?php echo json_encode($growth_data); ?>;
</script>

<!-- Include Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<?php require_once '../includes/footer.php'; ?>
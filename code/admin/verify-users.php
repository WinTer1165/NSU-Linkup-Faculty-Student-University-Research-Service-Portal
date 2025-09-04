<?php
// admin/verify-users.php
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

    if ($action === 'verify_user') {
        $user_id = intval($_POST['user_id']);

        $stmt = $db->prepare("UPDATE users SET is_verified = 1 WHERE user_id = ? AND user_type IN ('faculty', 'organizer')");
        $stmt->bind_param("i", $user_id);

        if ($stmt->execute()) {
            log_audit('VERIFY_USER', 'users', $user_id, ['is_verified' => 0], ['is_verified' => 1]);

            // Get user email for notification
            $stmt = $db->prepare("SELECT email FROM users WHERE user_id = ?");
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $user = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to verify user']);
        }
        $stmt->close();
    } elseif ($action === 'reject_user') {
        $user_id = intval($_POST['user_id']);
        $reason = sanitize_input($_POST['reason'] ?? '');

        // Delete unverified user
        $stmt = $db->prepare("DELETE FROM users WHERE user_id = ? AND is_verified = 0 AND user_type IN ('faculty', 'organizer')");
        $stmt->bind_param("i", $user_id);

        if ($stmt->execute()) {
            log_audit('REJECT_USER', 'users', $user_id, null, ['reason' => $reason]);

            // TODO: Send rejection email with reason

            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to reject user']);
        }
        $stmt->close();
    } elseif ($action === 'get_user_details') {
        $user_id = intval($_GET['user_id']);

        $stmt = $db->prepare("
            SELECT u.*,
                   CASE 
                       WHEN u.user_type = 'faculty' THEN f.full_name
                       WHEN u.user_type = 'organizer' THEN o.full_name
                   END as full_name,
                   CASE 
                       WHEN u.user_type = 'faculty' THEN f.title
                       WHEN u.user_type = 'organizer' THEN NULL
                   END as title,
                   CASE 
                       WHEN u.user_type = 'faculty' THEN f.office
                       WHEN u.user_type = 'organizer' THEN NULL
                   END as office,
                   CASE 
                       WHEN u.user_type = 'faculty' THEN f.education
                       WHEN u.user_type = 'organizer' THEN NULL
                   END as education,
                   CASE 
                       WHEN u.user_type = 'faculty' THEN f.research_interests
                       WHEN u.user_type = 'organizer' THEN NULL
                   END as research_interests,
                   o.organization,
                   o.phone as organizer_phone
            FROM users u
            LEFT JOIN faculty f ON u.user_id = f.user_id
            LEFT JOIN organizers o ON u.user_id = o.user_id
            WHERE u.user_id = ? AND u.is_verified = 0 AND u.user_type IN ('faculty', 'organizer')
        ");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            echo json_encode(['success' => true, 'data' => $result->fetch_assoc()]);
        } else {
            echo json_encode(['success' => false, 'message' => 'User not found']);
        }
        $stmt->close();
    }
    exit();
}

// Get pending verifications
$stmt = $db->prepare("
    SELECT u.*,
           CASE 
               WHEN u.user_type = 'faculty' THEN f.full_name
               WHEN u.user_type = 'organizer' THEN o.full_name
           END as full_name,
           CASE 
               WHEN u.user_type = 'faculty' THEN f.title
               WHEN u.user_type = 'organizer' THEN NULL
           END as title,
           CASE 
               WHEN u.user_type = 'faculty' THEN f.office
               WHEN u.user_type = 'organizer' THEN NULL
           END as office,
           o.organization
    FROM users u
    LEFT JOIN faculty f ON u.user_id = f.user_id
    LEFT JOIN organizers o ON u.user_id = o.user_id
    WHERE u.is_verified = 0 AND u.user_type IN ('faculty', 'organizer')
    ORDER BY u.created_at ASC
");
$stmt->execute();
$pending_users = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Get statistics
$stmt = $db->prepare("
    SELECT 
        COUNT(CASE WHEN user_type = 'faculty' AND is_verified = 0 THEN 1 END) as pending_faculty,
        COUNT(CASE WHEN user_type = 'organizer' AND is_verified = 0 THEN 1 END) as pending_organizers,
        COUNT(CASE WHEN user_type = 'faculty' AND is_verified = 1 AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY) THEN 1 END) as recent_faculty,
        COUNT(CASE WHEN user_type = 'organizer' AND is_verified = 1 AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY) THEN 1 END) as recent_organizers
    FROM users
    WHERE user_type IN ('faculty', 'organizer')
");
$stmt->execute();
$stats = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Page settings
$page_title = 'Verify Users';
$page_css = ['verify-users.css'];
$page_js = ['verify-users.js'];

// Include header
require_once '../includes/header.php';
?>

<div class="verify-users-container">
    <div class="page-header">
        <h1 class="page-title">Verify Users</h1>
        <p class="page-subtitle">Review and approve new faculty and organizer registrations</p>
    </div>

    <!-- Statistics -->
    <div class="verify-stats">
        <div class="stat-card pending">
            <div class="stat-icon">👨‍🏫</div>
            <div class="stat-content">
                <h3 class="stat-value"><?php echo $stats['pending_faculty']; ?></h3>
                <p class="stat-label">Pending Faculty</p>
            </div>
        </div>
        <div class="stat-card pending">
            <div class="stat-icon">🎯</div>
            <div class="stat-content">
                <h3 class="stat-value"><?php echo $stats['pending_organizers']; ?></h3>
                <p class="stat-label">Pending Organizers</p>
            </div>
        </div>
        <div class="stat-card approved">
            <div class="stat-icon">✅</div>
            <div class="stat-content">
                <h3 class="stat-value"><?php echo $stats['recent_faculty']; ?></h3>
                <p class="stat-label">Recently Approved Faculty</p>
            </div>
        </div>
        <div class="stat-card approved">
            <div class="stat-icon">✅</div>
            <div class="stat-content">
                <h3 class="stat-value"><?php echo $stats['recent_organizers']; ?></h3>
                <p class="stat-label">Recently Approved Organizers</p>
            </div>
        </div>
    </div>

    <!-- Pending Users List -->
    <div class="pending-users-section">
        <h2 class="section-title">Pending Verifications</h2>

        <?php if (empty($pending_users)): ?>
            <div class="empty-state">
                <div class="empty-icon">✅</div>
                <h3>All caught up!</h3>
                <p>No pending verifications at the moment.</p>
            </div>
        <?php else: ?>
            <div class="verification-list">
                <?php foreach ($pending_users as $user): ?>
                    <div class="verification-card" data-user-id="<?php echo $user['user_id']; ?>">
                        <div class="card-header">
                            <div class="user-type-badge <?php echo $user['user_type']; ?>">
                                <?php echo ucfirst($user['user_type']); ?>
                            </div>
                            <div class="registration-time">
                                Registered <?php echo time_ago($user['created_at']); ?>
                            </div>
                        </div>

                        <div class="user-info">
                            <h3 class="user-name"><?php echo htmlspecialchars($user['full_name']); ?></h3>
                            <p class="user-email"><?php echo htmlspecialchars($user['email']); ?></p>

                            <?php if ($user['user_type'] === 'faculty'): ?>
                                <?php if ($user['title']): ?>
                                    <p class="user-detail">
                                        <span class="detail-label">Title:</span>
                                        <?php echo htmlspecialchars($user['title']); ?>
                                    </p>
                                <?php endif; ?>
                                <?php if ($user['office']): ?>
                                    <p class="user-detail">
                                        <span class="detail-label">Office:</span>
                                        <?php echo htmlspecialchars($user['office']); ?>
                                    </p>
                                <?php endif; ?>
                            <?php elseif ($user['user_type'] === 'organizer'): ?>
                                <?php if ($user['organization']): ?>
                                    <p class="user-detail">
                                        <span class="detail-label">Organization:</span>
                                        <?php echo htmlspecialchars($user['organization']); ?>
                                    </p>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>

                        <div class="card-actions">
                            <button class="btn btn-secondary btn-sm"
                                onclick="viewDetails(<?php echo $user['user_id']; ?>)">
                                View Details
                            </button>
                            <button class="btn btn-success btn-sm"
                                onclick="verifyUser(<?php echo $user['user_id']; ?>, '<?php echo htmlspecialchars($user['full_name']); ?>')">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                                    <polyline points="22 4 12 14.01 9 11.01"></polyline>
                                </svg>
                                Approve
                            </button>
                            <button class="btn btn-danger btn-sm"
                                onclick="rejectUser(<?php echo $user['user_id']; ?>, '<?php echo htmlspecialchars($user['full_name']); ?>')">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <line x1="18" y1="6" x2="6" y2="18"></line>
                                    <line x1="6" y1="6" x2="18" y2="18"></line>
                                </svg>
                                Reject
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- User Details Modal -->
<div id="userDetailsModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2 class="modal-title">User Details</h2>
            <span class="modal-close" onclick="closeDetailsModal()">&times;</span>
        </div>
        <div id="userDetailsContent" class="user-details-content">
            <!-- Content will be loaded here -->
        </div>
    </div>
</div>

<!-- Reject Reason Modal -->
<div id="rejectModal" class="modal">
    <div class="modal-content" style="max-width: 500px;">
        <div class="modal-header">
            <h2 class="modal-title">Reject User</h2>
            <span class="modal-close" onclick="closeRejectModal()">&times;</span>
        </div>
        <form id="rejectForm">
            <input type="hidden" id="rejectUserId">
            <div class="form-group">
                <label class="form-label">Reason for Rejection</label>
                <textarea id="rejectReason"
                    class="form-control"
                    rows="4"
                    placeholder="Please provide a reason for rejection (optional)"></textarea>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeRejectModal()">Cancel</button>
                <button type="submit" class="btn btn-danger">Reject User</button>
            </div>
        </form>
    </div>
</div>

<?php
// Helper function for relative time
function time_ago($datetime)
{
    $time = strtotime($datetime);
    $now = time();
    $diff = $now - $time;

    if ($diff < 60) {
        return 'just now';
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
<?php
// admin/announcements.php
require_once '../includes/config.php';
require_once '../includes/db_connect.php';
require_once '../includes/functions.php';
require_once '../includes/auth_check.php';

// Check if user is admin
if (get_user_type() !== 'admin') {
    redirect('index.php');
    exit();
}

$admin_id = $_SESSION['admin_id'] ?? 0;

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'create') {
        $title = sanitize_input($_POST['title']);
        $content = sanitize_input($_POST['content']);
        $is_published = isset($_POST['is_published']) ? 1 : 0;

        if (!empty($title) && !empty($content)) {
            $stmt = $db->prepare("INSERT INTO announcements (admin_id, title, content, is_published) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("issi", $admin_id, $title, $content, $is_published);

            if ($stmt->execute()) {
                $announcement_id = $db->insert_id;
                log_audit('CREATE_ANNOUNCEMENT', 'announcements', $announcement_id);
                show_alert('Announcement created successfully!', 'success');
            } else {
                show_alert('Failed to create announcement.', 'error');
            }
            $stmt->close();
        } else {
            show_alert('Please fill in all required fields.', 'error');
        }
    }
}

// Handle AJAX requests
if (isset($_GET['ajax'])) {
    header('Content-Type: application/json');

    $action = $_GET['action'] ?? '';

    if ($action === 'toggle_publish') {
        $announcement_id = intval($_POST['announcement_id']);

        // Get current status
        $stmt = $db->prepare("SELECT is_published FROM announcements WHERE announcement_id = ?");
        $stmt->bind_param("i", $announcement_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $announcement = $result->fetch_assoc();
        $stmt->close();

        if ($announcement) {
            $new_status = $announcement['is_published'] ? 0 : 1;

            $stmt = $db->prepare("UPDATE announcements SET is_published = ? WHERE announcement_id = ?");
            $stmt->bind_param("ii", $new_status, $announcement_id);

            if ($stmt->execute()) {
                log_audit(
                    'UPDATE_ANNOUNCEMENT',
                    'announcements',
                    $announcement_id,
                    ['is_published' => $announcement['is_published']],
                    ['is_published' => $new_status]
                );
                echo json_encode(['success' => true, 'is_published' => $new_status]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to update status']);
            }
            $stmt->close();
        } else {
            echo json_encode(['success' => false, 'message' => 'Announcement not found']);
        }
    } elseif ($action === 'delete') {
        $announcement_id = intval($_POST['announcement_id']);

        $stmt = $db->prepare("DELETE FROM announcements WHERE announcement_id = ?");
        $stmt->bind_param("i", $announcement_id);

        if ($stmt->execute()) {
            log_audit('DELETE_ANNOUNCEMENT', 'announcements', $announcement_id);
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to delete announcement']);
        }
        $stmt->close();
    } elseif ($action === 'edit') {
        $announcement_id = intval($_POST['announcement_id']);
        $title = sanitize_input($_POST['title']);
        $content = sanitize_input($_POST['content']);

        $stmt = $db->prepare("UPDATE announcements SET title = ?, content = ? WHERE announcement_id = ?");
        $stmt->bind_param("ssi", $title, $content, $announcement_id);

        if ($stmt->execute()) {
            log_audit('UPDATE_ANNOUNCEMENT', 'announcements', $announcement_id);
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to update announcement']);
        }
        $stmt->close();
    }
    exit();
}

// Pagination
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$per_page = 10;
$offset = ($page - 1) * $per_page;

// Get total announcements
$stmt = $db->prepare("SELECT COUNT(*) as total FROM announcements");
$stmt->execute();
$total_announcements = $stmt->get_result()->fetch_assoc()['total'];
$stmt->close();

$total_pages = ceil($total_announcements / $per_page);

// Get announcements
$stmt = $db->prepare("
    SELECT a.*, ad.full_name as admin_name
    FROM announcements a
    JOIN admins ad ON a.admin_id = ad.admin_id
    ORDER BY a.created_at DESC
    LIMIT ? OFFSET ?
");
$stmt->bind_param("ii", $per_page, $offset);
$stmt->execute();
$announcements = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Page settings
$page_title = 'Manage Announcements';
$page_css = ['announcements2.css'];
$page_js = ['announcements2.js'];

// Include header
require_once '../includes/header.php';
?>

<div class="announcements-container">
    <div class="page-header">
        <h1 class="page-title">Manage Announcements</h1>
        <button class="btn btn-primary" onclick="showCreateModal()">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <line x1="12" y1="5" x2="12" y2="19"></line>
                <line x1="5" y1="12" x2="19" y2="12"></line>
            </svg>
            Create Announcement
        </button>
    </div>

    <?php display_alert(); ?>

    <!-- Statistics -->
    <div class="announcement-stats">
        <div class="stat-box">
            <div class="stat-icon">📢</div>
            <div class="stat-content">
                <h3><?php echo $total_announcements; ?></h3>
                <p>Total Announcements</p>
            </div>
        </div>
        <div class="stat-box">
            <div class="stat-icon">✅</div>
            <div class="stat-content">
                <?php
                $stmt = $db->prepare("SELECT COUNT(*) as published FROM announcements WHERE is_published = 1");
                $stmt->execute();
                $published = $stmt->get_result()->fetch_assoc()['published'];
                $stmt->close();
                ?>
                <h3><?php echo $published; ?></h3>
                <p>Published</p>
            </div>
        </div>
        <div class="stat-box">
            <div class="stat-icon">📝</div>
            <div class="stat-content">
                <h3><?php echo $total_announcements - $published; ?></h3>
                <p>Unpublished</p>
            </div>
        </div>
    </div>

    <!-- Announcements List -->
    <div class="announcements-list">
        <?php if (empty($announcements)): ?>
            <div class="empty-state">
                <div class="empty-icon">📢</div>
                <h3>No announcements yet</h3>
                <p>Create your first announcement to get started.</p>
            </div>
        <?php else: ?>
            <?php foreach ($announcements as $announcement): ?>
                <div class="announcement-card" data-id="<?php echo $announcement['announcement_id']; ?>">
                    <div class="announcement-header">
                        <div class="announcement-info">
                            <h3 class="announcement-title"><?php echo htmlspecialchars($announcement['title']); ?></h3>
                            <div class="announcement-meta">
                                <span class="meta-item">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                                        <circle cx="12" cy="7" r="4"></circle>
                                    </svg>
                                    <?php echo htmlspecialchars($announcement['admin_name']); ?>
                                </span>
                                <span class="meta-item">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                                        <line x1="16" y1="2" x2="16" y2="6"></line>
                                        <line x1="8" y1="2" x2="8" y2="6"></line>
                                        <line x1="3" y1="10" x2="21" y2="10"></line>
                                    </svg>
                                    <?php echo format_date($announcement['created_at']); ?>
                                </span>
                            </div>
                        </div>
                        <div class="announcement-actions">
                            <label class="toggle-switch">
                                <input type="checkbox"
                                    class="publish-toggle"
                                    data-id="<?php echo $announcement['announcement_id']; ?>"
                                    <?php echo $announcement['is_published'] ? 'checked' : ''; ?>>
                                <span class="toggle-slider"></span>
                            </label>
                            <button class="btn-icon btn-edit" onclick="editAnnouncement(<?php echo $announcement['announcement_id']; ?>)">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                    <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                                </svg>
                            </button>
                            <button class="btn-icon btn-delete" onclick="deleteAnnouncement(<?php echo $announcement['announcement_id']; ?>)">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <polyline points="3 6 5 6 21 6"></polyline>
                                    <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                    <line x1="10" y1="11" x2="10" y2="17"></line>
                                    <line x1="14" y1="11" x2="14" y2="17"></line>
                                </svg>
                            </button>
                        </div>
                    </div>
                    <div class="announcement-content">
                        <?php echo nl2br(htmlspecialchars($announcement['content'])); ?>
                    </div>
                    <div class="announcement-footer">
                        <span class="status-badge <?php echo $announcement['is_published'] ? 'published' : 'unpublished'; ?>">
                            <?php echo $announcement['is_published'] ? 'Published' : 'Unpublished'; ?>
                        </span>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- Pagination -->
    <?php if ($total_pages > 1): ?>
        <div class="pagination">
            <?php if ($page > 1): ?>
                <a href="?page=<?php echo $page - 1; ?>" class="pagination-link">Previous</a>
            <?php endif; ?>

            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <?php if ($i == 1 || $i == $total_pages || ($i >= $page - 2 && $i <= $page + 2)): ?>
                    <a href="?page=<?php echo $i; ?>"
                        class="pagination-link <?php echo $i == $page ? 'active' : ''; ?>">
                        <?php echo $i; ?>
                    </a>
                <?php elseif ($i == $page - 3 || $i == $page + 3): ?>
                    <span class="pagination-dots">...</span>
                <?php endif; ?>
            <?php endfor; ?>

            <?php if ($page < $total_pages): ?>
                <a href="?page=<?php echo $page + 1; ?>" class="pagination-link">Next</a>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

<!-- Create/Edit Modal -->
<div id="announcementModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2 class="modal-title" id="modalTitle">Create Announcement</h2>
            <span class="modal-close" onclick="closeModal()">&times;</span>
        </div>
        <form id="announcementForm" method="POST">
            <input type="hidden" name="action" value="create">
            <input type="hidden" name="announcement_id" id="announcementId">

            <div class="form-group">
                <label class="form-label">Title <span class="required">*</span></label>
                <input type="text"
                    name="title"
                    id="announcementTitle"
                    class="form-control"
                    required
                    maxlength="200">
            </div>

            <div class="form-group">
                <label class="form-label">Content <span class="required">*</span></label>
                <textarea name="content"
                    id="announcementContent"
                    class="form-control"
                    rows="6"
                    required></textarea>
            </div>

            <div class="form-group">
                <label class="checkbox-label">
                    <input type="checkbox" name="is_published" id="isPublished">
                    <span>Publish immediately</span>
                </label>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal()">Cancel</button>
                <button type="submit" class="btn btn-primary">
                    <span id="submitBtnText">Create Announcement</span>
                </button>
            </div>
        </form>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
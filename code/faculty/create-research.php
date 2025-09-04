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

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get form data
    $title = sanitize_input($_POST['title']);
    $min_cgpa = !empty($_POST['min_cgpa']) ? floatval($_POST['min_cgpa']) : null;
    $department = sanitize_input($_POST['department']);
    $apply_deadline = sanitize_input($_POST['apply_deadline']);
    $duration = sanitize_input($_POST['duration']);
    $tags = sanitize_input($_POST['tags']);
    $description = sanitize_input($_POST['description']);
    $student_roles = sanitize_input($_POST['student_roles']);
    $salary = sanitize_input($_POST['salary']);
    $number_required = !empty($_POST['number_required']) ? intval($_POST['number_required']) : null;

    // Validate inputs
    $errors = [];

    if (empty($title)) {
        $errors[] = "Title is required";
    }

    if (empty($department)) {
        $errors[] = "Department is required";
    }

    if (empty($apply_deadline)) {
        $errors[] = "Application deadline is required";
    } elseif (strtotime($apply_deadline) < strtotime('today')) {
        $errors[] = "Application deadline must be in the future";
    }

    if (empty($duration)) {
        $errors[] = "Duration is required";
    }

    if (empty($description)) {
        $errors[] = "Description is required";
    }

    if (empty($student_roles)) {
        $errors[] = "Student roles/responsibilities are required";
    }

    // If no errors, create research post
    if (empty($errors)) {
        $stmt = $conn->prepare("INSERT INTO research_posts (faculty_id, title, min_cgpa, department, apply_deadline, duration, tags, description, student_roles, salary, number_required) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("isdsssssssi", $faculty_id, $title, $min_cgpa, $department, $apply_deadline, $duration, $tags, $description, $student_roles, $salary, $number_required);

        if ($stmt->execute()) {
            $research_id = $conn->insert_id;
            $_SESSION['success'] = "Research post created successfully!";
            header("Location: manage-posts.php");
            exit();
        } else {
            $errors[] = "Failed to create research post. Please try again.";
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Research Post - NSU LinkUp</title>
    <link rel="stylesheet" href="../assets/css/common.css">
    <link rel="stylesheet" href="../assets/css/faculty-dashboard.css">
    <link rel="stylesheet" href="../assets/css/create-research.css">
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
                <a href="../faculty/create-research.php" class="active">
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
            <div class="page-header">
                <h1>Create Research Post</h1>
                <p>Post a new research opportunity for students</p>
            </div>

            <div class="create-research-container">
                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger">
                        <?php foreach ($errors as $error): ?>
                            <p><?php echo htmlspecialchars($error); ?></p>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="" class="research-form" data-validate>
                    <div class="form-section">
                        <h2>Basic Information</h2>

                        <div class="form-group">
                            <label for="title" class="form-label required">Research Title</label>
                            <input type="text"
                                id="title"
                                name="title"
                                class="form-control"
                                placeholder="e.g., Machine Learning Research Assistant for Healthcare AI Project"
                                value="<?php echo isset($_POST['title']) ? htmlspecialchars($_POST['title']) : ''; ?>"
                                required>
                            <small class="form-text">Choose a clear and descriptive title that attracts qualified students</small>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="department" class="form-label required">Department</label>
                                <input type="text"
                                    id="department"
                                    name="department"
                                    class="form-control"
                                    placeholder="e.g., Computer Science & Engineering"
                                    value="<?php echo isset($_POST['department']) ? htmlspecialchars($_POST['department']) : ''; ?>"
                                    list="departments"
                                    required>
                                <datalist id="departments">
                                    <option value="Computer Science & Engineering">
                                    <option value="Electrical & Electronic Engineering">
                                    <option value="Business Administration">
                                    <option value="English & Modern Languages">
                                    <option value="Mathematics & Natural Sciences">
                                    <option value="Architecture">
                                    <option value="Law">
                                    <option value="Public Health">
                                    <option value="Environmental Science">
                                    <option value="Economics">
                                </datalist>
                            </div>

                            <div class="form-group">
                                <label for="apply_deadline" class="form-label required">Application Deadline</label>
                                <input type="date"
                                    id="apply_deadline"
                                    name="apply_deadline"
                                    class="form-control"
                                    min="<?php echo date('Y-m-d', strtotime('+1 day')); ?>"
                                    value="<?php echo isset($_POST['apply_deadline']) ? htmlspecialchars($_POST['apply_deadline']) : ''; ?>"
                                    required>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="duration" class="form-label required">Duration</label>
                                <input type="text"
                                    id="duration"
                                    name="duration"
                                    class="form-control"
                                    placeholder="e.g., 3 months, 1 semester, 6 months"
                                    value="<?php echo isset($_POST['duration']) ? htmlspecialchars($_POST['duration']) : ''; ?>"
                                    required>
                            </div>

                            <div class="form-group">
                                <label for="number_required" class="form-label">Number of Students Required</label>
                                <input type="number"
                                    id="number_required"
                                    name="number_required"
                                    class="form-control"
                                    min="1"
                                    max="10"
                                    placeholder="e.g., 2"
                                    value="<?php echo isset($_POST['number_required']) ? htmlspecialchars($_POST['number_required']) : ''; ?>">
                                <small class="form-text">Leave blank if flexible</small>
                            </div>
                        </div>
                    </div>

                    <div class="form-section">
                        <h2>Requirements & Compensation</h2>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="min_cgpa" class="form-label">Minimum CGPA</label>
                                <input type="number"
                                    id="min_cgpa"
                                    name="min_cgpa"
                                    class="form-control"
                                    step="0.01"
                                    min="0"
                                    max="4"
                                    placeholder="e.g., 3.50"
                                    value="<?php echo isset($_POST['min_cgpa']) ? htmlspecialchars($_POST['min_cgpa']) : ''; ?>">
                                <small class="form-text">Leave blank if no minimum requirement</small>
                            </div>

                            <div class="form-group">
                                <label for="salary" class="form-label">Compensation/Stipend</label>
                                <input type="text"
                                    id="salary"
                                    name="salary"
                                    class="form-control"
                                    placeholder="e.g., 5000 BDT/month, Unpaid, Performance-based"
                                    value="<?php echo isset($_POST['salary']) ? htmlspecialchars($_POST['salary']) : ''; ?>">
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="tags" class="form-label">Skills/Tags</label>
                            <input type="text"
                                id="tags"
                                name="tags"
                                class="form-control"
                                placeholder="e.g., Python, Machine Learning, Data Analysis, Research Writing"
                                value="<?php echo isset($_POST['tags']) ? htmlspecialchars($_POST['tags']) : ''; ?>">
                            <small class="form-text">Separate tags with commas. This helps students find relevant opportunities.</small>
                        </div>
                    </div>

                    <div class="form-section">
                        <h2>Detailed Information</h2>

                        <div class="form-group">
                            <label for="description" class="form-label required">Project Description</label>
                            <textarea id="description"
                                name="description"
                                class="form-control"
                                rows="6"
                                placeholder="Provide a detailed description of the research project, its objectives, and expected outcomes..."
                                required><?php echo isset($_POST['description']) ? htmlspecialchars($_POST['description']) : ''; ?></textarea>
                            <small class="form-text">Be specific about the research area, methodology, and goals</small>
                        </div>

                        <div class="form-group">
                            <label for="student_roles" class="form-label required">Student Roles & Responsibilities</label>
                            <textarea id="student_roles"
                                name="student_roles"
                                class="form-control"
                                rows="5"
                                placeholder="List the specific tasks and responsibilities the student will undertake..."
                                required><?php echo isset($_POST['student_roles']) ? htmlspecialchars($_POST['student_roles']) : ''; ?></textarea>
                            <small class="form-text">Clearly outline what the student will be doing and learning</small>
                        </div>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="fas fa-check"></i>
                            Post Research Opportunity
                        </button>
                        <!-- <button type="button" class="btn btn-outline btn-lg" onclick="saveDraft()">
                            <i class="fas fa-save"></i>
                            Save as Draft
                        </button> -->
                        <a href="dashboard.php" class="btn btn-outline btn-lg">
                            Cancel
                        </a>
                    </div>
                </form>
            </div>

            <!-- Preview Modal -->
            <div id="previewModal" class="modal">
                <div class="modal-content modal-lg">
                    <span class="modal-close" onclick="closeModal('previewModal')">&times;</span>
                    <h2>Preview Research Post</h2>
                    <div id="previewContent">
                        <!-- Preview content will be loaded here -->
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Mobile Menu Toggle -->
    <button class="mobile-menu-toggle" id="mobileMenuToggle">
        <i class="fas fa-bars"></i>
    </button>

    <script src="../assets/js/common.js"></script>
    <script src="../assets/js/create-research.js"></script>
    <script>
        // Mobile menu toggle
        document.getElementById('mobileMenuToggle').addEventListener('click', function() {
            document.querySelector('.sidebar').classList.toggle('active');
        });

        // Save draft function
        function saveDraft() {
            // Implementation for saving draft
            alert('Draft saved successfully!');
        }

        // Close modal function
        function closeModal(modalId) {
            document.getElementById(modalId).style.display = 'none';
        }
    </script>
</body>

</html>

<?php $conn->close(); ?>
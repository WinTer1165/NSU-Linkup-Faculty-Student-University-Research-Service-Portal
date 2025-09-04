<?php
// faculty/chatbot.php
error_reporting(E_ALL & ~E_NOTICE);
// Check if session is already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../includes/db_connect.php';
require_once '../includes/auth_check.php';

// STRICT ACCESS CONTROL - Faculty Only
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'faculty') {
    // Log unauthorized access attempt if needed
    error_log("Unauthorized chatbot access attempt from user_id: " . ($_SESSION['user_id'] ?? 'not logged in'));

    // Redirect based on user type
    if (isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'student') {
        // If student, redirect to student dashboard with message
        $_SESSION['error_message'] = "This AI Assistant is available for faculty members only.";
        header('Location: ../student/dashboard.php');
    } else {
        // Otherwise redirect to login
        header('Location: ../auth/login.php');
    }
    exit();
}

$user_id = $_SESSION['user_id'];

// Get faculty details
$stmt = $conn->prepare("SELECT f.*, u.email FROM faculty f JOIN users u ON f.user_id = u.user_id WHERE f.user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$faculty = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Additional verification - ensure faculty record exists
if (!$faculty) {
    session_destroy();
    header('Location: ../auth/login.php');
    exit();
}

$user_name = $faculty['full_name'] ?? 'Faculty';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AI Research Assistant - Faculty Only - NSU LinkUp</title>
    <link rel="stylesheet" href="../assets/css/common.css">
    <link rel="stylesheet" href="../assets/css/chatbot.css">
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
                <a href="chatbot.php" class="active">
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
                    <h1>
                        <i class="fas fa-robot"></i>
                        AI Research Assistant
                    </h1>
                    <p>Faculty-exclusive AI assistant for research and student insights</p>
                </div>
                <div class="header-right">
                    <div class="user-info">
                        <span><?php echo htmlspecialchars($user_name); ?></span>
                        <img src="<?php echo $faculty['profile_image'] ? '../assets/uploads/profiles/' . $faculty['profile_image'] : '../assets/images/default-avatar.png'; ?>"
                            alt="Profile" class="user-avatar">
                    </div>
                </div>
            </header>

            <div class="chatbot-wrapper">
                <!-- Info Section -->
                <div class="chatbot-header-section">
                    <h1>Faculty AI Research Assistant</h1>
                    <p>Get intelligent insights about student qualifications, research trends, and collaboration opportunities. This tool is exclusively available for faculty members.</p>
                </div>

                <!-- Chat Container -->
                <div class="chat-container">
                    <!-- Chat Header -->
                    <div class="chat-header">
                        <h3>
                            <div class="chat-header-icon">
                                <i class="fas fa-robot"></i>
                            </div>
                            NSU Faculty Assistant
                        </h3>
                        <div class="chat-status">
                            <span class="status-dot"></span>
                            <span>Online</span>
                        </div>
                    </div>

                    <!-- Chat Messages -->
                    <div class="chat-messages" id="chatMessages">
                        <div class="welcome-message">
                            <h4>Welcome, <?php echo htmlspecialchars($user_name); ?>! 👋</h4>
                            <p>I'm your AI Faculty Assistant, exclusively designed for faculty members. I can help you with:</p>
                            <ul>
                                <li>Finding qualified students for your research projects</li>
                                <li>Understanding student skill distributions and academic trends</li>
                                <li>Connecting with other faculty members for collaboration</li>
                                <li>Analyzing research application patterns and success rates</li>
                                <li>Optimizing your research post requirements</li>
                                <li>Getting insights about department-wide research activities</li>
                            </ul>
                        </div>
                    </div>

                    <!-- Typing Indicator -->
                    <div class="typing-indicator" id="typingIndicator">
                        <span></span>
                        <span></span>
                        <span></span>
                    </div>

                    <!-- Chat Input Container -->
                    <div class="chat-input-container">
                        <div class="quick-actions">
                            <button class="quick-action-btn" data-message="Which students have skills in machine learning?">
                                <i class="fas fa-users"></i> ML Students
                            </button>
                            <button class="quick-action-btn" data-message="Show me students with CGPA above 3.5">
                                <i class="fas fa-trophy"></i> Top Students
                            </button>
                            <button class="quick-action-btn" data-message="Which faculty members work in AI research?">
                                <i class="fas fa-network-wired"></i> AI Faculty
                            </button>
                            <button class="quick-action-btn" data-message="What are the trending research areas?">
                                <i class="fas fa-chart-line"></i> Research Trends
                            </button>
                            <button class="quick-action-btn" data-message="How many applications do I have pending?">
                                <i class="fas fa-inbox"></i> My Applications
                            </button>
                            <button class="quick-action-btn" data-message="Student interest in my research areas">
                                <i class="fas fa-user-graduate"></i> Student Interest
                            </button>
                        </div>
                        <form class="chat-input-form" id="chatForm">
                            <input type="text"
                                class="chat-input"
                                id="chatInput"
                                placeholder="Ask about students, research collaborations, or faculty insights..."
                                autocomplete="off"
                                required>
                            <button type="submit" class="send-button" id="sendButton">
                                <span>Send</span>
                                <i class="fas fa-paper-plane"></i>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Mobile Menu Toggle -->
    <button class="mobile-menu-toggle" id="mobileMenuToggle">
        <i class="fas fa-bars"></i>
    </button>

    <!-- Security: Additional client-side verification -->
    <script>
        // Double-check user type on client side (not for security, just UX)
        const userType = '<?php echo $_SESSION['user_type']; ?>';
        if (userType !== 'faculty') {
            window.location.href = '../auth/login.php';
        }
    </script>

    <script src="../assets/js/common.js"></script>
    <script src="../assets/js/chatbot.js"></script>
</body>

</html>
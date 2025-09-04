<?php

error_reporting(E_ALL & ~E_NOTICE);
// student/chatbot.php
// Check if session is already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../includes/db_connect.php';
require_once '../includes/auth_check.php';

// STRICT ACCESS CONTROL - Students Only
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'student') {
    // Log unauthorized access attempt if needed
    error_log("Unauthorized student chatbot access attempt from user_id: " . ($_SESSION['user_id'] ?? 'not logged in'));

    // Redirect based on user type
    if (isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'faculty') {
        // If faculty, redirect to faculty chatbot
        header('Location: ../faculty/chatbot.php');
    } else {
        // Otherwise redirect to login
        header('Location: ../auth/login.php');
    }
    exit();
}

$user_id = $_SESSION['user_id'];

// Get student details
$stmt = $conn->prepare("SELECT s.*, u.email FROM students s JOIN users u ON s.user_id = u.user_id WHERE s.user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$student = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Additional verification - ensure student record exists
if (!$student) {
    session_destroy();
    header('Location: ../auth/login.php');
    exit();
}

$user_name = $student['full_name'] ?? 'Student';
$student_id = $student['student_id'];

// Get student's application count for context
$stmt = $conn->prepare("SELECT COUNT(*) as app_count FROM research_applications WHERE student_id = ?");
$stmt->bind_param("i", $student_id);
$stmt->execute();
$app_count = $stmt->get_result()->fetch_assoc()['app_count'];
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AI Study Assistant - Student Portal - NSU LinkUp</title>
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
                <p>Student Portal</p>
            </div>

            <nav class="sidebar-nav">
                <a href="dashboard.php">
                    <i class="fas fa-home"></i>
                    Dashboard
                </a>
                <a href="profile.php">
                    <i class="fas fa-user"></i>
                    My Profile
                </a>
                <a href="chatbot.php" class="active">
                    <i class="fas fa-robot"></i>
                    AI Assistant
                </a>
                <a href="research.php">
                    <i class="fas fa-microscope"></i>
                    Research Posts
                </a>
                <a href="students.php">
                    <i class="fas fa-graduation-cap"></i>
                    Students
                </a>
                <a href="faculty.php">
                    <i class="fas fa-chalkboard-teacher"></i>
                    Faculty
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
                        AI Study Assistant
                    </h1>
                    <p>Your personal academic assistant for research opportunities and career guidance</p>
                </div>
                <div class="header-right">
                    <div class="user-info">
                        <span><?php echo htmlspecialchars($user_name); ?></span>
                        <img src="<?php echo $student['profile_image'] ? '../assets/uploads/profiles/' . $student['profile_image'] : '../assets/images/default-avatar.png'; ?>"
                            alt="Profile" class="user-avatar">
                    </div>
                </div>
            </header>

            <div class="chatbot-wrapper">
                <!-- Info Section -->
                <div class="chatbot-header-section">
                    <h1>Student AI Study Assistant</h1>
                    <p>Get personalized guidance on research opportunities, academic planning, and career development tailored to your profile and interests.</p>
                </div>

                <!-- Chat Container -->
                <div class="chat-container">
                    <!-- Chat Header -->
                    <div class="chat-header">
                        <h3>
                            <div class="chat-header-icon">
                                <i class="fas fa-robot"></i>
                            </div>
                            NSU Study Assistant
                        </h3>
                        <div class="chat-status">
                            <span class="status-dot"></span>
                            <span>Online</span>
                        </div>
                    </div>

                    <!-- Chat Messages -->
                    <div class="chat-messages" id="chatMessages">
                        <div class="welcome-message">
                            <h4>Hello, <?php echo htmlspecialchars($user_name); ?>! 👋</h4>
                            <p>I'm your AI Study Assistant, here to help you navigate your academic journey. I can assist you with:</p>
                            <ul>
                                <li>Finding research opportunities that match your skills and interests</li>
                                <li>Understanding research requirements and improving your applications</li>
                                <li>Connecting with peers for study groups and collaborations</li>
                                <li>Getting information about faculty research areas and expertise</li>
                                <li>Academic planning and skill development recommendations</li>
                                <li>Tips for improving your profile to stand out to faculty</li>
                            </ul>
                            <?php if ($app_count > 0): ?>
                                <p style="margin-top: 1rem; color: var(--chatbot-secondary);">
                                    <strong>📊 You have submitted <?php echo $app_count; ?> research application<?php echo $app_count > 1 ? 's' : ''; ?> so far. Keep it up!</strong>
                                </p>
                            <?php endif; ?>
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
                            <button class="quick-action-btn" data-message="What research opportunities match my profile?">
                                <i class="fas fa-search"></i> Match Research
                            </button>
                            <button class="quick-action-btn" data-message="How can I improve my research application?">
                                <i class="fas fa-edit"></i> Application Tips
                            </button>
                            <button class="quick-action-btn" data-message="Which skills should I develop for research?">
                                <i class="fas fa-code"></i> Skill Advice
                            </button>
                            <button class="quick-action-btn" data-message="Show me upcoming research deadlines">
                                <i class="fas fa-calendar"></i> Deadlines
                            </button>
                            <button class="quick-action-btn" data-message="How to connect with faculty for research?">
                                <i class="fas fa-user-tie"></i> Faculty Tips
                            </button>
                            <button class="quick-action-btn" data-message="What's the status of my applications?">
                                <i class="fas fa-clipboard-check"></i> My Status
                            </button>
                        </div>
                        <form class="chat-input-form" id="chatForm">
                            <input type="text"
                                class="chat-input"
                                id="chatInput"
                                placeholder="Ask about research opportunities, academic advice, or career guidance..."
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
        if (userType !== 'student') {
            window.location.href = '../auth/login.php';
        }

        // Store student context for chatbot
        const studentContext = {
            name: '<?php echo addslashes($user_name); ?>',
            cgpa: '<?php echo $student['cgpa'] ?? 'Not set'; ?>',
            department: '<?php echo addslashes($student['department'] ?? 'Not specified'); ?>',
            applicationCount: <?php echo $app_count; ?>
        };
    </script>

    <script src="../assets/js/common.js"></script>
    <script src="../assets/js/chatbot.js"></script>
</body>

</html>
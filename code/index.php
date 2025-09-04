<?php
// index.php
require_once 'includes/config.php';
require_once 'includes/functions.php';

// Check if user is already logged in
if (is_logged_in()) {
    redirect(get_user_type() . '/dashboard.php');
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NSU LinkUp - Connect, Collaborate, Grow</title>
    <link rel="stylesheet" href="assets/css/common.css">
    <link rel="stylesheet" href="assets/css/index.css">
</head>

<body>
    <!-- Navigation -->
    <nav class="navbar">
        <div class="container navbar-container">
            <a href="index.php" class="navbar-brand">NSU LinkUp</a>
            <ul class="navbar-menu" id="navMenu">
                <li><a href="#home" class="navbar-link">Home</a></li>
                <li><a href="#features" class="navbar-link">Features</a></li>
                <li><a href="about.php" class="navbar-link">About</a></li>
                <li><a href="contact.php" class="navbar-link">Contact</a></li>
            </ul>
            <button class="mobile-menu-toggle" id="mobileMenuToggle">
                <span></span>
                <span></span>
                <span></span>
            </button>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero" id="home">
        <div class="hero-bg">
            <div class="hero-shape-1"></div>
            <div class="hero-shape-2"></div>
            <div class="hero-shape-3"></div>
        </div>
        <div class="container hero-content">
            <h1 class="hero-title">Welcome to NSU LinkUp</h1>
            <p class="hero-subtitle">Connect Students and Faculty in <br> One Unified Platform</p>
            <div class="hero-buttons">
                <button class="btn btn-primary btn-lg" onclick="showLoginModal()">Get Started</button>
                <a href="#features" class="btn btn-outline btn-lg">Learn More</a>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="features" id="features">
        <div class="container">
            <h2 class="section-title">Platform Features</h2>
            <div class="features-grid">
                <div class="feature-card">
                    <div class="feature-icon">
                        <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                            <circle cx="9" cy="7" r="4"></circle>
                            <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                            <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                        </svg>
                    </div>
                    <h3>Student Profiles</h3>
                    <p>Create comprehensive profiles showcasing skills, achievements, and research interests</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path>
                            <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path>
                        </svg>
                    </div>
                    <h3>Research Opportunities</h3>
                    <p>Faculty can post research positions and students can apply based on their interests</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                            <polyline points="14 2 14 8 20 8"></polyline>
                            <line x1="16" y1="13" x2="8" y2="13"></line>
                            <line x1="16" y1="17" x2="8" y2="17"></line>
                            <polyline points="10 9 9 9 8 9"></polyline>
                        </svg>
                    </div>
                    <h3>Announcements</h3>
                    <p>Stay updated with important university news, events and announcements</p>
                </div>
            </div>
        </div>
    </section>

    <!-- User Types Section -->
    <section class="user-types">
        <div class="container">
            <h2 class="section-title">Join As</h2>
            <div class="user-types-grid">
                <div class="user-type-card">
                    <h3>Student</h3>
                    <p>Build your profile, find research opportunities, and connect with faculty</p>
                    <span class="join-link">Join as Student →</span>
                </div>
                <div class="user-type-card">
                    <h3>Faculty</h3>
                    <p>Post research positions, manage students, and share your expertise</p>
                    <span class="join-link">Join as Faculty →</span>
                </div>
            </div>
        </div>
    </section>

    <!-- Login Modal -->
    <div id="loginModal" class="modal">
        <div class="modal-content">
            <span class="modal-close" onclick="closeModal('loginModal')">&times;</span>
            <h2 class="modal-title">Choose Login Type</h2>
            <div class="login-options">
                <a href="auth/student-login.php" class="login-option">
                    <div class="option-icon"></div>
                    <h3>Student Login</h3>
                </a>
                <a href="auth/faculty-login.php" class="login-option">
                    <div class="option-icon"></div>
                    <h3>Faculty Login</h3>
                </a>
                <a href="auth/admin-login.php" class="login-option">
                    <div class="option-icon"></div>
                    <h3>Admin Login</h3>
                </a>
            </div>
        </div>
    </div>
    <!-- Signup Modal -->
    <div id="signupModal" class="modal">
        <div class="modal-content">
            <span class="modal-close" onclick="closeModal('signupModal')">&times;</span>
            <h2 class="modal-title">Sign Up As <span id="signupType"></span></h2>
            <div class="signup-confirm">
                <p>You're about to create a <strong id="signupTypeText"></strong> account.</p>
                <a id="signupLink" href="#" class="btn btn-primary btn-lg">Continue to Sign Up</a>
            </div>
        </div>
    </div>

    <?php if (isset($_GET['banned'])): ?>
        <script>
            alert('Your account has been banned. Please contact administrator.');
        </script>
    <?php endif; ?>

    <script src="assets/js/common.js"></script>
    <script src="assets/js/index.js"></script>
</body>

</html>


<?php require_once __DIR__ . '/includes/footer.php'; ?>
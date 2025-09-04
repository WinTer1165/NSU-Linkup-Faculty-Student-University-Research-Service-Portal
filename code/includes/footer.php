<?php
// includes/footer.php
?>
</div>
</main>

<!-- Footer -->
<footer class="footer">
    <div class="container">
        <div class="footer-content">
            <div class="footer-section">
                <h3 class="footer-title">NSU LinkUp</h3>
                <p class="footer-description">Connecting students, faculty, and organizers to build a stronger academic community at North South University.</p>
                <div class="footer-social">
                    <a href="#" class="social-link" aria-label="Facebook">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"></path>
                        </svg>
                    </a>
                    <a href="#" class="social-link" aria-label="Twitter">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M23 3a10.9 10.9 0 0 1-3.14 1.53 4.48 4.48 0 0 0-7.86 3v1A10.66 10.66 0 0 1 3 4s-4 9 5 13a11.64 11.64 0 0 1-7 2c9 5 20 0 20-11.5a4.5 4.5 0 0 0-.08-.83A7.72 7.72 0 0 0 23 3z"></path>
                        </svg>
                    </a>
                    <a href="#" class="social-link" aria-label="LinkedIn">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M16 8a6 6 0 0 1 6 6v7h-4v-7a2 2 0 0 0-2-2 2 2 0 0 0-2 2v7h-4v-7a6 6 0 0 1 6-6z"></path>
                            <rect x="2" y="9" width="4" height="12"></rect>
                            <circle cx="4" cy="4" r="2"></circle>
                        </svg>
                    </a>
                </div>
            </div>

            <div class="footer-section">
                <h4 class="footer-subtitle">Quick Links</h4>
                <ul class="footer-links">
                    <li><a href="<?php echo SITE_URL; ?>/about.php">About Us</a></li>
                    <li><a href="<?php echo SITE_URL; ?>/contact.php">Contact</a></li>
                    <li><a href="<?php echo SITE_URL; ?>/privacy.php">Privacy Policy</a></li>
                    <li><a href="<?php echo SITE_URL; ?>/auth/admin-signup.php">Admin Registration</a></li>
                </ul>
            </div>

            <div class="footer-section">
                <h4 class="footer-subtitle">For Students</h4>
                <ul class="footer-links">
                    <li><a href="<?php echo SITE_URL; ?>/auth/student-signup.php">Student Registration</a></li>
                    <li><a href="<?php echo SITE_URL; ?>/auth/student-login.php">Student Login</a></li>
                </ul>
                <h4 class="footer-subtitle">For Admins</h4>
                <ul class="footer-links">
                    <li><a href="<?php echo SITE_URL; ?>/auth/admin-signup.php">Admin Registration</a></li>
                    <li><a href="<?php echo SITE_URL; ?>/auth/admin-login.php">Admin Login</a></li>
                </ul>
            </div>

            <div class="footer-section">
                <h4 class="footer-subtitle">For Faculty</h4>
                <ul class="footer-links">
                    <li><a href="<?php echo SITE_URL; ?>/auth/faculty-signup.php">Faculty Registration</a></li>
                    <li><a href="<?php echo SITE_URL; ?>/auth/faculty-login.php">Faculty Login</a></li>
                </ul>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; <?php echo date('Y'); ?> NSU LinkUp. All rights reserved.</p>
            <p>Developed for North South University Students</p>
        </div>
    </div>
</footer>

<!-- Login Modal (for non-logged in users) -->
<?php if (!is_logged_in()): ?>
    <div id="loginModal" class="modal">
        <div class="modal-content">
            <span class="modal-close" onclick="closeModal('loginModal')">&times;</span>
            <h2 class="modal-title">Choose Login Type</h2>
            <div class="login-options">
                <a href="<?php echo SITE_URL; ?>/auth/student-login.php" class="login-option">
                    <div class="option-icon">👨‍🎓</div>
                    <h3>Student Login</h3>
                </a>
                <a href="<?php echo SITE_URL; ?>/auth/faculty-login.php" class="login-option">
                    <div class="option-icon">👨‍🏫</div>
                    <h3>Faculty Login</h3>
                </a>
                <a href="<?php echo SITE_URL; ?>/auth/organizer-login.php" class="login-option">
                    <div class="option-icon">🎯</div>
                    <h3>Organizer Login</h3>
                </a>
            </div>
        </div>
    </div>
<?php endif; ?>

<!-- Scripts -->
<script src="<?php echo SITE_URL; ?>/assets/js/common.js"></script>
<?php if (isset($page_js)): ?>
    <?php foreach ($page_js as $js): ?>
        <script src="<?php echo SITE_URL; ?>/assets/js/<?php echo $js; ?>"></script>
    <?php endforeach; ?>
<?php endif; ?>

<script>
    // Initialize user dropdown
    document.addEventListener('DOMContentLoaded', function() {
        const dropdownToggle = document.querySelector('.user-dropdown-toggle');
        const dropdownMenu = document.querySelector('.user-dropdown-menu');

        if (dropdownToggle && dropdownMenu) {
            // Toggle on click for mobile
            dropdownToggle.addEventListener('click', function(e) {
                e.stopPropagation();
                dropdownMenu.classList.toggle('show');
            });

            // Close when clicking outside
            document.addEventListener('click', function() {
                dropdownMenu.classList.remove('show');
            });

            // Prevent closing when clicking inside
            dropdownMenu.addEventListener('click', function(e) {
                e.stopPropagation();
            });
        }

        // Mobile menu
        const mobileToggle = document.getElementById('mobileMenuToggle');
        const navMenu = document.getElementById('navMenu');

        if (mobileToggle && navMenu) {
            mobileToggle.addEventListener('click', function() {
                navMenu.classList.toggle('active');
                this.classList.toggle('active');
            });
        }
    });

    // Show login modal function
    function showLoginModal() {
        commonFunctions.showModal('loginModal');
    }

    // Close modal function
    function closeModal(modalId) {
        commonFunctions.closeModal(modalId);
    }
</script>
</body>

</html>

<style>
    /* Footer Styles */
    .footer {
        background: var(--dark-bg);
        color: white;
        padding: 3rem 0 1rem;
        margin-top: 5rem;
    }

    .footer-content {
        display: grid;
        grid-template-columns: 2fr 1fr 1fr 1fr;
        gap: 2rem;
        margin-bottom: 2rem;
    }

    .footer-title {
        font-size: 1.5rem;
        margin-bottom: 1rem;
        background: var(--gradient-primary);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }

    .footer-description {
        color: rgba(255, 255, 255, 0.7);
        margin-bottom: 1.5rem;
        line-height: 1.6;
    }

    .footer-social {
        display: flex;
        gap: 1rem;
    }

    .social-link {
        width: 40px;
        height: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: rgba(255, 255, 255, 0.1);
        border-radius: 50%;
        color: white;
        transition: all 0.3s ease;
    }

    .social-link:hover {
        background: var(--primary-color);
        transform: translateY(-3px);
    }

    .footer-subtitle {
        font-size: 1.125rem;
        margin-bottom: 1rem;
        color: white;
    }

    .footer-links {
        list-style: none;
    }

    .footer-links li {
        margin-bottom: 0.75rem;
    }

    .footer-links a {
        color: rgba(255, 255, 255, 0.7);
        text-decoration: none;
        transition: color 0.3s ease;
    }

    .footer-links a:hover {
        color: white;
    }

    .footer-bottom {
        text-align: center;
        padding-top: 2rem;
        border-top: 1px solid rgba(255, 255, 255, 0.1);
    }

    .footer-bottom p {
        color: rgba(255, 255, 255, 0.5);
        margin: 0.25rem 0;
    }

    /* Login Options Modal Styles */
    .login-options {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 1.5rem;
        margin-top: 2rem;
    }

    .login-option {
        background: var(--light-bg);
        padding: 2rem;
        border-radius: 0.5rem;
        text-align: center;
        transition: all 0.3s ease;
        text-decoration: none;
        color: var(--text-primary);
        border: 2px solid transparent;
    }

    .login-option:hover {
        background: var(--gradient-primary);
        color: white;
        transform: translateY(-5px);
        box-shadow: var(--shadow-lg);
    }

    .option-icon {
        font-size: 3rem;
        margin-bottom: 1rem;
    }

    .login-option h3 {
        font-size: 1.25rem;
        margin: 0;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .footer-content {
            grid-template-columns: 1fr;
            text-align: center;
        }

        .footer-social {
            justify-content: center;
        }

        .login-options {
            grid-template-columns: 1fr;
        }

        .user-dropdown-menu.show {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }
    }
</style>
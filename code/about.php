<?php
// about.php
require_once 'includes/config.php';
require_once 'includes/functions.php';

$page_title = 'About Us';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> - NSU LinkUp</title>
    <link rel="stylesheet" href="assets/css/common.css">
    <link rel="stylesheet" href="assets/css/about.css">
</head>

<body>
    <?php include 'includes/header.php'; ?>

    <main class="main-content">
        <!-- Hero Section -->
        <section class="about-hero">
            <div class="container">
                <div class="hero-content">
                    <h1>About NSU LinkUp</h1>
                    <p class="hero-subtitle">Bridging the gap between academia and opportunity at North South University</p>
                </div>
            </div>
        </section>

        <!-- Mission Section -->
        <section class="mission-section">
            <div class="container">
                <div class="content-grid">
                    <div class="content-text">
                        <h2>Our Mission</h2>
                        <p>NSU LinkUp is designed to foster meaningful connections within the North South University community by creating a unified platform where students, faculty, and organizers can collaborate, share opportunities, and build lasting academic relationships.</p>
                        <p>We believe that the best learning happens when bright minds connect, collaborate, and create together. Our platform breaks down traditional barriers between different levels of academia, enabling seamless communication and opportunity sharing.</p>
                    </div>
                    <div class="content-image">
                        <div class="mission-visual">
                            <div class="mission-icon">
                                <svg width="60" height="60" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                                    <circle cx="9" cy="7" r="4"></circle>
                                    <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                                    <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                                </svg>
                            </div>
                            <h3>Connect & Collaborate</h3>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Features Section -->
        <section class="features-section">
            <div class="container">
                <h2 class="section-title">What We Offer</h2>
                <div class="features-grid">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M19 21l-7-5-7 5V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2z"></path>
                            </svg>
                        </div>
                        <h3>Research Opportunities</h3>
                        <p>Faculty can post research positions, and students can discover and apply for opportunities that match their interests and qualifications.</p>
                    </div>

                    <div class="feature-card">
                        <div class="feature-icon">
                            <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                                <line x1="16" y1="2" x2="16" y2="6"></line>
                                <line x1="8" y1="2" x2="8" y2="6"></line>
                                <line x1="3" y1="10" x2="21" y2="10"></line>
                            </svg>
                        </div>
                        <h3>Event Management</h3>
                        <p>Organizers can create and manage academic events, workshops, hackathons, and fests, reaching the entire university community.</p>
                    </div>

                    <div class="feature-card">
                        <div class="feature-icon">
                            <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"></path>
                                <rect x="8" y="2" width="8" height="4" rx="1" ry="1"></rect>
                            </svg>
                        </div>
                        <h3>Comprehensive Profiles</h3>
                        <p>Students and faculty can showcase their academic achievements, research interests, skills, and professional experiences.</p>
                    </div>

                    <div class="feature-card">
                        <div class="feature-icon">
                            <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
                            </svg>
                        </div>
                        <h3>Communication Hub</h3>
                        <p>Centralized announcements and notifications keep the entire community informed about important updates and opportunities.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Values Section -->
        <section class="values-section">
            <div class="container">
                <h2 class="section-title">Our Values</h2>
                <div class="values-grid">
                    <div class="value-item">
                        <h3>🎓 Academic Excellence</h3>
                        <p>We promote the highest standards of academic achievement and scholarly pursuit within our community.</p>
                    </div>
                    <div class="value-item">
                        <h3>🤝 Collaboration</h3>
                        <p>We believe in the power of working together to achieve greater outcomes than what's possible individually.</p>
                    </div>
                    <div class="value-item">
                        <h3>🌟 Innovation</h3>
                        <p>We encourage creative thinking and innovative approaches to solving academic and research challenges.</p>
                    </div>
                    <div class="value-item">
                        <h3>🔒 Integrity</h3>
                        <p>We maintain the highest ethical standards in all our interactions and platform operations.</p>
                    </div>
                    <div class="value-item">
                        <h3>🌍 Inclusivity</h3>
                        <p>We welcome and support users from all backgrounds, fostering a diverse and inclusive academic environment.</p>
                    </div>
                    <div class="value-item">
                        <h3>📈 Growth</h3>
                        <p>We are committed to continuous improvement and helping our users achieve their academic and professional goals.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Team Section -->
        <section class="team-section">
            <div class="container">
                <h2 class="section-title">Built for NSU, by NSU</h2>
                <div class="team-content">
                    <p>NSU LinkUp was conceived and developed by students and faculty who understand the unique needs of our academic community. We recognized the challenge of connecting talented students with meaningful research opportunities and academic events.</p>
                    <p>Our platform reflects the dynamic, innovative spirit of North South University, providing a modern solution for academic collaboration that grows with our community's needs.</p>
                </div>
            </div>
        </section>

        <!-- Stats Section -->
        <section class="stats-section">
            <div class="container">
                <div class="stats-grid">
                    <div class="stat-item">
                        <div class="stat-number">500+</div>
                        <div class="stat-label">Active Students</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-number">50+</div>
                        <div class="stat-label">Faculty Members</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-number">100+</div>
                        <div class="stat-label">Research Projects</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-number">200+</div>
                        <div class="stat-label">Events Organized</div>
                    </div>
                </div>
            </div>
        </section>

        <!-- CTA Section -->
        <section class="cta-section">
            <div class="container">
                <div class="cta-content">
                    <h2>Join the NSU LinkUp Community</h2>
                    <p>Be part of a growing network of students, faculty, and organizers working together to advance academic excellence at North South University.</p>
                    <?php if (!is_logged_in()): ?>
                        <div class="cta-buttons">
                            <a href="auth/student-signup.php" class="btn btn-primary btn-lg">Join as Student</a>
                            <a href="auth/faculty-signup.php" class="btn btn-outline btn-lg">Join as Faculty</a>
                        </div>
                    <?php else: ?>
                        <a href="<?php echo get_user_type(); ?>/dashboard.php" class="btn btn-primary btn-lg">Go to Dashboard</a>
                    <?php endif; ?>
                </div>
            </div>
        </section>
    </main>

    <?php include 'includes/footer.php'; ?>
    <script src="assets/js/common.js"></script>
</body>

</html>
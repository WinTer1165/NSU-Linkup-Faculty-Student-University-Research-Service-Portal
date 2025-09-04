<?php
// includes/header.php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/functions.php';

// Get user details if logged in
$user_details = null;
if (is_logged_in()) {
    $user_details = get_user_details(get_user_id(), get_user_type());
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title . ' - ' : ''; ?>NSU LinkUp</title>
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/common.css">
    <?php if (isset($page_css)): ?>
        <?php foreach ($page_css as $css): ?>
            <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/<?php echo $css; ?>">
        <?php endforeach; ?>
    <?php endif; ?>
</head>

<body>
    <!-- Navigation -->
    <nav class="navbar">
        <div class="container navbar-container">
            <a href="<?php echo SITE_URL; ?>" class="navbar-brand">NSU LinkUp</a>

            <?php if (is_logged_in()): ?>
                <!-- Logged in navigation -->
                <ul class="navbar-menu" id="navMenu">
                    <?php if (get_user_type() == 'student'): ?>
                        <li><a href="<?php echo SITE_URL; ?>/student/dashboard.php" class="navbar-link">Dashboard</a></li>
                        <li><a href="<?php echo SITE_URL; ?>/student/profile.php" class="navbar-link">Profile</a></li>
                        <li><a href="<?php echo SITE_URL; ?>/student/research.php" class="navbar-link">Research</a></li>
                        <li><a href="<?php echo SITE_URL; ?>/student/events.php" class="navbar-link">Events</a></li>
                        <li><a href="<?php echo SITE_URL; ?>/student/announcements.php" class="navbar-link">Announcements</a></li>
                    <?php elseif (get_user_type() == 'faculty'): ?>
                        <li><a href="<?php echo SITE_URL; ?>/faculty/dashboard.php" class="navbar-link">Dashboard</a></li>
                        <li><a href="<?php echo SITE_URL; ?>/faculty/profile.php" class="navbar-link">Profile</a></li>
                        <li><a href="<?php echo SITE_URL; ?>/faculty/create-research.php" class="navbar-link">Create Research</a></li>
                        <li><a href="<?php echo SITE_URL; ?>/faculty/research.php" class="navbar-link">All Research</a></li>
                        <li><a href="<?php echo SITE_URL; ?>/faculty/students.php" class="navbar-link">Students</a></li>
                    <?php elseif (get_user_type() == 'organizer'): ?>
                        <li><a href="<?php echo SITE_URL; ?>/organizer/dashboard.php" class="navbar-link">Dashboard</a></li>
                        <li><a href="<?php echo SITE_URL; ?>/organizer/create-event.php" class="navbar-link">Create Event</a></li>
                        <li><a href="<?php echo SITE_URL; ?>/organizer/all-events.php" class="navbar-link">My Events</a></li>
                    <?php elseif (get_user_type() == 'admin'): ?>
                        <li><a href="<?php echo SITE_URL; ?>/admin/dashboard.php" class="navbar-link">Dashboard</a></li>
                        <li><a href="<?php echo SITE_URL; ?>/admin/announcements.php" class="navbar-link">Announcements</a></li>
                        <li><a href="<?php echo SITE_URL; ?>/admin/contact-queries.php" class="navbar-link">Contact Queries</a></li>
                        <li><a href="<?php echo SITE_URL; ?>/admin/verify-users.php" class="navbar-link">Verify Users</a></li>
                        <li><a href="<?php echo SITE_URL; ?>/admin/manage-users.php" class="navbar-link">Manage Users</a></li>
                        <li><a href="<?php echo SITE_URL; ?>/admin/audit-logs.php" class="navbar-link">Audit Logs</a></li>
                    <?php endif; ?>

                    <li class="navbar-user-menu">
                        <div class="user-dropdown">
                            <button class="user-dropdown-toggle">
                                <?php if (isset($user_details['profile_image']) && $user_details['profile_image']): ?>
                                    <img src="<?php echo SITE_URL; ?>/assets/uploads/profiles/<?php echo $user_details['profile_image']; ?>" alt="Profile" class="user-avatar">
                                <?php else: ?>
                                    <div class="user-avatar-placeholder">
                                        <?php echo strtoupper(substr($user_details['full_name'] ?? 'U', 0, 1)); ?>
                                    </div>
                                <?php endif; ?>
                                <span class="user-name"><?php echo htmlspecialchars($user_details['full_name'] ?? 'User'); ?></span>
                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <polyline points="6 9 12 15 18 9"></polyline>
                                </svg>
                            </button>
                            <div class="user-dropdown-menu">
                                <div class="dropdown-header">
                                    <strong><?php echo htmlspecialchars($user_details['full_name'] ?? 'User'); ?></strong>
                                    <small><?php echo ucfirst(get_user_type()); ?></small>
                                </div>
                                <div class="dropdown-divider"></div>

                                <?php if (get_user_type() !== 'admin'): ?>
                                    <!-- Only show Profile and Settings for non-admin users -->
                                    <a href="<?php echo SITE_URL; ?>/<?php echo get_user_type(); ?>/profile.php" class="dropdown-item">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                                            <circle cx="12" cy="7" r="4"></circle>
                                        </svg>
                                        My Profile
                                    </a>
                                    <a href="<?php echo SITE_URL; ?>/settings.php" class="dropdown-item">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <circle cx="12" cy="12" r="3"></circle>
                                            <path d="M12 1v6m0 6v6m11-11h-6m-6 0H1"></path>
                                        </svg>
                                        Settings
                                    </a>
                                    <div class="dropdown-divider"></div>
                                <?php endif; ?>

                                <a href="<?php echo SITE_URL; ?>/auth/logout.php" class="dropdown-item text-danger">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                                        <polyline points="16 17 21 12 16 7"></polyline>
                                        <line x1="21" y1="12" x2="9" y2="12"></line>
                                    </svg>
                                    Logout
                                </a>
                            </div>
                        </div>
                    </li>
                </ul>
            <?php else: ?>
                <!-- Guest navigation -->
                <ul class="navbar-menu" id="navMenu">
                    <li><a href="<?php echo SITE_URL; ?>" class="navbar-link">Home</a></li>
                    <li><a href="<?php echo SITE_URL; ?>/about.php" class="navbar-link">About</a></li>
                    <li><a href="<?php echo SITE_URL; ?>/contact.php" class="navbar-link">Contact</a></li>
                    <li><a href="<?php echo SITE_URL; ?>/privacy.php" class="navbar-link">Privacy Policy</a></li>
                </ul>
            <?php endif; ?>

            <button class="mobile-menu-toggle" id="mobileMenuToggle">
                <span></span>
                <span></span>
                <span></span>
            </button>
        </div>
    </nav>

    <!-- Page Content -->
    <main class="main-content">
        <div class="container">
            <?php display_alert(); ?>

            <style>
                /* User Dropdown Styles */
                .navbar-user-menu {
                    margin-left: auto;
                }

                .user-dropdown {
                    position: relative;
                }

                .user-dropdown-toggle {
                    display: flex;
                    align-items: center;
                    gap: 0.5rem;
                    background: none;
                    border: none;
                    cursor: pointer;
                    padding: 0.5rem;
                    border-radius: 0.5rem;
                    transition: background 0.3s ease;
                }

                .user-dropdown-toggle:hover {
                    background: var(--light-bg);
                }

                .user-avatar {
                    width: 32px;
                    height: 32px;
                    border-radius: 50%;
                    object-fit: cover;
                }

                .user-avatar-placeholder {
                    width: 32px;
                    height: 32px;
                    border-radius: 50%;
                    background: var(--gradient-primary);
                    color: white;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    font-weight: 600;
                }

                .user-name {
                    font-weight: 500;
                    color: var(--text-primary);
                }

                .user-dropdown-menu {
                    position: absolute;
                    top: 100%;
                    right: 0;
                    margin-top: 0.5rem;
                    background: var(--card-bg);
                    border-radius: 0.5rem;
                    box-shadow: var(--shadow-lg);
                    min-width: 200px;
                    opacity: 0;
                    visibility: hidden;
                    transform: translateY(-10px);
                    transition: all 0.3s ease;
                }

                .user-dropdown:hover .user-dropdown-menu {
                    opacity: 1;
                    visibility: visible;
                    transform: translateY(0);
                }

                .dropdown-header {
                    padding: 1rem;
                }

                .dropdown-header strong {
                    display: block;
                    color: var(--text-primary);
                }

                .dropdown-header small {
                    color: var(--text-secondary);
                    text-transform: capitalize;
                }

                .dropdown-divider {
                    height: 1px;
                    background: var(--border-color);
                    margin: 0;
                }

                .dropdown-item {
                    display: flex;
                    align-items: center;
                    gap: 0.75rem;
                    padding: 0.75rem 1rem;
                    color: var(--text-primary);
                    text-decoration: none;
                    transition: background 0.3s ease;
                }

                .dropdown-item:hover {
                    background: var(--light-bg);
                }

                .dropdown-item.text-danger {
                    color: var(--danger-color);
                }

                .main-content {
                    min-height: calc(100vh - 70px);
                    padding: 2rem 0;
                }
            </style>
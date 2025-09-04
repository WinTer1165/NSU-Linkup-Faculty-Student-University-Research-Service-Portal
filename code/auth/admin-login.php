<?php
// auth/admin-login.php
require_once '../includes/config.php';
require_once '../includes/db_connect.php';
require_once '../includes/functions.php';

// Check if already logged in
if (is_logged_in()) {
    redirect(get_user_type() . '/dashboard.php');
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = sanitize_input($_POST['email']);
    $password = $_POST['password'];
    $remember = isset($_POST['remember']);

    // Validate inputs
    $errors = [];

    if (empty($email)) {
        $errors[] = "Email is required";
    } elseif (!validate_email($email)) {
        $errors[] = "Invalid email format";
    }

    if (empty($password)) {
        $errors[] = "Password is required";
    }

    if (empty($errors)) {
        // Check credentials
        $stmt = $db->prepare("SELECT u.user_id, u.password, u.is_verified, u.is_banned, a.admin_id, a.full_name 
                             FROM users u 
                             JOIN admins a ON u.user_id = a.user_id 
                             WHERE u.email = ? AND u.user_type = 'admin'");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();

            if (verify_password($password, $user['password'])) {
                // Set session
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['user_type'] = 'admin';
                $_SESSION['admin_id'] = $user['admin_id'];
                $_SESSION['full_name'] = $user['full_name'];

                // Remember me
                if ($remember) {
                    setcookie('remember_token', bin2hex(random_bytes(32)), time() + (30 * 24 * 60 * 60), '/');
                }

                // Log the login
                log_audit('LOGIN', 'users', $user['user_id']);

                redirect('admin/dashboard.php');
            } else {
                $errors[] = "Invalid email or password";
            }
        } else {
            $errors[] = "Invalid email or password";
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
    <title>Admin Login - NSU LinkUp</title>
    <link rel="stylesheet" href="../assets/css/common.css">
    <link rel="stylesheet" href="../assets/css/login.css">
</head>

<body>
    <div class="login-container">
        <div class="login-box">
            <div class="login-header">
                <a href="../index.php" class="back-link">← Back to Home</a>
                <h1 class="login-title">Admin Login</h1>
                <p class="login-subtitle">Access the administration panel</p>
            </div>

            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger">
                    <?php foreach ($errors as $error): ?>
                        <p><?php echo htmlspecialchars($error); ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="" data-validate>
                <div class="form-group">
                    <label for="email" class="form-label">Admin Email</label>
                    <input type="email"
                        id="email"
                        name="email"
                        class="form-control"
                        placeholder="admin@northsouth.edu"
                        value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>"
                        required>
                </div>

                <div class="form-group">
                    <label for="password" class="form-label">Password</label>
                    <div class="password-wrapper">
                        <input type="password"
                            id="password"
                            name="password"
                            class="form-control"
                            placeholder="Enter your password"
                            data-min-length="6"
                            required>
                        <button type="button" class="password-toggle" onclick="togglePassword()">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                <circle cx="12" cy="12" r="3"></circle>
                            </svg>
                        </button>
                    </div>
                </div>

                <div class="form-group">
                    <label class="checkbox-label">
                        <input type="checkbox" name="remember" value="1">
                        <span>Remember me for 30 days</span>
                    </label>
                </div>

                <button type="submit" class="btn btn-primary btn-block">Login</button>

                <div class="login-footer">
                    <p class="text-muted">Admin access only. Unauthorized access is prohibited.</p>
                </div>
            </form>
        </div>

        <div class="login-banner" style="background: linear-gradient(135deg, #8B5CF6 0%, #6366F1 100%);">
            <div class="banner-content">
                <h2>Admin Portal</h2>
                <p>Manage the NSU LinkUp platform with powerful administrative tools.</p>
                <div class="banner-features">
                    <div class="banner-feature">
                        <span class="feature-icon">👥</span>
                        <span>User Management</span>
                    </div>
                    <div class="banner-feature">
                        <span class="feature-icon">📊</span>
                        <span>System Analytics</span>
                    </div>
                    <div class="banner-feature">
                        <span class="feature-icon">🔒</span>
                        <span>Security Controls</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="../assets/js/common.js"></script>
    <script src="../assets/js/login.js"></script>
</body>

</html>
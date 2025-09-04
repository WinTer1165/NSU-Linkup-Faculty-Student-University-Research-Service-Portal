<?php
require_once '../includes/config.php';
require_once '../includes/db_connect.php';
require_once '../includes/functions.php';

$conn = $db;

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    header("Location: ../admin/dashboard.php");
    exit();
}

// Admin signup requires special authorization
$admin_secret_key = "NSU_ADMIN_2025";

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    require_once '../includes/db_connect.php';

    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $full_name = filter_var($_POST['full_name'], FILTER_UNSAFE_RAW);
    $secret_key = $_POST['secret_key'] ?? '';

    // Validation
    if (empty($email) || empty($password) || empty($full_name) || empty($secret_key)) {
        $error = "All fields are required!";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format!";
    } elseif (!preg_match("/^[a-zA-Z0-9._%+-]+@northsouth\.edu$/", $email)) {
        $error = "Email must be an NSU email address!";
    } elseif (strlen($password) < 8) {
        $error = "Password must be at least 8 characters long!";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match!";
    } elseif ($secret_key !== $admin_secret_key) {
        $error = "Invalid admin authorization key!";
    } else {
        // Check if email already exists
        $stmt = $conn->prepare("SELECT user_id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $error = "Email already registered!";
        } else {
            // Hash password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            // Start transaction
            $conn->begin_transaction();

            try {
                // Insert into users table
                $stmt = $conn->prepare("INSERT INTO users (email, password, user_type, is_verified) VALUES (?, ?, 'admin', TRUE)");
                $stmt->bind_param("ss", $email, $hashed_password);
                $stmt->execute();

                $user_id = $conn->insert_id;

                // Insert into admins table
                $stmt = $conn->prepare("INSERT INTO admins (user_id, full_name) VALUES (?, ?)");
                $stmt->bind_param("is", $user_id, $full_name);
                $stmt->execute();

                // Log the action
                $action = "Admin account created";
                $ip_address = $_SERVER['REMOTE_ADDR'];
                $stmt = $conn->prepare("INSERT INTO audit_logs (user_id, action, table_affected, record_id, ip_address) VALUES (?, ?, 'admins', ?, ?)");
                $stmt->bind_param("isis", $user_id, $action, $user_id, $ip_address);
                $stmt->execute();

                $conn->commit();
                $success = "Admin account created successfully! Redirecting to login...";

                // Redirect after 2 seconds
                header("refresh:2;url=../auth/admin-login.php");
            } catch (Exception $e) {
                $conn->rollback();
                $error = "Registration failed. Please try again.";
            }
        }

        $stmt->close();
        $conn->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Sign Up - NSU LinkUp</title>
    <link rel="stylesheet" href="../assets/css/common.css">
    <link rel="stylesheet" href="../assets/css/admin-signup.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>

<body>
    <div class="container">
        <div class="signup-wrapper">
            <div class="signup-card admin-signup">
                <div class="signup-header">
                    <div class="logo">
                        <i class="fas fa-user-shield"></i>
                    </div>
                    <h1>Admin Registration</h1>
                    <p>Create an administrator account for NSU LinkUp</p>
                </div>

                <?php if ($error): ?>
                    <div class="alert alert-error">
                        <i class="fas fa-exclamation-circle"></i>
                        <?php echo $error; ?>
                    </div>
                <?php endif; ?>

                <?php if ($success): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i>
                        <?php echo $success; ?>
                    </div>
                <?php endif; ?>

                <form method="POST" class="signup-form" id="adminSignupForm">
                    <div class="form-group">
                        <label for="full_name">
                            <i class="fas fa-user"></i>
                            Full Name
                        </label>
                        <input type="text" id="full_name" name="full_name" required
                            placeholder="Enter your full name"
                            value="<?php echo isset($_POST['full_name']) ? htmlspecialchars($_POST['full_name']) : ''; ?>">
                    </div>

                    <div class="form-group">
                        <label for="email">
                            <i class="fas fa-envelope"></i>
                            NSU Email
                        </label>
                        <input type="email" id="email" name="email" required
                            placeholder="admin@northsouth.edu"
                            pattern="[a-zA-Z0-9._%+-]+@northsouth\.edu"
                            value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                        <small class="form-hint">Must be a valid NSU email address</small>
                    </div>

                    <div class="form-group">
                        <label for="password">
                            <i class="fas fa-lock"></i>
                            Password
                        </label>
                        <div class="password-input">
                            <input type="password" id="password" name="password" required
                                placeholder="Minimum 8 characters">
                            <button type="button" class="toggle-password" data-target="password">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                        <div class="password-strength" id="passwordStrength"></div>
                    </div>

                    <div class="form-group">
                        <label for="confirm_password">
                            <i class="fas fa-check-double"></i>
                            Confirm Password
                        </label>
                        <div class="password-input">
                            <input type="password" id="confirm_password" name="confirm_password" required
                                placeholder="Re-enter your password">
                            <button type="button" class="toggle-password" data-target="confirm_password">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="secret_key">
                            <i class="fas fa-key"></i>
                            Admin Authorization Key
                        </label>
                        <input type="password" id="secret_key" name="secret_key" required
                            placeholder="Enter admin secret key">
                        <small class="form-hint">Contact system administrator for the key</small>
                    </div>

                    <button type="submit" class="btn btn-primary btn-block">
                        <i class="fas fa-user-plus"></i>
                        Create Admin Account
                    </button>
                </form>

                <div class="signup-footer">
                    <p>Already have an account? <a href="admin-login.php">Login here</a></p>
                </div>
            </div>
        </div>
    </div>

    <script src="../assets/js/common.js"></script>
    <script src="../assets/js/signup.js"></script>
    <script src="../assets/js/admin-signup.js"></script>
</body>

</html>
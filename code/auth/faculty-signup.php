<?php
// auth/faculty-signup.php
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
    $confirm_password = $_POST['confirm_password'];
    $full_name = sanitize_input($_POST['full_name']);
    $title = sanitize_input($_POST['title']);
    $prefix = sanitize_input($_POST['prefix']);
    $office = sanitize_input($_POST['office']);
    $phone = sanitize_input($_POST['phone']);
    $education = sanitize_input($_POST['education']);
    $research_interests = sanitize_input($_POST['research_interests']);

    // Validate inputs
    $errors = [];

    // Email validation
    if (empty($email)) {
        $errors[] = "Email is required";
    } elseif (!validate_email($email)) {
        $errors[] = "Invalid email format";
    } elseif (!preg_match('/@northsouth\.edu$/', $email)) {
        $errors[] = "Please use your NSU email address";
    }

    // Password validation
    if (empty($password)) {
        $errors[] = "Password is required";
    } elseif (strlen($password) < 6) {
        $errors[] = "Password must be at least 6 characters";
    } elseif ($password !== $confirm_password) {
        $errors[] = "Passwords do not match";
    }

    // Other validations
    if (empty($full_name)) {
        $errors[] = "Full name is required";
    }

    if (empty($title)) {
        $errors[] = "Academic title is required";
    }

    if (empty($prefix)) {
        $errors[] = "Prefix is required";
    }

    if (empty($education)) {
        $errors[] = "Education background is required";
    }

    // Check if email already exists
    if (empty($errors)) {
        $stmt = $db->prepare("SELECT user_id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $errors[] = "Email already registered";
        }
        $stmt->close();
    }

    // If no errors, create account
    if (empty($errors)) {
        $db->begin_transaction();

        try {
            // Insert into users table (faculty needs verification)
            $hashed_password = hash_password($password);
            $stmt = $db->prepare("INSERT INTO users (email, password, user_type, is_verified) VALUES (?, ?, 'faculty', FALSE)");
            $stmt->bind_param("ss", $email, $hashed_password);
            $stmt->execute();
            $user_id = $db->insert_id;
            $stmt->close();

            // Insert into faculty table
            $stmt = $db->prepare("INSERT INTO faculty (user_id, full_name, title, prefix, office, phone, education, research_interests) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("isssssss", $user_id, $full_name, $title, $prefix, $office, $phone, $education, $research_interests);
            $stmt->execute();
            $stmt->close();

            $db->commit();

            // Log the registration
            log_audit('REGISTER', 'users', $user_id);

            show_alert('Registration successful! Your account is pending admin verification. You will be notified once approved.', 'success');
        } catch (Exception $e) {
            $db->rollback();
            $errors[] = "Registration failed. Please try again.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Faculty Sign Up - NSU LinkUp</title>
    <link rel="stylesheet" href="../assets/css/common.css">
    <link rel="stylesheet" href="../assets/css/signup.css">
</head>

<body>
    <div class="signup-container">
        <div class="signup-box">
            <div class="signup-header">
                <a href="../index.php" class="back-link">← Back to Home</a>
                <h1 class="signup-title">Create Faculty Account</h1>
                <p class="signup-subtitle">Join NSU LinkUp to post research opportunities</p>
            </div>

            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger">
                    <?php foreach ($errors as $error): ?>
                        <p><?php echo htmlspecialchars($error); ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <?php if (isset($_SESSION['alert']) && $_SESSION['alert']['type'] == 'success'): ?>
                <div class="alert alert-success">
                    <p><?php echo $_SESSION['alert']['message']; ?></p>
                </div>
                <?php unset($_SESSION['alert']); ?>
            <?php else: ?>

                <form method="POST" action="" data-validate>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="prefix" class="form-label">Prefix</label>
                            <select id="prefix" name="prefix" class="form-control" required>
                                <option value="">Select prefix</option>
                                <option value="Dr.">Dr.</option>
                                <option value="Prof.">Prof.</option>
                                <option value="Mr.">Mr.</option>
                                <option value="Ms.">Ms.</option>
                                <option value="Mrs.">Mrs.</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="full_name" class="form-label">Full Name</label>
                            <input type="text"
                                id="full_name"
                                name="full_name"
                                class="form-control"
                                placeholder="Mubin Islam"
                                value="<?php echo isset($_POST['full_name']) ? htmlspecialchars($_POST['full_name']) : ''; ?>"
                                required>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="title" class="form-label">Academic Title</label>
                            <select id="title" name="title" class="form-control" required>
                                <option value="">Select title</option>
                                <option value="Professor">Professor</option>
                                <option value="Associate Professor">Associate Professor</option>
                                <option value="Assistant Professor">Assistant Professor</option>
                                <option value="Senior Lecturer">Senior Lecturer</option>
                                <option value="Lecturer">Lecturer</option>
                                <option value="Teaching Assistant">Teaching Assistant</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="office" class="form-label">Office Location</label>
                            <input type="text"
                                id="office"
                                name="office"
                                class="form-control"
                                placeholder="e.g., NAC 1005"
                                value="<?php echo isset($_POST['office']) ? htmlspecialchars($_POST['office']) : ''; ?>">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="email" class="form-label">NSU Email</label>
                            <input type="email"
                                id="email"
                                name="email"
                                class="form-control"
                                placeholder="mubin.islam@northsouth.edu"
                                value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>"
                                required>
                            <small class="form-text">Use your official NSU email address</small>
                        </div>

                        <div class="form-group">
                            <label for="phone" class="form-label">Phone Number</label>
                            <input type="tel"
                                id="phone"
                                name="phone"
                                class="form-control"
                                placeholder="+880 1XXXXXXXXX"
                                value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="password" class="form-label">Password</label>
                            <input type="password"
                                id="password"
                                name="password"
                                class="form-control"
                                placeholder="Minimum 6 characters"
                                data-min-length="6"
                                required>
                        </div>

                        <div class="form-group">
                            <label for="confirm_password" class="form-label">Confirm Password</label>
                            <input type="password"
                                id="confirm_password"
                                name="confirm_password"
                                class="form-control"
                                placeholder="Re-enter password"
                                required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="education" class="form-label">Education Background</label>
                        <textarea id="education"
                            name="education"
                            class="form-control"
                            rows="3"
                            placeholder="e.g., PhD in Computer Science from MIT, MS in Software Engineering from Stanford"
                            required><?php echo isset($_POST['education']) ? htmlspecialchars($_POST['education']) : ''; ?></textarea>
                    </div>

                    <div class="form-group">
                        <label for="research_interests" class="form-label">Research Interests</label>
                        <textarea id="research_interests"
                            name="research_interests"
                            class="form-control"
                            rows="3"
                            placeholder="e.g., Machine Learning, Artificial Intelligence, Data Mining"><?php echo isset($_POST['research_interests']) ? htmlspecialchars($_POST['research_interests']) : ''; ?></textarea>
                    </div>

                    <div class="form-group">
                        <label class="checkbox-label">
                            <input type="checkbox" name="terms" value="1" required>
                            <span>I agree to the <a href="../privacy.php" target="_blank">Terms and Privacy Policy</a></span>
                        </label>
                    </div>

                    <div class="alert alert-info">
                        <p><strong>Note:</strong> Faculty accounts require admin verification before access is granted. You will receive an email notification once your account is approved.</p>
                    </div>

                    <button type="submit" class="btn btn-primary btn-block">Create Account</button>

                    <div class="signup-footer">
                        <p>Already have an account? <a href="faculty-login.php">Login here</a></p>
                    </div>
                </form>

            <?php endif; ?>
        </div>
    </div>

    <script src="../assets/js/common.js"></script>
    <script src="../assets/js/signup.js"></script>
</body>

</html>
<?php
// auth/student-signup.php
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
    $phone = sanitize_input($_POST['phone']);
    $degree = sanitize_input($_POST['degree']);
    $start_date = sanitize_input($_POST['start_date']);

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

    if (empty($degree)) {
        $errors[] = "Degree is required";
    }

    if (empty($start_date)) {
        $errors[] = "Start date is required";
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
            // Insert into users table
            $hashed_password = hash_password($password);
            $stmt = $db->prepare("INSERT INTO users (email, password, user_type, is_verified) VALUES (?, ?, 'student', TRUE)");
            $stmt->bind_param("ss", $email, $hashed_password);
            $stmt->execute();
            $user_id = $db->insert_id;
            $stmt->close();

            // Insert into students table
            $stmt = $db->prepare("INSERT INTO students (user_id, full_name, phone, degree, university, start_date) VALUES (?, ?, ?, ?, 'NSU', ?)");
            $stmt->bind_param("issss", $user_id, $full_name, $phone, $degree, $start_date);
            $stmt->execute();
            $student_id = $db->insert_id;
            $stmt->close();

            $db->commit();

            // Log the registration
            log_audit('REGISTER', 'users', $user_id);

            // Auto login
            $_SESSION['user_id'] = $user_id;
            $_SESSION['user_type'] = 'student';
            $_SESSION['student_id'] = $student_id;
            $_SESSION['full_name'] = $full_name;

            show_alert('Welcome to NSU LinkUp! Please complete your profile.', 'success');
            redirect('student/profile.php');
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
    <title>Student Sign Up - NSU LinkUp</title>
    <link rel="stylesheet" href="../assets/css/common.css">
    <link rel="stylesheet" href="../assets/css/signup.css">
</head>

<body>
    <div class="signup-container">
        <div class="signup-box">
            <div class="signup-header">
                <a href="../index.php" class="back-link">← Back to Home</a>
                <h1 class="signup-title">Create Student Account</h1>
                <p class="signup-subtitle">Join NSU LinkUp to connect with faculty and opportunities</p>
            </div>

            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger">
                    <?php foreach ($errors as $error): ?>
                        <p><?php echo htmlspecialchars($error); ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="" data-validate>
                <div class="form-row">
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

                <div class="form-row">
                    <div class="form-group">
                        <label for="phone" class="form-label">Phone Number (Optional)</label>
                        <input type="tel"
                            id="phone"
                            name="phone"
                            class="form-control"
                            placeholder="+880 1XXXXXXXXX"
                            value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>">
                    </div>

                    <div class="form-group">
                        <label for="degree" class="form-label">Degree Program</label>
                        <select id="degree" name="degree" class="form-control" required>
                            <option value="">Select your degree</option>
                            <option value="BSc in CSE">BSc in Computer Science & Engineering</option>
                            <option value="BSc in EEE">BSc in Electrical & Electronic Engineering</option>
                            <option value="BBA">Bachelor of Business Administration</option>
                            <option value="BA in English">BA in English</option>
                            <option value="BSc in Mathematics">BSc in Mathematics</option>
                            <option value="BSc in Physics">BSc in Physics</option>
                            <option value="BSc in Biochemistry">BSc in Biochemistry</option>
                            <option value="BArch">Bachelor of Architecture</option>
                            <option value="LLB">Bachelor of Laws</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label for="start_date" class="form-label">Program Start Date</label>
                    <input type="date"
                        id="start_date"
                        name="start_date"
                        class="form-control"
                        value="<?php echo isset($_POST['start_date']) ? htmlspecialchars($_POST['start_date']) : ''; ?>"
                        required>
                </div>

                <div class="form-group">
                    <label class="checkbox-label">
                        <input type="checkbox" name="terms" value="1" required>
                        <span>I agree to the <a href="../privacy.php" target="_blank">Terms and Privacy Policy</a></span>
                    </label>
                </div>

                <button type="submit" class="btn btn-primary btn-block">Create Account</button>

                <div class="signup-footer">
                    <p>Already have an account? <a href="student-login.php">Login here</a></p>
                </div>
            </form>
        </div>
    </div>

    <script src="../assets/js/common.js"></script>
    <script src="../assets/js/signup.js"></script>
</body>

</html>
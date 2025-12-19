<?php
$page_title = "Register";
require_once '../config/config.php';
require_once '../config/database.php';
require_once '../config/session.php';
require_once '../includes/functions.php';

$database = new Database();
$conn = $database->getConnection();

$error = '';
$success = '';

// Get departments and programs - FIXED: Removed status condition
try {
    $departments = $conn->query("SELECT * FROM departments ORDER BY department_name")->fetchAll();
    $programs = $conn->query("SELECT * FROM programs ORDER BY program_name")->fetchAll();
} catch (PDOException $e) {
    $departments = [];
    $programs = [];
    error_log("Database error: " . $e->getMessage());
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitize($_POST['username']);
    $email = sanitize($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $first_name = sanitize($_POST['first_name']);
    $last_name = sanitize($_POST['last_name']);
    $role = sanitize($_POST['role']);
    $department_id = !empty($_POST['department_id']) ? sanitize($_POST['department_id']) : null;
    $program_id = !empty($_POST['program_id']) ? sanitize($_POST['program_id']) : null;

    if (empty($username) || empty($email) || empty($password) || empty($first_name) || empty($last_name)) {
        $error = "All fields are required";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format";
    } else {
        try {
            // Check if username exists
            $stmt = $conn->prepare("SELECT user_id FROM users WHERE username = ?");
            $stmt->execute([$username]);
            if ($stmt->fetch()) {
                $error = "Username already exists";
            } else {
                // Check if email exists
                $stmt = $conn->prepare("SELECT user_id FROM users WHERE email = ?");
                $stmt->execute([$email]);
                if ($stmt->fetch()) {
                    $error = "Email already exists";
                } else {
                    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

                    // Insert new user
                    $stmt = $conn->prepare("INSERT INTO users (username, email, password, first_name, last_name, role, department_id, program_id, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'active')");

                    if ($stmt->execute([$username, $email, $hashed_password, $first_name, $last_name, $role, $department_id, $program_id])) {
                        $user_id = $conn->lastInsertId();
                        $success = "Registration successful! You can now login.";

                        // Log activity
                        try {
                            logActivity($conn, $user_id, 'REGISTER', 'users', $user_id, 'New user registered');
                        } catch (Exception $e) {
                            // Continue even if logging fails
                            error_log("Activity log error: " . $e->getMessage());
                        }
                    } else {
                        $error = "Registration failed. Please try again.";
                    }
                }
            }
        } catch (PDOException $e) {
            $error = "Registration failed. Please try again.";
            error_log("Registration error: " . $e->getMessage());
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> - Thesis Archive</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/auth.css">
</head>

<body class="auth-page">
    <div class="auth-minimal-container register-container">
        <div class="auth-minimal-box register-box">
            <div class="auth-minimal-header">
                <div class="auth-logo">📚</div>
                <h1>Create Account</h1>
                <p>Join Thesis Archive System</p>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-danger">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="alert alert-success">
                    <?php echo htmlspecialchars($success); ?>
                    <p style="margin-top: 10px;"><a href="login.php">Click here to login</a></p>
                </div>
            <?php endif; ?>

            <?php if (!$success): ?>
                <form method="POST" action="" class="auth-minimal-form">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="first_name" class="form-label">First Name</label>
                            <input type="text" class="form-control" id="first_name" name="first_name"
                                value="<?php echo isset($_POST['first_name']) ? htmlspecialchars($_POST['first_name']) : ''; ?>"
                                placeholder="Enter first name"
                                required>
                        </div>

                        <div class="form-group">
                            <label for="last_name" class="form-label">Last Name</label>
                            <input type="text" class="form-control" id="last_name" name="last_name"
                                value="<?php echo isset($_POST['last_name']) ? htmlspecialchars($_POST['last_name']) : ''; ?>"
                                placeholder="Enter last name"
                                required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="username" class="form-label">Username</label>
                        <input type="text" class="form-control" id="username" name="username"
                            value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>"
                            placeholder="Choose a username"
                            required>
                    </div>

                    <div class="form-group">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" name="email"
                            value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>"
                            placeholder="your.email@example.com"
                            required>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" class="form-control" id="password" name="password"
                                placeholder="Min. 6 characters" required>
                        </div>

                        <div class="form-group">
                            <label for="confirm_password" class="form-label">Confirm Password</label>
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password"
                                placeholder="Re-enter password" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="role" class="form-label">Role</label>
                        <select class="form-control" id="role" name="role" required>
                            <option value="">Select Role</option>
                            <option value="student" <?php echo (isset($_POST['role']) && $_POST['role'] === 'student') ? 'selected' : ''; ?>>Student</option>
                            <option value="adviser" <?php echo (isset($_POST['role']) && $_POST['role'] === 'adviser') ? 'selected' : ''; ?>>Adviser</option>
                        </select>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="department_id" class="form-label">Department (Optional)</label>
                            <select class="form-control" id="department_id" name="department_id">
                                <option value="">Select Department</option>
                                <?php foreach ($departments as $dept): ?>
                                    <option value="<?php echo $dept['department_id']; ?>"
                                        <?php echo (isset($_POST['department_id']) && $_POST['department_id'] == $dept['department_id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($dept['department_name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="program_id" class="form-label">Program (Optional)</label>
                            <select class="form-control" id="program_id" name="program_id">
                                <option value="">Select Program</option>
                                <?php foreach ($programs as $prog): ?>
                                    <option value="<?php echo $prog['program_id']; ?>"
                                        <?php echo (isset($_POST['program_id']) && $_POST['program_id'] == $prog['program_id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($prog['program_name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary w-100">
                        Create Account
                    </button>
                </form>
            <?php endif; ?>

            <div class="auth-minimal-footer">
                <p><a href="../index.php">← Back to Home</a></p>
                <p class="text-muted">Already have an account? <a href="login.php">Sign In</a></p>
            </div>
        </div>
    </div>

    <script>
        // Auto-hide alerts after 5 seconds
        document.addEventListener('DOMContentLoaded', function() {
            const alerts = document.querySelectorAll('.alert-danger');
            alerts.forEach(alert => {
                setTimeout(() => {
                    alert.style.opacity = '0';
                    setTimeout(() => alert.remove(), 300);
                }, 5000);
            });
        });

        // Password match validation
        const confirmPassword = document.getElementById('confirm_password');
        if (confirmPassword) {
            confirmPassword.addEventListener('input', function() {
                const password = document.getElementById('password').value;
                const confirmPasswordValue = this.value;

                if (password !== confirmPasswordValue) {
                    this.setCustomValidity('Passwords do not match');
                } else {
                    this.setCustomValidity('');
                }
            });
        }

        // Real-time password match indicator
        const password = document.getElementById('password');
        if (password && confirmPassword) {
            const checkPasswordMatch = () => {
                if (confirmPassword.value.length > 0) {
                    if (password.value === confirmPassword.value) {
                        confirmPassword.style.borderColor = 'var(--success)';
                    } else {
                        confirmPassword.style.borderColor = 'var(--danger)';
                    }
                } else {
                    confirmPassword.style.borderColor = 'var(--border)';
                }
            };

            password.addEventListener('input', checkPasswordMatch);
            confirmPassword.addEventListener('input', checkPasswordMatch);
        }
    </script>
</body>

</html>
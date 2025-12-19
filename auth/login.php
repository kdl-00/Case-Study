<?php
$page_title = "Login";
require_once '../config/config.php';
require_once '../config/database.php';
require_once '../config/session.php';
require_once '../includes/functions.php';

// Redirect if already logged in
if (isLoggedIn()) {
    $redirect_url = BASE_URL . getRole() . '/index.php';
    header("Location: $redirect_url");
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitize($_POST['username']);
    $password = $_POST['password'];

    if (empty($username) || empty($password)) {
        $error = "All fields are required";
    } else {
        $database = new Database();
        $conn = $database->getConnection();

        $stmt = $conn->prepare("SELECT * FROM users WHERE username = ? AND status = 'active'");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['first_name'] = $user['first_name'];
            $_SESSION['last_name'] = $user['last_name'];

            logActivity($conn, $user['user_id'], 'LOGIN', 'users', $user['user_id'], 'User logged in');

            $redirect_url = BASE_URL . $user['role'] . '/index.php';
            header("Location: $redirect_url");
            exit();
        } else {
            $error = "Invalid username or password";
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
    <div class="auth-minimal-container">
        <div class="auth-minimal-box">
            <div class="auth-minimal-header">
                <div class="auth-logo">📚</div>
                <h1>Thesis Archive</h1>
                <p>Sign in to continue</p>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-danger">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="" class="auth-minimal-form">
                <div class="form-group">
                    <label for="username" class="form-label">Username</label>
                    <input
                        type="text"
                        class="form-control"
                        id="username"
                        name="username"
                        placeholder="Enter username"
                        value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>"
                        required
                        autofocus>
                </div>

                <div class="form-group">
                    <label for="password" class="form-label">Password</label>
                    <input
                        type="password"
                        class="form-control"
                        id="password"
                        name="password"
                        placeholder="Enter password"
                        required>
                </div>

                <button type="submit" class="btn btn-primary w-100">
                    Sign In
                </button>
            </form>

            <div class="auth-minimal-footer">
                <p><a href="../index.php">← Back to Home</a></p>
                <p class="text-muted">Don't have an account? <a href="register.php">Register</a></p>
            </div>
        </div>
    </div>

    <script>
        // Auto-hide alerts after 4 seconds
        document.addEventListener('DOMContentLoaded', function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                setTimeout(() => {
                    alert.style.opacity = '0';
                    setTimeout(() => alert.remove(), 300);
                }, 4000);
            });
        });
    </script>
</body>

</html>
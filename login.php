<?php
session_start();
require_once 'config.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        $error = 'Please enter both username and password.';
    } else {
        $query = $db->prepare("SELECT id, password, full_name, user_type, profile_picture, signature FROM users WHERE username = ?");
        $query->execute([$username]);
        $user = $query->fetch();

        if ($user && password_verify($password, $user['password'])) {
            // Check if profile picture and signature are uploaded
            if (empty($user['profile_picture']) || empty($user['signature'])) {
                $_SESSION['temp_user_id'] = $user['id'];
                header('Location: completeProfile.php');
                exit();
            }

            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['full_name'];
            $_SESSION['user_type'] = $user['user_type'];

            header('Location: index.php');
            exit();
        } else {
            $error = 'Invalid username or password.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Enrollment System</title>
    <link rel="stylesheet" href="css/auth.css">
</head>

<body>
    <div class="login-container">
        <div class="logo">
            <h1>🎓 Enrollment System</h1>
            <p>Sign in to your account</p>
        </div>

        <?php if ($error): ?>
            <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" required>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>

            <button type="submit" class="btn">Sign In</button>
        </form>

        <div class="register-link">
            Don't have an account? <a href="register.php">Register here</a>
        </div>

        <div class="demo-credentials">
            <h4>Demo Credentials:</h4>
            <p><strong>Admin:</strong> admin / password</p>
            <p><strong>Faculty:</strong> faculty / faculty</p>
            <p><strong>Student:</strong> student / student</p>
        </div>
    </div>
</body>

</html>
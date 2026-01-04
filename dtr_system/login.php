<?php
require_once 'config.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    // Get users
    $users = [];
    if (file_exists(USERS_FILE)) {
        $content = file_get_contents(USERS_FILE);
        if (!empty($content)) {
            $users = unserialize($content);
        }
    }

    // Find user
    $foundUser = null;
    foreach ($users as $user) {
        if ($user['email'] === $email) {
            $foundUser = $user;
            break;
        }
    }

    if ($foundUser && password_verify($password, $foundUser['password'])) {
        $_SESSION['user'] = $foundUser;
        header('Location: dashboard.php');
        exit;
    } else {
        $error = 'Invalid email or password';
    }
}

// Only redirect if already logged in AND not processing a login attempt
if (isset($_SESSION['user']) && !isset($_POST['login'])) {
    header('Location: dashboard.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - DTR System</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>

    <div class="container">
        <div class="card">
            <h1>Login - DTR System</h1>

            <?php if ($error): ?>
                <div class="message error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <form method="POST">
                <div class="form-group">
                    <label>Email:</label>
                    <input type="email" name="email" required>
                </div>

                <div class="form-group">
                    <label>Password:</label>
                    <input type="password" name="password" required>
                </div>

                <button type="submit" name="login">Login</button>
            </form>

            <p style="margin-top: 20px;">
                Don't have an account? <a href="register.php">Register here</a>
            </p>
        </div>
    </div>

</body>

</html>
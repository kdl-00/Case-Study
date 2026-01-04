<?php
require_once 'config.php';

// Check if already logged in
if (isset($_SESSION['user'])) {
    header('Location: dashboard.php');
    exit;
}

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register'])) {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $userType = $_POST['user_type'] ?? 'faculty';

    if (empty($name) || empty($email) || empty($password)) {
        $error = 'All fields are required';
    } else {
        // Get existing users
        $users = [];
        if (file_exists(USERS_FILE)) {
            $content = file_get_contents(USERS_FILE);
            if (!empty($content)) {
                $users = unserialize($content);
            }
        }

        // Check if email exists
        $emailExists = false;
        foreach ($users as $user) {
            if ($user['email'] === $email) {
                $emailExists = true;
                break;
            }
        }

        if ($emailExists) {
            $error = 'Email already exists';
        } else {
            // Handle profile picture upload
            $picturePath = '';
            if (isset($_FILES['picture']) && $_FILES['picture']['error'] === UPLOAD_ERR_OK) {
                $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
                $fileInfo = finfo_open(FILEINFO_MIME_TYPE);
                $detectedType = finfo_file($fileInfo, $_FILES['picture']['tmp_name']);
                finfo_close($fileInfo);

                if (in_array($detectedType, $allowedTypes)) {
                    $ext = pathinfo($_FILES['picture']['name'], PATHINFO_EXTENSION);
                    $fileName = uniqid() . '.' . $ext;
                    $picturePath = UPLOAD_DIR . $fileName;

                    if (!move_uploaded_file($_FILES['picture']['tmp_name'], $picturePath)) {
                        $picturePath = '';
                    }
                }
            }

            // Add new user
            $users[] = [
                'name' => $name,
                'email' => $email,
                'password' => password_hash($password, PASSWORD_DEFAULT),
                'user_type' => $userType,
                'picture' => $picturePath,
                'created_at' => date('Y-m-d H:i:s')
            ];

            file_put_contents(USERS_FILE, serialize($users));
            $message = 'Registration successful! You can now login.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - DTR System</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>

    <div class="container">
        <div class="card">
            <h1>Register - DTR System</h1>

            <?php if ($message): ?>
                <div class="message success"><?php echo htmlspecialchars($message); ?></div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="message error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <form method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label>Full Name:</label>
                    <input type="text" name="name" required>
                </div>

                <div class="form-group">
                    <label>Email:</label>
                    <input type="email" name="email" required>
                </div>

                <div class="form-group">
                    <label>Password:</label>
                    <input type="password" name="password" required>
                </div>

                <div class="form-group">
                    <label>User Type:</label>
                    <select name="user_type" required>
                        <option value="faculty">Faculty</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Profile Picture (Optional):</label>
                    <input type="file" name="picture" accept="image/*">
                </div>

                <button type="submit" name="register">Register</button>
            </form>

            <p style="margin-top: 20px;">
                Already have an account? <a href="login.php">Login here</a>
            </p>
        </div>
    </div>

</body>

</html>
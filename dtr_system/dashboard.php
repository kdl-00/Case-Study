<?php
require_once 'config.php';

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['delete_account'])) {
        // Get users
        $users = [];
        if (file_exists(USERS_FILE)) {
            $content = file_get_contents(USERS_FILE);
            if (!empty($content)) {
                $users = unserialize($content);
            }
        }

        // Remove current user
        $users = array_filter($users, function ($user) {
            return $user['email'] !== $_SESSION['user']['email'];
        });

        file_put_contents(USERS_FILE, serialize(array_values($users)));
        session_destroy();
        header('Location: login.php');
        exit;
    }
}

// Check if there's a message in session
if (isset($_SESSION['message'])) {
    $message = $_SESSION['message'];
    unset($_SESSION['message']);
}

// Check if logged in - must be AFTER processing POST
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - DTR System</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>

    <div class="container">
        <div class="card">
            <h1>Welcome to DTR System</h1>

            <?php if ($message): ?>
                <div class="message success"><?php echo htmlspecialchars($message); ?></div>
            <?php endif; ?>

            <div class="user-profile">
                <?php
                $picturePath = isset($_SESSION['user']['picture']) ? $_SESSION['user']['picture'] : '';
                if (!empty($picturePath) && file_exists($picturePath)) {
                    echo '<img src="' . htmlspecialchars($picturePath) . '" alt="Profile Picture" class="profile-picture">';
                } else {
                    echo '<div class="profile-picture" style="background: #ddd; display: flex; align-items: center; justify-content: center; font-size: 40px;">👤</div>';
                }
                ?>
                <div class="user-info">
                    <h3><?php echo htmlspecialchars($_SESSION['user']['name']); ?></h3>
                    <p><strong>Email:</strong> <?php echo htmlspecialchars($_SESSION['user']['email']); ?></p>
                    <p><strong>User Type:</strong>
                        <span class="badge <?php echo $_SESSION['user']['user_type']; ?>">
                            <?php echo strtoupper($_SESSION['user']['user_type']); ?>
                        </span>
                    </p>
                    <p><strong>Member Since:</strong> <?php echo htmlspecialchars($_SESSION['user']['created_at']); ?></p>
                </div>
            </div>

            <div class="action-buttons">
                <?php if ($_SESSION['user']['user_type'] === 'admin'): ?>
                    <a href="admin.php"><button>Admin Panel</button></a>
                <?php endif; ?>

                <a href="logout.php"><button>Logout</button></a>

                <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete your account? This action cannot be undone.');">
                    <button type="submit" name="delete_account" class="danger">Delete Account</button>
                </form>
            </div>
        </div>
    </div>

</body>

</html>
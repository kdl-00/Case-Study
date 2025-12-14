<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$message = '';
$message_type = '';

// Get user data
$query = $db->prepare("SELECT * FROM users WHERE id = ?");
$query->execute([$user_id]);
$user = $query->fetch();

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = $_POST['full_name'];
    $email = $_POST['email'];

    $profile_pic = $user['profile_picture'];
    $signature = $user['signature'];

    // Upload new profile picture if provided
    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === 0) {
        $result = uploadImage($_FILES['profile_picture'], UPLOAD_PATH_PROFILES);
        if ($result['success']) {
            // Delete old image
            if ($profile_pic && file_exists(UPLOAD_PATH_PROFILES . $profile_pic)) {
                unlink(UPLOAD_PATH_PROFILES . $profile_pic);
            }
            $profile_pic = $result['filename'];
        } else {
            $message = $result['message'];
            $message_type = 'error';
        }
    }

    // Upload new signature if provided
    if (isset($_FILES['signature']) && $_FILES['signature']['error'] === 0 && !$message) {
        $result = uploadImage($_FILES['signature'], UPLOAD_PATH_SIGNATURES);
        if ($result['success']) {
            // Delete old image
            if ($signature && file_exists(UPLOAD_PATH_SIGNATURES . $signature)) {
                unlink(UPLOAD_PATH_SIGNATURES . $signature);
            }
            $signature = $result['filename'];
        } else {
            $message = $result['message'];
            $message_type = 'error';
        }
    }

    // Update password if provided
    if (!empty($_POST['new_password'])) {
        if ($_POST['new_password'] === $_POST['confirm_password']) {
            $new_password = password_hash($_POST['new_password'], PASSWORD_DEFAULT);
            $query = $db->prepare("UPDATE users SET full_name = ?, email = ?, password = ?, profile_picture = ?, signature = ? WHERE id = ?");
            $query->execute([$full_name, $email, $new_password, $profile_pic, $signature, $user_id]);
        } else {
            $message = 'Passwords do not match.';
            $message_type = 'error';
        }
    } else {
        $query = $db->prepare("UPDATE users SET full_name = ?, email = ?, profile_picture = ?, signature = ? WHERE id = ?");
        $query->execute([$full_name, $email, $profile_pic, $signature, $user_id]);
    }

    if (!$message) {
        $message = 'Profile updated successfully!';
        $message_type = 'success';
        $_SESSION['user_name'] = $full_name;

        // Refresh user data
        $query = $db->prepare("SELECT * FROM users WHERE id = ?");
        $query->execute([$user_id]);
        $user = $query->fetch();
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile</title>
    <link rel="stylesheet" href="css/main.css">
    <link rel="stylesheet" href="css/profile.css">
</head>

<body>
    <nav class="navbar">
        <a href="index.php" class="navbar-brand">🎓 Enrollment System</a>
        <a href="index.php" class="btn btn-secondary">← Back to Dashboard</a>
    </nav>

    <div class="container">
        <div class="profile-card">
            <div class="profile-header">
                <img src="uploads/profiles/<?php echo htmlspecialchars($user['profile_picture']); ?>"
                    alt="Profile" class="profile-avatar">
                <h2><?php echo htmlspecialchars($user['full_name']); ?></h2>
                <span class="user-type-badge badge-<?php echo strtolower($user['user_type']); ?>">
                    <?php echo htmlspecialchars($user['user_type']); ?>
                </span>
                <div style="margin-top: 1rem;">
                    <small style="color: #666;">Digital Signature:</small>
                    <img src="uploads/signatures/<?php echo htmlspecialchars($user['signature']); ?>"
                        alt="Signature" class="profile-signature">
                </div>
            </div>

            <?php if ($message): ?>
                <div class="message <?php echo $message_type; ?>">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>

            <form method="POST" enctype="multipart/form-data">
                <div class="form-section">
                    <h3>Basic Information</h3>

                    <div class="form-group">
                        <label>Username</label>
                        <input type="text" value="<?php echo htmlspecialchars($user['username']); ?>" disabled>
                    </div>

                    <div class="form-group">
                        <label>Full Name</label>
                        <input type="text" name="full_name" value="<?php echo htmlspecialchars($user['full_name']); ?>" required>
                    </div>

                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                    </div>
                </div>

                <div class="form-section">
                    <h3>Update Images</h3>

                    <div class="form-group">
                        <label>Profile Picture</label>
                        <div class="file-input-wrapper">
                            <input type="file" id="profile_picture" name="profile_picture" accept="image/*">
                            <label for="profile_picture" class="file-input-label">
                                📤 Click to upload new profile picture
                            </label>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Digital Signature</label>
                        <div class="file-input-wrapper">
                            <input type="file" id="signature" name="signature" accept="image/*">
                            <label for="signature" class="file-input-label">
                                ✍️ Click to upload new signature
                            </label>
                        </div>
                    </div>
                </div>

                <div class="form-section">
                    <h3>Change Password (Optional)</h3>

                    <div class="form-group">
                        <label>New Password</label>
                        <input type="password" name="new_password" placeholder="Leave blank to keep current password">
                    </div>

                    <div class="form-group">
                        <label>Confirm New Password</label>
                        <input type="password" name="confirm_password" placeholder="Confirm new password">
                    </div>
                </div>

                <button type="submit" class="btn btn-primary">Update Profile</button>
            </form>
        </div>
    </div>
</body>

</html>
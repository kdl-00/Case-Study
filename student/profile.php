<?php
$page_title = "Profile";
require_once '../config/config.php';
require_once '../config/database.php';
require_once '../config/session.php';
require_once '../includes/functions.php';

requireRole('student');

$database = new Database();
$conn = $database->getConnection();
$user_id = getUserId();

$error = '';
$success = '';

// Get user data
$stmt = $conn->prepare("SELECT * FROM users WHERE user_id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_profile'])) {
        $first_name = sanitize($_POST['first_name']);
        $last_name = sanitize($_POST['last_name']);
        $email = sanitize($_POST['email']);

        $stmt = $conn->prepare("UPDATE users SET first_name = ?, last_name = ?, email = ? WHERE user_id = ?");
        if ($stmt->execute([$first_name, $last_name, $email, $user_id])) {
            $_SESSION['first_name'] = $first_name;
            $_SESSION['last_name'] = $last_name;
            logActivity($conn, $user_id, 'PROFILE_UPDATE', 'users', $user_id, 'Updated profile information');
            $success = "Profile updated successfully!";
            // Refresh user data
            $stmt = $conn->prepare("SELECT * FROM users WHERE user_id = ?");
            $stmt->execute([$user_id]);
            $user = $stmt->fetch();
        }
    }

    if (isset($_POST['update_password'])) {
        $current_password = $_POST['current_password'];
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];

        if (password_verify($current_password, $user['password'])) {
            if ($new_password === $confirm_password) {
                if (strlen($new_password) >= 6) {
                    $hashed = password_hash($new_password, PASSWORD_DEFAULT);
                    $stmt = $conn->prepare("UPDATE users SET password = ? WHERE user_id = ?");
                    if ($stmt->execute([$hashed, $user_id])) {
                        logActivity($conn, $user_id, 'PASSWORD_CHANGE', 'users', $user_id, 'Changed password');
                        $success = "Password updated successfully!";
                    }
                } else {
                    $error = "Password must be at least 6 characters";
                }
            } else {
                $error = "Passwords do not match";
            }
        } else {
            $error = "Current password is incorrect";
        }
    }

    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
        $upload_result = uploadFile($_FILES['profile_picture'], PROFILE_PATH, ALLOWED_IMAGE_TYPES);
        if ($upload_result['success']) {
            // Delete old picture
            if ($user['profile_picture'] && file_exists(PROFILE_PATH . $user['profile_picture'])) {
                unlink(PROFILE_PATH . $user['profile_picture']);
            }

            $stmt = $conn->prepare("UPDATE users SET profile_picture = ? WHERE user_id = ?");
            if ($stmt->execute([$upload_result['file_name'], $user_id])) {
                logActivity($conn, $user_id, 'PROFILE_PICTURE_UPDATE', 'users', $user_id, 'Updated profile picture');
                $success = "Profile picture updated!";
                // Refresh user data
                $stmt = $conn->prepare("SELECT * FROM users WHERE user_id = ?");
                $stmt->execute([$user_id]);
                $user = $stmt->fetch();
            }
        } else {
            $error = $upload_result['message'];
        }
    }

    if (isset($_FILES['signature']) && $_FILES['signature']['error'] === UPLOAD_ERR_OK) {
        $upload_result = uploadFile($_FILES['signature'], SIGNATURE_PATH, ALLOWED_IMAGE_TYPES);
        if ($upload_result['success']) {
            // Delete old signature
            if ($user['signature'] && file_exists(SIGNATURE_PATH . $user['signature'])) {
                unlink(SIGNATURE_PATH . $user['signature']);
            }

            $stmt = $conn->prepare("UPDATE users SET signature = ? WHERE user_id = ?");
            if ($stmt->execute([$upload_result['file_name'], $user_id])) {
                logActivity($conn, $user_id, 'SIGNATURE_UPDATE', 'users', $user_id, 'Updated signature');
                $success = "Signature updated!";
                // Refresh user data
                $stmt = $conn->prepare("SELECT * FROM users WHERE user_id = ?");
                $stmt->execute([$user_id]);
                $user = $stmt->fetch();
            }
        } else {
            $error = $upload_result['message'];
        }
    }
}

require_once '../includes/header.php';
require_once '../includes/navbar.php';
?>

<div class="container mt-4 mb-5">

    <!-- PAGE HEADER -->
    <div class="mb-4">
        <h1>My Profile</h1>
        <p class="text-muted">
            Manage your personal information, password, and signature
        </p>
    </div>

    <?php if ($error): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="alert alert-success"><?php echo $success; ?></div>
    <?php endif; ?>

    <div class="row gap-3">

        <!-- LEFT COLUMN -->
        <div class="col-8">

            <!-- PROFILE INFO -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Profile Information</h3>
                    <p class="card-subtitle">
                        Update your name and email address
                    </p>
                </div>

                <form method="POST">
                    <div class="row">
                        <div class="col-6">
                            <label class="form-label">First Name</label>
                            <input type="text" class="form-control"
                                name="first_name"
                                value="<?php echo $user['first_name']; ?>"
                                required>
                        </div>

                        <div class="col-6">
                            <label class="form-label">Last Name</label>
                            <input type="text" class="form-control"
                                name="last_name"
                                value="<?php echo $user['last_name']; ?>"
                                required>
                        </div>
                    </div>

                    <div class="mt-3">
                        <label class="form-label">Email</label>
                        <input type="email" class="form-control"
                            name="email"
                            value="<?php echo $user['email']; ?>"
                            required>
                    </div>

                    <div class="mt-3">
                        <label class="form-label">Username</label>
                        <input type="text" class="form-control"
                            value="<?php echo $user['username']; ?>"
                            disabled>
                    </div>

                    <div class="mt-4">
                        <button type="submit"
                            name="update_profile"
                            class="btn btn-primary">
                            Save Changes
                        </button>
                    </div>
                </form>
            </div>

            <!-- PASSWORD -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Change Password</h3>
                    <p class="card-subtitle">
                        Use a strong password to keep your account secure
                    </p>
                </div>

                <form method="POST">
                    <div class="mt-2">
                        <label class="form-label">Current Password</label>
                        <input type="password" class="form-control"
                            name="current_password" required>
                    </div>

                    <div class="mt-3">
                        <label class="form-label">New Password</label>
                        <input type="password" class="form-control"
                            name="new_password" required>
                    </div>

                    <div class="mt-3">
                        <label class="form-label">Confirm New Password</label>
                        <input type="password" class="form-control"
                            name="confirm_password" required>
                    </div>

                    <div class="mt-4">
                        <button type="submit"
                            name="update_password"
                            class="btn btn-warning">
                            Update Password
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- RIGHT COLUMN -->
        <div class="col-4">

            <!-- IDENTITY CARD -->
            <div class="card text-center">
                <?php if ($user['profile_picture']): ?>
                    <img src="<?php echo BASE_URL; ?>uploads/profiles/<?php echo $user['profile_picture']; ?>"
                        class="user-avatar"
                        style="width:120px;height:120px;margin:0 auto 16px;">
                <?php else: ?>
                    <div class="empty-state-icon">👤</div>
                <?php endif; ?>

                <h3><?php echo $user['first_name'] . ' ' . $user['last_name']; ?></h3>
                <p class="text-muted"><?php echo $user['email']; ?></p>

                <form method="POST" enctype="multipart/form-data" class="mt-3">
                    <input type="file" name="profile_picture" class="form-control mb-2">
                    <button class="btn btn-secondary btn-sm">
                        Update Photo
                    </button>
                </form>
            </div>

            <!-- SIGNATURE -->
            <div class="card text-center">
                <div class="card-header">
                    <h3 class="card-title">Digital Signature</h3>
                    <p class="card-subtitle">
                        Used for thesis approval
                    </p>
                </div>

                <?php if ($user['signature']): ?>
                    <img src="<?php echo BASE_URL; ?>uploads/signatures/<?php echo $user['signature']; ?>"
                        style="max-width:180px;margin:16px auto;">
                <?php else: ?>
                    <p class="text-muted mt-2">
                        No signature uploaded
                    </p>
                <?php endif; ?>

                <form method="POST" enctype="multipart/form-data" class="mt-3">
                    <input type="file" name="signature" class="form-control mb-2">
                    <button class="btn btn-success btn-sm">
                        Upload Signature
                    </button>
                </form>
            </div>

        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
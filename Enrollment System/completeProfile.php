<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['temp_user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['temp_user_id'];
$error = '';
$success = '';

// Get user details
$query = $db->prepare("SELECT full_name FROM users WHERE id = ?");
$query->execute([$user_id]);
$user = $query->fetch();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $profile_uploaded = false;
    $signature_uploaded = false;

    // Upload profile picture
    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === 0) {
        $result = uploadImage($_FILES['profile_picture'], UPLOAD_PATH_PROFILES);
        if ($result['success']) {
            $profile_filename = $result['filename'];
            $profile_uploaded = true;
        } else {
            $error = $result['message'];
        }
    }

    // Upload signature
    if (isset($_FILES['signature']) && $_FILES['signature']['error'] === 0 && !$error) {
        $result = uploadImage($_FILES['signature'], UPLOAD_PATH_SIGNATURES);
        if ($result['success']) {
            $signature_filename = $result['filename'];
            $signature_uploaded = true;
        } else {
            $error = $result['message'];
        }
    }

    // Update database if both uploaded
    if ($profile_uploaded && $signature_uploaded && !$error) {
        $query = $db->prepare("UPDATE users SET profile_picture = ?, signature = ? WHERE id = ?");
        if ($query->execute([$profile_filename, $signature_filename, $user_id])) {
            // Get updated user info
            $query = $db->prepare("SELECT full_name, user_type FROM users WHERE id = ?");
            $query->execute([$user_id]);
            $user_info = $query->fetch();

            $_SESSION['user_id'] = $user_id;
            $_SESSION['user_name'] = $user_info['full_name'];
            $_SESSION['user_type'] = $user_info['user_type'];
            unset($_SESSION['temp_user_id']);

            header('Location: index.php');
            exit();
        } else {
            $error = 'Failed to save profile information.';
        }
    } elseif (!$error) {
        $error = 'Please upload both profile picture and signature.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Complete Profile - Enrollment System</title>
    <link rel="stylesheet" href="css/auth.css">
</head>

<body>
    <div class="profile-container">
        <div class="logo">
            <h1>🎓 Complete Your Profile</h1>
            <p>Welcome, <?php echo htmlspecialchars($user['full_name']); ?>!</p>
        </div>

        <div class="alert-info">
            📸 Please upload your profile picture and signature to continue.
        </div>

        <?php if ($error): ?>
            <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form method="POST" action="" enctype="multipart/form-data">
            <div class="form-group">
                <label>Profile Picture</label>
                <div class="file-input-wrapper">
                    <input type="file" id="profile_picture" name="profile_picture" accept="image/*" required onchange="previewImage(this, 'profile-preview')">
                    <label for="profile_picture" class="file-input-label">
                        📤 Click to upload profile picture
                    </label>
                </div>
                <div id="profile-preview" class="file-preview"></div>
            </div>

            <div class="form-group">
                <label>Digital Signature</label>
                <div class="file-input-wrapper">
                    <input type="file" id="signature" name="signature" accept="image/*" required onchange="previewImage(this, 'signature-preview')">
                    <label for="signature" class="file-input-label">
                        ✍️ Click to upload signature
                    </label>
                </div>
                <div id="signature-preview" class="file-preview"></div>
            </div>

            <button type="submit" class="btn">Complete Profile</button>
        </form>
    </div>

    <script>
        function previewImage(input, previewId) {
            const preview = document.getElementById(previewId);
            const file = input.files[0];

            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.innerHTML = `
                        <img src="${e.target.result}" alt="Preview">
                        <div class="file-name">${file.name}</div>
                    `;
                }
                reader.readAsDataURL(file);
            }
        }
    </script>
</body>

</html>
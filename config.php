<?php
// Database configuration
$host = 'localhost';
$dbname = 'enrollment_system';
$username = 'root';
$password = '';

try {
    $db = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Upload directories
define('UPLOAD_PATH_PROFILES', 'uploads/profiles/');
define('UPLOAD_PATH_SIGNATURES', 'uploads/signatures/');

// Create upload directories if they don't exist
if (!file_exists(UPLOAD_PATH_PROFILES)) {
    mkdir(UPLOAD_PATH_PROFILES, 0777, true);
}
if (!file_exists(UPLOAD_PATH_SIGNATURES)) {
    mkdir(UPLOAD_PATH_SIGNATURES, 0777, true);
}

// Helper function to check if user is logged in
function checkLogin()
{
    if (!isset($_SESSION['user_id'])) {
        header('Location: ../login.php');
        exit();
    }
}

// Helper function to check user type
function checkUserType($required_type)
{
    if ($_SESSION['user_type'] !== $required_type) {
        header('Location: ../index.php');
        exit();
    }
}

// Helper function to upload image
function uploadImage($file, $path)
{
    $allowed_types = ['image/jpeg', 'image/png', 'image/jpg', 'image/gif'];
    $max_size = 5 * 1024 * 1024; // 5MB

    if (!in_array($file['type'], $allowed_types)) {
        return ['success' => false, 'message' => 'Invalid file type. Only JPG, PNG, and GIF allowed.'];
    }

    if ($file['size'] > $max_size) {
        return ['success' => false, 'message' => 'File too large. Maximum size is 5MB.'];
    }

    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid() . '_' . time() . '.' . $extension;
    $destination = $path . $filename;

    if (move_uploaded_file($file['tmp_name'], $destination)) {
        return ['success' => true, 'filename' => $filename];
    }

    return ['success' => false, 'message' => 'Failed to upload file.'];
}

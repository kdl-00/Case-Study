<?php
require_once '../config/config.php';
require_once '../config/database.php';
require_once '../config/session.php';
require_once '../includes/functions.php';

if (isLoggedIn()) {
    $database = new Database();
    $conn = $database->getConnection();
    logActivity($conn, getUserId(), 'LOGOUT', 'users', getUserId(), 'User logged out');
}

session_destroy();
header("Location: " . BASE_URL . "auth/login.php");
exit();
?>
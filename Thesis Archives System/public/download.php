<?php
require_once '../config/config.php';
require_once '../config/database.php';
require_once '../config/session.php';

$database = new Database();
$conn = $database->getConnection();
$thesis_id = $_GET['id'] ?? 0;

$stmt = $conn->prepare("SELECT * FROM thesis WHERE thesis_id = ? AND status = 'approved'");
$stmt->execute([$thesis_id]);
$thesis = $stmt->fetch();

if ($thesis && file_exists($thesis['file_path'])) {
    // Update download count
    $conn->prepare("UPDATE thesis SET downloads = downloads + 1 WHERE thesis_id = ?")->execute([$thesis_id]);

    // Force download
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="' . basename($thesis['file_path']) . '"');
    header('Content-Length: ' . filesize($thesis['file_path']));
    readfile($thesis['file_path']);
    exit();
} else {
    header("Location: library.php");
    exit();
}

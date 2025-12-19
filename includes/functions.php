<?php
require_once __DIR__ . '/../config/database.php';

// Sanitize Input
function sanitize($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

// Log Activity
function logActivity($conn, $user_id, $action, $table_name = null, $record_id = null, $description = null) {
    try {
        $stmt = $conn->prepare("INSERT INTO activity_logs (user_id, action, table_name, record_id, description, ip_address, user_agent) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $ip = $_SERVER['REMOTE_ADDR'];
        $user_agent = $_SERVER['HTTP_USER_AGENT'];
        $stmt->execute([$user_id, $action, $table_name, $record_id, $description, $ip, $user_agent]);
    } catch(PDOException $e) {
        error_log("Log Activity Error: " . $e->getMessage());
    }
}

// Upload File
function uploadFile($file, $target_dir, $allowed_types) {
    $file_name = $file['name'];
    $file_tmp = $file['tmp_name'];
    $file_size = $file['size'];
    $file_error = $file['error'];
    
    if ($file_error !== UPLOAD_ERR_OK) {
        return ['success' => false, 'message' => 'File upload error'];
    }
    
    $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
    
    if (!in_array($file_ext, $allowed_types)) {
        return ['success' => false, 'message' => 'Invalid file type'];
    }
    
    if ($file_size > MAX_FILE_SIZE) {
        return ['success' => false, 'message' => 'File size exceeds limit'];
    }
    
    $new_file_name = uniqid() . '_' . time() . '.' . $file_ext;
    $target_file = $target_dir . $new_file_name;
    
    if (move_uploaded_file($file_tmp, $target_file)) {
        return ['success' => true, 'file_name' => $new_file_name, 'file_path' => $target_file];
    }
    
    return ['success' => false, 'message' => 'Failed to move uploaded file'];
}

// Delete File
function deleteFile($file_path) {
    if (file_exists($file_path)) {
        return unlink($file_path);
    }
    return false;
}

// Format File Size
function formatFileSize($bytes) {
    $units = ['B', 'KB', 'MB', 'GB'];
    $i = 0;
    while ($bytes >= 1024 && $i < count($units) - 1) {
        $bytes /= 1024;
        $i++;
    }
    return round($bytes, 2) . ' ' . $units[$i];
}

// Send Notification (Email simulation)
function sendNotification($to_user_id, $subject, $message, $conn) {
    try {
        // In production, implement actual email sending
        // For now, just log it
        logActivity($conn, $to_user_id, 'NOTIFICATION_SENT', 'users', $to_user_id, $subject . ': ' . $message);
        return true;
    } catch(Exception $e) {
        return false;
    }
}

// Get User Info
function getUserInfo($user_id, $conn) {
    $stmt = $conn->prepare("SELECT * FROM users WHERE user_id = ?");
    $stmt->execute([$user_id]);
    return $stmt->fetch();
}

// Generate Pagination
function generatePagination($total_records, $current_page, $items_per_page, $url) {
    $total_pages = ceil($total_records / $items_per_page);
    
    if ($total_pages <= 1) return '';
    
    $pagination = '<nav><ul class="pagination">';
    
    // Previous
    if ($current_page > 1) {
        $pagination .= '<li class="page-item"><a class="page-link" href="' . $url . '?page=' . ($current_page - 1) . '">Previous</a></li>';
    }
    
    // Pages
    for ($i = 1; $i <= $total_pages; $i++) {
        $active = ($i == $current_page) ? 'active' : '';
        $pagination .= '<li class="page-item ' . $active . '"><a class="page-link" href="' . $url . '?page=' . $i . '">' . $i . '</a></li>';
    }
    
    // Next
    if ($current_page < $total_pages) {
        $pagination .= '<li class="page-item"><a class="page-link" href="' . $url . '?page=' . ($current_page + 1) . '">Next</a></li>';
    }
    
    $pagination .= '</ul></nav>';
    return $pagination;
}
?>
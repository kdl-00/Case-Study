<?php
// Site Configuration
define('SITE_NAME', 'Thesis Archive Management System');
define('BASE_URL', 'http://localhost/thesis-archive/');

// Path Configuration
define('ROOT_PATH', dirname(__DIR__));
define('UPLOAD_PATH', ROOT_PATH . '/uploads/');
define('THESIS_PATH', UPLOAD_PATH . 'thesis/');
define('PROFILE_PATH', UPLOAD_PATH . 'profiles/');
define('SIGNATURE_PATH', UPLOAD_PATH . 'signatures/');

// File Upload Settings
define('MAX_FILE_SIZE', 52428800); // 50MB
define('ALLOWED_THESIS_TYPES', ['pdf', 'doc', 'docx']);
define('ALLOWED_IMAGE_TYPES', ['jpg', 'jpeg', 'png', 'gif']);

// Pagination
define('ITEMS_PER_PAGE', 10);

// Create upload directories if they don't exist
$directories = [UPLOAD_PATH, THESIS_PATH, PROFILE_PATH, SIGNATURE_PATH];
foreach ($directories as $dir) {
    if (!file_exists($dir)) {
        mkdir($dir, 0755, true);
    }
}

// Timezone
date_default_timezone_set('Asia/Manila');

// Error Reporting (disable in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

<?php
$page_title = "Home";
require_once 'config/config.php';
require_once 'config/session.php';

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    switch ($_SESSION['role']) {
        case 'admin':
            //admin account
            //username: admin
            //password: password
            header("Location: admin/dashboard.php");
            break;
        case 'adviser':
            header("Location: adviser/dashboard.php");
            break;
        case 'student':
            header("Location: student/dashboard.php");
            break;
    }
    exit();
}

require_once 'config/database.php';

// Get some basic stats (optional)
$database = new Database();
$conn = $database->getConnection();

try {
    $stmt = $conn->query("SELECT COUNT(*) as total FROM thesis WHERE status = 'approved'");
    $total_thesis = $stmt->fetch()['total'] ?? 0;
} catch (Exception $e) {
    $total_thesis = 0;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thesis Archive Management System</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/home.css">
</head>

<body>
    <!-- Navigation -->
    <nav class="navbar">
        <div class="container">
            <a href="index.php" class="navbar-brand">📚 Thesis Archive</a>
            <ul class="navbar-menu">
                <li><a href="public/library.php">Library</a></li>
                <li><a href="auth/login.php" class="btn btn-primary btn-sm" style="margin-left: 20px; padding: 8px 16px;">Login</a></li>
            </ul>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="home-minimal-hero">
        <div class="container">
            <div class="home-minimal-content">
                <div class="home-logo">📚</div>
                <h1>Thesis Archive Management System</h1>
                <p>Digital repository for academic research and thesis documents</p>
                <div class="home-actions">
                    <a href="public/library.php" class="btn btn-primary">Browse Library</a>
                    <a href="public/search.php" class="btn btn-secondary">Search Thesis</a>
                </div>
                <?php if ($total_thesis > 0): ?>
                    <p class="home-stats"><?php echo number_format($total_thesis); ?> thesis documents available</p>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="home-features">
        <div class="container">
            <div class="row">
                <div class="col-4">
                    <div class="home-feature-card">
                        <div class="feature-icon">👨‍🎓</div>
                        <h3>For Students</h3>
                        <p>Upload and manage your thesis submissions</p>
                        <a href="auth/login.php" class="btn btn-primary w-100">Login</a>
                    </div>
                </div>
                <div class="col-4">
                    <div class="home-feature-card">
                        <div class="feature-icon">👨‍🏫</div>
                        <h3>For Advisers</h3>
                        <p>Review and approve student submissions</p>
                        <a href="auth/login.php" class="btn btn-success w-100">Login</a>
                    </div>
                </div>
                <div class="col-4">
                    <div class="home-feature-card">
                        <div class="feature-icon">👨‍💼</div>
                        <h3>For Administrators</h3>
                        <p>Manage system and user accounts</p>
                        <a href="auth/login.php" class="btn btn-warning w-100">Login</a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="home-footer">
        <div class="container">
            <p>&copy; <?php echo date('Y'); ?> Thesis Archive Management System. All rights reserved.</p>
        </div>
    </footer>
</body>

</html>
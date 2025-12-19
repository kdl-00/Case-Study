<?php
$page_title = "Admin Dashboard";
require_once '../config/config.php';
require_once '../config/database.php';
require_once '../config/session.php';
require_once '../includes/functions.php';

requireRole('admin');

$database = new Database();
$conn = $database->getConnection();

$stats = [
    'users'     => $conn->query("SELECT COUNT(*) FROM users")->fetchColumn(),
    'students'  => $conn->query("SELECT COUNT(*) FROM users WHERE role='student'")->fetchColumn(),
    'advisers'  => $conn->query("SELECT COUNT(*) FROM users WHERE role='adviser'")->fetchColumn(),
    'thesis'    => $conn->query("SELECT COUNT(*) FROM thesis")->fetchColumn(),
    'pending'   => $conn->query("SELECT COUNT(*) FROM thesis WHERE status='pending'")->fetchColumn(),
    'approved'  => $conn->query("SELECT COUNT(*) FROM thesis WHERE status='approved'")->fetchColumn(),
];

require_once '../includes/header.php';
require_once '../includes/navbar.php';
?>

<main class="container py-4">

    <!-- HEADER -->
    <div class="mb-4">
        <h2 class="fw-bold">Admin Dashboard</h2>
        <p class="text-muted">System overview and administrative controls</p>
    </div>

    <!-- STATS -->
    <div class="row g-3 mb-4">
        <?php
        $cards = [
            ['Users', $stats['users'], 'users', 'primary'],
            ['Students', $stats['students'], 'user-graduate', 'info'],
            ['Advisers', $stats['advisers'], 'user-tie', 'success'],
            ['Total Thesis', $stats['thesis'], 'file-alt', 'warning'],
            ['Pending', $stats['pending'], 'clock', 'secondary'],
            ['Approved', $stats['approved'], 'check-circle', 'success'],
        ];
        foreach ($cards as $c):
        ?>
            <div class="col-md-4 col-lg-2">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body text-center">
                        <i class="fas fa-<?= $c[2]; ?> fa-2x text-<?= $c[3]; ?> mb-2"></i>
                        <h3 class="fw-bold mb-0"><?= $c[1]; ?></h3>
                        <small class="text-muted"><?= $c[0]; ?></small>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- MANAGEMENT MODULES -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-light">
            <h5 class="mb-0">Administration</h5>
        </div>

        <div class="card-body">
            <div class="row g-3">

                <?php
                $modules = [
                    ['users.php', 'Users', 'users', 'primary'],
                    ['departments.php', 'Departments', 'building', 'info'],
                    ['programs.php', 'Programs', 'graduation-cap', 'success'],
                    ['approve-thesis.php', 'Approve Thesis', 'check-circle', 'warning'],
                    ['activity-logs.php', 'Activity Logs', 'history', 'secondary'],
                    ['backup.php', 'System Backup', 'database', 'danger'],
                ];
                foreach ($modules as $m):
                ?>
                    <div class="col-md-4 col-lg-3">
                        <a href="<?= $m[0]; ?>" class="text-decoration-none">
                            <div class="card h-100 border-0 shadow-sm hover-card">
                                <div class="card-body text-center">
                                    <i class="fas fa-<?= $m[2]; ?> fa-2x text-<?= $m[3]; ?> mb-2"></i>
                                    <h6 class="fw-semibold mb-0"><?= $m[1]; ?></h6>
                                </div>
                            </div>
                        </a>
                    </div>
                <?php endforeach; ?>

            </div>
        </div>
    </div>

</main>

<?php require_once '../includes/footer.php'; ?>
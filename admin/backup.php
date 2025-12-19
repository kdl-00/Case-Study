<?php
$page_title = "Backup System";
require_once '../config/config.php';
require_once '../config/database.php';
require_once '../config/session.php';
require_once '../includes/functions.php';

requireRole('admin');

$message = '';

if (isset($_POST['backup'])) {
    $backup_file = 'backup_' . date('Y-m-d_H-i-s') . '.sql';
    $backup_path = ROOT_PATH . '/backups/';

    if (!file_exists($backup_path)) {
        mkdir($backup_path, 0755, true);
    }

    $command = "mysqldump -u root thesis_archive_db > " . $backup_path . $backup_file;
    system($command, $output);

    if ($output === 0) {
        $message = "Backup created successfully: $backup_file";
        logActivity($conn, getUserId(), 'BACKUP_CREATE', null, null, "Created backup: $backup_file");
    } else {
        $message = "Backup failed!";
    }
}

require_once '../includes/header.php';
require_once '../includes/navbar.php';
?>

<main class="container py-4">

    <!-- PAGE HEADER -->
    <div class="mb-4">
        <h2 class="fw-bold">
            <i class="fas fa-database text-primary"></i> Backup System
        </h2>
        <p class="text-muted mb-0">
            Create and download database backups
        </p>
    </div>

    <?php if ($message): ?>
        <div class="alert alert-info">
            <?= htmlspecialchars($message) ?>
        </div>
    <?php endif; ?>

    <!-- BACKUP ACTION -->
    <div class="mb-5">
        <h5 class="fw-semibold mb-2">Create Backup</h5>
        <p class="text-muted">
            This will generate a complete SQL backup of the system database.
        </p>

        <form method="POST">
            <button type="submit" name="backup" class="btn btn-primary">
                <i class="fas fa-database"></i> Create Backup
            </button>
        </form>
    </div>

    <!-- BACKUP LIST -->
    <div>
        <h5 class="fw-semibold mb-3">Previous Backups</h5>

        <?php
        $backup_dir = ROOT_PATH . '/backups/';
        if (file_exists($backup_dir)) {
            $files = scandir($backup_dir);
            $backups = array_values(array_filter($files, function ($file) {
                return pathinfo($file, PATHINFO_EXTENSION) === 'sql';
            }));

            if (count($backups) > 0):
        ?>
                <ul class="list-group">
                    <?php foreach ($backups as $backup): ?>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span><?= htmlspecialchars($backup) ?></span>
                            <a href="<?= BASE_URL ?>backups/<?= urlencode($backup) ?>"
                                class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-download"></i> Download
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <p class="text-muted">No backups found.</p>
        <?php
            endif;
        }
        ?>
    </div>

</main>

<?php require_once '../includes/footer.php'; ?>
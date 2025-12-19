<?php
$page_title = "Student Dashboard";
require_once '../config/config.php';
require_once '../config/database.php';
require_once '../config/session.php';
require_once '../includes/functions.php';

requireRole('student');

$database = new Database();
$conn = $database->getConnection();
$user_id = getUserId();

/* ===== STATISTICS ===== */
$stmt = $conn->prepare("SELECT COUNT(*) FROM thesis WHERE author_id = ?");
$stmt->execute([$user_id]);
$stats['total'] = $stmt->fetchColumn();

$stmt = $conn->prepare("SELECT COUNT(*) FROM thesis WHERE author_id = ? AND status = 'pending'");
$stmt->execute([$user_id]);
$stats['pending'] = $stmt->fetchColumn();

$stmt = $conn->prepare("SELECT COUNT(*) FROM thesis WHERE author_id = ? AND status = 'under_review'");
$stmt->execute([$user_id]);
$stats['review'] = $stmt->fetchColumn();

$stmt = $conn->prepare("SELECT COUNT(*) FROM thesis WHERE author_id = ? AND status = 'approved'");
$stmt->execute([$user_id]);
$stats['approved'] = $stmt->fetchColumn();

/* ===== RECENT ===== */
$stmt = $conn->prepare("
    SELECT t.*, d.department_name, p.program_name, u.first_name, u.last_name
    FROM thesis t
    JOIN departments d ON t.department_id = d.department_id
    JOIN programs p ON t.program_id = p.program_id
    JOIN users u ON t.adviser_id = u.user_id
    WHERE t.author_id = ?
    ORDER BY t.submission_date DESC
    LIMIT 5
");
$stmt->execute([$user_id]);
$recent_submissions = $stmt->fetchAll();

require_once '../includes/header.php';
require_once '../includes/navbar.php';
?>

<main class="content container py-4">

    <!-- HEADER -->
    <div class="mb-4">
        <h2 class="fw-bold mb-1">Student Dashboard</h2>
        <small class="text-muted">
            Welcome back, <?= $_SESSION['first_name'] ?> <?= $_SESSION['last_name'] ?>
        </small>
    </div>

    <!-- STATS -->
    <div class="row g-3 mb-4">
        <?php
        $cards = [
            ['Total', $stats['total'], 'file-alt', 'primary'],
            ['Pending', $stats['pending'], 'clock', 'warning'],
            ['Under Review', $stats['review'], 'eye', 'info'],
            ['Approved', $stats['approved'], 'check-circle', 'success'],
        ];
        foreach ($cards as [$label, $count, $icon, $color]):
        ?>
            <div class="col-md-3 col-sm-6">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-body text-center">
                        <i class="fas fa-<?= $icon ?> fa-2x text-<?= $color ?> mb-2"></i>
                        <h3 class="fw-bold mb-0"><?= $count ?></h3>
                        <small class="text-muted"><?= $label ?></small>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- RECENT SUBMISSIONS -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">
            <h5 class="mb-0 fw-semibold">Recent Submissions</h5>

            <a href="my-submissions.php" class="btn btn-sm btn-outline-primary">
                <i class="fas fa-list"></i> My Submissions
            </a>
        </div>

        <?php if ($recent_submissions): ?>
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Title</th>
                            <th>Adviser</th>
                            <th>Department</th>
                            <th>Date</th>
                            <th>Status</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recent_submissions as $t): ?>
                            <tr>
                                <td class="fw-semibold">
                                    <?= htmlspecialchars(substr($t['title'], 0, 60)) ?>
                                    <?= strlen($t['title']) > 60 ? '…' : '' ?>
                                </td>
                                <td><?= $t['first_name'] . ' ' . $t['last_name'] ?></td>
                                <td><?= $t['department_name'] ?></td>
                                <td><?= date('M d, Y', strtotime($t['submission_date'])) ?></td>
                                <td>
                                    <?php
                                    $badge = [
                                        'pending' => 'warning',
                                        'under_review' => 'info',
                                        'approved' => 'success',
                                        'rejected' => 'danger'
                                    ];
                                    ?>
                                    <span class="badge bg-<?= $badge[$t['status']] ?>">
                                        <?= ucfirst(str_replace('_', ' ', $t['status'])) ?>
                                    </span>
                                </td>
                                <td class="text-end">
                                    <a href="my-submissions.php?id=<?= $t['thesis_id'] ?>"
                                        class="btn btn-sm btn-outline-primary">
                                        View
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="p-5 text-center text-muted">
                <i class="fas fa-inbox fa-3x mb-3"></i>
                <h5>No submissions yet</h5>
                <p>Upload your first thesis to get started.</p>
                <a href="upload-thesis.php" class="btn btn-primary btn-sm">
                    Upload Thesis
                </a>
            </div>
        <?php endif; ?>
    </div>


</main>

<?php require_once '../includes/footer.php'; ?>
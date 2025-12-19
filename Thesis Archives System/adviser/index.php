<?php
$page_title = "Adviser Dashboard";
require_once '../config/config.php';
require_once '../config/database.php';
require_once '../config/session.php';
require_once '../includes/functions.php';

requireRole('adviser');

$database = new Database();
$conn = $database->getConnection();
$user_id = getUserId();

/* ===== STATISTICS ===== */
$stmt = $conn->prepare("SELECT COUNT(*) FROM thesis WHERE adviser_id = ?");
$stmt->execute([$user_id]);
$total = $stmt->fetchColumn();

$stmt = $conn->prepare("SELECT COUNT(*) FROM thesis WHERE adviser_id = ? AND status = 'pending'");
$stmt->execute([$user_id]);
$pending = $stmt->fetchColumn();

$stmt = $conn->prepare("SELECT COUNT(*) FROM thesis WHERE adviser_id = ? AND status = 'approved'");
$stmt->execute([$user_id]);
$approved = $stmt->fetchColumn();

/* ===== RECENT SUBMISSIONS ===== */
$stmt = $conn->prepare("
    SELECT t.*, u.first_name, u.last_name
    FROM thesis t
    JOIN users u ON u.user_id = t.author_id
    WHERE t.adviser_id = ?
    ORDER BY t.submission_date DESC
    LIMIT 5
");
$stmt->execute([$user_id]);
$recent = $stmt->fetchAll(PDO::FETCH_ASSOC);

require_once '../includes/header.php';
require_once '../includes/navbar.php';
?>

<main class="container py-4">

    <!-- PAGE HEADER -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-0">Adviser Dashboard</h2>
            <small class="text-muted">Manage and review supervised thesis submissions</small>
        </div>
        <span class="badge bg-primary">Adviser</span>
    </div>

    <!-- STAT CARDS -->
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card shadow-sm border-0">
                <div class="card-body text-center">
                    <i class="fas fa-book fa-2x text-primary mb-2"></i>
                    <h3 class="fw-bold"><?= $total ?></h3>
                    <p class="text-muted mb-0">Total Supervised</p>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card shadow-sm border-0 bg-warning-subtle">
                <div class="card-body text-center">
                    <i class="fas fa-hourglass-half fa-2x text-warning mb-2"></i>
                    <h3 class="fw-bold"><?= $pending ?></h3>
                    <p class="text-muted mb-0">Pending Review</p>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card shadow-sm border-0 bg-success-subtle">
                <div class="card-body text-center">
                    <i class="fas fa-check-circle fa-2x text-success mb-2"></i>
                    <h3 class="fw-bold"><?= $approved ?></h3>
                    <p class="text-muted mb-0">Approved</p>
                </div>
            </div>
        </div>
    </div>

    <!-- RECENT SUBMISSIONS -->
    <div class="card shadow-sm border-0">
        <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">
            <div>
                <h5 class="mb-0 fw-semibold">Recent Submissions</h5>
                <small class="text-muted">Latest thesis submitted by students</small>
            </div>

            <a href="submissions.php" class="btn btn-sm btn-outline-primary">
                <i class="fas fa-list"></i> View All
            </a>
        </div>

        <?php if (!empty($recent)): ?>
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Title</th>
                            <th>Student</th>
                            <th>Date</th>
                            <th>Status</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recent as $r): ?>
                            <tr>
                                <td class="fw-semibold">
                                    <?= htmlspecialchars($r['title']) ?>
                                </td>
                                <td>
                                    <?= htmlspecialchars($r['first_name'] . ' ' . $r['last_name']) ?>
                                </td>
                                <td>
                                    <?= date('M d, Y', strtotime($r['submission_date'])) ?>
                                </td>
                                <td>
                                    <?php if ($r['status'] === 'pending'): ?>
                                        <span class="badge bg-warning text-dark">Pending</span>
                                    <?php elseif ($r['status'] === 'approved'): ?>
                                        <span class="badge bg-success">Approved</span>
                                    <?php else: ?>
                                        <span class="badge bg-info"><?= ucfirst($r['status']) ?></span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end">
                                    <a href="review-thesis.php?id=<?= $r['thesis_id'] ?>"
                                        class="btn btn-sm btn-outline-primary">
                                        Review
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="p-5 text-center text-muted">
                <i class="fas fa-folder-open fa-3x mb-3"></i>
                <h5>No submissions yet</h5>
                <p class="mb-0">Student thesis submissions will appear here.</p>
            </div>
        <?php endif; ?>
    </div>

</main>

<?php require_once '../includes/footer.php'; ?>
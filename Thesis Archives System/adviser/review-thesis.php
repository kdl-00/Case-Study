<?php
$page_title = "Review Thesis";
require_once '../config/config.php';
require_once '../config/database.php';
require_once '../config/session.php';
require_once '../includes/functions.php';

requireRole('adviser');

$database = new Database();
$conn = $database->getConnection();
$user_id = getUserId();
$thesis_id = $_GET['id'] ?? 0;

$error = $success = '';

/* ===== FETCH THESIS ===== */
$stmt = $conn->prepare("
    SELECT t.*, u.first_name, u.last_name, u.email,
           d.department_name, p.program_name
    FROM thesis t
    JOIN users u ON t.author_id = u.user_id
    JOIN departments d ON t.department_id = d.department_id
    JOIN programs p ON t.program_id = p.program_id
    WHERE t.thesis_id = ? AND t.adviser_id = ?
");
$stmt->execute([$thesis_id, $user_id]);
$thesis = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$thesis) {
    header("Location: submissions.php");
    exit();
}

/* ===== HANDLE REVIEW ===== */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $status = $_POST['status'];
    $comments = sanitize($_POST['comments']);

    $stmt = $conn->prepare("
        UPDATE thesis 
        SET status = ?, approval_date = NOW() 
        WHERE thesis_id = ?
    ");
    $stmt->execute([$status, $thesis_id]);

    $stmt = $conn->prepare("
        INSERT INTO approvals (thesis_id, reviewer_id, status, comments)
        VALUES (?, ?, ?, ?)
    ");
    $stmt->execute([$thesis_id, $user_id, $status, $comments]);

    $stmt = $conn->prepare("
        INSERT INTO review_logs (thesis_id, reviewer_id, action, comments)
        VALUES (?, ?, ?, ?)
    ");
    $stmt->execute([$thesis_id, $user_id, 'REVIEW_' . strtoupper($status), $comments]);

    logActivity($conn, $user_id, 'THESIS_REVIEW', 'thesis', $thesis_id, "Reviewed thesis: $status");
    sendNotification($thesis['author_id'], 'Thesis Review Update', "Your thesis has been $status.", $conn);

    $success = "Review submitted successfully.";
}

require_once '../includes/header.php';
require_once '../includes/navbar.php';
?>

<main class="container py-4">

    <!-- PAGE HEADER -->
    <div class="mb-4">
        <h2 class="fw-bold mb-1">Review Thesis</h2>
        <small class="text-muted">
            Submitted by <?= htmlspecialchars($thesis['first_name'] . ' ' . $thesis['last_name']) ?>
        </small>
    </div>

    <?php if ($success): ?>
        <div class="alert alert-success"><?= $success ?></div>
    <?php endif; ?>

    <div class="row g-4">

        <!-- THESIS DETAILS -->
        <div class="col-lg-8">
            <div class="card shadow-sm border-0">
                <div class="card-body">

                    <h4 class="fw-bold mb-3">
                        <?= htmlspecialchars($thesis['title']) ?>
                    </h4>

                    <div class="row mb-3 text-muted">
                        <div class="col-md-6"><strong>Department:</strong> <?= $thesis['department_name'] ?></div>
                        <div class="col-md-6"><strong>Program:</strong> <?= $thesis['program_name'] ?></div>
                        <div class="col-md-6"><strong>Year:</strong> <?= $thesis['publication_year'] ?></div>
                        <div class="col-md-6"><strong>Keywords:</strong> <?= $thesis['keywords'] ?></div>
                    </div>

                    <hr>

                    <h6 class="fw-semibold">Abstract</h6>
                    <p class="text-muted">
                        <?= nl2br(htmlspecialchars($thesis['abstract'])) ?>
                    </p>

                    <hr>

                    <a href="<?= BASE_URL . str_replace(ROOT_PATH . '/', '', $thesis['file_path']) ?>"
                        class="btn btn-outline-primary"
                        target="_blank">
                        <i class="fas fa-download"></i> Download Thesis
                    </a>

                </div>
            </div>
        </div>

        <!-- REVIEW PANEL -->
        <div class="col-lg-4">

            <!-- STATUS -->
            <div class="card shadow-sm border-0 mb-3">
                <div class="card-body text-center">
                    <p class="text-muted mb-1">Current Status</p>
                    <span class="badge bg-warning text-dark fs-6">
                        <?= ucfirst($thesis['status']) ?>
                    </span>
                    <p class="text-muted mt-2 mb-0">
                        Submitted on <?= date('M d, Y', strtotime($thesis['submission_date'])) ?>
                    </p>
                </div>
            </div>

            <!-- REVIEW FORM -->
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <h6 class="fw-semibold mb-3">Submit Review</h6>

                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label">Decision</label>
                            <select name="status" class="form-select" required>
                                <option value="">Select decision</option>
                                <option value="approved">Approve</option>
                                <option value="rejected">Reject</option>
                                <option value="under_review">Request Revision</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Comments</label>
                            <textarea name="comments"
                                class="form-control"
                                rows="4"
                                required></textarea>
                        </div>

                        <button type="submit" class="btn btn-success w-100">
                            <i class="fas fa-check-circle"></i> Submit Review
                        </button>
                    </form>

                </div>
            </div>

        </div>
    </div>

</main>

<?php require_once '../includes/footer.php'; ?>
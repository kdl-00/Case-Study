<?php
$page_title = "Thesis Library";
require_once '../config/config.php';
require_once '../config/database.php';
require_once '../config/session.php';
require_once '../includes/functions.php';

$database = new Database();
$conn = $database->getConnection();

// Get approved thesis
try {
    $stmt = $conn->prepare("
        SELECT t.*, u.first_name, u.last_name, d.department_name, p.program_name
        FROM thesis t
        JOIN users u ON t.author_id = u.user_id
        JOIN departments d ON t.department_id = d.department_id
        JOIN programs p ON t.program_id = p.program_id
        WHERE t.status = 'approved'
        ORDER BY t.publication_year DESC, t.title
    ");
    $stmt->execute();
    $thesis_list = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Query failed: " . $e->getMessage());
}

require_once '../includes/header.php';
require_once '../includes/navbar.php';
?>

<div class="container mt-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold">
                <i class="fas fa-book-open text-primary"></i> Thesis Library
            </h2>
            <p class="text-muted mb-0">Browse approved academic research papers</p>
        </div>
        <span class="badge bg-success fs-6">
            <?= count($thesis_list); ?> Theses
        </span>
    </div>

    <div class="row g-4">
        <?php foreach ($thesis_list as $thesis): ?>
            <div class="col-lg-6">
                <div class="card h-100 shadow-sm border-0 thesis-card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <h5 class="fw-semibold">
                                <?= htmlspecialchars($thesis['title']); ?>
                            </h5>
                            <span class="badge bg-info">
                                <?= $thesis['publication_year']; ?>
                            </span>
                        </div>

                        <p class="text-muted small mb-2">
                            <i class="fas fa-user"></i>
                            <?= $thesis['first_name'] . ' ' . $thesis['last_name']; ?>
                            &nbsp; | &nbsp;
                            <i class="fas fa-building"></i>
                            <?= $thesis['department_name']; ?>
                        </p>

                        <p class="text-secondary">
                            <?= substr($thesis['abstract'], 0, 180); ?>...
                        </p>
                    </div>

                    <div class="card-footer bg-white border-0 d-flex justify-content-between">
                        <a href="view-thesis.php?id=<?= $thesis['thesis_id']; ?>"
                            class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-eye"></i> View Thesis
                        </a>
                        <small class="text-muted">
                            <i class="fas fa-eye"></i> <?= $thesis['views']; ?> views
                        </small>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>

        <?php if (empty($thesis_list)): ?>
            <div class="col-12">
                <div class="alert alert-info text-center">
                    No approved theses available.
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
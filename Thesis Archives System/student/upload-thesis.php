<?php
$page_title = "Upload Thesis";
require_once '../config/config.php';
require_once '../config/database.php';
require_once '../config/session.php';
require_once '../includes/functions.php';

requireRole('student');

$database = new Database();
$conn = $database->getConnection();
$user_id = getUserId();

$error = '';
$success = '';

$departments = $conn->query("SELECT * FROM departments ORDER BY department_name")->fetchAll();
$programs = $conn->query("SELECT * FROM programs ORDER BY program_name")->fetchAll();
$advisers = $conn->query("SELECT user_id, first_name, last_name FROM users WHERE role = 'adviser' AND status = 'active' ORDER BY first_name")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = sanitize($_POST['title']);
    $abstract = sanitize($_POST['abstract']);
    $keywords = sanitize($_POST['keywords']);
    $co_authors = sanitize($_POST['co_authors']);
    $adviser_id = sanitize($_POST['adviser_id']);
    $department_id = sanitize($_POST['department_id']);
    $program_id = sanitize($_POST['program_id']);
    $publication_year = sanitize($_POST['publication_year']);

    if (empty($title) || empty($abstract) || empty($adviser_id) || !isset($_FILES['thesis_file'])) {
        $error = "Please fill all required fields and upload a file.";
    } else {
        $upload_result = uploadFile($_FILES['thesis_file'], THESIS_PATH, ALLOWED_THESIS_TYPES);

        if ($upload_result['success']) {
            $file_path = $upload_result['file_path'];
            $file_size = $_FILES['thesis_file']['size'];
            $file_type = pathinfo($upload_result['file_name'], PATHINFO_EXTENSION);

            $stmt = $conn->prepare("
                INSERT INTO thesis
                (title, abstract, keywords, author_id, co_authors, adviser_id, department_id, program_id, publication_year, file_path, file_size, file_type, status)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending')
            ");

            if ($stmt->execute([$title, $abstract, $keywords, $user_id, $co_authors, $adviser_id, $department_id, $program_id, $publication_year, $file_path, $file_size, $file_type])) {
                $thesis_id = $conn->lastInsertId();
                logActivity($conn, $user_id, 'THESIS_UPLOAD', 'thesis', $thesis_id, "Uploaded thesis: $title");
                sendNotification($adviser_id, 'New Thesis Submission', "A new thesis titled \"$title\" has been submitted.", $conn);
                $success = "Thesis uploaded successfully!";
            } else {
                deleteFile($file_path);
                $error = "Failed to save thesis information.";
            }
        } else {
            $error = $upload_result['message'];
        }
    }
}

require_once '../includes/header.php';
require_once '../includes/navbar.php';
?>

<main class="content container py-4">

    <!-- PAGE HEADER -->
    <div class="mb-4">
        <h2 class="fw-bold mb-1">Upload Thesis</h2>
        <p class="text-muted mb-0">Submit your thesis for adviser review</p>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-9">

            <?php if ($error): ?>
                <div class="alert alert-danger"><?= $error ?></div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="alert alert-success d-flex justify-content-between align-items-center">
                    <span><?= $success ?></span>
                    <a href="my-submissions.php" class="btn btn-sm btn-outline-success">
                        View Submissions
                    </a>
                </div>
            <?php endif; ?>

            <div class="card shadow-sm border-0">
                <div class="card-body p-4">

                    <form method="POST" enctype="multipart/form-data">

                        <!-- TITLE -->
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Thesis Title *</label>
                            <input type="text" name="title" class="form-control" required>
                        </div>

                        <!-- ABSTRACT -->
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Abstract *</label>
                            <textarea name="abstract" class="form-control" rows="6" required></textarea>
                        </div>

                        <!-- KEYWORDS -->
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Keywords</label>
                            <input type="text" name="keywords" class="form-control"
                                placeholder="e.g. machine learning, data science">
                        </div>

                        <!-- CO AUTHORS -->
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Co-authors</label>
                            <input type="text" name="co_authors" class="form-control"
                                placeholder="Optional">
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold">Adviser *</label>
                                <select name="adviser_id" class="form-select" required>
                                    <option value="">Select adviser</option>
                                    <?php foreach ($advisers as $a): ?>
                                        <option value="<?= $a['user_id'] ?>">
                                            <?= $a['first_name'] . ' ' . $a['last_name'] ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold">Publication Year *</label>
                                <input type="number" name="publication_year"
                                    class="form-control"
                                    value="<?= date('Y') ?>"
                                    max="<?= date('Y') ?>" required>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold">Department *</label>
                                <select name="department_id" class="form-select" required>
                                    <option value="">Select department</option>
                                    <?php foreach ($departments as $d): ?>
                                        <option value="<?= $d['department_id'] ?>">
                                            <?= $d['department_name'] ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold">Program *</label>
                                <select name="program_id" class="form-select" required>
                                    <option value="">Select program</option>
                                    <?php foreach ($programs as $p): ?>
                                        <option value="<?= $p['program_id'] ?>">
                                            <?= $p['program_name'] ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <!-- FILE -->
                        <div class="mb-4">
                            <label class="form-label fw-semibold">Thesis File *</label>
                            <input type="file" name="thesis_file"
                                class="form-control"
                                accept=".pdf,.doc,.docx" required>
                            <small class="text-muted">
                                Max file size: <?= formatFileSize(MAX_FILE_SIZE) ?>
                            </small>
                        </div>

                        <!-- ACTIONS -->
                        <div class="d-flex justify-content-end gap-2">
                            <a href="index.php" class="btn btn-outline-secondary">
                                Cancel
                            </a>
                            <button class="btn btn-primary">
                                <i class="fas fa-upload"></i> Submit Thesis
                            </button>
                        </div>

                    </form>

                </div>
            </div>

        </div>
    </div>

</main>

<?php require_once '../includes/footer.php'; ?>
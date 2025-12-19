<?php
$page_title = "View Thesis";
require_once '../config/config.php';
require_once '../config/database.php';
require_once '../config/session.php';
require_once '../includes/functions.php';

$database = new Database();
$conn = $database->getConnection();
$thesis_id = $_GET['id'] ?? 0;

// Get thesis
$stmt = $conn->prepare("
    SELECT t.*, u.first_name, u.last_name, u.email, 
           d.department_name, p.program_name,
           adv.first_name as adv_first, adv.last_name as adv_last
    FROM thesis t
    JOIN users u ON t.author_id = u.user_id
    JOIN users adv ON t.adviser_id = adv.user_id
    JOIN departments d ON t.department_id = d.department_id
    JOIN programs p ON t.program_id = p.program_id
    WHERE t.thesis_id = ? AND t.status = 'approved'
");
$stmt->execute([$thesis_id]);
$thesis = $stmt->fetch();

if (!$thesis) {
    header("Location: library.php");
    exit();
}

// Update views
$conn->prepare("UPDATE thesis SET views = views + 1 WHERE thesis_id = ?")->execute([$thesis_id]);

require_once '../includes/header.php';
require_once '../includes/navbar.php';
?>

<div class="container mt-4 mb-5">
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-body">
                    <h3><?php echo htmlspecialchars($thesis['title']); ?></h3>
                    <hr>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <p><strong>Author:</strong> <?php echo $thesis['first_name'] . ' ' . $thesis['last_name']; ?></p>
                            <p><strong>Adviser:</strong> <?php echo $thesis['adv_first'] . ' ' . $thesis['adv_last']; ?></p>
                            <p><strong>Department:</strong> <?php echo $thesis['department_name']; ?></p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Program:</strong> <?php echo $thesis['program_name']; ?></p>
                            <p><strong>Year:</strong> <?php echo $thesis['publication_year']; ?></p>
                            <p><strong>Views:</strong> <?php echo $thesis['views']; ?> | <strong>Downloads:</strong> <?php echo $thesis['downloads']; ?></p>
                        </div>
                    </div>

                    <?php if ($thesis['keywords']): ?>
                        <p><strong>Keywords:</strong>
                            <?php
                            $keywords = explode(',', $thesis['keywords']);
                            foreach ($keywords as $kw) {
                                echo '<span class="badge bg-secondary me-1">' . trim($kw) . '</span>';
                            }
                            ?>
                        </p>
                    <?php endif; ?>

                    <hr>
                    <h5>Abstract</h5>
                    <p class="text-justify"><?php echo nl2br(htmlspecialchars($thesis['abstract'])); ?></p>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5>Download Thesis</h5>
                </div>
                <div class="card-body text-center">
                    <i class="fas fa-file-pdf fa-5x text-danger mb-3"></i>
                    <p>File Size: <?php echo formatFileSize($thesis['file_size']); ?></p>
                    <a href="download.php?id=<?php echo $thesis['thesis_id']; ?>" class="btn btn-success w-100">
                        <i class="fas fa-download"></i> Download PDF
                    </a>
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-header">
                    <h6>Citation</h6>
                </div>
                <div class="card-body">
                    <small>
                        <?php echo $thesis['first_name'] . ' ' . $thesis['last_name']; ?> (<?php echo $thesis['publication_year']; ?>).
                        <em><?php echo $thesis['title']; ?></em>.
                        <?php echo $thesis['department_name']; ?>.
                    </small>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
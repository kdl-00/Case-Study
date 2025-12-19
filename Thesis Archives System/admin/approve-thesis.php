<?php
$page_title = "Approve Thesis";
require_once '../config/config.php';
require_once '../config/database.php';
require_once '../config/session.php';
require_once '../includes/functions.php';

requireRole('admin');

$database = new Database();
$conn = $database->getConnection();

$thesis_list = $conn->query("
    SELECT t.*, u.first_name, u.last_name, d.department_name 
    FROM thesis t 
    JOIN users u ON t.author_id = u.user_id 
    JOIN departments d ON t.department_id = d.department_id 
    ORDER BY t.submission_date DESC
")->fetchAll();

require_once '../includes/header.php';
require_once '../includes/navbar.php';
?>

<main class="container py-4">

    <!-- PAGE HEADER -->
    <div class="mb-4">
        <h2 class="fw-bold">
            <i class="fas fa-check-circle text-success"></i> Approve Thesis
        </h2>
        <p class="text-muted mb-0">
            Review and approve submitted thesis documents
        </p>
    </div>

    <!-- TABLE -->
    <div class="table-responsive">
        <table class="table align-middle" id="thesisTable">
            <thead class="bg-light text-uppercase small">
                <tr>
                    <th>Title</th>
                    <th>Author</th>
                    <th>Department</th>
                    <th>Year</th>
                    <th>Status</th>
                    <th class="text-end">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($thesis_list as $t): ?>
                    <?php
                    $statusMap = [
                        'pending' => ['warning', 'clock'],
                        'under_review' => ['info', 'eye'],
                        'approved' => ['success', 'check-circle'],
                        'rejected' => ['danger', 'times-circle']
                    ];
                    [$color, $icon] = $statusMap[$t['status']] ?? ['secondary', 'question'];
                    ?>
                    <tr>
                        <td class="fw-semibold">
                            <?= htmlspecialchars($t['title'] ?? '') ?>
                        </td>

                        <td>
                            <?= htmlspecialchars(($t['first_name'] ?? '') . ' ' . ($t['last_name'] ?? '')) ?>
                        </td>

                        <td><?= htmlspecialchars($t['department_name'] ?? '') ?></td>

                        <td><?= htmlspecialchars($t['publication_year'] ?? '') ?></td>

                        <td>
                            <span class="badge rounded-pill bg-<?= $color ?>">
                                <i class="fas fa-<?= $icon ?>"></i>
                                <?= ucfirst(str_replace('_', ' ', $t['status'])) ?>
                            </span>
                        </td>

                        <td class="text-end">
                            <a href="../public/view-thesis.php?id=<?= $t['thesis_id'] ?>"
                                class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-eye"></i> View
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

</main>

<script>
    $(document).ready(function() {
        $('#thesisTable').DataTable({
            order: [
                [3, 'desc']
            ],
            pageLength: 10,
            lengthChange: false
        });
    });
</script>

<?php require_once '../includes/footer.php'; ?>
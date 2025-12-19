<?php
$page_title = "My Submissions";
require_once '../config/config.php';
require_once '../config/database.php';
require_once '../config/session.php';
require_once '../includes/functions.php';

requireRole('student');

$database = new Database();
$conn = $database->getConnection();
$user_id = getUserId();

$stmt = $conn->prepare("
    SELECT t.*, d.department_name, p.program_name,
           u.first_name AS adviser_first, u.last_name AS adviser_last
    FROM thesis t
    JOIN departments d ON t.department_id = d.department_id
    JOIN programs p ON t.program_id = p.program_id
    JOIN users u ON t.adviser_id = u.user_id
    WHERE t.author_id = ?
    ORDER BY t.submission_date DESC
");
$stmt->execute([$user_id]);
$submissions = $stmt->fetchAll();

require_once '../includes/header.php';
require_once '../includes/navbar.php';
?>

<main class="container py-4">

    <!-- HEADER -->
    <div class="mb-4">
        <h2 class="fw-bold">My Thesis Submissions</h2>
        <p class="text-muted mb-3">
            View the status and details of your submitted research work
        </p>
        <a href="upload-thesis.php" class="btn btn-primary">
            <i class="fas fa-upload"></i> Upload New Thesis
        </a>
    </div>

    <?php if ($submissions): ?>
        <div class="card border-0 shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-borderless align-middle mb-0" id="submissionsTable">
                        <thead class="bg-light">
                            <tr>
                                <th>Thesis</th>
                                <th>Adviser</th>
                                <th>Department</th>
                                <th>Status</th>
                                <th>Submitted</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($submissions as $thesis): ?>
                                <?php
                                $status = $thesis['status'];
                                $statusUI = [
                                    'pending' => ['warning', 'clock'],
                                    'under_review' => ['info', 'eye'],
                                    'approved' => ['success', 'check-circle'],
                                    'rejected' => ['danger', 'times-circle']
                                ];
                                ?>
                                <tr class="border-bottom">
                                    <td>
                                        <div class="fw-semibold">
                                            <?= htmlspecialchars($thesis['title']); ?>
                                        </div>
                                        <small class="text-muted">
                                            <?= $thesis['program_name']; ?> · <?= $thesis['publication_year']; ?>
                                        </small>
                                    </td>

                                    <td>
                                        <?= $thesis['adviser_first'] . ' ' . $thesis['adviser_last']; ?>
                                    </td>

                                    <td><?= $thesis['department_name']; ?></td>

                                    <td>
                                        <span class="badge rounded-pill bg-<?= $statusUI[$status][0]; ?>">
                                            <i class="fas fa-<?= $statusUI[$status][1]; ?>"></i>
                                            <?= ucfirst(str_replace('_', ' ', $status)); ?>
                                        </span>
                                    </td>

                                    <td class="text-muted">
                                        <?= date('M d, Y', strtotime($thesis['submission_date'])); ?>
                                    </td>

                                    <td class="text-end">
                                        <button class="btn btn-sm btn-outline-primary view-btn"
                                            data-id="<?= $thesis['thesis_id']; ?>">
                                            <i class="fas fa-eye"></i> View
                                        </button>

                                        <?php if ($status === 'pending'): ?>
                                            <button class="btn btn-sm btn-outline-danger delete-btn"
                                                data-id="<?= $thesis['thesis_id']; ?>">
                                                <i class="fas fa-trash"></i> Delete
                                            </button>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    <?php else: ?>
        <!-- EMPTY STATE -->
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center py-5">
                <i class="fas fa-file-alt fa-3x text-muted mb-3"></i>
                <h5>No thesis submissions yet</h5>
                <p class="text-muted mb-3">
                    Upload your first thesis to begin the review process.
                </p>
                <a href="upload-thesis.php" class="btn btn-primary">
                    Upload Thesis
                </a>
            </div>
        </div>
    <?php endif; ?>

</main>

<!-- VIEW MODAL -->
<div class="modal fade" id="viewModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Thesis Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="modalContent"></div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {

        const table = $('#submissionsTable').DataTable({
            order: [
                [4, 'desc']
            ],
            pageLength: 8,
            lengthChange: false
        });

        // VIEW BUTTON (WORKS WITH DATATABLES)
        $(document).on('click', '.view-btn', function() {
            const id = $(this).data('id');

            $('#modalContent').html(
                '<div class="text-center py-5">' +
                '<i class="fas fa-spinner fa-spin fa-2x"></i>' +
                '</div>'
            );

            $('#viewModal').modal('show');
            $('#modalContent').load('view-submission.php?id=' + id);
        });

        // DELETE BUTTON (WORKS WITH DATATABLES)
        $(document).on('click', '.delete-btn', function() {
            const id = $(this).data('id');

            if (confirm('Are you sure you want to delete this submission?')) {
                window.location.href = 'delete-submission.php?id=' + id;
            }
        });

    });
</script>


<?php require_once '../includes/footer.php'; ?>
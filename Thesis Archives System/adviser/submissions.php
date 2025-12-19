<?php
$page_title = "All Submissions";
require_once '../config/config.php';
require_once '../config/database.php';
require_once '../config/session.php';
require_once '../includes/functions.php';

requireRole('adviser');

$database = new Database();
$conn = $database->getConnection();
$user_id = getUserId();

$stmt = $conn->prepare("
    SELECT t.*, u.first_name, u.last_name, d.department_name, p.program_name
    FROM thesis t 
    JOIN users u ON t.author_id = u.user_id 
    JOIN departments d ON t.department_id = d.department_id
    JOIN programs p ON t.program_id = p.program_id
    WHERE t.adviser_id = ? 
    ORDER BY t.submission_date DESC
");
$stmt->execute([$user_id]);
$submissions = $stmt->fetchAll();

require_once '../includes/header.php';
require_once '../includes/navbar.php';
?>

<div class="container mt-4">

    <!-- PAGE HEADER -->
    <div class="search-results-header">
        <h2>📑 All Thesis Submissions</h2>
        <p>Review and manage theses submitted under your supervision</p>
    </div>

    <!-- TABLE CARD -->
    <div class="card">

        <table class="modern-table" id="thesisTable">
            <thead>
                <tr>
                    <th>Thesis</th>
                    <th>Author</th>
                    <th>Department</th>
                    <th>Year</th>
                    <th>Status</th>
                    <th class="text-center">Action</th>
                </tr>
            </thead>

            <tbody>
                <?php foreach ($submissions as $s): ?>
                    <tr>
                        <td>
                            <div class="table-title">
                                <?php echo htmlspecialchars($s['title']); ?>
                            </div>
                        </td>

                        <td>
                            <?php echo htmlspecialchars($s['first_name'] . ' ' . $s['last_name']); ?>
                        </td>

                        <td>
                            <?php echo htmlspecialchars($s['department_name']); ?>
                        </td>

                        <td>
                            <?php echo htmlspecialchars($s['publication_year']); ?>
                        </td>

                        <td>
                            <?php
                            $badges = [
                                'pending' => 'badge-warning',
                                'under_review' => 'badge-info',
                                'approved' => 'badge-success',
                                'rejected' => 'badge-danger'
                            ];
                            ?>
                            <span class="badge <?php echo $badges[$s['status']]; ?>">
                                <?php echo ucwords(str_replace('_', ' ', $s['status'])); ?>
                            </span>
                        </td>

                        <td class="text-center">
                            <a href="review-thesis.php?id=<?php echo $s['thesis_id']; ?>"
                                class="btn btn-primary btn-sm">
                                Review
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>


    </div>
</div>


<script>
    $(document).ready(function() {
        $('#thesisTable').DataTable();
    });
</script>

<?php require_once '../includes/footer.php'; ?>
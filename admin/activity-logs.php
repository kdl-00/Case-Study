<?php
$page_title = "Activity Logs";
require_once '../config/config.php';
require_once '../config/database.php';
require_once '../config/session.php';
require_once '../includes/functions.php';

requireRole('admin');

$database = new Database();
$conn = $database->getConnection();

$logs = $conn->query("
    SELECT a.*, u.username, u.first_name, u.last_name 
    FROM activity_logs a 
    LEFT JOIN users u ON a.user_id = u.user_id 
    ORDER BY a.created_at DESC 
    LIMIT 500
")->fetchAll();

require_once '../includes/header.php';
require_once '../includes/navbar.php';
?>

<main class="container py-4">

    <!-- PAGE HEADER -->
    <div class="mb-4">
        <h2 class="fw-bold">
            <i class="fas fa-history text-primary"></i> Activity Logs
        </h2>
        <p class="text-muted">
            System and user activity records for auditing and monitoring
        </p>
    </div>

    <!-- LOGS TABLE -->
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">

            <div class="table-responsive">
                <table class="table align-middle mb-0" id="logsTable">
                    <thead class="bg-light text-uppercase small">
                        <tr>
                            <th>Date & Time</th>
                            <th>User</th>
                            <th>Action</th>
                            <th>Description</th>
                            <th>IP Address</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($logs as $log): ?>
                            <tr class="border-bottom">
                                <td class="text-muted">
                                    <?= date('M d, Y • H:i', strtotime($log['created_at'])); ?>
                                </td>

                                <td class="fw-semibold">
                                    <?= $log['username'] ?? '<span class="text-muted">System</span>'; ?>
                                </td>

                                <td>
                                    <span class="badge rounded-pill bg-info text-dark">
                                        <?= htmlspecialchars($log['action']); ?>
                                    </span>
                                </td>

                                <td style="max-width: 400px;">
                                    <?= htmlspecialchars($log['description']); ?>
                                </td>

                                <td class="text-muted">
                                    <?= $log['ip_address']; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

        </div>
    </div>

</main>

<script>
    $(document).ready(function() {
        $('#logsTable').DataTable({
            order: [
                [0, 'desc']
            ],
            pageLength: 10,
            lengthChange: false,
            responsive: true
        });
    });
</script>

<?php require_once '../includes/footer.php'; ?>
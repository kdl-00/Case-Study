<?php
$page_title = "Manage Users";
require_once '../config/config.php';
require_once '../config/database.php';
require_once '../config/session.php';
require_once '../includes/functions.php';

requireRole('admin');

$database = new Database();
$conn = $database->getConnection();

// Delete user
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM users WHERE user_id = ?");
    $stmt->execute([$id]);

    logActivity($conn, getUserId(), 'USER_DELETE', 'users', $id, 'Deleted user');
    header("Location: users.php");
    exit();
}

// Fetch users
$users = $conn->query("
    SELECT u.*, d.department_name, p.program_name
    FROM users u
    LEFT JOIN departments d ON u.department_id = d.department_id
    LEFT JOIN programs p ON u.program_id = p.program_id
    ORDER BY u.user_id DESC
")->fetchAll();

require_once '../includes/header.php';
require_once '../includes/navbar.php';
?>

<div class="container py-4">

    <h2 class="mb-3">
        <i class="fas fa-users"></i> Manage Users
    </h2>

    <table class="table table-bordered table-sm align-middle" id="usersTable">
        <thead class="table-light">
            <tr>
                <th style="width:60px;">ID</th>
                <th>Name</th>
                <th>Username</th>
                <th>Email</th>
                <th style="width:100px;">Role</th>
                <th style="width:150px;">Department</th>
                <th style="width:90px;">Status</th>
                <th style="width:70px;" class="text-center">Action</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($users): ?>
                <?php foreach ($users as $user): ?>
                    <tr>
                        <td><?= $user['user_id']; ?></td>

                        <td><?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></td>

                        <td><?= htmlspecialchars($user['username']); ?></td>

                        <td><?= htmlspecialchars($user['email']); ?></td>

                        <td><?= ucfirst($user['role']); ?></td>

                        <td><?= $user['department_name'] ?? 'N/A'; ?></td>

                        <td>
                            <?= ucfirst($user['status']); ?>
                        </td>

                        <td class="text-center">
                            <a href="?delete=<?= $user['user_id']; ?>"
                                class="btn btn-sm btn-outline-danger"
                                onclick="return confirm('Delete this user?')">
                                <i class="fas fa-trash"></i>
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="8" class="text-center text-muted">
                        No users found.
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

</div>

<script>
    $(document).ready(function() {
        $('#usersTable').DataTable({
            order: [
                [0, 'desc']
            ],
            pageLength: 10,
            lengthChange: false
        });
    });
</script>

<?php require_once '../includes/footer.php'; ?>
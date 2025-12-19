<?php
$page_title = "Manage Programs";
require_once '../config/config.php';
require_once '../config/database.php';
require_once '../config/session.php';
require_once '../includes/functions.php';

requireRole('admin');

$database = new Database();
$conn = $database->getConnection();

$error = $success = '';

// Add program
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add'])) {
    $name   = sanitize($_POST['program_name']);
    $code   = sanitize($_POST['program_code']);
    $degree = sanitize($_POST['degree_level']);
    $desc   = sanitize($_POST['description']);

    $stmt = $conn->prepare("
        INSERT INTO programs (program_name, program_code, degree_level, description)
        VALUES (?, ?, ?, ?)
    ");

    if ($stmt->execute([$name, $code, $degree, $desc])) {
        $success = "Program added successfully.";
        logActivity($conn, getUserId(), 'PROG_ADD', 'programs', $conn->lastInsertId(), "Added program: $name");
    } else {
        $error = "Failed to add program.";
    }
}

// Delete program
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM programs WHERE program_id = ?");
    $stmt->execute([$id]);

    logActivity($conn, getUserId(), 'PROG_DELETE', 'programs', $id, 'Deleted program');
    header("Location: programs.php");
    exit();
}

// Fetch programs
$programs = $conn->query("
    SELECT * FROM programs
    ORDER BY program_name
")->fetchAll();

require_once '../includes/header.php';
require_once '../includes/navbar.php';
?>

<div class="container py-4">

    <h2 class="mb-3">
        <i class="fas fa-graduation-cap"></i> Manage Programs
    </h2>

    <?php if ($error): ?>
        <div class="alert alert-danger"><?= $error; ?></div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="alert alert-success"><?= $success; ?></div>
    <?php endif; ?>

    <!-- ADD PROGRAM FORM -->
    <form method="POST" class="mb-4">
        <div class="row g-2">
            <div class="col-md-3">
                <input type="text" name="program_name" class="form-control" placeholder="Program name" required>
            </div>

            <div class="col-md-2">
                <input type="text" name="program_code" class="form-control" placeholder="Code" required>
            </div>

            <div class="col-md-2">
                <input type="text" name="degree_level" class="form-control" placeholder="Degree level" required>
            </div>

            <div class="col-md-3">
                <input type="text" name="description" class="form-control" placeholder="Description">
            </div>

            <div class="col-md-2">
                <button type="submit" name="add" class="btn btn-primary w-100">
                    <i class="fas fa-plus"></i> Add
                </button>
            </div>
        </div>
    </form>

    <!-- PROGRAMS TABLE -->
    <table class="table table-bordered table-sm align-middle" id="programsTable">
        <thead class="table-light">
            <tr>
                <th>Code</th>
                <th>Program Name</th>
                <th>Degree Level</th>
                <th>Description</th>
                <th style="width:70px;" class="text-center">Action</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($programs): ?>
                <?php foreach ($programs as $prog): ?>
                    <tr>
                        <td><?= htmlspecialchars($prog['program_code'] ?? ''); ?></td>
                        <td><?= htmlspecialchars($prog['program_name'] ?? ''); ?></td>
                        <td><?= htmlspecialchars($prog['degree_level'] ?? ''); ?></td>
                        <td><?= htmlspecialchars($prog['description'] ?? '—'); ?></td>

                        <td class="text-center">
                            <a href="?delete=<?= $prog['program_id']; ?>"
                                class="btn btn-sm btn-outline-danger"
                                onclick="return confirm('Delete this program?')">
                                <i class="fas fa-trash"></i>
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="5" class="text-center text-muted">
                        No programs found.
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

</div>

<script>
    $(document).ready(function() {
        $('#programsTable').DataTable({
            pageLength: 10,
            lengthChange: false
        });
    });
</script>

<?php require_once '../includes/footer.php'; ?>
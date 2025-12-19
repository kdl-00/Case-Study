<?php
$page_title = "Manage Departments";
require_once '../config/config.php';
require_once '../config/database.php';
require_once '../config/session.php';
require_once '../includes/functions.php';

requireRole('admin');

$database = new Database();
$conn = $database->getConnection();

$error = $success = '';

// Add department
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add'])) {
    $name = sanitize($_POST['department_name']);
    $code = sanitize($_POST['department_code']);
    $desc = sanitize($_POST['description']);

    $stmt = $conn->prepare("
        INSERT INTO departments (department_name, department_code, description)
        VALUES (?, ?, ?)
    ");

    if ($stmt->execute([$name, $code, $desc])) {
        $success = "Department added successfully.";
        logActivity($conn, getUserId(), 'DEPT_ADD', 'departments', $conn->lastInsertId(), "Added department: $name");
    } else {
        $error = "Failed to add department.";
    }
}

// Delete department
if (isset($_GET['delete'])) {
    $stmt = $conn->prepare("DELETE FROM departments WHERE department_id = ?");
    $stmt->execute([$_GET['delete']]);
    logActivity($conn, getUserId(), 'DEPT_DELETE', 'departments', $_GET['delete'], 'Deleted department');
    header("Location: departments.php");
    exit();
}

$departments = $conn->query("
    SELECT * FROM departments ORDER BY department_name
")->fetchAll();

require_once '../includes/header.php';
require_once '../includes/navbar.php';
?>

<div class="container py-4">

    <h2 class="mb-3">
        <i class="fas fa-building"></i> Manage Departments
    </h2>

    <?php if ($error): ?>
        <div class="alert alert-danger"><?= $error; ?></div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="alert alert-success"><?= $success; ?></div>
    <?php endif; ?>

    <!-- ADD DEPARTMENT FORM -->
    <form method="POST" class="row g-3 mb-4">
        <div class="col-md-4">
            <label class="form-label">Department Name *</label>
            <input type="text" name="department_name" class="form-control" required>
        </div>

        <div class="col-md-3">
            <label class="form-label">Code *</label>
            <input type="text" name="department_code" class="form-control" required>
        </div>

        <div class="col-md-4">
            <label class="form-label">Description</label>
            <input type="text" name="description" class="form-control">
        </div>

        <div class="col-md-1 d-flex align-items-end">
            <button type="submit" name="add" class="btn btn-primary w-100">
                Add
            </button>
        </div>
    </form>

    <!-- DEPARTMENTS TABLE -->
    <table class="table table-bordered table-sm align-middle">
        <thead class="table-light">
            <tr>
                <th style="width: 120px;">Code</th>
                <th style="width: 200px;">Name</th>
                <th>Description</th>
                <th style="width: 80px;" class="text-center">Action</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($departments): ?>
                <?php foreach ($departments as $dept): ?>
                    <tr>
                        <td><?= htmlspecialchars($dept['department_code']); ?></td>
                        <td><?= htmlspecialchars($dept['department_name']); ?></td>
                        <td><?= htmlspecialchars($dept['description']); ?></td>
                        <td class="text-center">
                            <a href="?delete=<?= $dept['department_id']; ?>"
                                class="btn btn-sm btn-outline-danger"
                                onclick="return confirm('Delete this department?')">
                                <i class="fas fa-trash"></i>
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="4" class="text-center text-muted">
                        No departments found.
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

</div>

<?php require_once '../includes/footer.php'; ?>
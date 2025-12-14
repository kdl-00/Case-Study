<?php
session_start();
require_once '../config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

if ($_SESSION['user_type'] !== 'Administrator') {
    header('Location: ../index.php');
    exit();
}

$message = '';
$message_type = '';

// Handle enrollment actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'add') {
            $student_id = $_POST['student_id'];
            $subject_id = $_POST['subject_id'];

            // Check if already enrolled
            $query = $db->prepare("SELECT id FROM enrollments WHERE student_id = ? AND subject_id = ?");
            $query->execute([$student_id, $subject_id]);

            if ($query->fetch()) {
                $message = 'Student is already enrolled in this subject.';
                $message_type = 'error';
            } else {
                $query = $db->prepare("INSERT INTO enrollments (student_id, subject_id) VALUES (?, ?)");
                if ($query->execute([$student_id, $subject_id])) {
                    $message = 'Enrollment added successfully!';
                    $message_type = 'success';
                }
            }
        } elseif ($_POST['action'] === 'update') {
            $enrollment_id = $_POST['enrollment_id'];
            $grade = $_POST['grade'];
            $status = $_POST['status'];
            $remarks = $_POST['remarks'];

            $query = $db->prepare("UPDATE enrollments SET grade = ?, status = ?, remarks = ? WHERE id = ?");
            if ($query->execute([$grade, $status, $remarks, $enrollment_id])) {
                $message = 'Enrollment updated successfully!';
                $message_type = 'success';
            }
        } elseif ($_POST['action'] === 'delete') {
            $enrollment_id = $_POST['enrollment_id'];

            $query = $db->prepare("DELETE FROM enrollments WHERE id = ?");
            if ($query->execute([$enrollment_id])) {
                $message = 'Enrollment deleted successfully!';
                $message_type = 'success';
            }
        }
    }
}

// Get filter parameters
$filter_student = $_GET['student_id'] ?? '';
$filter_subject = $_GET['subject_id'] ?? '';
$filter_status = $_GET['status'] ?? '';

// Build query with filters
$query = "
    SELECT 
        e.id,
        e.enrollment_date,
        e.grade,
        e.status,
        e.remarks,
        u.id as student_id,
        u.full_name as student_name,
        u.email as student_email,
        s.id as subject_id,
        s.subject_code,
        s.subject_name
    FROM enrollments e
    JOIN users u ON e.student_id = u.id
    JOIN subjects s ON e.subject_id = s.id
    WHERE 1=1
";

$params = [];

if ($filter_student) {
    $query .= " AND u.id = ?";
    $params[] = $filter_student;
}

if ($filter_subject) {
    $query .= " AND s.id = ?";
    $params[] = $filter_subject;
}

if ($filter_status) {
    $query .= " AND e.status = ?";
    $params[] = $filter_status;
}

$query .= " ORDER BY e.enrollment_date DESC";

$query = $db->prepare($query);
$query->execute($params);
$enrollments = $query->fetchAll();

// Get all students and subjects for dropdowns
$students = $db->query("SELECT id, full_name FROM users WHERE user_type = 'Student' ORDER BY full_name")->fetchAll();
$subjects = $db->query("SELECT id, subject_code, subject_name FROM subjects ORDER BY subject_code")->fetchAll();

// Get statistics
$stats = $db->query("
    SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN status = 'Enrolled' THEN 1 ELSE 0 END) as enrolled,
        SUM(CASE WHEN status = 'Completed' THEN 1 ELSE 0 END) as completed,
        SUM(CASE WHEN status = 'Failed' THEN 1 ELSE 0 END) as failed,
        SUM(CASE WHEN status = 'Dropped' THEN 1 ELSE 0 END) as dropped
    FROM enrollments
")->fetch();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Enrollments</title>
    <link rel="stylesheet" href="../css/main.css">
    <link rel="stylesheet" href="../css/admin.css">
</head>

<body>
    <nav class="navbar">
        <a href="../index.php" class="navbar-brand">🎓 Enrollment System</a>
        <a href="../index.php" class="btn btn-secondary">← Back to Dashboard</a>
    </nav>

    <div class="container">
        <div class="page-header">
            <div>
                <h1>📋 Manage Enrollments</h1>
                <p>Override and manage student enrollments</p>
            </div>
            <button class="btn btn-primary" onclick="openModal()">+ Add Enrollment</button>
        </div>

        <?php if ($message): ?>
            <div class="message <?php echo $message_type; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <!-- Statistics -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon icon-blue">📊</div>
                <div class="stat-info">
                    <h3><?php echo $stats['total']; ?></h3>
                    <p>Total Enrollments</p>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon icon-purple">📝</div>
                <div class="stat-info">
                    <h3><?php echo $stats['enrolled']; ?></h3>
                    <p>Enrolled</p>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon icon-green">✓</div>
                <div class="stat-info">
                    <h3><?php echo $stats['completed']; ?></h3>
                    <p>Completed</p>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon icon-orange">⚠</div>
                <div class="stat-info">
                    <h3><?php echo $stats['failed'] + $stats['dropped']; ?></h3>
                    <p>Failed/Dropped</p>
                </div>
            </div>
        </div>

        <!-- Filter Bar -->
        <div class="filter-bar">
            <form method="GET" style="display: contents;">
                <select name="student_id" class="filter-select">
                    <option value="">All Students</option>
                    <?php foreach ($students as $student): ?>
                        <option value="<?php echo $student['id']; ?>" <?php echo ($filter_student == $student['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($student['full_name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <select name="subject_id" class="filter-select">
                    <option value="">All Subjects</option>
                    <?php foreach ($subjects as $subject): ?>
                        <option value="<?php echo $subject['id']; ?>" <?php echo ($filter_subject == $subject['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($subject['subject_code'] . ' - ' . $subject['subject_name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <select name="status" class="filter-select">
                    <option value="">All Statuses</option>
                    <option value="Enrolled" <?php echo ($filter_status === 'Enrolled') ? 'selected' : ''; ?>>Enrolled</option>
                    <option value="Completed" <?php echo ($filter_status === 'Completed') ? 'selected' : ''; ?>>Completed</option>
                    <option value="Failed" <?php echo ($filter_status === 'Failed') ? 'selected' : ''; ?>>Failed</option>
                    <option value="Dropped" <?php echo ($filter_status === 'Dropped') ? 'selected' : ''; ?>>Dropped</option>
                </select>

                <button type="submit" class="btn btn-primary">Apply Filters</button>
                <a href="enrollments.php" class="btn btn-secondary">Clear</a>
            </form>
        </div>

        <!-- Enrollments Table -->
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Student</th>
                        <th>Subject</th>
                        <th>Enrolled Date</th>
                        <th>Status</th>
                        <th>Grade</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($enrollments)): ?>
                        <tr>
                            <td colspan="6" style="text-align: center; padding: 2rem; color: #666;">
                                No enrollments found
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($enrollments as $enrollment): ?>
                            <tr>
                                <td>
                                    <strong><?php echo htmlspecialchars($enrollment['student_name']); ?></strong><br>
                                    <small style="color: #666;"><?php echo htmlspecialchars($enrollment['student_email']); ?></small>
                                </td>
                                <td>
                                    <span class="subject-code-badge"><?php echo htmlspecialchars($enrollment['subject_code']); ?></span>
                                    <?php echo htmlspecialchars($enrollment['subject_name']); ?>
                                </td>
                                <td><?php echo date('M d, Y', strtotime($enrollment['enrollment_date'])); ?></td>
                                <td>
                                    <span class="role-badge role-<?php echo strtolower($enrollment['status']); ?>">
                                        <?php echo htmlspecialchars($enrollment['status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($enrollment['grade']): ?>
                                        <strong><?php echo $enrollment['grade']; ?></strong>
                                    <?php else: ?>
                                        <span style="color: #999;">Not graded</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <button class="btn btn-primary btn-sm" onclick='editEnrollment(<?php echo json_encode($enrollment); ?>)'>Edit</button>
                                    <form method="POST" style="display: inline;" onsubmit="return confirm('Delete this enrollment?');">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="enrollment_id" value="<?php echo $enrollment['id']; ?>">
                                        <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Add Enrollment Modal -->
    <div id="addModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Add Enrollment</h2>
                <button class="close-btn" onclick="closeAddModal()">&times;</button>
            </div>

            <form method="POST">
                <input type="hidden" name="action" value="add">

                <div class="form-group">
                    <label>Student</label>
                    <select name="student_id" required>
                        <option value="">-- Select Student --</option>
                        <?php foreach ($students as $student): ?>
                            <option value="<?php echo $student['id']; ?>"><?php echo htmlspecialchars($student['full_name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>Subject</label>
                    <select name="subject_id" required>
                        <option value="">-- Select Subject --</option>
                        <?php foreach ($subjects as $subject): ?>
                            <option value="<?php echo $subject['id']; ?>">
                                <?php echo htmlspecialchars($subject['subject_code'] . ' - ' . $subject['subject_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <button type="submit" class="btn btn-primary">Add Enrollment</button>
            </form>
        </div>
    </div>

    <!-- Edit Enrollment Modal -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Edit Enrollment</h2>
                <button class="close-btn" onclick="closeEditModal()">&times;</button>
            </div>

            <form method="POST" id="editForm">
                <input type="hidden" name="action" value="update">
                <input type="hidden" name="enrollment_id" id="editEnrollmentId">

                <div class="form-group">
                    <label>Student</label>
                    <input type="text" id="editStudentName" disabled>
                </div>

                <div class="form-group">
                    <label>Subject</label>
                    <input type="text" id="editSubjectName" disabled>
                </div>

                <div class="form-group">
                    <label>Grade</label>
                    <input type="number" name="grade" id="editGrade" min="0" max="100" step="0.01">
                </div>

                <div class="form-group">
                    <label>Status</label>
                    <select name="status" id="editStatus" required>
                        <option value="Enrolled">Enrolled</option>
                        <option value="Completed">Completed</option>
                        <option value="Failed">Failed</option>
                        <option value="Dropped">Dropped</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Remarks</label>
                    <textarea name="remarks" id="editRemarks"></textarea>
                </div>

                <button type="submit" class="btn btn-primary">Update Enrollment</button>
            </form>
        </div>
    </div>

    <script>
        function openModal() {
            document.getElementById('addModal').classList.add('active');
        }

        function closeAddModal() {
            document.getElementById('addModal').classList.remove('active');
        }

        function closeEditModal() {
            document.getElementById('editModal').classList.remove('active');
        }

        function editEnrollment(enrollment) {
            document.getElementById('editModal').classList.add('active');
            document.getElementById('editEnrollmentId').value = enrollment.id;
            document.getElementById('editStudentName').value = enrollment.student_name;
            document.getElementById('editSubjectName').value = enrollment.subject_code + ' - ' + enrollment.subject_name;
            document.getElementById('editGrade').value = enrollment.grade || '';
            document.getElementById('editStatus').value = enrollment.status;
            document.getElementById('editRemarks').value = enrollment.remarks || '';
        }

        window.onclick = function(event) {
            const addModal = document.getElementById('addModal');
            const editModal = document.getElementById('editModal');
            if (event.target === addModal) {
                closeAddModal();
            }
            if (event.target === editModal) {
                closeEditModal();
            }
        }
    </script>
</body>

</html>
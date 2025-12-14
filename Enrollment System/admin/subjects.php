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

// Handle subject creation/update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'add' || $_POST['action'] === 'edit') {
            $subject_code = $_POST['subject_code'];
            $subject_name = $_POST['subject_name'];
            $description = $_POST['description'];
            $units = $_POST['units'];
            $faculty_id = !empty($_POST['faculty_id']) ? $_POST['faculty_id'] : null;
            $prerequisites = $_POST['prerequisites'] ?? [];

            if ($_POST['action'] === 'add') {
                $query = $db->prepare("INSERT INTO subjects (subject_code, subject_name, description, units, faculty_id) VALUES (?, ?, ?, ?, ?)");
                if ($query->execute([$subject_code, $subject_name, $description, $units, $faculty_id])) {
                    $subject_id = $db->lastInsertId();

                    // Add prerequisites
                    foreach ($prerequisites as $prereq_id) {
                        $query = $db->prepare("INSERT INTO prerequisites (subject_id, prerequisite_id) VALUES (?, ?)");
                        $query->execute([$subject_id, $prereq_id]);
                    }

                    $message = 'Subject added successfully!';
                    $message_type = 'success';
                }
            } else {
                $subject_id = $_POST['subject_id'];
                $query = $db->prepare("UPDATE subjects SET subject_code = ?, subject_name = ?, description = ?, units = ?, faculty_id = ? WHERE id = ?");
                if ($query->execute([$subject_code, $subject_name, $description, $units, $faculty_id, $subject_id])) {
                    // Delete and re-add prerequisites
                    $query = $db->prepare("DELETE FROM prerequisites WHERE subject_id = ?");
                    $query->execute([$subject_id]);

                    foreach ($prerequisites as $prereq_id) {
                        $query = $db->prepare("INSERT INTO prerequisites (subject_id, prerequisite_id) VALUES (?, ?)");
                        $query->execute([$subject_id, $prereq_id]);
                    }

                    $message = 'Subject updated successfully!';
                    $message_type = 'success';
                }
            }
        } elseif ($_POST['action'] === 'delete') {
            $subject_id = $_POST['subject_id'];
            $query = $db->prepare("DELETE FROM subjects WHERE id = ?");
            if ($query->execute([$subject_id])) {
                $message = 'Subject deleted successfully!';
                $message_type = 'success';
            }
        }
    }
}

// Get all subjects
$query = $db->query("
    SELECT 
        s.*,
        u.full_name as faculty_name,
        GROUP_CONCAT(ps.subject_name SEPARATOR ', ') as prerequisites
    FROM subjects s
    LEFT JOIN users u ON s.faculty_id = u.id
    LEFT JOIN prerequisites p ON s.id = p.subject_id
    LEFT JOIN subjects ps ON p.prerequisite_id = ps.id
    GROUP BY s.id
    ORDER BY s.subject_code
");
$subjects = $query->fetchAll();

// Get all faculty
$query = $db->query("SELECT id, full_name FROM users WHERE user_type = 'Faculty' ORDER BY full_name");
$faculty = $query->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Subjects</title>
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
                <h1>📚 Manage Subjects</h1>
                <p>Add, edit, and delete subjects with prerequisites</p>
            </div>
            <button class="btn btn-primary" onclick="openModal()">+ Add Subject</button>
        </div>

        <?php if ($message): ?>
            <div class="message <?php echo $message_type; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Code</th>
                        <th>Subject Name</th>
                        <th>Units</th>
                        <th>Faculty</th>
                        <th>Prerequisites</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($subjects as $subject): ?>
                        <tr>
                            <td><span class="subject-code-badge"><?php echo htmlspecialchars($subject['subject_code']); ?></span></td>
                            <td><?php echo htmlspecialchars($subject['subject_name']); ?></td>
                            <td><?php echo $subject['units']; ?></td>
                            <td><?php echo htmlspecialchars($subject['faculty_name'] ?? 'TBA'); ?></td>
                            <td>
                                <?php if ($subject['prerequisites']): ?>
                                    <?php foreach (explode(', ', $subject['prerequisites']) as $prereq): ?>
                                        <span class="prereq-tag"><?php echo htmlspecialchars($prereq); ?></span>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <span style="color: #999;">None</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <button class="btn btn-primary btn-sm" onclick='editSubject(<?php echo json_encode($subject); ?>)'>Edit</button>
                                <form method="POST" style="display: inline;" onsubmit="return confirm('Delete this subject?');">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="subject_id" value="<?php echo $subject['id']; ?>">
                                    <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal -->
    <div id="subjectModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="modalTitle">Add Subject</h2>
                <button class="close-btn" onclick="closeModal()">&times;</button>
            </div>

            <form method="POST" id="subjectForm">
                <input type="hidden" name="action" id="formAction" value="add">
                <input type="hidden" name="subject_id" id="subjectId">

                <div class="form-group">
                    <label>Subject Code</label>
                    <input type="text" name="subject_code" id="subjectCode" required>
                </div>

                <div class="form-group">
                    <label>Subject Name</label>
                    <input type="text" name="subject_name" id="subjectName" required>
                </div>

                <div class="form-group">
                    <label>Description</label>
                    <textarea name="description" id="description"></textarea>
                </div>

                <div class="form-group">
                    <label>Units</label>
                    <input type="number" name="units" id="units" min="1" max="6" value="3" required>
                </div>

                <div class="form-group">
                    <label>Faculty (Optional)</label>
                    <select name="faculty_id" id="facultyId">
                        <option value="">-- Select Faculty --</option>
                        <?php foreach ($faculty as $fac): ?>
                            <option value="<?php echo $fac['id']; ?>"><?php echo htmlspecialchars($fac['full_name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>Prerequisites (Hold Ctrl/Cmd to select multiple)</label>
                    <select name="prerequisites[]" id="prerequisites" multiple size="5">
                        <?php foreach ($subjects as $subj): ?>
                            <option value="<?php echo $subj['id']; ?>"><?php echo htmlspecialchars($subj['subject_code'] . ' - ' . $subj['subject_name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <button type="submit" class="btn btn-primary">Save Subject</button>
            </form>
        </div>
    </div>

    <script>
        function openModal() {
            document.getElementById('subjectModal').classList.add('active');
            document.getElementById('modalTitle').textContent = 'Add Subject';
            document.getElementById('formAction').value = 'add';
            document.getElementById('subjectForm').reset();
        }

        function closeModal() {
            document.getElementById('subjectModal').classList.remove('active');
        }

        function editSubject(subject) {
            document.getElementById('subjectModal').classList.add('active');
            document.getElementById('modalTitle').textContent = 'Edit Subject';
            document.getElementById('formAction').value = 'edit';
            document.getElementById('subjectId').value = subject.id;
            document.getElementById('subjectCode').value = subject.subject_code;
            document.getElementById('subjectName').value = subject.subject_name;
            document.getElementById('description').value = subject.description;
            document.getElementById('units').value = subject.units;
            document.getElementById('facultyId').value = subject.faculty_id || '';
        }

        window.onclick = function(event) {
            const modal = document.getElementById('subjectModal');
            if (event.target === modal) {
                closeModal();
            }
        }
    </script>
</body>

</html>
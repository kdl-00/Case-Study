<?php
session_start();
require_once '../config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

if ($_SESSION['user_type'] !== 'Faculty') {
    header('Location: ../index.php');
    exit();
}

$faculty_id = $_SESSION['user_id'];
$message = '';
$message_type = '';

// Handle grade submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['enrollment_id'])) {
    $enrollment_id = $_POST['enrollment_id'];
    $grade = $_POST['grade'];
    $status = $_POST['status'];
    $remarks = $_POST['remarks'] ?? '';

    $query = $db->prepare("UPDATE enrollments SET grade = ?, status = ?, remarks = ? WHERE id = ?");
    if ($query->execute([$grade, $status, $remarks, $enrollment_id])) {
        $message = 'Grade submitted successfully!';
        $message_type = 'success';
    } else {
        $message = 'Failed to submit grade.';
        $message_type = 'error';
    }
}

// Get faculty's subjects
$query = $db->prepare("
    SELECT id, subject_code, subject_name 
    FROM subjects 
    WHERE faculty_id = ? 
    ORDER BY subject_code
");
$query->execute([$faculty_id]);
$subjects = $query->fetchAll();

// Get selected subject
$selected_subject = $_GET['subject_id'] ?? null;
$students = [];

if ($selected_subject) {
    // Verify this subject belongs to the faculty
    $query = $db->prepare("SELECT subject_code, subject_name FROM subjects WHERE id = ? AND faculty_id = ?");
    $query->execute([$selected_subject, $faculty_id]);
    $subject_details = $query->fetch();

    if ($subject_details) {
        // Get students enrolled in this subject
        $query = $db->prepare("
            SELECT 
                e.id as enrollment_id,
                u.id as student_id,
                u.full_name,
                u.email,
                u.profile_picture,
                e.enrollment_date,
                e.grade,
                e.status,
                e.remarks
            FROM enrollments e
            JOIN users u ON e.student_id = u.id
            WHERE e.subject_id = ?
            ORDER BY u.full_name
        ");
        $query->execute([$selected_subject]);
        $students = $query->fetchAll();
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Submit Grades</title>
    <link rel="stylesheet" href="../css/main.css">
    <link rel="stylesheet" href="../css/faculty.css">
</head>

<body>
    <nav class="navbar">
        <a href="../index.php" class="navbar-brand">🎓 Enrollment System</a>
        <a href="../index.php" class="btn btn-secondary">← Back to Dashboard</a>
    </nav>

    <div class="container">
        <div class="page-header">
            <h1>📝 Submit Grades</h1>
            <p>Enter and manage student grades for your subjects</p>
        </div>

        <?php if ($message): ?>
            <div class="message <?php echo $message_type; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <div class="subjects-list">
            <?php foreach ($subjects as $subject): ?>
                <div class="subject-card <?php echo ($selected_subject == $subject['id']) ? 'active' : ''; ?>"
                    onclick="location.href='?subject_id=<?php echo $subject['id']; ?>'">
                    <div class="subject-code"><?php echo htmlspecialchars($subject['subject_code']); ?></div>
                    <h3><?php echo htmlspecialchars($subject['subject_name']); ?></h3>
                </div>
            <?php endforeach; ?>
        </div>

        <?php if ($selected_subject && isset($subject_details)): ?>
            <div class="students-section">
                <h2>Grade Submission - <?php echo htmlspecialchars($subject_details['subject_code']); ?> - <?php echo htmlspecialchars($subject_details['subject_name']); ?></h2>

                <?php if (empty($students)): ?>
                    <div class="no-data">No students enrolled in this subject yet.</div>
                <?php else: ?>
                    <div class="students-grid">
                        <?php foreach ($students as $student): ?>
                            <div class="student-card">
                                <div style="display: flex; align-items: center; gap: 1.5rem;">
                                    <img src="../uploads/profiles/<?php echo htmlspecialchars($student['profile_picture']); ?>"
                                        alt="Profile" class="profile-img">

                                    <div style="flex: 1;">
                                        <h3><?php echo htmlspecialchars($student['full_name']); ?></h3>
                                        <p class="info-item"><strong>Email:</strong> <?php echo htmlspecialchars($student['email']); ?></p>
                                        <p class="info-item"><strong>Enrolled:</strong> <?php echo date('M d, Y', strtotime($student['enrollment_date'])); ?></p>

                                        <form method="POST" style="margin-top: 1rem;">
                                            <input type="hidden" name="enrollment_id" value="<?php echo $student['enrollment_id']; ?>">

                                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
                                                <div>
                                                    <label style="display: block; font-size: 0.875rem; font-weight: 600; margin-bottom: 0.5rem;">Grade</label>
                                                    <input type="number" name="grade"
                                                        value="<?php echo $student['grade'] ?? ''; ?>"
                                                        min="0" max="100" step="0.01"
                                                        placeholder="0-100"
                                                        style="width: 100%; padding: 0.5rem; border: 2px solid #e0e0e0; border-radius: 5px;"
                                                        required>
                                                </div>

                                                <div>
                                                    <label style="display: block; font-size: 0.875rem; font-weight: 600; margin-bottom: 0.5rem;">Status</label>
                                                    <select name="status"
                                                        style="width: 100%; padding: 0.5rem; border: 2px solid #e0e0e0; border-radius: 5px;"
                                                        required>
                                                        <option value="Enrolled" <?php echo ($student['status'] === 'Enrolled') ? 'selected' : ''; ?>>Enrolled</option>
                                                        <option value="Completed" <?php echo ($student['status'] === 'Completed') ? 'selected' : ''; ?>>Completed</option>
                                                        <option value="Failed" <?php echo ($student['status'] === 'Failed') ? 'selected' : ''; ?>>Failed</option>
                                                        <option value="Dropped" <?php echo ($student['status'] === 'Dropped') ? 'selected' : ''; ?>>Dropped</option>
                                                    </select>
                                                </div>
                                            </div>

                                            <div style="margin-bottom: 1rem;">
                                                <label style="display: block; font-size: 0.875rem; font-weight: 600; margin-bottom: 0.5rem;">Remarks (Optional)</label>
                                                <textarea name="remarks"
                                                    style="width: 100%; padding: 0.5rem; border: 2px solid #e0e0e0; border-radius: 5px; min-height: 60px;"
                                                    placeholder="Enter any remarks or comments"><?php echo htmlspecialchars($student['remarks'] ?? ''); ?></textarea>
                                            </div>

                                            <button type="submit" class="btn btn-primary">
                                                <?php echo $student['grade'] ? 'Update Grade' : 'Submit Grade'; ?>
                                            </button>
                                        </form>

                                        <?php if ($student['grade']): ?>
                                            <div style="margin-top: 1rem; padding: 0.75rem; background: #f8f9fa; border-radius: 5px;">
                                                <div style="display: flex; justify-content: space-between; align-items: center;">
                                                    <span style="font-size: 0.875rem; color: #666;">Current Grade:</span>
                                                    <span class="grade-badge <?php echo ($student['grade'] >= 75) ? 'passed' : 'failed'; ?>">
                                                        <?php echo $student['grade']; ?>
                                                    </span>
                                                </div>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</body>

</html>
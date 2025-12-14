<?php
session_start();
require_once '../config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

if ($_SESSION['user_type'] !== 'Student') {
    header('Location: ../index.php');
    exit();
}

$student_id = $_SESSION['user_id'];
$message = '';
$message_type = '';

// Handle enrollment
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['subject_id'])) {
    $subject_id = $_POST['subject_id'];

    // Check if already enrolled
    $query = $db->prepare("SELECT id FROM enrollments WHERE student_id = ? AND subject_id = ?");
    $query->execute([$student_id, $subject_id]);

    if ($query->fetch()) {
        $message = 'You are already enrolled in this subject.';
        $message_type = 'error';
    } else {
        // Check prerequisites
        $query = $db->prepare("
            SELECT p.prerequisite_id, s.subject_name 
            FROM prerequisites p
            JOIN subjects s ON p.prerequisite_id = s.id
            WHERE p.subject_id = ?
        ");
        $query->execute([$subject_id]);
        $prerequisites = $query->fetchAll();

        $can_enroll = true;
        $missing_prereqs = [];

        foreach ($prerequisites as $prereq) {
            // Check if student has completed this prerequisite
            $query = $db->prepare("
                SELECT id FROM enrollments 
                WHERE student_id = ? 
                AND subject_id = ? 
                AND status = 'Completed'
            ");
            $query->execute([$student_id, $prereq['prerequisite_id']]);

            if (!$query->fetch()) {
                $can_enroll = false;
                $missing_prereqs[] = $prereq['subject_name'];
            }
        }

        if ($can_enroll) {
            $query = $db->prepare("INSERT INTO enrollments (student_id, subject_id) VALUES (?, ?)");
            if ($query->execute([$student_id, $subject_id])) {
                $message = 'Successfully enrolled in the subject!';
                $message_type = 'success';
            } else {
                $message = 'Enrollment failed. Please try again.';
                $message_type = 'error';
            }
        } else {
            $message = 'Cannot enroll. You must complete the following prerequisite(s) first: ' . implode(', ', $missing_prereqs);
            $message_type = 'error';
        }
    }
}

// Get available subjects with enrollment status
$query = $db->prepare("
    SELECT 
        s.id,
        s.subject_code,
        s.subject_name,
        s.description,
        s.units,
        u.full_name as faculty_name,
        e.id as enrolled,
        GROUP_CONCAT(ps.subject_name SEPARATOR ', ') as prerequisites
    FROM subjects s
    LEFT JOIN users u ON s.faculty_id = u.id
    LEFT JOIN enrollments e ON s.id = e.subject_id AND e.student_id = ?
    LEFT JOIN prerequisites p ON s.id = p.subject_id
    LEFT JOIN subjects ps ON p.prerequisite_id = ps.id
    GROUP BY s.id
    ORDER BY s.subject_code
");
$query->execute([$student_id]);
$subjects = $query->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enroll in Subjects</title>
    <link rel="stylesheet" href="../css/main.css">
    <link rel="stylesheet" href="../css/student.css">
</head>

<body>
    <nav class="navbar">
        <a href="../index.php" class="navbar-brand">🎓 Enrollment System</a>
        <a href="../index.php" class="btn btn-secondary">← Back to Dashboard</a>
    </nav>

    <div class="container">
        <div class="page-header">
            <h1>📚 Enroll in Subjects</h1>
            <p>Browse available subjects and enroll based on your prerequisites</p>
        </div>

        <?php if ($message): ?>
            <div class="message <?php echo $message_type; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <div class="subjects-grid">
            <?php foreach ($subjects as $subject): ?>
                <div class="subject-card">
                    <div class="subject-code"><?php echo htmlspecialchars($subject['subject_code']); ?></div>
                    <h3><?php echo htmlspecialchars($subject['subject_name']); ?></h3>

                    <p class="subject-info">
                        <strong>Description:</strong> <?php echo htmlspecialchars($subject['description']); ?>
                    </p>

                    <p class="subject-info">
                        <strong>Units:</strong> <?php echo $subject['units']; ?>
                    </p>

                    <p class="subject-info">
                        <strong>Faculty:</strong> <?php echo htmlspecialchars($subject['faculty_name'] ?? 'TBA'); ?>
                    </p>

                    <?php if ($subject['prerequisites']): ?>
                        <div class="prerequisites">
                            <strong>Prerequisites:</strong> <?php echo htmlspecialchars($subject['prerequisites']); ?>
                        </div>
                    <?php endif; ?>

                    <?php if ($subject['enrolled']): ?>
                        <span class="enrolled-badge">✓ Enrolled</span>
                    <?php else: ?>
                        <form method="POST" style="margin-top: 1rem;">
                            <input type="hidden" name="subject_id" value="<?php echo $subject['id']; ?>">
                            <button type="submit" class="btn btn-primary">Enroll Now</button>
                        </form>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</body>

</html>
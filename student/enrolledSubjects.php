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

// Get enrolled subjects with grades
$query = $db->prepare("
    SELECT 
        s.id,
        s.subject_code,
        s.subject_name,
        s.description,
        s.units,
        u.full_name as faculty_name,
        e.enrollment_date,
        e.grade,
        e.status,
        e.remarks
    FROM enrollments e
    JOIN subjects s ON e.subject_id = s.id
    LEFT JOIN users u ON s.faculty_id = u.id
    WHERE e.student_id = ?
    ORDER BY e.enrollment_date DESC
");
$query->execute([$student_id]);
$enrollments = $query->fetchAll();

// Calculate statistics
$total_enrolled = count($enrollments);
$total_completed = 0;
$total_units = 0;
$total_grade_sum = 0;
$graded_count = 0;

foreach ($enrollments as $enrollment) {
    if ($enrollment['status'] === 'Completed') {
        $total_completed++;
    }
    if ($enrollment['grade']) {
        $total_grade_sum += $enrollment['grade'];
        $graded_count++;
    }
    $total_units += $enrollment['units'];
}

$gpa = $graded_count > 0 ? round($total_grade_sum / $graded_count, 2) : 0;
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Subjects</title>
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
            <h1>📋 My Subjects</h1>
            <p>View your enrolled subjects and grades</p>
        </div>

        <!-- Statistics Cards -->
        <div class="subjects-grid" style="margin-bottom: 2rem;">
            <div class="subject-card" style="text-align: center;">
                <div style="font-size: 2.5rem; color: #667eea; font-weight: bold;"><?php echo $total_enrolled; ?></div>
                <div style="color: #666; margin-top: 0.5rem;">Total Enrolled</div>
            </div>

            <div class="subject-card" style="text-align: center;">
                <div style="font-size: 2.5rem; color: #28a745; font-weight: bold;"><?php echo $total_completed; ?></div>
                <div style="color: #666; margin-top: 0.5rem;">Completed</div>
            </div>

            <div class="subject-card" style="text-align: center;">
                <div style="font-size: 2.5rem; color: #ffc107; font-weight: bold;"><?php echo $total_units; ?></div>
                <div style="color: #666; margin-top: 0.5rem;">Total Units</div>
            </div>

            <div class="subject-card" style="text-align: center;">
                <div style="font-size: 2.5rem; color: #7b1fa2; font-weight: bold;"><?php echo $gpa; ?></div>
                <div style="color: #666; margin-top: 0.5rem;">GPA</div>
            </div>
        </div>

        <?php if (empty($enrollments)): ?>
            <div class="no-data">
                <div style="font-size: 3rem; margin-bottom: 1rem;">📚</div>
                <h3>No Enrolled Subjects Yet</h3>
                <p>Start by enrolling in available subjects</p>
                <a href="enroll.php" class="btn btn-primary" style="margin-top: 1rem;">Browse Subjects</a>
            </div>
        <?php else: ?>
            <div class="subjects-grid">
                <?php foreach ($enrollments as $enrollment): ?>
                    <div class="subject-card">
                        <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 0.75rem;">
                            <div class="subject-code"><?php echo htmlspecialchars($enrollment['subject_code']); ?></div>
                            <span class="status-badge status-<?php echo strtolower($enrollment['status']); ?>">
                                <?php echo htmlspecialchars($enrollment['status']); ?>
                            </span>
                        </div>

                        <h3><?php echo htmlspecialchars($enrollment['subject_name']); ?></h3>

                        <p class="subject-info">
                            <strong>Description:</strong> <?php echo htmlspecialchars($enrollment['description']); ?>
                        </p>

                        <p class="subject-info">
                            <strong>Units:</strong> <?php echo $enrollment['units']; ?>
                        </p>

                        <p class="subject-info">
                            <strong>Faculty:</strong> <?php echo htmlspecialchars($enrollment['faculty_name'] ?? 'TBA'); ?>
                        </p>

                        <p class="subject-info">
                            <strong>Enrolled:</strong> <?php echo date('M d, Y', strtotime($enrollment['enrollment_date'])); ?>
                        </p>

                        <div style="margin-top: 1rem; padding-top: 1rem; border-top: 1px solid #e0e0e0;">
                            <?php if ($enrollment['grade']): ?>
                                <div style="display: flex; justify-content: space-between; align-items: center;">
                                    <span style="font-weight: 600; color: #333;">Grade:</span>
                                    <span class="grade-badge <?php echo ($enrollment['grade'] >= 75) ? 'passed' : 'failed'; ?>">
                                        <?php echo $enrollment['grade']; ?>
                                    </span>
                                </div>
                                <?php if ($enrollment['remarks']): ?>
                                    <p class="subject-info" style="margin-top: 0.5rem;">
                                        <strong>Remarks:</strong> <?php echo htmlspecialchars($enrollment['remarks']); ?>
                                    </p>
                                <?php endif; ?>
                            <?php else: ?>
                                <span class="grade-badge pending">⏳ Awaiting Grade</span>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</body>

</html>
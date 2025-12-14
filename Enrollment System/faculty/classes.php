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

// Get faculty's subjects with student counts
$query = $db->prepare("
    SELECT 
        s.id,
        s.subject_code,
        s.subject_name,
        s.units,
        COUNT(e.id) as student_count
    FROM subjects s
    LEFT JOIN enrollments e ON s.id = e.subject_id AND e.status = 'Enrolled'
    WHERE s.faculty_id = ?
    GROUP BY s.id
    ORDER BY s.subject_code
");
$query->execute([$faculty_id]);
$subjects = $query->fetchAll();

// Get students for selected subject
$selected_subject = $_GET['subject_id'] ?? null;
$students = [];

if ($selected_subject) {
    $query = $db->prepare("
        SELECT 
            u.id,
            u.full_name,
            u.email,
            u.profile_picture,
            u.signature,
            e.enrollment_date,
            e.grade,
            e.status
        FROM enrollments e
        JOIN users u ON e.student_id = u.id
        WHERE e.subject_id = ?
        ORDER BY u.full_name
    ");
    $query->execute([$selected_subject]);
    $students = $query->fetchAll();

    // Get subject details
    $query = $db->prepare("SELECT subject_code, subject_name FROM subjects WHERE id = ?");
    $query->execute([$selected_subject]);
    $subject_details = $query->fetch();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Classes</title>
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
            <h1>👥 My Classes</h1>
            <p>View students enrolled in your subjects</p>
        </div>

        <div class="subjects-list">
            <?php foreach ($subjects as $subject): ?>
                <div class="subject-card <?php echo ($selected_subject == $subject['id']) ? 'active' : ''; ?>"
                    onclick="location.href='?subject_id=<?php echo $subject['id']; ?>'">
                    <div class="subject-code"><?php echo htmlspecialchars($subject['subject_code']); ?></div>
                    <span class="student-count"><?php echo $subject['student_count']; ?> students</span>
                    <h3><?php echo htmlspecialchars($subject['subject_name']); ?></h3>
                    <p style="color: #666; font-size: 0.875rem;">Units: <?php echo $subject['units']; ?></p>
                </div>
            <?php endforeach; ?>
        </div>

        <?php if ($selected_subject && $subject_details): ?>
            <div class="students-section">
                <h2>Students in <?php echo htmlspecialchars($subject_details['subject_code']); ?> - <?php echo htmlspecialchars($subject_details['subject_name']); ?></h2>

                <?php if (empty($students)): ?>
                    <div class="no-data">No students enrolled in this subject yet.</div>
                <?php else: ?>
                    <div class="students-grid">
                        <?php foreach ($students as $student): ?>
                            <div class="student-card">
                                <div class="student-images">
                                    <img src="../uploads/profiles/<?php echo htmlspecialchars($student['profile_picture']); ?>"
                                        alt="Profile" class="profile-img">
                                    <div style="text-align: center; font-size: 0.75rem; color: #666;">Signature:</div>
                                    <img src="../uploads/signatures/<?php echo htmlspecialchars($student['signature']); ?>"
                                        alt="Signature" class="signature-img">
                                </div>

                                <div class="student-info">
                                    <h3><?php echo htmlspecialchars($student['full_name']); ?></h3>
                                    <p class="info-item"><strong>Email:</strong> <?php echo htmlspecialchars($student['email']); ?></p>
                                    <p class="info-item"><strong>Enrolled:</strong> <?php echo date('M d, Y', strtotime($student['enrollment_date'])); ?></p>
                                    <p class="info-item">
                                        <strong>Status:</strong> <?php echo htmlspecialchars($student['status']); ?>
                                    </p>
                                    <?php if ($student['grade']): ?>
                                        <p class="info-item">
                                            <strong>Grade:</strong>
                                            <span class="grade-badge passed"><?php echo $student['grade']; ?></span>
                                        </p>
                                    <?php else: ?>
                                        <span class="grade-badge pending">No grade yet</span>
                                    <?php endif; ?>
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
<?php
session_start();
require_once 'config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$user_type = $_SESSION['user_type'];
$user_name = $_SESSION['user_name'];

// Get user profile picture
$query = $db->prepare("SELECT profile_picture FROM users WHERE id = ?");
$query->execute([$user_id]);
$user = $query->fetch();
$profile_pic = $user['profile_picture'] ?? 'default-avatar.png';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enrollment System Dashboard</title>
    <link rel="stylesheet" href="css/main.css">
</head>

<body>
    <nav class="navbar">
        <a href="index.php" class="navbar-brand">🎓 Enrollment System</a>
        <div class="navbar-user">
            <img src="uploads/profiles/<?php echo htmlspecialchars($profile_pic); ?>" alt="Profile" class="user-avatar">
            <span><?php echo htmlspecialchars($user_name); ?></span>
            <a href="logout.php" class="btn btn-secondary">Logout</a>
        </div>
    </nav>

    <div class="container">
        <div class="welcome-card">
            <h1>Welcome back, <?php echo htmlspecialchars($user_name); ?>!</h1>
            <span class="user-type-badge badge-<?php echo strtolower($user_type); ?>">
                <?php echo htmlspecialchars($user_type); ?>
            </span>
        </div>

        <div class="dashboard-grid">
            <?php if ($user_type === 'Student'): ?>
                <div class="dashboard-card" onclick="location.href='student/enroll.php'">
                    <div class="card-icon icon-purple">📚</div>
                    <h3>Enroll in Subjects</h3>
                    <p>Browse and enroll in available subjects</p>
                </div>

                <div class="dashboard-card" onclick="location.href='student/enrolledSubjects.php'">
                    <div class="card-icon icon-blue">📋</div>
                    <h3>My Subjects</h3>
                    <p>View your enrolled subjects and grades</p>
                </div>

                <div class="dashboard-card" onclick="location.href='profile.php'">
                    <div class="card-icon icon-green">👤</div>
                    <h3>My Profile</h3>
                    <p>Update your profile and signature</p>
                </div>

            <?php elseif ($user_type === 'Faculty'): ?>
                <div class="dashboard-card" onclick="location.href='faculty/classes.php'">
                    <div class="card-icon icon-purple">👥</div>
                    <h3>My Classes</h3>
                    <p>View students enrolled in your classes</p>
                </div>

                <div class="dashboard-card" onclick="location.href='faculty/grades.php'">
                    <div class="card-icon icon-blue">📝</div>
                    <h3>Submit Grades</h3>
                    <p>Enter and manage student grades</p>
                </div>

                <div class="dashboard-card" onclick="location.href='profile.php'">
                    <div class="card-icon icon-green">👤</div>
                    <h3>My Profile</h3>
                    <p>Update your profile and signature</p>
                </div>

            <?php elseif ($user_type === 'Administrator'): ?>
                <div class="dashboard-card" onclick="location.href='admin/users.php'">
                    <div class="card-icon icon-purple">👥</div>
                    <h3>Manage Users</h3>
                    <p>Add, edit, and delete users</p>
                </div>

                <div class="dashboard-card" onclick="location.href='admin/subjects.php'">
                    <div class="card-icon icon-blue">📚</div>
                    <h3>Manage Subjects</h3>
                    <p>Add subjects and set prerequisites</p>
                </div>

                <div class="dashboard-card" onclick="location.href='admin/enrollments.php'">
                    <div class="card-icon icon-orange">📋</div>
                    <h3>Manage Enrollments</h3>
                    <p>Override and manage enrollments</p>
                </div>

                <div class="dashboard-card" onclick="location.href='profile.php'">
                    <div class="card-icon icon-green">👤</div>
                    <h3>My Profile</h3>
                    <p>Update your profile and signature</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>

</html>
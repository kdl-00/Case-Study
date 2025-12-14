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

// Handle user creation/update/delete
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'add') {
            $username = $_POST['username'];
            $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
            $full_name = $_POST['full_name'];
            $email = $_POST['email'];
            $user_type = $_POST['user_type'];

            // Check if username or email exists
            $query = $db->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
            $query->execute([$username, $email]);

            if ($query->fetch()) {
                $message = 'Username or email already exists.';
                $message_type = 'error';
            } else {
                $query = $db->prepare("INSERT INTO users (username, password, full_name, email, user_type) VALUES (?, ?, ?, ?, ?)");
                if ($query->execute([$username, $password, $full_name, $email, $user_type])) {
                    $message = 'User added successfully!';
                    $message_type = 'success';
                } else {
                    $message = 'Failed to add user.';
                    $message_type = 'error';
                }
            }
        } elseif ($_POST['action'] === 'edit') {
            $user_id = $_POST['user_id'];
            $full_name = $_POST['full_name'];
            $email = $_POST['email'];
            $user_type = $_POST['user_type'];

            // Update password only if provided
            if (!empty($_POST['password'])) {
                $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
                $query = $db->prepare("UPDATE users SET full_name = ?, email = ?, user_type = ?, password = ? WHERE id = ?");
                $query->execute([$full_name, $email, $user_type, $password, $user_id]);
            } else {
                $query = $db->prepare("UPDATE users SET full_name = ?, email = ?, user_type = ? WHERE id = ?");
                $query->execute([$full_name, $email, $user_type, $user_id]);
            }

            $message = 'User updated successfully!';
            $message_type = 'success';
        } elseif ($_POST['action'] === 'delete') {
            $user_id = $_POST['user_id'];

            // Don't allow deleting yourself
            if ($user_id == $_SESSION['user_id']) {
                $message = 'You cannot delete your own account.';
                $message_type = 'error';
            } else {
                $query = $db->prepare("DELETE FROM users WHERE id = ?");
                if ($query->execute([$user_id])) {
                    $message = 'User deleted successfully!';
                    $message_type = 'success';
                }
            }
        }
    }
}

// Get all users with statistics
$query = $db->query("
    SELECT 
        u.*,
        (SELECT COUNT(*) FROM enrollments WHERE student_id = u.id) as enrollment_count,
        (SELECT COUNT(*) FROM subjects WHERE faculty_id = u.id) as subject_count
    FROM users u
    ORDER BY u.user_type, u.full_name
");
$users = $query->fetchAll();

// Get user type counts
$query = $db->query("SELECT user_type, COUNT(*) as count FROM users GROUP BY user_type");
$type_counts = $query->fetchAll(PDO::FETCH_KEY_PAIR);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users</title>
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
                <h1>👥 Manage Users</h1>
                <p>Add, edit, and delete system users</p>
            </div>
            <button class="btn btn-primary" onclick="openModal()">+ Add User</button>
        </div>

        <?php if ($message): ?>
            <div class="message <?php echo $message_type; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <!-- Statistics -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon icon-blue">👨‍🎓</div>
                <div class="stat-info">
                    <h3><?php echo $type_counts['Student'] ?? 0; ?></h3>
                    <p>Students</p>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon icon-purple">👨‍🏫</div>
                <div class="stat-info">
                    <h3><?php echo $type_counts['Faculty'] ?? 0; ?></h3>
                    <p>Faculty</p>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon icon-orange">👨‍💼</div>
                <div class="stat-info">
                    <h3><?php echo $type_counts['Administrator'] ?? 0; ?></h3>
                    <p>Administrators</p>
                </div>
            </div>
        </div>

        <!-- Users Table -->
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Profile</th>
                        <th>Name</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td>
                                <?php if ($user['profile_picture']): ?>
                                    <img src="../uploads/profiles/<?php echo htmlspecialchars($user['profile_picture']); ?>"
                                        alt="Profile" class="user-avatar-small">
                                <?php else: ?>
                                    <div style="width: 50px; height: 50px; border-radius: 50%; background: #e0e0e0; display: flex; align-items: center; justify-content: center; font-size: 1.5rem;">👤</div>
                                <?php endif; ?>
                            </td>
                            <td><strong><?php echo htmlspecialchars($user['full_name']); ?></strong></td>
                            <td><?php echo htmlspecialchars($user['username']); ?></td>
                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                            <td>
                                <span class="role-badge role-<?php echo strtolower($user['user_type']); ?>">
                                    <?php echo htmlspecialchars($user['user_type']); ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($user['profile_picture'] && $user['signature']): ?>
                                    <span style="color: #28a745;">✓ Complete</span>
                                <?php else: ?>
                                    <span style="color: #dc3545;">⚠ Incomplete</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <button class="btn btn-primary btn-sm" onclick='editUser(<?php echo json_encode($user); ?>)'>Edit</button>
                                <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                    <form method="POST" style="display: inline;" onsubmit="return confirm('Delete this user?');">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                        <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                                    </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal -->
    <div id="userModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="modalTitle">Add User</h2>
                <button class="close-btn" onclick="closeModal()">&times;</button>
            </div>

            <form method="POST" id="userForm">
                <input type="hidden" name="action" id="formAction" value="add">
                <input type="hidden" name="user_id" id="userId">

                <div class="form-group">
                    <label>Full Name</label>
                    <input type="text" name="full_name" id="fullName" required>
                </div>

                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" id="email" required>
                </div>

                <div class="form-group">
                    <label>Username</label>
                    <input type="text" name="username" id="username" required>
                </div>

                <div class="form-group">
                    <label>User Type</label>
                    <select name="user_type" id="userType" required>
                        <option value="Student">Student</option>
                        <option value="Faculty">Faculty</option>
                        <option value="Administrator">Administrator</option>
                    </select>
                </div>

                <div class="form-group">
                    <label id="passwordLabel">Password</label>
                    <input type="password" name="password" id="password">
                    <small id="passwordHint" style="color: #666; display: none;">Leave blank to keep current password</small>
                </div>

                <button type="submit" class="btn btn-primary">Save User</button>
            </form>
        </div>
    </div>

    <script>
        function openModal() {
            document.getElementById('userModal').classList.add('active');
            document.getElementById('modalTitle').textContent = 'Add User';
            document.getElementById('formAction').value = 'add';
            document.getElementById('userForm').reset();
            document.getElementById('username').disabled = false;
            document.getElementById('password').required = true;
            document.getElementById('passwordLabel').textContent = 'Password';
            document.getElementById('passwordHint').style.display = 'none';
        }

        function closeModal() {
            document.getElementById('userModal').classList.remove('active');
        }

        function editUser(user) {
            document.getElementById('userModal').classList.add('active');
            document.getElementById('modalTitle').textContent = 'Edit User';
            document.getElementById('formAction').value = 'edit';
            document.getElementById('userId').value = user.id;
            document.getElementById('fullName').value = user.full_name;
            document.getElementById('email').value = user.email;
            document.getElementById('username').value = user.username;
            document.getElementById('username').disabled = true;
            document.getElementById('userType').value = user.user_type;
            document.getElementById('password').value = '';
            document.getElementById('password').required = false;
            document.getElementById('passwordLabel').textContent = 'New Password (Optional)';
            document.getElementById('passwordHint').style.display = 'block';
        }

        window.onclick = function(event) {
            const modal = document.getElementById('userModal');
            if (event.target === modal) {
                closeModal();
            }
        }
    </script>
</body>

</html>
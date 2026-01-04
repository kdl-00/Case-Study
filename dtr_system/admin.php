<?php
require_once 'config.php';

// Check if logged in
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

// Check if admin
if ($_SESSION['user']['user_type'] !== 'admin') {
    header('Location: dashboard.php');
    exit;
}

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Add user
    if (isset($_POST['admin_add_user'])) {
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $userType = $_POST['user_type'] ?? 'faculty';

        if (!empty($name) && !empty($email) && !empty($password)) {
            // Get users
            $users = [];
            if (file_exists(USERS_FILE)) {
                $content = file_get_contents(USERS_FILE);
                if (!empty($content)) {
                    $users = unserialize($content);
                }
            }

            // Check if email exists
            $emailExists = false;
            foreach ($users as $user) {
                if ($user['email'] === $email) {
                    $emailExists = true;
                    break;
                }
            }

            if (!$emailExists) {
                $users[] = [
                    'name' => $name,
                    'email' => $email,
                    'password' => password_hash($password, PASSWORD_DEFAULT),
                    'user_type' => $userType,
                    'picture' => '',
                    'created_at' => date('Y-m-d H:i:s')
                ];

                file_put_contents(USERS_FILE, serialize($users));
                $message = 'User added successfully';
            } else {
                $error = 'Email already exists';
            }
        } else {
            $error = 'All fields are required';
        }
    }

    // Delete user
    if (isset($_POST['admin_delete_user'])) {
        $emailToDelete = $_POST['delete_email'] ?? '';
        if (!empty($emailToDelete) && $emailToDelete !== $_SESSION['user']['email']) {
            // Get users
            $users = [];
            if (file_exists(USERS_FILE)) {
                $content = file_get_contents(USERS_FILE);
                if (!empty($content)) {
                    $users = unserialize($content);
                }
            }

            // Remove user
            $users = array_filter($users, function ($user) use ($emailToDelete) {
                return $user['email'] !== $emailToDelete;
            });

            file_put_contents(USERS_FILE, serialize(array_values($users)));
            $message = 'User deleted successfully';
        }
    }
}

// Get filter and sort parameters
$searchKeyword = $_GET['search'] ?? '';
$sortBy = $_GET['sort'] ?? '';
$sortOrder = $_GET['order'] ?? 'asc';

// Get users
$users = [];
if (file_exists(USERS_FILE)) {
    $content = file_get_contents(USERS_FILE);
    if (!empty($content)) {
        $users = unserialize($content);
    }
}

// Filter users
if ($searchKeyword) {
    $users = array_filter($users, function ($user) use ($searchKeyword) {
        return stripos($user['name'], $searchKeyword) !== false ||
            stripos($user['email'], $searchKeyword) !== false;
    });
}

// Sort users
if ($sortBy) {
    usort($users, function ($a, $b) use ($sortBy, $sortOrder) {
        $valA = $a[$sortBy] ?? '';
        $valB = $b[$sortBy] ?? '';

        $result = strcasecmp($valA, $valB);
        return $sortOrder === 'desc' ? -$result : $result;
    });
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - DTR System</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>

    <div class="container">
        <div class="card">
            <h1>Admin Panel - User Management</h1>

            <a href="dashboard.php"><button class="secondary">Back to Dashboard</button></a>

            <?php if ($message): ?>
                <div class="message success"><?php echo htmlspecialchars($message); ?></div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="message error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <h2>Add New User</h2>
            <form method="POST">
                <div class="form-group">
                    <label>Full Name:</label>
                    <input type="text" name="name" required>
                </div>

                <div class="form-group">
                    <label>Email:</label>
                    <input type="email" name="email" required>
                </div>

                <div class="form-group">
                    <label>Password:</label>
                    <input type="password" name="password" required>
                </div>

                <div class="form-group">
                    <label>User Type:</label>
                    <select name="user_type" required>
                        <option value="faculty">Faculty</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>

                <button type="submit" name="admin_add_user">Add User</button>
            </form>

            <hr style="margin: 30px 0;">

            <h2>User List</h2>

            <div class="search-box">
                <form method="GET">
                    <input type="text" name="search" placeholder="Search by name or email..."
                        value="<?php echo htmlspecialchars($searchKeyword); ?>">
                    <button type="submit">Search</button>
                    <?php if ($searchKeyword): ?>
                        <a href="admin.php"><button type="button" class="secondary">Clear</button></a>
                    <?php endif; ?>
                </form>
            </div>

            <table>
                <thead>
                    <tr>
                        <th>Picture</th>
                        <th onclick="sortTable('name')">
                            Name
                            <?php if ($sortBy === 'name'): ?>
                                <span class="sort-icon"><?php echo $sortOrder === 'asc' ? '▲' : '▼'; ?></span>
                            <?php endif; ?>
                        </th>
                        <th onclick="sortTable('email')">
                            Email
                            <?php if ($sortBy === 'email'): ?>
                                <span class="sort-icon"><?php echo $sortOrder === 'asc' ? '▲' : '▼'; ?></span>
                            <?php endif; ?>
                        </th>
                        <th onclick="sortTable('user_type')">
                            User Type
                            <?php if ($sortBy === 'user_type'): ?>
                                <span class="sort-icon"><?php echo $sortOrder === 'asc' ? '▲' : '▼'; ?></span>
                            <?php endif; ?>
                        </th>
                        <th onclick="sortTable('created_at')">
                            Created At
                            <?php if ($sortBy === 'created_at'): ?>
                                <span class="sort-icon"><?php echo $sortOrder === 'asc' ? '▲' : '▼'; ?></span>
                            <?php endif; ?>
                        </th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td>
                                <?php
                                $picturePath = isset($user['picture']) ? $user['picture'] : '';
                                if (!empty($picturePath) && file_exists($picturePath)) {
                                    echo '<img src="' . htmlspecialchars($picturePath) . '" alt="Profile" class="user-list-img">';
                                } else {
                                    echo '<div class="user-list-img" style="background: #ddd; display: flex; align-items: center; justify-content: center; font-size: 20px;">👤</div>';
                                }
                                ?>
                            </td>
                            <td><?php echo htmlspecialchars($user['name']); ?></td>
                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                            <td>
                                <span class="badge <?php echo $user['user_type']; ?>">
                                    <?php echo strtoupper($user['user_type']); ?>
                                </span>
                            </td>
                            <td><?php echo htmlspecialchars($user['created_at']); ?></td>
                            <td>
                                <?php if ($user['email'] !== $_SESSION['user']['email']): ?>
                                    <form method="POST" style="display: inline;"
                                        onsubmit="return confirm('Are you sure you want to delete this user?');">
                                        <input type="hidden" name="delete_email" value="<?php echo htmlspecialchars($user['email']); ?>">
                                        <button type="submit" name="admin_delete_user" class="danger" style="padding: 8px 15px; font-size: 14px;">Delete</button>
                                    </form>
                                <?php else: ?>
                                    <span style="color: #999;">Current User</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <?php if (empty($users)): ?>
                <p style="text-align: center; padding: 20px; color: #999;">No users found.</p>
            <?php endif; ?>
        </div>
    </div>

    <script>
        function sortTable(column) {
            const urlParams = new URLSearchParams(window.location.search);
            const currentSort = urlParams.get('sort');
            const currentOrder = urlParams.get('order') || 'asc';

            let newOrder = 'asc';
            if (currentSort === column && currentOrder === 'asc') {
                newOrder = 'desc';
            }

            urlParams.set('sort', column);
            urlParams.set('order', newOrder);

            window.location.search = urlParams.toString();
        }
    </script>

</body>

</html>
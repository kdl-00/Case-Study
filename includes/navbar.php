<nav class="navbar">
    <div class="container">

        <!-- SYSTEM TITLE (NOT CLICKABLE) -->
        <div class="navbar-brand">
            📚 Thesis Archive Management System
        </div>

        <!-- NAV MENU -->
        <ul class="navbar-menu">

            <li>
                <a href="<?php echo BASE_URL; ?>public/library.php">
                    Library
                </a>
            </li>

            <li>
                <a href="<?php echo BASE_URL; ?>public/search.php"
                    class="<?php echo basename($_SERVER['PHP_SELF']) === 'search.php' ? 'active' : ''; ?>">
                    Search
                </a>
            </li>

            <?php if (isLoggedIn()): ?>

                <?php if (hasRole('student')): ?>
                    <li>
                        <a href="<?php echo BASE_URL; ?>student/index.php">
                            Dashboard
                        </a>
                    </li>
                <?php elseif (hasRole('adviser')): ?>
                    <li>
                        <a href="<?php echo BASE_URL; ?>adviser/index.php">
                            Dashboard
                        </a>
                    </li>
                <?php elseif (hasRole('admin')): ?>
                    <li>
                        <a href="<?php echo BASE_URL; ?>admin/index.php">
                            Admin Panel
                        </a>
                    </li>
                <?php endif; ?>

                <li>
                    <a href="<?php echo BASE_URL . $_SESSION['role']; ?>/profile.php">
                        <?php echo $_SESSION['first_name']; ?>
                    </a>
                </li>

                <li>
                    <a href="<?php echo BASE_URL; ?>auth/logout.php"
                        class="btn btn-danger btn-sm"
                        style="margin-left: 20px; padding: 8px 16px;">
                        Logout
                    </a>
                </li>

            <?php else: ?>

                <li>
                    <a href="<?php echo BASE_URL; ?>auth/login.php"
                        class="btn btn-primary btn-sm"
                        style="margin-left: 20px; padding: 8px 16px;">
                        Login
                    </a>
                </li>

            <?php endif; ?>

        </ul>
    </div>
</nav>
<?php
require_once 'config.php';

// Redirect to appropriate page based on login status
if (isset($_SESSION['user'])) {
    header('Location: dashboard.php');
    exit;
} else {
    header('Location: login.php');
    exit;
}

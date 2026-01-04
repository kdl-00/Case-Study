<?php
session_start();

define('USERS_FILE', 'data/users.txt');
define('UPLOAD_DIR', 'uploads/');

if (!file_exists('data')) {
    mkdir('data', 0755, true);
}

if (!file_exists(USERS_FILE)) {
    file_put_contents(USERS_FILE, '');
}

if (!file_exists(UPLOAD_DIR)) {
    mkdir(UPLOAD_DIR, 0755, true);
}

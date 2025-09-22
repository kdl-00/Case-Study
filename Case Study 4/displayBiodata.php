<?php
session_start();

if (!isset($_SESSION['biodata'])) {
    header("Location: biodata.php");
    exit;
}

$data = $_SESSION['biodata'];

function sanitizeData($input)
{
    return htmlspecialchars(stripslashes(trim($input)));
}
?>
<!DOCTYPE html>
<html>

<head>
    <title>Submitted Bio-Data</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <div class="output-box">
        <div class="header">
            <h2>Bio-Data</h2>
            <?php if (!empty($data['picture'])): ?>
                <div class="photo">
                    <img src="<?= sanitizeData($data['picture']) ?>" alt="Profile Picture">
                </div>
            <?php endif; ?>
        </div>

        <h3>PERSONAL DATA</h3>
        <div class="two-column">
            <!-- Column 1 -->
            <div>
                <p><strong>Position Desired:</strong> <?= sanitizeData($data['position']) ?></p>
                <p><strong>Name:</strong> <?= sanitizeData($data['name']) ?></p>
                <p><strong>City Address:</strong> <?= sanitizeData($data['city']) ?></p>
                <p><strong>Provincial Address:</strong> <?= sanitizeData($data['provincial']) ?></p>
                <p><strong>Telephone:</strong> <?= sanitizeData($data['telephone']) ?></p>
                <p><strong>Mobile:</strong> <?= sanitizeData($data['mobile']) ?></p>
                <p><strong>Email:</strong> <?= sanitizeData($data['email']) ?></p>
                <p><strong>Date of Birth:</strong> <?= sanitizeData($data['dob']) ?></p>
                <p><strong>Place of Birth:</strong> <?= sanitizeData($data['pob']) ?></p>
                <p><strong>Age:</strong> <?= sanitizeData($data['age']) ?></p>
            </div>

            <!-- Column 2 -->
            <div>
                <p><strong>Gender:</strong> <?= sanitizeData($data['gender']) ?></p>
                <p><strong>Civil Status:</strong> <?= sanitizeData($data['status']) ?></p>
                <p><strong>Citizenship:</strong> <?= sanitizeData($data['citizenship']) ?></p>
                <p><strong>Height:</strong> <?= sanitizeData($data['height']) ?></p>
                <p><strong>Weight:</strong> <?= sanitizeData($data['weight']) ?></p>
                <p><strong>Religion:</strong> <?= sanitizeData($data['religion']) ?></p>
                <p><strong>Language/Dialect:</strong> <?= sanitizeData($data['language']) ?></p>
                <p><strong>Father's Name:</strong> <?= sanitizeData($data['father']) ?></p>
                <p><strong>Mother's Name:</strong> <?= sanitizeData($data['mother']) ?></p>
            </div>
        </div>

        <h4>EDUCATIONAL BACKGROUND</h4>
        <div>
            <p><strong>Elementary:</strong> <?= sanitizeData($data['elementary']) ?></p>
        </div>
        <div>
            <p><strong>High School:</strong> <?= sanitizeData($data['highschool']) ?></p>
            <p><strong>College:</strong> <?= sanitizeData($data['college']) ?></p>
        </div>
    </div>
</body>

</html>
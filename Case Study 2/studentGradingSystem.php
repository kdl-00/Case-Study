<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Grading System</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background: #fff;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }

        .card {
            border: 1px solid #ddd;
            padding: 25px;
            border-radius: 8px;
            width: 380px;
            background: #fafafa;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
        }

        h2 {
            margin-bottom: 15px;
            color: #444;
            text-align: center;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        td,
        th {
            border: 1px solid #ccc;
            padding: 10px;
            text-align: left;
            color: #333;
        }

        th {
            background: #007bff;
            color: white;
        }

        .remarks {
            margin-top: 15px;
            font-weight: bold;
            color: #007bff;
            text-align: center;
        }
    </style>

</head>

<body>
    <?php
    $name = $_GET["name"];
    $score = $_GET["score"];

    $grade = "";
    $description = "";
    $remarks = "";

    if ($score >= 95 && $score <= 100) {
        $grade = "A";
        $description = "Excellent";
        $remarks = "Outstanding Performance!";
    } elseif ($score >= 90 && $score <= 94) {
        $grade = "B";
        $description = "Very Good";
        $remarks = "Great Job!";
    } elseif ($score >= 85 && $score <= 89) {
        $grade = "C";
        $description = "Good";
        $remarks = "Good effort, keep it up!";
    } elseif ($score >= 75 && $score <= 84) {
        $grade = "D";
        $description = "Needs Improvement";
        $remarks = "Work harder next time.";
    } elseif ($score >= 0 && $score < 75) {
        $grade = "F";
        $description = "Failed";
        $remarks = "You need to improve.";
    } else {
        $grade = "N/A";
        $description = "Invalid Score";
        $remarks = "Please enter a score between 0 and 100.";
    }
    ?>

    <div class="card">
        <h2>Student Grade Report</h2>
        <table>
            <tr>
                <th>Field</th>
                <th>Details</th>
            </tr>
            <tr>
                <td><strong>Name</strong></td>
                <td><?php echo htmlspecialchars($name); ?></td>
            </tr>
            <tr>
                <td><strong>Score</strong></td>
                <td><?php echo $score; ?></td>
            </tr>
            <tr>
                <td><strong>Grade</strong></td>
                <td><?php echo $grade . " (" . $description . ")"; ?></td>
            </tr>
        </table>
        <div class="remarks"><?php echo $remarks; ?></div>
    </div>
</body>

</html>
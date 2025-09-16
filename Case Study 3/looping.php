<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Number Grid</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #ffffff;
            padding: 20px;
            text-align: center;
        }

        table {
            border-collapse: collapse;
            margin: 20px auto;
        }

        td {
            border: 1px solid #000;
            width: 40px;
            height: 40px;
            text-align: center;
        }

        td.odd {
            background-color: yellow;
            font-weight: bold;
        }

        form {
            margin-bottom: 15px;
        }

        input[type="number"] {
            padding: 6px 10px;
            font-size: 16px;
            border: 1px solid #ccc;
            border-radius: 4px;
            margin: 5px;
        }

        input[type="submit"] {
            padding: 6px 15px;
            font-size: 16px;
            border: none;
            border-radius: 4px;
            background-color: #007bff;
            color: white;
            cursor: pointer;
        }

        input[type="submit"]:hover {
            background-color: #0056b3;
        }
    </style>
</head>

<body>
    <form method="post">
        Rows: <input type="number" name="rows" min="1" required>
        Columns: <input type="number" name="columns" min="1" required>
        <input type="submit" value="Submit">
    </form>

    <?php
    if ($_POST) {
        $rows = $_POST['rows'];
        $columns = $_POST['columns'];
        $number = 1;

        echo "<table>";
        for ($i = 1; $i <= $rows; $i++) {
            echo "<tr>";
            for ($j = 1; $j <= $columns; $j++) {
                $class = ($number % 2 != 0) ? "odd" : "";
                echo "<td class='$class'>$number</td>";
                $number++;
            }
            echo "</tr>";
        }
        echo "</table>";
    }
    ?>
</body>

</html>
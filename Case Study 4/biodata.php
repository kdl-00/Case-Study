<?php
session_start();

$errors = [];
$position = $name = $city = $provincial = $telephone = $mobile = $email = "";
$dob = $pob = $age = $gender = $status = $citizenship = $height = $weight = "";
$religion = $language = $father = $mother = $elementary = $highschool = $college = "";
$picture = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    function sanitizeData($data)
    {
        return htmlspecialchars(trim($data));
    }

    if (empty($_POST["position"])) {
        $errors["position"] = "Position is required";
    } else {
        $position = sanitizeData($_POST["position"]);
    }

    if (empty($_POST["name"])) {
        $errors["name"] = "Name is required";
    } else {
        $name = sanitizeData($_POST["name"]);
    }

    if (empty($_POST["city"])) {
        $errors["city"] = "City address is required";
    } else {
        $city = sanitizeData($_POST["city"]);
    }

    if (empty($_POST["provincial"])) {
        $errors["provincial"] = "Provincial address is required";
    } else {
        $provincial = sanitizeData($_POST["provincial"]);
    }

    if (empty($_POST["telephone"])) {
        $errors["telephone"] = "Telephone is required";
    } elseif (!preg_match("/^[0-9]{7,15}$/", $_POST["telephone"])) {
        $errors["telephone"] = "Telephone must be 7–15 digits";
    } else {
        $telephone = $_POST["telephone"];
    }

    if (empty($_POST["mobile"])) {
        $errors["mobile"] = "Mobile is required";
    } elseif (!preg_match("/^[0-9]{10,15}$/", $_POST["mobile"])) {
        $errors["mobile"] = "Mobile must be 10–15 digits";
    } else {
        $mobile = $_POST["mobile"];
    }

    if (empty($_POST["email"])) {
        $errors["email"] = "Email is required";
    } elseif (!filter_var($_POST["email"], FILTER_VALIDATE_EMAIL)) {
        $errors["email"] = "Invalid email format";
    } else {
        $email = $_POST["email"];
    }

    if (empty($_POST["dob"])) {
        $errors["dob"] = "Date of Birth is required";
    } else {
        $dob = $_POST["dob"];
    }

    if (empty($_POST["pob"])) {
        $errors["pob"] = "Place of Birth is required";
    } else {
        $pob = sanitizeData($_POST["pob"]);
    }

    if (empty($_POST["age"])) {
        $errors["age"] = "Age is required";
    } elseif (!is_numeric($_POST["age"])) {
        $errors["age"] = "Age must be a number";
    } else {
        $age = $_POST["age"];
    }

    if (empty($_POST["gender"])) {
        $errors["gender"] = "Gender is required";
    } else {
        $gender = sanitizeData($_POST["gender"]);
    }

    if (empty($_POST["status"])) {
        $errors["status"] = "Civil Status is required";
    } else {
        $status = sanitizeData($_POST["status"]);
    }

    if (empty($_POST["citizenship"])) {
        $errors["citizenship"] = "Citizenship is required";
    } else {
        $citizenship = sanitizeData($_POST["citizenship"]);
    }

    if (empty($_POST["height"])) {
        $errors["height"] = "Height is required";
    } else {
        $height = sanitizeData($_POST["height"]);
    }

    if (empty($_POST["weight"])) {
        $errors["weight"] = "Weight is required";
    } else {
        $weight = sanitizeData($_POST["weight"]);
    }

    if (empty($_POST["religion"])) {
        $errors["religion"] = "Religion is required";
    } else {
        $religion = sanitizeData($_POST["religion"]);
    }

    if (empty($_POST["language"])) {
        $errors["language"] = "Language/Dialect is required";
    } else {
        $language = sanitizeData($_POST["language"]);
    }

    if (empty($_POST["father"])) {
        $errors["father"] = "Father's Name is required";
    } else {
        $father = sanitizeData($_POST["father"]);
    }

    if (empty($_POST["mother"])) {
        $errors["mother"] = "Mother's Name is required";
    } else {
        $mother = sanitizeData($_POST["mother"]);
    }

    if (empty($_POST["elementary"])) {
        $errors["elementary"] = "Elementary is required";
    } else {
        $elementary = sanitizeData($_POST["elementary"]);
    }

    if (empty($_POST["highschool"])) {
        $errors["highschool"] = "High School is required";
    } else {
        $highschool = sanitizeData($_POST["highschool"]);
    }

    if (empty($_POST["college"])) {
        $errors["college"] = "College is required";
    } else {
        $college = sanitizeData($_POST["college"]);
    }

    //File Upload
    if (empty($errors)) {
        if (isset($_FILES['myfile']) && $_FILES['myfile']['error'] == 0) {
            $target_dir = "uploads/";
            if (!is_dir($target_dir)) {
                mkdir($target_dir, 0777, true);
            }

            $file_name = time() . "_" . basename($_FILES["myfile"]["name"]);
            $target_file = $target_dir . $file_name;
            $fileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
            $allowed_types = ["jpg", "jpeg", "png", "gif"];

            if (in_array($fileType, $allowed_types) && $_FILES["myfile"]["size"] <= 2 * 1024 * 1024) {
                if (move_uploaded_file($_FILES["myfile"]["tmp_name"], $target_file)) {
                    $picture = $target_file;
                }
            }
        }

        $_SESSION['biodata'] = compact(
            'position',
            'name',
            'city',
            'provincial',
            'telephone',
            'mobile',
            'email',
            'dob',
            'pob',
            'age',
            'gender',
            'status',
            'citizenship',
            'height',
            'weight',
            'religion',
            'language',
            'father',
            'mother',
            'elementary',
            'highschool',
            'college',
            'picture'
        );

        header("Location: displayBiodata.php");
        exit;
    }
}
?>
<!DOCTYPE html>
<html>

<head>
    <title>Bio-Data Form</title>
    <link rel="stylesheet" href="style.css">

</head>

<body>
    <form method="post" action="" enctype="multipart/form-data">
        <h2>Bio-Data Form</h2>
        <h3>Personal Data</h3>

        <div class="two-column">
            <!-- Column 1 -->
            <div>
                <label>Position Desired:</label>
                <input type="text" name="position" value="<?= $position ?>">
                <small class="error"><?= $errors["position"] ?? "" ?></small>

                <label>Name:</label>
                <input type="text" name="name" value="<?= $name ?>">
                <small class="error"><?= $errors["name"] ?? "" ?></small>

                <label>City Address:</label>
                <input type="text" name="city" value="<?= $city ?>">
                <small class="error"><?= $errors["city"] ?? "" ?></small>

                <label>Provincial Address:</label>
                <input type="text" name="provincial" value="<?= $provincial ?>">
                <small class="error"><?= $errors["provincial"] ?? "" ?></small>

                <label>Telephone:</label>
                <input type="text" name="telephone" value="<?= $telephone ?>">
                <small class="error"><?= $errors["telephone"] ?? "" ?></small>

                <label>Mobile:</label>
                <input type="text" name="mobile" value="<?= $mobile ?>">
                <small class="error"><?= $errors["mobile"] ?? "" ?></small>

                <label>Email:</label>
                <input type="text" name="email" value="<?= $email ?>">
                <small class="error"><?= $errors["email"] ?? "" ?></small>

                <label>Date of Birth:</label>
                <input type="date" name="dob" value="<?= $dob ?>">
                <small class="error"><?= $errors["dob"] ?? "" ?></small>

                <label>Place of Birth:</label>
                <input type="text" name="pob" value="<?= $pob ?>">
                <small class="error"><?= $errors["pob"] ?? "" ?></small>

                <label>Age:</label>
                <input type="text" name="age" value="<?= $age ?>">
                <small class="error"><?= $errors["age"] ?? "" ?></small>
            </div>

            <!-- Column 2 -->
            <div>
                <label>Gender:</label>
                <input type="radio" name="gender" value="Male" <?= $gender === "Male" ? "checked" : "" ?>> Male
                <input type="radio" name="gender" value="Female" <?= $gender === "Female" ? "checked" : "" ?>> Female
                <small class="error"><?= $errors["gender"] ?? "" ?></small>

                <label>Civil Status:</label>
                <input type="text" name="status" value="<?= $status ?>">
                <small class="error"><?= $errors["status"] ?? "" ?></small>

                <label>Citizenship:</label>
                <input type="text" name="citizenship" value="<?= $citizenship ?>">
                <small class="error"><?= $errors["citizenship"] ?? "" ?></small>

                <label>Height:</label>
                <input type="text" name="height" value="<?= $height ?>">
                <small class="error"><?= $errors["height"] ?? "" ?></small>

                <label>Weight:</label>
                <input type="text" name="weight" value="<?= $weight ?>">
                <small class="error"><?= $errors["weight"] ?? "" ?></small>

                <label>Religion:</label>
                <input type="text" name="religion" value="<?= $religion ?>">
                <small class="error"><?= $errors["religion"] ?? "" ?></small>

                <label>Language/Dialect:</label>
                <input type="text" name="language" value="<?= $language ?>">
                <small class="error"><?= $errors["language"] ?? "" ?></small>

                <label>Father's Name:</label>
                <input type="text" name="father" value="<?= $father ?>">
                <small class="error"><?= $errors["father"] ?? "" ?></small>

                <label>Mother's Name:</label>
                <input type="text" name="mother" value="<?= $mother ?>">
                <small class="error"><?= $errors["mother"] ?? "" ?></small>
            </div>
        </div>

        <h4>Educational Background</h4>

        <label>Elementary:</label>
        <input type="text" name="elementary" value="<?= $elementary ?>">
        <small class="error"><?= $errors["elementary"] ?? "" ?></small>

        <label>High School:</label>
        <input type="text" name="highschool" value="<?= $highschool ?>">
        <small class="error"><?= $errors["highschool"] ?? "" ?></small>

        <label>College:</label>
        <input type="text" name="college" value="<?= $college ?>">
        <small class="error"><?= $errors["college"] ?? "" ?></small>

        <label>Upload Picture:</label>
        <input type="file" name="myfile" accept="image/*">

        <br><br>
        <div style="text-align:center;">
            <input type="submit" value="Submit">
        </div>
    </form>
</body>

</html>
<?php

//personal information
$name = "Katerina Dawn P. Loresco";
$major = "Web and Mobile Developer";
$phone = "0961 754 6693";
$email = "klorezco@gmail.com";
$linkedin = "www.linkedin.com/in/loresco-129b1a381";
$github = "github.com/kdl-00";
$address = "Tuliao, Sta. Barbara, Pangasinan";
$dob = "7 August 2003";
$gender = "Female";
$nationality = "Filipino";
$profile_pic = "image.jpg";

//about me
$about = "An IT student majoring in Mobile and Web Development, learning how to build and design websites and mobile applications. 
I am motivated to grow my skills and use them in practical projects.";

//education
$highschoolYears = "2014–2020";
$highschoolName = "Daniel Maramba National High School";

$collegeYears = "2021–Present";
$program = "Bachelor of Science in Information Technology";
$university = "Pangasinan State University-Urdaneta Campus";
$specialization = "Web and Mobile Developing";

//skills
$skill1 = "PHP";
$skill2 = "Dart";
$skill3 = "C#";
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            background: #ffffff;
        }

        .header {
            background: #0077b6;
            color: white;
            padding: 20px;
            display: flex;
            align-items: center;
        }

        .header img {
            width: 120px;
            height: 120px;
            margin-right: 20px;
            border: 3px solid white;
            object-fit: cover;
        }

        .header .info h1 {
            margin: 0;
            font-size: 26px;
        }

        .header .info h3 {
            margin: 5px 0 10px;
            font-weight: normal;
        }

        .contact {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 5px 20px;
            margin-top: 10px;
            font-size: 14px;
        }

        .section {
            padding-left: 20px;
            padding-right: 20px;
            padding-bottom: 10px;
        }

        .section h2 {
            margin-top: 0;
            color: #0077b6;
            border-bottom: 2px solid #ddd;
            padding-bottom: 5px;
        }
    </style>
</head>

<body>

    <!-- Header -->
    <div class="header">
        <img src="<?php echo $profile_pic; ?>">
        <div class="info">
            <h1><?php echo $name; ?></h1>
            <h3><?php echo $major; ?></h3>
            <br>
            <div class="contact">
                <div><b>Phone:</b> <?php echo $phone; ?></div>
                <div><b>Address:</b> <?php echo $address; ?></div>
                <div><b>Email:</b> <?php echo $email; ?></div>
                <div><b>Date of Birth:</b> <?php echo $dob; ?></div>
                <div><b>LinkedIn:</b> <?php echo $linkedin; ?></div>
                <div><b>Gender:</b> <?php echo $gender; ?></div>
                <div><b>GitHub:</b> <?php echo $github; ?></div>
                <div><b>Nationality:</b> <?php echo $nationality; ?></div>
            </div>
        </div>
    </div>

    <!-- About -->
    <div class="section">
        <p><?php echo $about; ?></p>
    </div>

    <!-- Education -->
    <div class="section">
        <h2>Education</h2>
        <p><b><?php echo $highschoolYears; ?> - High School Diploma</b><br>
            <i><?php echo $highschoolName; ?></i><br><br>
            Activies:
        <ul>
            <li>N/A</li>
            <li>N/A</li>
            <li>N/A</li>
        </ul>

        <p><b><?php echo $collegeYears; ?> - <?php echo $program; ?></b><br>
            <i><?php echo $university; ?></i><br><br>
            Specialization:
        <ul>
            <li>
                <?php echo $specialization; ?></p>
            </li>
        </ul>
    </div>

    <!-- Experience -->
    <div class="section">
        <h2>Experience</h2>
        <p><b>N/A</b><br>
    </div>

    <!-- Skills -->
    <div class="section">
        <h2>Skills</h2>
        <ul>
            <li><?php echo $skill1; ?></li>
            <li><?php echo $skill2; ?></li>
            <li><?php echo $skill3; ?></li>
        </ul>
    </div>

</body>

</html>
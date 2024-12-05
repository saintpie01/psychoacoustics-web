<?php
session_start();
$testMsg = "Which is the modulated noise?";
?>


<!doctype html>
<html lang="en">

<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" type="image/x-icon" href="files/logo.png">

    <!-- Bootstrap CSS -->
    <link href="bootstrap/css/bootstrap.min.css" rel="stylesheet">

    <title>Psychoacoustics-web - Duration test</title>

    <script type="text/javascript" src="js/fetchTexts.js"></script>

    <?php include 'view_modules/export_test_parameters.php'; ?>
    
    <script type="text/javascript"
        src="js/test_common/generatorSoundAndNoise.js<?php if (isset($_SESSION['version'])) echo "?{$_SESSION['version']}"; ?>"
        defer></script>
    <script src="js/test_common/test_shared.js"></script>
    <script type="text/javascript"
        src="js/noisesModulation.js<?php if (isset($_SESSION['version'])) echo "?{$_SESSION['version']}"; ?>"
        defer></script>
    <script src="bootstrap/js/bootstrap.bundle.min.js"></script>
</head>

<body>
    <?php include 'view_modules/test_dashboard.php'; ?>
</body>

</html>
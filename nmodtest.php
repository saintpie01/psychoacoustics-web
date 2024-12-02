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

    <script>
        // pass info from php session to js
        var carAmpDb = parseFloat(<?php echo $_SESSION["amplitude"]; ?>);
        var freq = parseFloat(<?php echo $_SESSION["frequency"]; ?>);
        var dur = parseFloat(<?php echo $_SESSION["duration"]; ?>);
        var carDur = parseFloat(<?php echo $_SESSION["duration"]; ?>);
        var modFreq = parseFloat(<?php echo $_SESSION["modFrequency"]; ?>);
        var onRamp = parseFloat(<?php echo $_SESSION["onRamp"]; ?>);
        var offRamp = parseFloat(<?php echo $_SESSION["offRamp"]; ?>);
        var modAmpDb = parseFloat(<?php echo $_SESSION["modAmplitude"]; ?>);
        var modFreq = parseFloat(<?php echo $_SESSION["modFrequency"]; ?>);
        var modPhase = parseFloat(<?php echo $_SESSION["modPhase"]; ?>);
        var blocks = parseInt(<?php echo $_SESSION["blocks"]; ?>);
        var delta = parseFloat(<?php echo $_SESSION["delta"]; ?>);
        var nAFC = parseInt(<?php echo $_SESSION["nAFC"]; ?>);
        var ITI = parseInt(<?php echo $_SESSION["ITI"]; ?>);
        var ISI = parseInt(<?php echo $_SESSION["ISI"]; ?>);
        var feedback = parseInt(<?php echo $_SESSION["checkFb"]; ?>);
        var factor = parseFloat(<?php echo $_SESSION["factor"]; ?>);
        var secondFactor = parseFloat(<?php echo $_SESSION["secFactor"]; ?>);
        var reversals = parseInt(<?php echo $_SESSION["reversals"]; ?>);
        var secondReversals = parseInt(<?php echo $_SESSION["secReversals"]; ?>);
        var reversalThreshold = parseInt(<?php echo $_SESSION["threshold"]; ?>);
        var algorithm = <?php echo "'{$_SESSION["algorithm"]}'"; ?>;
        var currentBlock = parseInt(<?php if (isset($_SESSION["currentBlock"])) echo $_SESSION["currentBlock"] + 1;
                                    else echo "1" ?>);
    </script>
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
    <?php include 'html_modules/test_dashboard.php'; ?>
</body>

</html>
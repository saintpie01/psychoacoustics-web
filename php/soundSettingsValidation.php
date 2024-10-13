<?php

session_start();

include "config.php";
require_once "dbconnect.php";
require 'error_codes/soundSettingsErrorCodes.php';

unset($_SESSION['score']);
unset($_SESSION['geometric_score']);
unset($_SESSION['currentBlock']);
unset($_SESSION['results']);


if (isset($_SESSION['test'])) { //referral present

    //fetch  all the test parameters from DB (referrral test saved as a mockup test in the "test" table)
    $sql = "SELECT Type, Amplitude as amp, Frequency as freq, Duration as dur, OnRamp as onRamp, OffRamp as offRamp, blocks, Delta, nAFC, 
			    ISI, ITI, Factor as fact, Reversal as rev, SecFactor as secfact, SecReversal as secrev, Feedback as feedback,
				Threshold as thr, Algorithm as alg, ModAmplitude as modAmp, ModFrequency as modFreq, ModPhase as modPhase
					
			FROM test
					
			WHERE Guest_ID='{$_SESSION['test']['guest']}' AND Test_count='{$_SESSION['test']['count']}'";
            
    try {

        $conn = connectdb();
        $result = $conn->query($sql);
        $row = $result->fetch_assoc();

        //select the test type to start
        if ($row['Type'] == 'PURE_TONE_INTENSITY')
            $type = "amp";
        else if ($row['Type'] == 'PURE_TONE_FREQUENCY')
            $type = "freq";
        else if ($row['Type'] == 'PURE_TONE_DURATION')
            $type = "dur";
        else if ($row['Type'] == 'WHITE_NOISE_GAP')
            $type = "gap";
        else if ($row['Type'] == 'WHITE_NOISE_DURATION')
            $type = "ndur";
        else if ($row['Type'] == 'WHITE_NOISE_MODULATION')
            $type = "nmod";

        $_SESSION['testTypeCmp'] = $type;

        $_SESSION["amplitude"] = $row["amp"];
        $_SESSION["frequency"] = $row["freq"];
        $_SESSION["duration"] = $row["dur"];
        $_SESSION["onRamp"] = $row["onRamp"];
        $_SESSION["offRamp"] = $row["offRamp"];
        $_SESSION["modAmplitude"] = $row["modAmp"];
        $_SESSION["modFrequency"] = $row["modFreq"];
        $_SESSION["modPhase"] = $row["modPhase"];
        $_SESSION["blocks"] = $row["blocks"];
        $_SESSION["nAFC"] = $row["nAFC"];
        $_SESSION["ITI"] = $row["ITI"];
        $_SESSION["ISI"] = $row["ISI"];
        $_SESSION["delta"] = $row["Delta"];
        $_SESSION["checkFb"] = $row["feedback"];
        $_SESSION["saveSettings"] = 0;
        $_SESSION["factor"] = $row["fact"];
        $_SESSION["secFactor"] = $row["secfact"];
        $_SESSION["reversals"] = $row["rev"];
        $_SESSION["secReversals"] = $row["secrev"];
        $_SESSION["threshold"] = $row["thr"];
        $_SESSION["algorithm"] = $row["alg"];


        header("Location: ../{$type}test.php");
        exit;

    } catch (Exception $e) {
        error_log($e, 3, "errors_log.txt");
        header("Location: ../index.php?err=db");
        exit;
    }
}


//this section calls the section required to check all the forms inserted
//stored in soundSettinsValidation.php, if no redirect string is returned, it goes on
$redirect = "";
$redirect = checkSSEC();
if ($redirect != "") {
    header($redirect);
    exit;
}

$checkFb = 0;
if (isset($_POST["checkFb"]))
    $checkFb = 1;

$saveSettings = 0;
if (isset($_POST['saveSettings']))
    $saveSettings = 1;

$_SESSION["amplitude"] = $_POST["amplitude"];
$_SESSION["frequency"] = $_POST["frequency"];
$_SESSION["duration"] = $_POST["duration"];
$_SESSION["onRamp"] = $_POST["onRamp"];
$_SESSION["offRamp"] = $_POST["offRamp"];
if ($_GET['test'] == "nmod") {
    $_SESSION["modAmplitude"] = $_POST["modAmplitude"];
    $_SESSION["modFrequency"] = $_POST["modFrequency"];
    $_SESSION["modPhase"] = $_POST["modPhase"];
}
$_SESSION["blocks"] = $_POST["blocks"];
$_SESSION["nAFC"] = $_POST["nAFC"];
$_SESSION["ITI"] = $_POST["ITI"];
$_SESSION["ISI"] = $_POST["ISI"];
$_SESSION["delta"] = $_POST["delta"];
$_SESSION["checkFb"] = $checkFb;
$_SESSION["saveSettings"] = $saveSettings;
$_SESSION["factor"] = $_POST["factor"];
$_SESSION["secFactor"] = $_POST["secFactor"];
$_SESSION["reversals"] = $_POST["reversals"];
$_SESSION["secReversals"] = $_POST["secReversals"];
$_SESSION["threshold"] = $_POST["threshold"];
$_SESSION["algorithm"] = $_POST["algorithm"];

		
$_SESSION['testTypeCmp'] = $_GET['test'];

//??????????????????
/*if ($checkSave) {
        $sql = "UPDATE account SET fk_guestTest = '$id', fk_testCount = '$count' WHERE username = '{$_SESSION['currentLoggedUsername']}' ";
        $conn->query($sql);
    }*/
header("Location: ../{$_GET['test']}test.php");


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

        $_SESSION['testTypeShort'] = $type;

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
        //$_SESSION["saveSettings"] = 0;
        $_SESSION["factor"] = $row["fact"];
        $_SESSION["secFactor"] = $row["secfact"];
        $_SESSION["reversals"] = $row["rev"];
        $_SESSION["secReversals"] = $row["secrev"];
        $_SESSION["threshold"] = $row["thr"];
        $_SESSION["algorithm"] = $row["alg"];

        /*$id = $_SESSION['idGuestTest'];

        $t_type = $row["Type"];

        $sql = "SELECT Max(Test_count) as count FROM test WHERE Guest_ID='$id'";
        $result = $conn->query($sql);
        $row = $result->fetch_assoc();

        //il test corrente è il numero di test già effettuati + 1
        $count = $row['count'] + 1;


        if ($type == "gap" || $type == "ndur") {
            $sql = "INSERT INTO test VALUES ('$id', '$count', current_timestamp(), '$t_type', ";
            $sql .= "'{$_SESSION['amplitude']}', NULL, '{$_SESSION['duration']}', '{$_SESSION['onRamp']}', '{$_SESSION['offRamp']}', '{$_SESSION['blocks']}', '{$_SESSION['delta']}', ";
            $sql .= "'{$_SESSION['nAFC']}', '{$_SESSION['ITI']}', '{$_SESSION['ISI']}', '{$_SESSION['factor']}', '{$_SESSION['reversals']}', ";
            $sql .= "'{$_SESSION['secFactor']}', '{$_SESSION['secReversals']}', '{$_SESSION['threshold']}', '{$_SESSION['algorithm']}', '', '0','$checkFb', NULL, NULL, NULL, '$deviceInfo')";
        } else if ($type == "nmod") {
            $sql = "INSERT INTO test VALUES ('$id', '$count', current_timestamp(), '$t_type', ";
            $sql .= "'{$_SESSION['amplitude']}', NULL, '{$_SESSION['duration']}', '{$_SESSION['onRamp']}', '{$_SESSION['offRamp']}', '{$_SESSION['blocks']}', '{$_SESSION['delta']}', ";
            $sql .= "'{$_SESSION['nAFC']}', '{$_SESSION['ITI']}', '{$_SESSION['ISI']}', '{$_SESSION['factor']}', '{$_SESSION['reversals']}', ";
            $sql .= "'{$_SESSION['secFactor']}', '{$_SESSION['secReversals']}', '{$_SESSION['threshold']}', '{$_SESSION['algorithm']}', '', '0','$checkFb', '" . floatval($_SESSION["modAmplitude"]) . "', '{$_SESSION["modFrequency"]}', '{$_SESSION["modPhase"]}', '$deviceInfo')";
        } else {
            $sql = "INSERT INTO test VALUES ('$id', '$count', current_timestamp(), '$t_type', ";
            $sql .= "'{$_SESSION['amplitude']}', '{$_SESSION['frequency']}', '{$_SESSION['duration']}', '{$_SESSION['onRamp']}', '{$_SESSION['offRamp']}', '{$_SESSION['blocks']}', '{$_SESSION['delta']}', ";
            $sql .= "'{$_SESSION['nAFC']}', '{$_SESSION['ITI']}', '{$_SESSION['ISI']}', '{$_SESSION['factor']}', '{$_SESSION['reversals']}', ";
            $sql .= "'{$_SESSION['secFactor']}', '{$_SESSION['secReversals']}', '{$_SESSION['threshold']}', '{$_SESSION['algorithm']}', '', '0','$checkFb', NULL, NULL, NULL, '$deviceInfo')";
        }

        $conn->query($sql);*/
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

		/*$_SESSION["amp"] = $_GET['amp'];
		$_SESSION["freq"] = $_GET['freq'];
		$_SESSION["dur"] = $_GET['dur'];
		$_SESSION["onRamp"] = $_GET['onRamp'];
		$_SESSION["offRamp"] = $_GET['offRamp'];
		if ($_GET['type'] == "nmod") {
			$_SESSION["modAmp"] = $_GET["modAmp"];
			$_SESSION["modFreq"] = $_GET["modFreq"];
			$_SESSION["modPhase"] = $_GET["modPhase"];
		}
		$_SESSION["delta"] = $_GET['delta'];
		$_SESSION["nAFC"] = $_GET['nAFC'];
		$_SESSION["ITI"] = $_GET['ITI'];
		$_SESSION["ISI"] = $_GET['ISI'];
		$_SESSION["fact"] = $_GET['fact'];
		$_SESSION["secFact"] = $_GET['secFact'];
		$_SESSION["rev"] = $_GET['rev'];
		$_SESSION["secRev"] = $_GET['secRev'];
		$_SESSION["thr"] = $_GET['threshold'];
		$_SESSION["alg"] = $_GET['alg'];
		$_SESSION["sampleRate"] = $_GET['sampleRate'];*/


/*$id = $_SESSION['idGuestTest'];

$type = "";
if ($_GET['test'] == "freq")
    $type = "PURE_TONE_FREQUENCY";
else if ($_GET['test'] == "amp")
    $type = "PURE_TONE_INTENSITY";
else if ($_GET['test'] == "dur")
    $type = "PURE_TONE_DURATION";
else if ($_GET['test'] == "gap")
    $type = "WHITE_NOISE_GAP";
else if ($_GET['test'] == "ndur")
    $type = "WHITE_NOISE_DURATION";
else if ($_GET['test'] == "nmod")
    $type = "WHITE_NOISE_MODULATION";**/

$_SESSION['testTypeCmp'] = $_GET['test'];

/*$sql = "SELECT Max(Test_count) as count FROM test WHERE Guest_ID='$id'";
    $conn = connectdb();
    $result = $conn->query($sql);
    $row = $result->fetch_assoc();

    //il test corrente è il numero di test già effettuati + 1
    $count = $row['count'] + 1;

    if ($_GET['test'] == "gap" || $_GET['test'] == "ndur") {
        $sql = "INSERT INTO test VALUES ('$id', '$count', current_timestamp(), '$type', ";
        $sql .= "'{$_POST['amplitude']}', NULL, '{$_POST['duration']}', '{$_POST['onRamp']}', '{$_POST['offRamp']}', '{$_POST['blocks']}', '{$_POST['delta']}', ";
        $sql .= "'{$_POST['nAFC']}', '{$_POST['ITI']}', '{$_POST['ISI']}', '{$_POST['factor']}', '{$_POST['reversals']}', ";
        $sql .= "'{$_POST['secFactor']}', '{$_POST['secReversals']}', '{$_POST['threshold']}', '{$_POST['algorithm']}', '', '0','$checkFb', NULL, NULL, NULL, '$deviceInfo')";
    } else if ($_GET['test'] == "nmod") {
        $sql = "INSERT INTO test VALUES ('$id', '$count', current_timestamp(), '$type', ";
        $sql .= "'{$_POST['amplitude']}', NULL, '{$_POST['duration']}', '{$_POST['onRamp']}', '{$_POST['offRamp']}', '{$_POST['blocks']}', '{$_POST['delta']}', ";
        $sql .= "'{$_POST['nAFC']}', '{$_POST['ITI']}', '{$_POST['ISI']}', '{$_POST['factor']}', '{$_POST['reversals']}', ";
        $sql .= "'{$_POST['secFactor']}', '{$_POST['secReversals']}', '{$_POST['threshold']}', '{$_POST['algorithm']}', '', '0','$checkFb', '" . floatval($_POST["modAmplitude"]) . "', '{$_POST["modFrequency"]}', '{$_POST["modPhase"]}', '$deviceInfo')";
    } else {
        $sql = "INSERT INTO test VALUES ('$id', '$count', current_timestamp(), '$type', ";
        $sql .= "'{$_POST['amplitude']}', '{$_POST['frequency']}', '{$_POST['duration']}', '{$_POST['onRamp']}', '{$_POST['offRamp']}', '{$_POST['blocks']}', '{$_POST['delta']}', ";
        $sql .= "'{$_POST['nAFC']}', '{$_POST['ITI']}', '{$_POST['ISI']}', '{$_POST['factor']}', '{$_POST['reversals']}', ";
        $sql .= "'{$_POST['secFactor']}', '{$_POST['secReversals']}', '{$_POST['threshold']}', '{$_POST['algorithm']}', '', '0','$checkFb', NULL, NULL, NULL, '$deviceInfo')";
    }

    $conn->query($sql);*/

//??????????????????
/*if ($checkSave) {
        $sql = "UPDATE account SET fk_guestTest = '$id', fk_testCount = '$count' WHERE username = '{$_SESSION['currentLoggedUsername']}' ";
        $conn->query($sql);
    }*/
header("Location: ../{$_GET['test']}test.php");

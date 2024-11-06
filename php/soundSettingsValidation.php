<?php
/**
 * retrieve the test parameters inserted in soundsettings.php or fetch referral parameters
 */
session_start();

include "config.php";
require_once "dbconnect.php";
require 'error_codes/soundSettingsErrorCodes.php';
include_once "utils.php";


unset($_SESSION['currentBlock']);
/**
 * initialize score and results
 */
$_SESSION['score'] = '';
$_SESSION['geometric_score'] = '';
$_SESSION['results'] = '';
/**
 *contains the test type in compact form
 */
unset($_SESSION['testTypeCmp']);


if (isset($_SESSION['referralTest'])) { //referral present

    //fetch  all the test parameters from DB (referral test saved as a mockup test in the "test" table)
    $sql = "SELECT Type, Amplitude AS amplitude, Frequency AS frequency, Duration AS duration, 
                OnRamp AS onRamp, OffRamp AS offRamp, blocks, Delta AS delta, nAFC AS nAFC, 
                ISI AS ISI, ITI AS ITI, Factor AS factor, Reversal AS reversals, 
                SecFactor AS secFactor, SecReversal AS secReversals, Feedback AS checkFb, 
                Threshold AS threshold, Algorithm AS algorithm, 
                ModAmplitude AS modAmplitude, ModFrequency AS modFrequency, 
                ModPhase AS modPhase
					
			FROM test
					
			WHERE Guest_ID='{$_SESSION['referralTest']['guest']}' AND Test_count='{$_SESSION['referralTest']['count']}'";

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

        $testParameters = initializeTestParameter($row);
        $_SESSION = array_merge($testParameters, $_SESSION);
        

        header("Location: ../{$type}test.php");
        exit;

    } catch (Exception $e) {
        error_log($e, 3, "errors_log.txt");
        header("Location: ../index.php?err=db");
        exit;
    }
}

//this section calls a function to check all the forms inserted
//stored in soundSettinsValidation.php, if no redirect string is returned, it goes on
$redirect = "";
$redirect = checkSSEC();
if ($redirect != "") {
    header($redirect);
    exit;
}

$_SESSION['testTypeCmp'] = $_GET['test'];


$testParameters = initializeTestParameter($_POST);
$_SESSION = array_merge($testParameters, $_SESSION);


header("Location: ../{$_GET['test']}test.php");

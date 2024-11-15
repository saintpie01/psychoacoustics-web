<?php

/**
 * retrieve the test parameters inserted in soundsettings.php or fetch new referral parameters
 */
session_start();

require_once "dbconnect.php";
require 'error_codes/soundSettingsErrorCodes.php';
include_once "utils.php";
include_once "dbCommonFunctions.php";


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

    $refId = $_SESSION['referralTest']['guest'];
    $refCount = $_SESSION['referralTest']['count'];

    try {

        $conn = connectdb();
        $row = getTestParameters($refId, $refCount, $conn);

    } catch (Exception $e) {
        error_log($e, 3, "errors_log.txt");
        header("Location: ../index.php?err=db");
        exit;
    }

    //select the test type to perform
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

    //insert all the test parameters into session to retrieve later
    $_SESSION = array_merge($testParameters, $_SESSION);

    header("Location: ../{$type}test.php");
    exit;
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

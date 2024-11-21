<?php

/**
 * saves the data of the test when all blocks are executed
 */
session_start();

include "config.php";
require_once "db_connect.php";
require_once "helpers/database_functions.php";
include_once "helpers/utils.php";


if ((!(isset($_GET['blocks'])
	&& isset($_GET['score'])
	&& isset($_GET['geometric_score'])
	&& isset($_GET['currentBlock'])))) {

	header("Location: ../index.php?err=2");
	exit;
}

//compose the score of each block
$_SESSION["geometric_score"] .= $_GET['geometric_score'] . ";";
$_SESSION["score"] .= $_GET['score'] . ";";
$_SESSION["results"] .= $_GET['result'];

$_SESSION["blocks"] = $_GET['blocks'];
$_SESSION["currentBlock"] = $_GET['currentBlock'];

//check if all block have been executed, otherwise repeat the test
if ($_GET['currentBlock'] < $_GET['blocks']) {
	header("Location: ../results.php?continue=1");
	exit;
}


//readability
//the score results might have a semicolon at the end of the sting in the DB,
//that does not change anything since we access them with indexes and not with string handling
$score = $_SESSION['score'];
$finalResults = $_SESSION['results'];
$geometricScore = $_SESSION['geometric_score'];

//better not to touch this
if (!$_SESSION["saveData"]) {
	if ($_GET['saveSettings']) {
		header("Location: ../results.php?continue=0&err=1");
		exit;
	} else {
		header("Location: ../results.php?continue=0");
		exit;
	}
}

//initialize some variables needed for test insertion
$id = $_SESSION['idGuestTest'];
$testTypeCmp = $_SESSION['testTypeCmp'];
$_SESSION['sampleRate'] = $_GET['sampleRate'];

$testTypeExt = getExtfromCmpType($testTypeCmp);

try {
	$conn = connectdb();

	//find the number of tests taken by the user
	$count = getLastTestCount($id, $conn);
	//new test count is the number of test taken + 1
	$count++;


	insertTest(
		$id,
		$count,
		null, //this field is only for referral tests
		$testTypeCmp,
		$_SESSION,
		$finalResults,
		$score,
		$geometricScore,
		$conn
	);



	//log usage
	$referrerString = "";
	if (isset($_SESSION['referralTest']))
		$referrerString = " referred by user #{$_SESSION['referralTest']['guest']}";

	logEvent("User #$id completed a $testTypeExt test" . $referrerString);

	header("Location: ../results.php?continue=0");
	exit;
} catch (Exception $e) {
	header("Location: ../index.php?err=db");
	error_log($e, 3, "errors_log.txt");
}
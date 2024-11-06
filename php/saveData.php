<?php

session_start();

include "config.php";
require_once "dbconnect.php";
require_once "dbCommonFunctions.php";
include_once "utils.php";


if ((!(isset($_GET['blocks'])
	&& isset($_GET['score'])
	&& isset($_GET['geometric_score'])
	&& isset($_GET['currentBlock'])))) {

	header("Location: ../index.php?err=2");
	exit;
}

//compose the score of each block
$_SESSION["geometric_score"] .= $_GET['geometric_score'] . ";" ;
$_SESSION["score"] .= $_GET['score'] . ";" ;
$_SESSION["results"] .= $_GET['result'];

$_SESSION["blocks"] = $_GET['blocks'];
$_SESSION["currentBlock"] = $_GET['currentBlock'];

//check if all block have been executed, otherwise repeat the test
if ($_GET['currentBlock'] < $_GET['blocks']) {
	header("Location: ../results.php?continue=1");
	exit;
}

$_SESSION["geometric_score"] = substr($_SESSION["geometric_score"], 0, -1); //removes the last ';'
$_SESSION["score"] = substr($_SESSION["score"], 0, -1);
$finalResults = $_SESSION['results']; //readability


if (!$_SESSION["saveData"]) {
	if ($_GET['saveSettings']) {
		header("Location: ../results.php?continue=0&err=1");
		exit;
	} else {
		header("Location: ../results.php?continue=0");
		exit;
	}
}

//is this check necessary?
if (!isset($_SESSION['idGuestTest'])) {
	header("Location: ../index.php?err=2");
	exit;
}

//initialize some variables needed for test insertion
$id = $_SESSION['idGuestTest'];
$testTypeCmp = $_SESSION['testTypeCmp'];
$_SESSION['sampleRate'] = $_GET['sampleRate'];


try {
	$conn = connectdb();

	//find the number of tests taken by the user
	$sql = "SELECT Max(Test_count) as count 
			FROM test 
			WHERE Guest_ID='$id'";

	$result = $conn->query($sql);
	$row = $result->fetch_assoc();

	//new test count is the number of test taken + 1
	$count = $row['count'] + 1;

	insertTest($id, $count, $testTypeCmp, $_SESSION, $finalResults, $conn);

	header("Location: ../results.php?continue=0");
	exit;

} catch (Exception $e) {
	header("Location: ../index.php?err=db");
	error_log($e, 3, "errors_log.txt");
}

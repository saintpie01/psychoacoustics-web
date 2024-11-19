<?php

/**
 * creates new referral test in userSettings.php
 */
session_start();
include_once "db_connect.php";
include_once "helpers/utils.php";
include_once "helpers/database_functions.php";


$id = $_SESSION['currentLoggedID'];

//takes all the useful test parameters given in POST
$testParameters = initializeTestParameter($_POST);

//name of the referral
$refName = $_GET['refn'];
$testType = $_GET['test'];

try {

	$conn = connectdb();

	//find how many test are associated with to the ID
	$count = getLastTestCount($id, $conn);

	//new test number is test taken + 1
	$count++;

	insertTest(
		$id,
		$count,
		$refName,
		$testType,
		$testParameters,
		null,
		null, //the referral test is just a test without results
		null,
		$conn
	);

	//the referral is identified by the $count number in the account table
	$sql = "UPDATE account 
			SET fk_TestCount = '$count'  
			WHERE Username = '{$_SESSION['currentLoggedUsername']}' ";
	$conn->query($sql);

	unset($_SESSION['creatingNewReferral']);

	logEvent("User #$id created a new referral test");
	header("Location: ../userSettings.php?err=4");

} catch (Exception $e) {
	error_log($e, 3, "errors_log.txt");
	header("Location: ../index.php?err=db");
}

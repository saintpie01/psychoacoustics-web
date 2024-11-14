<?php
session_start();
include "config.php";
include_once "dbconnect.php";
include_once "utils.php";
include_once "dbCommonFunctions.php";

$referralName = $_POST['refn'];
if ($referralName == "" || !isset($referralName)) { //IF NO TEST NAME GIVEN
	header('Location: ../userSettings.php?err=8');
	exit;
}

$testType = $_POST['testType'];
if ($testType == "" || !isset($testType)) { //IF NO TEST TYPE HAS BEEN SELECTED
	header('Location: ../userSettings.php?err=5');
	exit;
}

try {
	$conn = connectdb();

	//check if the referral test already exist
	$sql = "SELECT COUNT(*) 
			FROM test 
			WHERE Ref_name='$referralName' AND Guest_ID = '{$_SESSION['currentLoggedID']}'";
	
	$result = $conn->query($sql);
	$row = $result->fetch_assoc();
	//chech if it already exist
	if ($row['COUNT(*)'] > 0) {
		header('Location: ../userSettings.php?err=7'); //this test name already exist for this user
		exit;
	}

} catch (Exception $e) {
	header('Location: ../index.php?err=db');
	exit;
}


$_SESSION['updatingSavedSettings'] = true;
header("Location: ../soundSettings.php?test=" . $testType . "&refn=" . $referralName);

<?php
/**
 * updates the parameters of the referral test in userSettings.php
 */
session_start();
include "config.php";
include_once "dbconnect.php";
include_once "utils.php";
include_once "dbCommonFunctions.php";


$id = $_SESSION['currentLoggedID'];
$testParameters = initializeTestParameter($_POST);

try {


	$conn = connectdb();

	//find how many test are associated with to the ID
	$sql = "SELECT Max(Test_count) as count 
			FROM test 
			WHERE Guest_ID='$id'";

	$result = $conn->query($sql);
	$row = $result->fetch_assoc();
	
	//new test number is test taken + 1
	$count = $row['count'] + 1;

	insertTest($id, $count, $_GET['test'], $testParameters, '', $conn); //the referral test is just a test without result

	//the referral is identified by the $count number in the account table
	$sql = "UPDATE account 
			SET fk_TestCount = '$count'  
			WHERE Username = '{$_SESSION['currentLoggedUsername']}' ";
	$conn->query($sql);

	unset($_SESSION['updatingSavedSettings']);

	header("Location: ../userSettings.php?err=4");
} catch (Exception $e) {
	error_log($e, 3, "errors_log.txt");
	header("Location: ../index.php?err=db");
}

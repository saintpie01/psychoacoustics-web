<?php
/**
 * Takes usr and psw from login page form, if user is verifed:
 * $_SESSION[usr] takes user Username (unique)
 * $_SESSION[idGuest] takes user Guest_ID (unique)
 */

include_once "dbCommonFunctions.php";
include "config.php";
include_once "dbconnect.php";

ini_set('log_errors', 'On');
ini_set('error_log', 'error.txt');
ini_set('display_errors', 'Off'); // Ensure errors are not displayed in the browser
error_reporting(E_ALL); // Log all types of errors

session_start();

//uses function from dbCommonFunction to verify any injection on POST data
$specialCharacters = checkSpecialCharacter(['usr', 'psw']);
if ($specialCharacters) {
	header("Location: ../login.php?&err=0");
	exit;
}

try {
	//connect to Database
	$conn = connectdb();

	//get usr and password from login form
	$usr = $_POST['usr'];
	$psw = $_POST['psw'];

	//check if the user actually exists
	$sql = "SELECT Guest_ID FROM account WHERE Username='$usr' AND Password=SHA2('$psw', 256)";
	$result = $conn->query($sql);

	if ($result->num_rows <= 0) { //error if no user is found
		header('Location: ../login.php?err=1');
		$conn->close();
		exit;
	}

	$row = $result->fetch_assoc();

	//initialize SESSION variable with logged User data
	//actually usr and guest id are both unique for all users so there is no need to use both (mystery)
	$_SESSION['currentLoggedUsername'] = $usr;
	$_SESSION['currentLoggedID'] = $row['Guest_ID'];

	header('Location: ../index.php');
	$conn->close();

} catch (Exception $e) {
	header("Location: ../index.php?err=db");
	$conn->close();
}

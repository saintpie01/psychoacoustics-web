<?php

/**
 * validate the usr and psw given to log User
 */

session_start();

include_once "dbCommonFunctions.php";
include "config.php";
include_once "dbconnect.php";
include_once "utils.php";


//verify any injection on POST data
$specialCharacters = checkSpecialCharacter(['usr', 'psw']);
if ($specialCharacters) {
	header("Location: ../login.php?&err=0");
	exit;
}

//get usr and password from login form
$usr = $_POST['usr'];
$psw = $_POST['psw'];

try {
	//connect to Database
	$conn = connectdb();

	//check if the user actually exists
	$sql = "SELECT Guest_ID 
			FROM account 
			WHERE Username='$usr' AND Password=SHA2('$psw', 256)";

	$result = $conn->query($sql);

	if ($result->num_rows <= 0) { //error if no user is found
		header('Location: ../login.php?err=1');
		$conn->close();
		exit;
	}

	$row = $result->fetch_assoc();


	$_SESSION['currentLoggedUsername'] = $usr;
	$_SESSION['currentLoggedID'] = $row['Guest_ID'];

	header('Location: ../index.php');
	$conn->close();
	
} catch (Exception $e) {
	header("Location: ../index.php?err=db");
	$conn->close();
}

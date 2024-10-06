<?php
/**
 * updates user password from setting page
 */

include "config.php";
include "dbconnect.php";
include "dbCommonFunctions.php";

session_start();

//uses function from dbCommonFunction to verify any injection on POST data
$specialCharacters = checkSpecialCharacter(['oldPsw', 'newPsw']);
if ($specialCharacters) {
	header("Location: ../userSettings.php?&err=0");
	exit;
}

try {
	//esablish conection with DB
	$conn = connectdb();

	//takes new and old psw received from form
 	$oldPsw = $_POST['oldPsw'];
	$newPsw = $_POST['newPsw'];

	//looking for user with credentials given, takes email
	$sql = "SELECT email FROM account WHERE Username ='" . $_SESSION['currentLoggedUsername'] . "' AND password=SHA2('$oldPsw', 256)";
	$result = $conn->query($sql);

	if ($result->num_rows <= 0) { //error if usr could not be found (no rows in results)
		header('Location: ../userSettings.php?err=2');
		$conn->close();
		exit;
	}

	$row = $result->fetch_assoc(); //<---takes selected attributes
	
	//update password
	$sql = "UPDATE account SET password = SHA2('$newPsw', 256)  WHERE username= '" . $_SESSION['currentLoggedUsername'] . "'";
	$conn->query($sql);

	//send confirmation email to fetched email
	$email = $row['email'];
	mail($email, 'Password changing', 'you have correctly changed the password');
	
	header('Location: ../userSettings.php?err=3'); //this is not an error
	$conn->close();

} catch (Exception $e) {
	header("Location: ../index.php?err=db");
	error_log($e, 3, "error.txt");
}

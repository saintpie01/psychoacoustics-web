<?php

include "config.php";
include "dbconnect.php";
include "dbCommonFunctions.php";

session_start();

$specialCharacters = verifyInjection(['oldPsw', 'newPsw']);
if ($specialCharacters) {
	header("Location: ../userSettings.php?&err=0");
	exit;
}

try {
	$conn = connectdb();

	// query 
	$oldPsw = $_POST['oldPsw'];
	$newPsw = $_POST['newPsw'];

	$sql = "SELECT password , email FROM account WHERE Username ='" . $_SESSION['usr'] . "' AND password=SHA2('$oldPsw', 256)";
	$result = $conn->query($sql);
	$row = $result->fetch_assoc();

	if ($result->num_rows <= 0) {
		header('Location: ../userSettings.php?err=2');
		exit;
	}

	$psw = $row['password'];
	$email = $row['email'];

	$sql = "UPDATE account SET password = SHA2('$newPsw', 256)  WHERE username= '" . $_SESSION['usr'] . "'";
	$conn->query($sql);

	mail($email, 'Password changing', 'you have correctly changed the password');
	header('Location: ../userSettings.php?err=3'); //this is not an error
	$conn->close();

} catch (Exception $e) {
	header("Location: ../index.php?err=db");
}

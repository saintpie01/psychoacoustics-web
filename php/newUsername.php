<?php

include "config.php";
include "dbconnect.php";
include "dbCommonFunctions.php";

session_start();

$specialCharacters = verifyInjection(["name"]);
if ($specialCharacters) {
	header("Location: ../userSettings.php?&err=0");
	exit;
}

try {
	$conn = connectdb();

	//prendo i dati del guest
	$usr = $_SESSION['usr'];
	$id = $_SESSION['idGuest'];

	//controllo di sicurezza
	$sql = "SELECT Type FROM account WHERE Guest_ID='$id' AND Username='$usr'";
	$result = $conn->query($sql);
	$row = $result->fetch_assoc();

	if ($row['Type'] == 1) {
		$sql = "UPDATE account SET Type='1' WHERE Username='" . $_POST['username'] . "'";
		$conn->query($sql);
	}

	header("Location: ../userSettings.php");
	
} catch (Exception $e) {
	header("Location: ../index.php?err=db");
}

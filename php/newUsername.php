<?php
/**
 * function only used by Superusers
 * deprecated??
 */
session_start();

include_once "config.php";
include "db_connect.php";
include "helpers/database_functions.php";

$specialCharacters = checkSpecialCharacter(["name"]);
if ($specialCharacters) {
	header("Location: ../userSettings.php?&err=0");
	exit;
}
	
$usr = $_SESSION['currentLoggedUsername'];
$id = $_SESSION['currentLoggedID'];

try {
	$conn = connectdb();

	
	$sql = "SELECT Type FROM account WHERE Guest_ID='$id' AND Username='$usr'";
	$result = $conn->query($sql);
	$row = $result->fetch_assoc();

	if ($row['Type'] == 1) { //if superuser
		$sql = "UPDATE account SET Type='1' WHERE Username='" . $_POST['username'] . "'";
		$conn->query($sql);
	}

	header("Location: ../userSettings.php");
	
} catch (Exception $e) {
	header("Location: ../index.php?err=db");
}


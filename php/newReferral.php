<?php

include "config.php";
include_once "dbconnect.php";
session_start();

try {

	$conn = connectdb();

	//takes the current referral like of the current logged account
	$sql = "SELECT Referral FROM account WHERE Username='" . $_SESSION['currentLoggedUsername'] . "'";
	$result = $conn->query($sql);
	$row = $result->fetch_assoc();
	$ref = $row['referral'];

	$newRef = base64_encode($_SESSION['currentLoggedUsername'] . rand(-9999, 9999));
	while ($newRef == $ref)
		$newRef = base64_encode($_SESSION['currentLoggedUsername'] . rand(-9999, 9999));

	$sql = "UPDATE account SET Referral='$newRef' WHERE Username='" . $_SESSION['currentLoggedUsername'] . "'";
	$conn->query($sql);

	header("Location: ../userSettings.php");
} catch (Exception $e) {
	header("Location: ../index.php?err=db");
}

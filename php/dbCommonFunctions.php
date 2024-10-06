<?php

include_once "dbconnect.php";

function checkSpecialCharacter($formElements)
{
	$elements = $formElements;
	$characters = ['"', "\\", chr(0)];
	$specialCharacters = false;
	foreach ($elements as $elem) {
		$_POST[$elem] = str_replace("'", "''", $_POST[$elem]);
		foreach ($characters as $char)
			$specialCharacters |= is_numeric(strpos($_POST[$elem], $char));
	}
	return $specialCharacters;
}



function fetchReferralInfo($referral)
{

	$conn = connectdb();

	$refSQL = "SELECT Username, fk_GuestTest, fk_TestCount FROM account WHERE Referral='$referral';";
	$result = $conn->query($refSQL);
	$refrow = $result->fetch_assoc();
	$conn->close();


	if (!isset($refrow['Username'])) { //in case the referral is incorrect and no related username could be found
		throw new Exception("referral not valid");
	}
	return $refrow;
}

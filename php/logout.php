<?php
	session_start();
	Include_once "helpers/utils.php";
	
	logEvent("User #{$_SESSION['currentLoggedID']} logged out");

	unset($_SESSION['currentLoggedUsername']);
	unset($_SESSION['currentLoggedID']);
	

	header("Location: ../index.php");


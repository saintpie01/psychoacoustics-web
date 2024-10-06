<?php
	session_start();

	unset($_SESSION['currentLoggedUsername']);
	unset($_SESSION['currentLoggedID']);
	
	header("Location: ../index.php");
?>

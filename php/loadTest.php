<?php

/**
 *this page make a give test the active one for the logged user
 */
session_start();

include "config.php";
require_once "dbconnect.php";

//i only need the Count, the ID is the logged one
$testCount = $_POST['testCount'];

try {
    $conn = connectdb();
    $sql = "UPDATE account 
            SET fk_TestCount = '$testCount'  
            WHERE Username = '{$_SESSION['currentLoggedUsername']}' ";
    $conn->query($sql);

	header("Location: ../userSettings.php");

} catch (Exception $e) {
    error_log($e, 3, "errors_log.txt");
	header("Location: ../index.php?err=db");
}

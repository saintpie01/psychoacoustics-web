<?php
session_start();

include "config.php";
require_once "dbconnect.php";

///$testId = $_POST['testId'];
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

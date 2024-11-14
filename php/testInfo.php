<?php 
session_start();

include_once "config.php";
require_once "dbconnect.php";
require_once "dbCommonFunctions.php";
include_once "utils.php";

$testId = $_SESSION['currentLoggedID'];
$testCount = $_POST['testCount'];

try {
    $conn = connectdb();

    $row = getTestParameters($testId, $testCount, $conn);
    $_SESSION['testInfoParameters'] = $row;

    header("Location: ../userSettings.php");

} catch (Exception $e) {
    error_log($e, 3, "errors_log.txt");
	header("Location: ../index.php?err=db");
}

?>
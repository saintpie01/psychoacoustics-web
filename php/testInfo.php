<?php 
/**
 * fetch info (parameters) of a given test
 */
session_start();

require_once "dbconnect.php";
require_once "dbCommonFunctions.php";

$testId = $_SESSION['currentLoggedID'];
$testCount = $_POST['testCount'];

try {
    $conn = connectdb();

    $row = getTestParameters($testId, $testCount, $conn);

    //put everythin in a session array
    $_SESSION['testInfoParameters'] = $row;

    header("Location: ../userSettings.php");

} catch (Exception $e) {
    error_log($e, 3, "errors_log.txt");
	header("Location: ../index.php?err=db");
}

?>
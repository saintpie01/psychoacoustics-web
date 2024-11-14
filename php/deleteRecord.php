<?php
session_start();

include "config.php";
require_once "dbconnect.php";

$testId = $_POST['testId'];
$testCount = $_POST['testCount'];
$returnPage = $_SERVER['HTTP_REFERER'];

try{
    $conn = connectdb();
    $sql = "DELETE 
            FROM test 
            WHERE Guest_ID = '{$testId}' AND Test_count = '{$testCount}'";

    $conn->query($sql);

    header("Location: $returnPage");

}catch(Exception $e) {
    error_log($e, 3, "errors_log.txt");
	header("Location: ../index.php?err=db");
}

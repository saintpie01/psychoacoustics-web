<?php

include "config.php";
include_once('dbconnect.php');
include_once('dbCommonFunctions.php');

session_start();

ini_set('log_errors', 'On');
ini_set('error_log', 'error.txt');
ini_set('display_errors', 'Off'); // Ensure errors are not displayed in the browser
error_reporting(E_ALL); // Log all types of errors

unset($_SESSION['idGuestTest']); //se c'erano stati altri guest temporanei, li elimino per evitare collisioni
unset($_SESSION['name']); //se è settato dopo questa pagina, allora è stato creato un nuovo guest
unset($_SESSION['test']); //se è settato dopo questa pagina, allora è stato usato un referral
unset($_SESSION('referralCode'));


//creates concatenation string to quick pass test type later
/*if (isset($_GET["test"]))*/
$type = "test=" . $_GET["test"];


$redirection = "Location: ../soundSettings.php?" . $type;
//check if a referral code in the link is present, create a session variable and change the redirection path
if (isset($_POST["ref"]))
    $_SESSION['referralCode'] = $_POST["ref"];
$redirection = "Location: ../info.php";

if (isset($_SESSION['currentLoggedID'])) //if the user if logged
    $_SESSION['idGuestTest'] = $_SESSION['currentLoggedID'];


$_SESSION["checkSave"] = true;
if (!isset($_POST["checkSave"])) { //if checksave is unchecked, no data need to be saved, skip to next page

    $_SESSION["checkSave"] = false;
    header($redirection);
    exit;
}
//error_log($_POST["checkSave"], 3, "error.txt");

//this section dismiss some special cases where no data manipulation is needed
if (isset($_SESSION['currentLoggedID'])) { //if the user if logged
    header($redirection);
    exit;
}

if ($_POST["name"] == "") { //name is mandatory if not logged and checksave on
    header("Location: ../demographicData.php?" . $type . $ref . "&err=1");
    exit;
}

$_SESSION['name'] = $_POST["name"];


//sql injections handling
$specialCharacters = checkSpecialCharacter(['name', 'surname', 'notes', 'ref']);
$specialCharacters |= (!is_numeric($_POST["age"]) && $_POST["age"] != "");

//if sql injection test fail, return to previous page
if ($specialCharacters) {
    header("Location: ../demographicData.php?" . $type . $ref . "&err=0");
    exit;
}

//i start writing the beginning of the insertion query
//i create a new guest
$sql = "INSERT INTO guest VALUES (NULL, '" . $_POST["name"] . "',";


if ($_POST["surname"] == "") {
    $sql .= "NULL, ";
} else {
    $sql .= "'" . $_POST["surname"] . "', ";
}

if ($_POST["age"] == "") {
    $sql .= "NULL, ";
} else {
    $sql .= "'" . $_POST["age"] . "', ";
}

if (!isset($_POST["gender"])) {
    $sql .= "NULL, ";
} else {
    $sql .= "'" . $_POST["gender"] . "', ";
}

if ($_POST["notes"] == "") {
    $sql .= "NULL, ";
} else {
    $sql .= "'" . $_POST["notes"] . "', ";
}

$sql .= "NULL);SELECT LAST_INSERT_ID() as id;";


try {
    //now i can  connect to db to handle and save the demographic data passed
    $conn = connectdb();


    $conn->multi_query($sql);
    $conn->next_result();
    $result = $conn->store_result();
    $row = $result->fetch_assoc();

    $id = $row['id'];
    $_SESSION['currentLoggedID'] = $id; //take the generated id for future uses
    $_SESSION['idGuestTest'] = $id;

    header($redirection);
    exit;

} catch (Exception $e) {
    error_log($e, 3, "error.txt");
    header("Location: ../index.php?err=db");
}

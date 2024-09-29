<?php

ini_set('log_errors', 'On');
ini_set('error_log', 'error.txt');
ini_set('display_errors', 'Off'); // Ensure errors are not displayed in the browser
error_reporting(E_ALL); // Log all types of errors

include "config.php";
include_once('dbconnect.php');


session_start();

unset($_SESSION['idGuestTest']); //se c'erano stati altri guest temporanei, li elimino per evitare collisioni
unset($_SESSION['name']); //se è settato dopo questa pagina, allora è stato creato un nuovo guest
unset($_SESSION['test']); //se è settato dopo questa pagina, allora è stato usato un referral


//creates concatenation string to quick pass test type later
$type = "test=" . $_GET["test"];


//if checksave box is not ticked i don't need to set up the data for the DB, skip to next page
if (!isset($_POST["checkSave"])) {
    header("Location: ../soundSettings.php?" . $type);
    exit;
}
$_SESSION["checkSave"] = true;



//check if a referral code in the link is present, creates a concatenation string
if (isset($_POST["ref"]))
    $ref = "&ref=" . $_POST["ref"];
else
    $ref = "";




//this section dismiss some special cases where no data manipulation is needed
if (isset($_SESSION['idGuest'])) { //if the user if logged

    //error_log($_SESSION['idGuest'], 3, "error.txt"); // debug printing - ignore
    if ($_POST["ref"] == "") { //no referral is present, go ahead
        //error_log($_POST['ref'], 3, "error.txt"); // debug printing - ignore
        $_SESSION['idGuestTest'] = $_SESSION['idGuest'];
        header("Location: ../soundSettings.php?" . $type);
        exit;
    } else 
        if ($_POST["name"] == "") { //referral is present but no name given (mandatory) return an error
        header("Location: ../demographicData.php?" . $type . $ref . "&err=2");
        exit;
    }
} else
       
    if ($_POST["name"] == "") { //name is mandatory if not logged
        header("Location: ../demographicData.php?" . $type . $ref . "&err=1");
        exit;
}
$_SESSION['name'] = $_POST["name"];

try {
    //sql injections handling
    $elements = ['name', 'surname', 'notes', 'ref'];
    $characters = ['"', "\\", chr(0)];
    $specialCharacters = false;
    foreach ($elements as $elem) {
        $_POST[$elem] = str_replace("'", "''", $_POST[$elem]);
        foreach ($characters as $char)
            $specialCharacters |= is_numeric(strpos($_POST[$elem], $char));
    }
    $specialCharacters |= (!is_numeric($_POST["age"]) && $_POST["age"] != "");

    //if sql injection test fail, return to previous page
    if ($specialCharacters) {
        header("Location: ../demographicData.php?" . $type . $ref . "&err=0");
        exit;
    }


    //now i can  connect to db to handle and save tha passed data
    $conn = connectdb();

    //this block verify if the referral code, if present, is valid
    if ($_POST["ref"] != "") { //referral is present, fetch referrer data

        $_SESSION["ref"] = $_POST["ref"]; //session[ref] is actually never used again...
        $refSQL = "SELECT Username, fk_GuestTest, fk_TestCount FROM account WHERE Referral='{$_POST["ref"]}';";
        $result = $conn->query($refSQL);
        $refrow = $result->fetch_assoc();


        if (!isset($refrow['Username'])) { //in case the referral is incorrect and no related username could be found
            header("Location: ../demographicData.php?&ref=&err=3");
            exit;
        }

        //who wrote this??
        $_SESSION['test'] = array(
            "guest" => $refrow['fk_GuestTest'],
            "count" => $refrow['fk_TestCount']
        );
    }

    //i start writing the beginning of the insertion query
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

    //no user is logged, but name is present: create new guest
    if (!isset($_SESSION['idGuest'])) {


        //no referral
        if ($_POST["ref"] == "") {
            $_SESSION["ref"] = null;
            $sql .= "NULL);SELECT LAST_INSERT_ID() as id;";
            $conn->multi_query($sql);
            $conn->next_result();
            $result = $conn->store_result();
            $row = $result->fetch_assoc();



            $id = $row['id'];
            $_SESSION['idGuest'] = $id; //take the generated id for future uses
            $_SESSION['idGuestTest'] = $id;
            header("Location: ../soundSettings.php?" . $type);
            exit;
        }

        //referral is present, fetch referrer data
        /*$_SESSION["ref"] = $_POST["ref"];
        $refSQL = "SELECT Username, fk_GuestTest, fk_TestCount FROM account WHERE Referral='{$_POST["ref"]}';";
        $result = $conn->query($refSQL);
        $row = $result->fetch_assoc();


        if (!isset($row['Username'])) { //in case the referral is incorrect and no related username could be found
            header("Location: ../demographicData.php?&ref=&err=3");
            exit;
        }*/


        //for whatever reason this variable is an array.
        //takes the test inviter 'GuestTest' and how many test were referred by them
        /*$_SESSION['test'] = array(
            "guest" => $refrow['fk_GuestTest'],
            "count" => $refrow['fk_TestCount']
        );*/


        //insert the referrer name in the DB along with user demographics (fk_guest field)
        $sql .= "'" . $refrow['Username'] . "');SELECT LAST_INSERT_ID() as id;";
        $conn->multi_query($sql);
        $conn->next_result();
        $result = $conn->store_result();
        $row = $result->fetch_assoc();

        $id = $row['id'];
        //error_log($id, 3, "error.txt"); // debug printing - ignore
        $_SESSION['idGuestTest'] = $id;
        if (isset($_SESSION['test'])) {
            header("Location: ../info.php");
            exit;
        }

        // a user is logged
    }

    /*if ($_POST["name"] == "" && $_POST['ref'] == "") { //no referal, no new guest to create, can skip ahead
            $_SESSION['idGuestTest'] = $_SESSION['idGuest'];
            header("Location: ../soundSettings.php?" . $type);*/

    //is this situation even possible??
    /*if ($_POST["name"] != "" && $_POST['ref'] == "") { //log in e nome ma niente referral, va creato un nuovo guest e va collegato all'account che ha fatto il log in
            header("Location: ../demographicData.php?" . $type);*/

    /*if ($_POST["name"] == "" && $_POST['ref'] != "") { //log in e referral ma niente nome, va lanciato un errore (nome obbligatorio col referral)
            header("Location: ../demographicData.php?" . $type . $ref . "&err=2");*/

    //this case should be validated by default ath this point
    /*if ($_POST["name"] != "" && $_POST['ref'] != "") { //log in, referral e nome, va creato un nuovo guest e va collegato all'account del referral
            $_SESSION["name"] = $_POST["name"];*/


    /*$_SESSION["ref"] = $_POST["ref"];

            $refSQL = "SELECT Username FROM account WHERE Referral='{$_SESSION["ref"]}';";
            $result = $conn->query($refSQL);
            $row = $result->fetch_assoc();    // dopo aver fatto la query controllo se il risultato é nullo, se lo é, il referral non é valido

            if (!isset($row['Username'])) {
                header("Location: ../demographicData.php?" . $type . $ref . "&err=3");
                exit;
            }*/


    $sql .= "'" . $refrow['Username'] . "');SELECT LAST_INSERT_ID() as id;";

    $conn->multi_query($sql);
    $conn->next_result();
    $result = $conn->store_result();
    $row = $result->fetch_assoc();

    $id = $row['id'];
    $_SESSION['idGuestTest'] = $id;

    /*$refSQL = "SELECT fk_GuestTest, fk_TestCount FROM account WHERE Referral='{$_SESSION["ref"]}';";
            $result = $conn->query($refSQL);
            $row = $result->fetch_assoc();


            $_SESSION['test'] = array(
                "guest" => $row['fk_GuestTest'],
                "count" => $row['fk_TestCount']
            );*/

    if (isset($_SESSION['test'])){
        header("Location: ../info.php");
        exit;
    }
 


    else
        header("Location: ../soundSettings.php?" . $type);
} catch (Exception $e) {
    error_log($e, 3, "error.txt");
    header("Location: ../index.php?err=db");
}

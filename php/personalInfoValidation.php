<?php

include "config.php";
include_once('dbconnect.php');

session_start();

try {

    unset($_SESSION['idGuestTest']); //se c'erano stati altri guest temporanei, li elimino per evitare collisioni
    unset($_SESSION['name']); //se è settato dopo questa pagina, allora è stato creato un nuovo guest
    unset($_SESSION['test']); //se è settato dopo questa pagina, allora è stato usato un referral


    //creates concatenation string to quick pass test type later
    $type = "test=" . $_GET["test"];

    if ($_POST["name"] == "" && !isset($_SESSION["idGuest"]))  //return back if there is no name inserted and no logged user (impossible situation)
        header("Location: ../demographicData.php?" . $type . $ref . "&err=1");

    //if checksave box is not ticked i don't need to set up the data for the DB, skip to next page
    if (!isset($_POST["checkSave"]))
        header("Location: ../soundSettings.php?" . $type);
    $_SESSION["checkSave"] = true;


    //check if a referral code in the link is present, creates a concatenation string
    if (isset($_POST["ref"]))
        $ref = "&ref=" . $_POST["ref"];
    else
        $ref = "";


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
    if ($specialCharacters)
        header("Location: ../demographicData.php?" . $type . $ref . "&err=0");



    //now i can  connect to db to handle and save tha passed data
    $conn = connectdb();



    //scrivo la query di creazione del guest
    $sql = "INSERT INTO guest VALUES (NULL, '" . $_POST["name"] . "',";
    $_SESSION['name'] = $_POST["name"];

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
    if (!isset($_SESSION["idGuest"])) {


        //no referral
        if ($_POST["ref"] == "") {
            $_SESSION["ref"] = null;
            $sql .= "NULL);SELECT LAST_INSERT_ID() as id;";
            $conn->multi_query($sql);
            $conn->next_result();
            $result = $conn->store_result();
            $row = $result->fetch_assoc();


            //take the generated it for future use
            $id = $row['id'];
            $_SESSION['idGuest'] = $id;
            $_SESSION['idGuestTest'] = $id;
            header("Location: ../soundSettings.php?" . $type);
        }

        //referral is present, fetch referrer data
        $_SESSION["ref"] = $_POST["ref"];
        $refSQL = "SELECT Username, fk_GuestTest, fk_TestCount FROM account WHERE Referral='{$_POST["ref"]}';";
        $result = $conn->query($refSQL);
        $row = $result->fetch_assoc();


        if (!isset($row['Username']))  //in case the referral is incorrect no related username will be found
            header("Location: ../demographicData.php?&ref=&err=3");


        //?????
        $_SESSION['test'] = array(
            "guest" => $row['fk_GuestTest'],
            "count" => $row['fk_TestCount']
        );
        $sql .= "'" . $row['Username'] . "');SELECT LAST_INSERT_ID() as id;";
        $conn->multi_query($sql);
        $conn->next_result();
        $result = $conn->store_result();
        $row = $result->fetch_assoc();

        $id = $row['id'];
        $_SESSION['idGuestTest'] = $id;
        if (isset($_SESSION['test']))
            header("Location: ../info.php");

    // a user is logged
    } else {
        if ($_POST["name"] == "" && $_POST['ref'] == "") { //log in ma niente nome e niente referral, il test va collegato all'account che ha fatto il log in
            $_SESSION['idGuestTest'] = $_SESSION['idGuest'];
            header("Location: ../soundSettings.php?" . $type);
        } else if ($_POST["name"] != "" && $_POST['ref'] == "") { //log in e nome ma niente referral, va creato un nuovo guest e va collegato all'account che ha fatto il log in

            header("Location: ../demographicData.php?" . $type);
        } else if ($_POST["name"] == "" && $_POST['ref'] != "") { //log in e referral ma niente nome, va lanciato un errore (nome obbligatorio col referral)
            header("Location: ../demographicData.php?" . $type . $ref . "&err=2");
        } else if ($_POST["name"] != "" && $_POST['ref'] != "") { //log in, referral e nome, va creato un nuovo guest e va collegato all'account del referral
            $_SESSION["name"] = $_POST["name"];

            $_SESSION["ref"] = $_POST["ref"];

            $refSQL = "SELECT Username FROM account WHERE Referral='{$_SESSION["ref"]}';";
            $result = $conn->query($refSQL);
            $row = $result->fetch_assoc();    // dopo aver fatto la query controllo se il risultato é nullo, se lo é, il referral non é valido

            if (!isset($row['Username']))
                header("Location: ../demographicData.php?" . $type . $ref . "&err=3");


            $sql .= "'" . $row['Username'] . "');SELECT LAST_INSERT_ID() as id;";

            $conn->multi_query($sql);
            $conn->next_result();
            $result = $conn->store_result();
            $row = $result->fetch_assoc();

            $id = $row['id'];
            $_SESSION['idGuestTest'] = $id;

            $refSQL = "SELECT fk_GuestTest, fk_TestCount FROM account WHERE Referral='{$_SESSION["ref"]}';";
            $result = $conn->query($refSQL);
            $row = $result->fetch_assoc();

            $_SESSION['test'] = array(
                "guest" => $row['fk_GuestTest'],
                "count" => $row['fk_TestCount']
            );
            if (isset($_SESSION['test']))
                header("Location: ../info.php");
            else
                header("Location: ../soundSettings.php?" . $type);
        }
    }
} catch (Exception $e) {
    error_log($e, 3, "error.txt");
    header("Location: ../index.php?err=db");
}

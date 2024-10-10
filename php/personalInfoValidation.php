<?php   

session_start();

//include "config.php";
include_once('dbconnect.php');
include_once('dbCommonFunctions.php');

unset($_SESSION['idGuestTest']); //se c'erano stati altri guest temporanei, li elimino per evitare collisioni
unset($_SESSION['name']); //se è settato dopo questa pagina, allora è stato creato un nuovo guest
unset($_SESSION['test']); //se è settato dopo questa pagina, allora è stato usato un referral

//creates concatenation string to quick pass test type later
$type = "";
if (isset($_GET["test"]))
    $type = "test=" . $_GET["test"];

//verify injection on POST data
$specialCharacters = checkSpecialCharacter(['name', 'surname', 'notes', 'ref']);
$specialCharacters |= (!is_numeric($_POST["age"]) && $_POST["age"] != "");
//if sql injection test fail, return to previous page
if ($specialCharacters) {
    header("Location: ../demographicData.php?" . $type . $ref . "&err=0");
    exit;
}

$ref = "";
$redirection = "Location: ../soundSettings.php?" . $type; //redirection string based on the presence of referal code
if (($_POST["ref"]) != "") { //check if a referral code in the link is present and valid, create a session variable and change the redirection path

    try {
        $refrow = fetchReferralInfo($_POST['ref']); //return an array with referral data

    } catch (Exception $e) { //if invalid
        header("Location: ../demographicData.php?" . $type . $ref . "&ref=&err=3");
        exit;
    }

    $_SESSION['test'] = array( //gather referral data
        "guest" => $refrow['fk_GuestTest'],
        "count" => $refrow['fk_TestCount']
    );

    $ref = "&ref=" . $_POST["ref"];
    $redirection = "Location: ../info.php"; //all referral tests get redirected to this page
}

$_SESSION["saveData"] = true;

//this section dismiss some special cases where no data manipulation is needed
if (!isset($_POST["checkSave"])) { //no data needs to be saved, skip ahead
    $_SESSION["saveData"] = false;
    //error_log('referral presen'.$ref, 3, "errors_log.txt");
    header($redirection);
    exit;
}

if (isset($_SESSION['currentLoggedID'])) { //if the user if logged
    $_SESSION['idGuestTest'] = $_SESSION['currentLoggedID']; 

    if ($_POST["ref"] == "") { //no referral is present, go ahead
        header($redirection);
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

//if none of the condition before validate i must create a new Guest
$_SESSION['name'] = $_POST["name"];
// beginning of the insertion query
// create a new guest
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


if (isset($_POST["ref"])) {
    $sql .= "NULL);SELECT LAST_INSERT_ID() as id;"; //no referral
} else {
    $sql .= "'" . $refrow['Username'] . "');SELECT LAST_INSERT_ID() as id;";
}

try {
    $conn = connectdb();
    $conn->multi_query($sql);
    $conn->next_result();
    $result = $conn->store_result();
    $row = $result->fetch_assoc();

    $id = $row['id'];
    //$_SESSION['currentLoggedID'] = $id; //take the generated id for future uses
    $_SESSION['idGuestTest'] = $id;

    header($redirection);

} catch (Exception $e) {
    error_log($e, 3, "errors_log.txt");
    header("Location: ../index.php?err=db");
}

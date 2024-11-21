<?php   
/**
 * insert the demographics of the user in the database
 * if the user is logged no insertion is needed.
 * if a referral is present, it is MANDATORY to create a new Guest
 * 
 */
session_start();

include "config.php";
include_once('db_connect.php');
include_once('helpers/database_functions.php');
include_once "helpers/utils.php";

/*
 * @var int contains the id of the test taker weather it is a guest or a logged user
 */
unset($_SESSION['idGuestTest']); 
/*
 * @var string contains the name of the guest, if it is setted after this page, a new user 
 * has been created
 */
unset($_SESSION['name']); 
/*
 * @var array contains the key of the referral test if present
 */
unset($_SESSION['referralTest']); 

//creates concatenation string to quick pass test type later
$type = "";
if (isset($_GET["test"]))
    $type = "test=" . $_GET["test"];

//takes referral Code if present
if (isset($_POST["ref"]) &&  (($_POST["ref"]) != "")){
    $referralCode = $_POST["ref"];
    $ref = "&ref=" . $referralCode;
}
   

$_SESSION['saveSettings'] = 0; //this is useless but if you remove it the program breaks because it is written wery well and i dont want to go through javascript hell so here it stays

//verify injection on POST data
$specialCharacters = checkSpecialCharacter(['name', 'surname', 'notes', 'ref']);
$specialCharacters |= (!is_numeric($_POST["age"]) && $_POST["age"] != "");
//if sql injection test fail, return to previous page
if ($specialCharacters) {
    header("Location: ../demographicData.php?" . $type . $ref . "&err=0");
    exit;
}

$redirection = "Location: ../soundSettings.php?" . $type; //redirection string based on the presence of referal code

if (($referralCode) != "") { //check if a referral code in the link is present and valid

    try {
        $conn = connectdb();
        $refrow = getReferralKeyFromInviteCode($_POST['ref'], $conn); //return an array with referral data

    } catch (Exception $e) { //if invalid
        header("Location: ../demographicData.php?" . $type . $ref . "&ref=&err=3");
        exit;
    }

    $_SESSION['referralTest'] = array( //gather referral key
        "guest" => $refrow['fk_GuestTest'],
        "count" => $refrow['fk_TestCount']
    );

    error_log('referral username = '.$refrow['Username'] .'\n', 3, "errors_log.txt");

    $redirection = "Location: ../info.php"; //all referral tests get redirected to info.php page
}

if (!isset($_POST["checkSave"])) { //no data to save, no user to create, skip ahead
    $_SESSION["saveData"] = false;
    header($redirection);
    exit;
}
$_SESSION["saveData"] = true;

if (isUserLogged()) { 
    $_SESSION['idGuestTest'] = $_SESSION['currentLoggedID']; 

    if ($referralCode == "") { //no referral is present, go ahead
        header($redirection);
        exit;
    } else 
        if ($_POST["name"] == "") { //referral is present but no name given (mandatory) return an error
        header("Location: ../demographicData.php?" . $type . $ref . "&err=2");
        exit;
    }
} else {
    if ($_POST["name"] == "") { //name is mandatory if not logged
        header("Location: ../demographicData.php?" . $type . $ref . "&err=1");
        exit;
    }
}


//if none of the condition above validate : i must create a new Guest

// beginning of the insertion query
// create a new guest
$sql = "INSERT INTO guest VALUES (NULL, '" . $_POST["name"] . "',";

$sql .= ($_POST["surname"] == "") ? "NULL, " : "'" . $_POST["surname"] . "', ";
$sql .= ($_POST["age"] == "") ? "NULL, " : "'" . $_POST["age"] . "', ";
$sql .= (!isset($_POST["gender"])) ? "NULL, " : "'" . $_POST["gender"] . "', ";
$sql .= ($_POST["notes"] == "") ? "NULL, " : "'" . $_POST["notes"] . "', ";

if (($referralCode) == "") {
    $sql .= "NULL);SELECT LAST_INSERT_ID() as id;"; //no referral
} else {
    $sql .= "'" . $refrow['Username'] . "');SELECT LAST_INSERT_ID() as id;"; //if referral present i must insert the referrer Username
}

try {
    $conn = connectdb();
    $conn->multi_query($sql);
    $conn->next_result();
    $result = $conn->store_result();
    $row = $result->fetch_assoc();

    $_SESSION['idGuestTest'] = $row['id']; //the test take's ID is the new user created
    $_SESSION['name'] = $_POST["name"];

    logEvent("Guest #{$_SESSION['idGuestTest']} created");
    header($redirection);

} catch (Exception $e) {
    error_log($e, 3, "errors_log.txt");
    header("Location: ../index.php?err=db");
}

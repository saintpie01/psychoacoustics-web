<?php

session_start();

include_once "config.php";
include_once "dbconnect.php";
include_once "dbCommonFunctions.php";



//sql injections handling
$specialCharacters = checkSpecialCharacter(['usr', 'psw', 'name', 'surname', 'email', 'notes']);
if ($specialCharacters) {
	header("Location: ../register.php?&err=0");
	exit;
}

try {
	//apro la connessione con il db
	$conn = connectdb();

	//recupero username dal form di registrazione
	$usr = $_POST['usr'];

	//controllo se esiste già
	$sql = "SELECT * FROM account WHERE Username='$usr'";
	$result = $conn->query($sql);
	if ($result->num_rows > 0) {
		header('Location: ../register.php?err=1'); //errore 1: lo definisco come errore di username già esistente
		exit;
	}
	//se non esiste eseguo la registrazione

	//prendo tutti i dati
	$psw = $_POST['psw'];
	$name = $_POST['name'];
	$surname = $_POST['surname'];
	$date = $_POST['date'];
	$gender = strtoupper($_POST['gender']);
	$notes = $_POST['notes'];
	$email = $_POST['email'];  // permetto la creazione di piú utenti username diversi ma con la stessa email 

	//inizio a creare la query inserendo i valori non NULL
	$sql = "INSERT INTO guest (Name";
	$sqlVal = " VALUES ('$name'";

	if ($surname != "") {
		$sql .= ",Surname";
		$sqlVal .= ",'$surname'";
	}

	if ($gender != "NULL") {
		$sql .= ",Gender";
		$sqlVal .= ",'$gender'";
	}

	if ($notes != "") {
		$sql .= ",Notes";
		$sqlVal .= ",'$notes'";
	}

	$sql .= ")";
	$sqlVal .= ");SELECT LAST_INSERT_ID() as id;";

	//creo il guest
	$sql .= $sqlVal;

	$conn->multi_query($sql);
	$conn->next_result();
	$result = $conn->store_result();
	$row = $result->fetch_assoc();
	$id = $row['id'];

	//creo e collego l'account, salvo l'hash della password con sha2-256, tipo di account 0 (base)
	$sql = "INSERT INTO account VALUES ('$usr', SHA2('$psw', 256) ";

	if ($date != "")
		$sql .= ",'$date' ";
	else
		$sql .= ",NULL ";

	$sql .= ",'$id', '0', '" . base64_encode($usr) . "', NULL, NULL, '$email');";
	$conn->query($sql);

	//faccio sapere alle altre pagine quale utente è loggato
	$_SESSION['currentLoggedUsername'] = $usr;
	$_SESSION['currentLoggedID'] = $id;

	$conn->close();

	header('Location: ../index.php');
	
} catch (Exception $e) {
	header("Location: ../index.php?err=db");
}

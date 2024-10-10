<?php

session_start();

include "config.php";
require_once "dbconnect.php";
require_once "dbCommonFunctions.php";


try {
	if (
		/*isset($_POST['result'])/* && isset($_GET['timestamp']) && isset($_GET['type'])
		&& isset($_GET['amp']) && isset($_GET['freq']) && isset($_GET['dur']) && isset($_GET['onRamp']) && isset($_GET['offRamp'])*/  isset($_GET['blocks']) /*&& isset($_GET['delta'])
		&& isset($_GET['nAFC']) && isset($_GET['ITI']) && isset($_GET['ISI']) && isset($_GET['fact']) && isset($_GET['secFact']) && isset($_GET['rev'])
		&& isset($_GET['secRev']) && isset($_GET['threshold']) && isset($_GET['alg'])*/ && isset($_GET['score']) && isset($_GET['geometric_score'])
		&& isset($_GET['saveSettings']) && isset($_GET['currentBlock'])
	) {


		//$result = $_GET['result'];
		$result = $_POST['result'];



		if (isset($_SESSION["score"]))
			$_SESSION["score"] .= ";" . $_GET['score'];
		else
			$_SESSION["score"] = $_GET['score'];

		if (isset($_SESSION["geometric_score"]))
			$_SESSION["geometric_score"] .= ";" . $_GET['geometric_score'];
		else
			$_SESSION["geometric_score"] = $_GET['geometric_score'];

		if (isset($_SESSION["results"]))
			$_SESSION["results"] .= $result;
		else
			$_SESSION["results"] = $result;

		$_SESSION["blocks"] = $_GET['blocks'];
		$_SESSION["currentBlock"] = $_GET['currentBlock'];

		if ($_GET['currentBlock'] < $_GET['blocks']) {
			header("Location: ../results.php?continue=1");
			exit;
		}

		//take user device info
		$deviceInfo = str_replace(";", " ", $_SERVER['HTTP_USER_AGENT']);

		$testTypeCmp = $_SESSION['testTypeCmp'];
		//find test type
		$type = "";
		if ($testTypeCmp == "freq")
			$type = "PURE_TONE_FREQUENCY";
		else if ($testTypeCmp == "amp")
			$type = "PURE_TONE_INTENSITY";
		else if ($testTypeCmp == "dur")
			$type = "PURE_TONE_DURATION";
		else if ($testTypeCmp == "gap")
			$type = "WHITE_NOISE_GAP";
		else if ($testTypeCmp == "ndur")
			$type = "WHITE_NOISE_DURATION";
		else if ($testTypeCmp == "nmod")
			$type = "WHITE_NOISE_MODULATION";

		$_SESSION['testTypeExt'] = $type;





		

		//$_SESSION["time"] = $_GET['timestamp'];
		//$_SESSION["type"] = $type;
		/*$_SESSION["amp"] = $_GET['amp'];
		$_SESSION["freq"] = $_GET['freq'];
		$_SESSION["dur"] = $_GET['dur'];
		$_SESSION["onRamp"] = $_GET['onRamp'];
		$_SESSION["offRamp"] = $_GET['offRamp'];
		if ($_GET['type'] == "nmod") {
			$_SESSION["modAmp"] = $_GET["modAmp"];
			$_SESSION["modFreq"] = $_GET["modFreq"];
			$_SESSION["modPhase"] = $_GET["modPhase"];
		}
		$_SESSION["delta"] = $_GET['delta'];
		$_SESSION["nAFC"] = $_GET['nAFC'];
		$_SESSION["ITI"] = $_GET['ITI'];
		$_SESSION["ISI"] = $_GET['ISI'];
		$_SESSION["fact"] = $_GET['fact'];
		$_SESSION["secFact"] = $_GET['secFact'];
		$_SESSION["rev"] = $_GET['rev'];
		$_SESSION["secRev"] = $_GET['secRev'];
		$_SESSION["thr"] = $_GET['threshold'];
		$_SESSION["alg"] = $_GET['alg'];
		$_SESSION["sampleRate"] = $_GET['sampleRate'];*/

		//apro la connessione con il db
		$conn = connectdb();

		//save the test, if it must be saved
		if ($_SESSION["saveData"]) {
			if (!isset($_SESSION['idGuestTest'])) {
				header("Location: ../index.php?err=2");
				exit;
			}

			//trovo l'id a cui associare il test
			$id = $_SESSION['idGuestTest'];

			//trova il numero di test effettuati fin'ora
			$sql = "SELECT Max(Test_count) as count FROM test WHERE Guest_ID='$id'";
			$result = $conn->query($sql);
			$row = $result->fetch_assoc();

			//il test corrente è il numero di test già effettuati + 1
			$count = $row['count'] + 1;

			//inserisci i dati del nuovo test
			//$sql = "UPDATE test SET Result = '{$_SESSION['results']}', Timestamp='{$_GET['timestamp']}', SampleRate='{$_GET['sampleRate']}' WHERE Guest_ID = '$id' and Test_count = '$count'";
			/* ('$id', '$count', '{$_GET['timestamp']}', '$type', ";
						$sql .= "'{$_GET['amp']}', '{$_GET['freq']}', '{$_GET['dur']}', '{$_GET['ramp']}', '{$_GET['blocks']}', '{$_GET['delta']}', ";
						$sql .= "'{$_GET['nAFC']}', '{$_GET['ITI']}', '{$_GET['ISI']}', '{$_GET['fact']}', '{$_GET['rev']}', ";
						$sql .= "'{$_GET['secFact']}', '{$_GET['secRev']}', '{$_GET['threshold']}', '{$_GET['alg']}', '{$_GET['result']}', '{$_GET['sampleRate']}')";
						*/

			/*if ($_GET['test'] == "gap" || $_GET['test'] == "ndur") {
				$sql = "INSERT INTO test VALUES ('$id', '$count', current_timestamp(), '$type', ";
				$sql .= "'{$_SESSION["amp"]}', NULL, '{$_SESSION["dur"] }', '{$_SESSION["onRamp"]}', '{$_SESSION["offRamp"]}', '{$_SESSION["blocks"]}', '{$_SESSION["delta"]}', ";
				$sql .= "'{$_SESSION["nAFC"]}', '{$_SESSION["ITI"]}', '{$_SESSION["ISI"]}', '{$_SESSION["fact"]}', '{$_SESSION["rev"]}', ";
				$sql .= "'{$_SESSION["secFact"]}', '{$_SESSION["secRev"]}', '{$_SESSION["thr"]}', '{$_SESSION["alg"]}', '{$_SESSION['results']}', '0','$checkFb', NULL, NULL, NULL, '$deviceInfo')";
			} else if ($_GET['test'] == "nmod") {
				$sql = "INSERT INTO test VALUES ('$id', '$count', current_timestamp(), '$type', ";
				$sql .= "'{$_SESSION["amp"]}', NULL, '{$_SESSION["dur"] }', '{$_SESSION["onRamp"]}', '{$_SESSION["offRamp"]}', '{$_SESSION["blocks"]}', '{$_SESSION["delta"]}', ";
				$sql .= "'{$_SESSION["nAFC"]}', '{$_SESSION["ITI"]}', '{$_SESSION["ISI"]}', '{$_SESSION["fact"]}', '{$_SESSION["rev"]}', ";
				$sql .= "'{$_SESSION["secFact"]}', '{$_SESSION["secRev"]}', '{$_SESSION["thr"]}', '{$_SESSION["alg"]}', '{$_SESSION['results']}', '0','$checkFb', '" . floatval($_SESSION["modAmp"]) . "', '{$_SESSION["modFreq"]}', '{$_SESSION["modPhase"]}', '$deviceInfo')";
			} else {
				$sql = "INSERT INTO test VALUES ('$id', '$count', current_timestamp(), '$type', ";
				$sql .= "'{$_SESSION["amp"]}', '{$_SESSION["freq"]}', '{$_SESSION["dur"] }', '{$_SESSION["onRamp"]}', '{$_SESSION["offRamp"]}', '{$_SESSION["blocks"]}', '{$_SESSION["delta"]}', ";
				$sql .= "'{$_SESSION["nAFC"]}', '{$_SESSION["ITI"]}', '{$_SESSION["ISI"]}', '{$_SESSION["fact"]}', '{$_SESSION["rev"]}', ";
				$sql .= "'{$_SESSION["secFact"]}', '{$_SESSION["secRev"]}', '{$_SESSION["thr"]}', '{$_SESSION["alg"]}', '{$_SESSION['results']}', '0','$_SESSION["checkFb"]', NULL, NULL, NULL, '$deviceInfo')";
			}*/


			if ($testTypeCmp == "gap" || $testTypeCmp == "ndur") {
				$sql = "INSERT INTO test VALUES ('$id', '$count', current_timestamp(), '$type', ";
				$sql .= "'{$_SESSION['amplitude']}', NULL, '{$_SESSION['duration']}', '{$_SESSION['onRamp']}', '{$_SESSION['offRamp']}', '{$_SESSION['blocks']}', '{$_SESSION['delta']}', ";
				$sql .= "'{$_SESSION['nAFC']}', '{$_SESSION['ITI']}', '{$_SESSION['ISI']}', '{$_SESSION['factor']}', '{$_SESSION['reversals']}', ";
				$sql .= "'{$_SESSION['secFactor']}', '{$_SESSION['secReversals']}', '{$_SESSION['threshold']}', '{$_SESSION['algorithm']}', '{$_SESSION["results"]}', '0','{$_SESSION["checkFb"]}', NULL, NULL, NULL, '$deviceInfo')";
			} else if ($testTypeCmp == "nmod") {
				$sql = "INSERT INTO test VALUES ('$id', '$count', current_timestamp(), '$type', ";
				$sql .= "'{$_SESSION['amplitude']}', NULL, '{$_SESSION['duration']}', '{$_SESSION['onRamp']}', '{$_SESSION['offRamp']}', '{$_SESSION['blocks']}', '{$_SESSION['delta']}', ";
				$sql .= "'{$_SESSION['nAFC']}', '{$_SESSION['ITI']}', '{$_SESSION['ISI']}', '{$_SESSION['factor']}', '{$_SESSION['reversals']}', ";
				$sql .= "'{$_SESSION['secFactor']}', '{$_SESSION['secReversals']}', '{$_SESSION['threshold']}', '{$_SESSION['algorithm']}', '{$_SESSION["results"]}', '0','{$_SESSION["checkFb"]}', '" . floatval($_SESSION["modAmplitude"]) . "', '{$_SESSION["modFrequency"]}', '{$_SESSION["modPhase"]}', '$deviceInfo')";
			} else {
				$sql = "INSERT INTO test VALUES ('$id', '$count', current_timestamp(), '$type', ";
				$sql .= "'{$_SESSION['amplitude']}', '{$_SESSION['frequency']}', '{$_SESSION['duration']}', '{$_SESSION['onRamp']}', '{$_SESSION['offRamp']}', '{$_SESSION['blocks']}', '{$_SESSION['delta']}', ";
				$sql .= "'{$_SESSION['nAFC']}', '{$_SESSION['ITI']}', '{$_SESSION['ISI']}', '{$_SESSION['factor']}', '{$_SESSION['reversals']}', ";
				$sql .= "'{$_SESSION['secFactor']}', '{$_SESSION['secReversals']}', '{$_SESSION['threshold']}', '{$_SESSION['algorithm']}', '{$_SESSION["results"]}', '0','{$_SESSION["checkFb"]}', NULL, NULL, NULL, '$deviceInfo')";
			}

			$conn->query($sql);

			if($_SESSION['saveSettings']){
				$sql = "UPDATE account SET fk_guestTest = '$id', fk_testCount = '$count' WHERE username = '{$_SESSION['currentLoggedUsername']}' ";
				$conn->query($sql);
			}

		}

		if (!$_SESSION["saveData"] && $_GET['saveSettings']) {
			header("Location: ../results.php?continue=0&err=1");
		} else {
			header("Location: ../results.php?continue=0");
		}
	} else
		header("Location: ../index.php?err=2");
} catch (Exception $e) {
	header("Location: ../index.php?err=db");
	error_log($e, 3, "errors_log.txt");
}

<?php
session_start();

include_once "dbconnect.php";
include_once "utils.php";

//this func should be in a separate file, but it's only used in this page
function writeResults($txt, $firstValues, $results, $score, $geometricScore)
{
	$blockNumber = 1;
	$end = false;
	//results will be ["bl1;tr1;del1;var1;varpos1;but1;cor1;rev1", "bl2;tr2;...", ...]
	for ($i = 0; $i < count($results) - 1; $i++) {

		//take the next trial else the test is finished
		if ($i + 1 < count($results) - 1)
			$arrayResultsNext = explode(";", $results[$i + 1]);
		else
			$end = true;

		fwrite($txt, $firstValues . ";"); //write the first values

		fwrite($txt, $results[$i] . ";"); //write the i result block


		//if the block ends
		if ($end || ($blockNumber != $arrayResultsNext[0])) {
			fwrite($txt, $score[$blockNumber - 1] . ";"); //write the score values
			fwrite($txt, $geometricScore[$blockNumber - 1]);
			$blockNumber++;
		}

		fwrite($txt, "\n"); //next line
	}
}


function writeTest($row, $age, $txt)
{

	//value of the first fixed lines
	$firstValues = $row["guestID"] . ";" . $row["name"] . ";" . $row["surname"] . ";" . $age . ";" . $row["gender"] . ";" . $row["notes"] . ";" . $row["count"] . ";" . $row["type"] . ";"
		. $row["time"] . ";" . $row["sampleRate"] . ";" . $row["deviceInfo"] . ";";
	$firstValues .= $row["amp"] . ";" . $row["freq"] . ";" . $row["dur"] . ";" . $row["onRamp"] . ";" . $row["offRamp"] . ";" . $row["modAmp"] . ";" . $row["modFreq"] . ";" . $row["modPhase"] . ";" . $row["blocks"] . ";" . $row["nafc"] . ";" . $row["isi"] . ";" . $row["iti"] . ";";
	$firstValues .= $row["fact"] . ";" . $row["rev"] . ";" . $row["secfact"] . ";" . $row["secrev"] . ";" . $row["thr"] . ";" . $row["alg"];
	$results = explode(",", $row["results"]);
	$score = explode(";", $row["score"]);
	$geometricScore = explode(";", $row["geometricScore"]);
	writeResults($txt, $firstValues, $results, $score, $geometricScore);
}


try {

	$conn = connectdb();

	$usr = $_SESSION['currentLoggedUsername'];
	$id = $_SESSION['currentLoggedID'];

	//create and open the csv file, unique for every ID
	$path = $id . "yourResults.csv";
	$txt = fopen($path, "w") or die("Unable to open file!");
	fwrite($txt, chr(0xEF) . chr(0xBB) . chr(0xBF)); //utf8 encoding

	//columns names
	$line = "Guest_ID;Name;Surname;Age;Gender;Notes;Test Count;Test Type;Timestamp;Sample Rate;Device Info;Amplitude;Frequency;Duration;Onset Ramp;Offset Ramp;Modulator Amplitude;ModulatorFrequency;Modulator Phase;n. of blocks;";
	$line .= "nAFC;ISI;ITI;First factor;First reversals;Second factor;Second reversals;reversal threshold;algorithm;";
	$line .= "block;trials;delta;variable;Variable Position;Pressed button;correct?;reversals,threshold (arithmetic mean);threshold (geometric mean)\n";

	fwrite($txt, $line);


	if (isset($_GET['all']) && $_GET['all'] == 1) {

		//take the loggeduser's tests
		$sql = "SELECT guest.ID as guestID, guest.Name as name, guest.Surname as surname, guest.Gender as gender, 
				test.Test_count as count, test.Type as type, test.Timestamp as time, test.Amplitude as amp, test.Frequency as freq, test.Duration as dur, test.OnRamp as onRamp, test.OffRamp as offRamp,
				test.ModAmplitude as modAmp, test.ModFrequency as modFreq, test.ModPhase as modPhase,
				test.SampleRate as sampleRate, test.blocks as blocks, test.nAFC as nafc, test.ISI as isi, test.ITI as iti, test.Factor as fact, test.Reversal as rev, 
				test.SecFactor as secfact, test.SecReversal as secrev, test.Threshold as thr, test.Algorithm as alg, test.Result as results, test.DeviceInfo as deviceInfo,
				account.date as date, guest.Notes as notes, test.Score as score, test.GeometricScore as geometricScore

                FROM account 
                INNER JOIN guest ON account.Guest_ID=guest.ID
                INNER JOIN test ON guest.ID=test.Guest_ID

                WHERE guest.ID='$id' AND test.result <> ''"; //i only want real, completed tests


		$result = $conn->query($sql);

		//iterate through all tests
		while ($row = $result->fetch_assoc()) {

			// Create DateTime objects
			$birthDate = new DateTime($row['date']); 
			$currentDate = new DateTime();        

			// Calculate the difference
			$age = $birthDate->diff($currentDate)->y;

			writeTest($row, $age, $txt);
		}
	} else {
		//data of user's guest
		$sql = "SELECT guest.ID as guestID, guest.Name as name, guest.Surname as surname, guest.Age as age, guest.Gender as gender, guest.Notes as notes,
				test.Test_count as count, test.Type as type, test.Timestamp as time, test.Amplitude as amp, test.Frequency as freq, test.Duration as dur, test.OnRamp as onRamp, test.OffRamp as offRamp,
				test.ModAmplitude as modAmp, test.ModFrequency as modFreq, test.ModPhase as modPhase,
				test.SampleRate as sampleRate, test.blocks as blocks, test.nAFC as nafc, test.ISI as isi, test.ITI as iti, test.Factor as fact, test.Reversal as rev,
				test.SecFactor as secfact, test.SecReversal as secrev, test.Threshold as thr, test.Algorithm as alg, test.Result as results, test.DeviceInfo as deviceInfo, guest.Notes as notes

				FROM guest
				INNER JOIN test ON guest.ID=test.Guest_ID

				WHERE guest.fk_guest='$usr'";

		$result = $conn->query($sql);

		while ($row = $result->fetch_assoc()) {
			$age = $row['age'];

			writeTest($row, $age, $txt);
		}
	}


	fclose($txt);
	ob_clean();

	header('Content-Description: File Transfer');
	header('Content-Disposition: attachment; filename=' . basename($path));
	header('Expires: 0');
	header('Cache-Control: must-revalidate');
	header('Pragma: public');
	header('Content-Length: ' . filesize($path));
	header("Content-Type: text/plain");
	readfile($path);

	unlink($path); //elimino il file dal server
} catch (Exception $e) {
	header("Location: ../index.php?err=db");
}

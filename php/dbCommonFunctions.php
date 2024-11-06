<?php
/**
 *this file contains all the functions where a database interaction is required
 */
include_once "dbconnect.php";


/**
 * retrieve the key of the referral test based on the referral string passed
 * @param string $referral contains the referral string
 * @param \mysqli $conn connection to the database
 * @return array $refrow contains the test key  (fk_GuestTest, fk_TestCount)
 */
function fetchReferralInfo($referral, $conn)
{

	$refSQL = "SELECT 
					Username, 
					fk_GuestTest, 
					fk_TestCount 
			   FROM 
			   		account 
			   WHERE
			   		Referral='$referral'";

	$result = $conn->query($refSQL);
	$refrow = $result->fetch_assoc();

	if (!isset($refrow['Username'])) { //in case the referral is incorrect and no related username could be found
		throw new Exception("referral not valid");
	}
	return $refrow;
}

//ignore
function selectFromTable($columns, $table, $conditions, $conn)
{

	// Prepare the columns part
	$columnsList = implode(", ", $columns);

	// Prepare the conditions part (conditions are passed as strings directly)
	$conditionString = implode(" AND ", $conditions);

	// Build the SQL query
	$sql = "SELECT $columnsList FROM $table WHERE $conditionString";

	// Execute the query
	return $conn->query($sql);
}


/**
 * insert a new test based on given data
 * this insertion works with all the test tipology, since all possible null value are checked
 * maybe is not a super cool function but it does the job
 * 
 * @param int $id user id
 * @param int $count number of the test take by the $id, dont ask why the key is composed like this
 * @param string $testTypeCmp contains the tipology of the test in compact form
 * @param array $param contains all the parameter of the test
 * @param string $result contains the string with the result 
 * @param \mysqli $conn contains connnection with the database
 */
function insertTest($id, $count, $testTypeCmp, $param, $result, $conn)
{


	$type = getExtfromCmpType($testTypeCmp);
	$deviceInfo = str_replace(";", " ", $_SERVER['HTTP_USER_AGENT']);	//take user device info

	//depending on the type of test is going to be saved, these parameters
	//might not be setted, in that case, i set a corrisponding variable to null for db insertion
	$frequency = isset($param['frequency']) ? $param['frequency'] : "NULL";
	$modAmplitude = isset($param['modAmplitude']) ? $param['modAmplitude'] : "NULL";
	$modFrequency = isset($param['modFrequency']) ? $param['modFrequency'] : "NULL";
	$modPhase = isset($param['modPhase']) ? $param['modPhase'] : "NULL";
	$sampleRate = isset($param['sampleRate']) ? $param['sampleRate'] : "0";
	$algorithm = (string) $param['algorithm']; //this need to be a string




	$values = [
		$id,                                  // Guest_ID
		$count,                               // Test_count
		"current_timestamp()",                // Timestamp
		"'$type'",                            // Type
		$param['amplitude'],                  // Amplitude
		$frequency,                           // Frequency
		$param['duration'],                   // Duration
		$param['onRamp'],                     // OnRamp
		$param['offRamp'],                    // OffRamp
		$param['blocks'],                     // Blocks
		$param['delta'],                      // Delta
		$param['nAFC'],                       // nAFC
		$param['ITI'],                        // ITI
		$param['ISI'],                        // ISI
		$param['factor'],                     // Factor
		$param['reversals'],                  // Reversal
		$param['secFactor'],                  // SecFactor
		$param['secReversals'],               // SecReversal
		$param['threshold'],                  // Threshold
		"'$algorithm'",                  	  // Algorithm
		"'$result'",                          // Result
		$sampleRate,                          // SampleRate
		$param['checkFb'],                    // Feedback
		$modAmplitude,                        // ModAmplitude
		$modFrequency,                        // ModFrequency
		$modPhase,                            // ModPhase
		"'$deviceInfo'"                       // DeviceInfo
	];
	
	// Join all values with commas and wrap in parentheses
	$sql = "INSERT INTO test VALUES (" . implode(", ", $values) . ");";

	$conn->query($sql);
}

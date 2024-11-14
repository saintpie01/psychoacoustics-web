<?php
/**
 * this file contains all the common functions where no DB interaction in required
 */

/**
 * this function sanitize the post data based on the names passed in the array
 * 
 * @param array $formElements Array containin the name of the post variables to sanitize
 * @return bool return true if the variables are ok to insert in sql
 */
function checkSpecialCharacter($formElements)
{
	$elements = $formElements;
	$characters = ['"', "\\", chr(0)];
	$specialCharacters = false;
	foreach ($elements as $elem) {
		$_POST[$elem] = str_replace("'", "''", $_POST[$elem]);
		foreach ($characters as $char)
			$specialCharacters |= is_numeric(strpos($_POST[$elem], $char));
	}
	return $specialCharacters;
}


/**
 * convert compact test type to extended test type
 * @param string $testTypeCmp contains compact string test type
 * @return string contains string containin extended test type
 */
function getExtfromCmpType($testTypeCmp){

    switch ($testTypeCmp) {
        case "freq":
            return "PURE_TONE_FREQUENCY";
            break;
        case "amp":
            return "PURE_TONE_INTENSITY";
            break;
        case "dur":
            return "PURE_TONE_DURATION";
            break;
        case "gap":
            return "WHITE_NOISE_GAP";
            break;
        case "ndur":
            return "WHITE_NOISE_DURATION";
            break;
        case "nmod":
            return "WHITE_NOISE_MODULATION";
            break;
        default:
            return null;
            break;
    }
}

/**
 * initialize an array with all the test parameters required
 * this function may seems uselsess, but it extract all the variable nedded from overcrowded arrays like
 * $_POST or $_SESSION to create an ordinated and restricted array of parameter.
 * 
 * Notice how this function retrieve all the parameters, even those that might not be initialized 
 * based on the test selected, setting them to null
 * 
 * @param array $rawParameters contains an array with miscellaneous extracted from a test
 * @return array $newParam contains all and only the parametes needed to perform and save the test
 */
function initializeTestParameter($rawParameters) {
    
    $newParam = [];

    $newParam["amplitude"] = $rawParameters["amplitude"];
    $newParam["frequency"] = $rawParameters["frequency"] ?? NULL;
    $newParam["duration"] = $rawParameters["duration"];
    $newParam["onRamp"] = $rawParameters["onRamp"];
    $newParam["offRamp"] = $rawParameters["offRamp"];
    $newParam["modAmplitude"] = $rawParameters["modAmplitude"] ?? NULL;
    $newParam["modFrequency"] = $rawParameters["modFrequency"] ?? NULL;
    $newParam["modPhase"] = $rawParameters["modPhase"] ?? NULL;
    $newParam["blocks"] = $rawParameters["blocks"];
    $newParam["nAFC"] = $rawParameters["nAFC"];
    $newParam["ITI"] = $rawParameters["ITI"];
    $newParam["ISI"] = $rawParameters["ISI"];
    $newParam["delta"] = $rawParameters["delta"];
    $newParam["checkFb"] = $rawParameters["checkFb"] ?? 0;
    $newParam["factor"] = $rawParameters["factor"];
    $newParam["secFactor"] = $rawParameters["secFactor"];
    $newParam["reversals"] = $rawParameters["reversals"];
    $newParam["secReversals"] = $rawParameters["secReversals"];
    $newParam["threshold"] = $rawParameters["threshold"];
    $newParam["algorithm"] = $rawParameters["algorithm"];

    return $newParam;

}

/*
 * self explaining
 */
function isUserLogged(){
    if (isset($_SESSION["currentLoggedID"]) && isset($_SESSION["currentLoggedUsername"]))
        return true;
    return false;
}



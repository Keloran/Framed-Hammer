<?php
#http://www.csgnetwork.com/phonenumcvtrev.html
define("ADMIN_ERROR", 23646);
define("CACHE_ERROR", 22243);
define("CHARTS_ERROR", 242787);
define("CURL_ERROR", 2875);
define("DATABASE_ERROR", 32822273);
define("EMAIL_ERROR", 36245);
define("ENCRYPTION_ERROR", 3627978466);
define("FOOTER_ERROR", 366837);
define("FORM_ERROR", 3676);
define("FORUM_ERROR", 36786);
define("GALLERY_ERROR", 4255379);
define("GOOGLE_ERROR", 466453);
define("HEAD_ERROR", 4323);
define("IMAGE_ERROR", 46243);


/**
 * printRead()
 *
 * @param mixed $mString
 * @param mixed $mOptions
 * @param string $cFireLevel this is the level at which we give to FirePHP
 * @return mixed
 */
function printRead($mString, $mOptions = null, $cFireLevel = null) {
	//there is nothing here, so why continue processing
	if (!$mString) { return null; }

	//options that are set later
	$bReturn	= false;
	$bColor		= false;
	$bConsole	= false;
	$bEmail		= false;
	$bColored	= false;
	$bFirePHP	= false;
	$bScreen	= true;
	$cName		= false;
	$bFile		= false;

	//are hte options an array
	if (is_array($mOptions)) {
		for ($i = 0; $i < count($mOptions); $i++) {
			//Since this can be anything
			switch ($mOptions[$i]) {
				case "email":
					$bEmail	= true;
					break;

				case "console":
					$bConsole	= true;
					break;

				case "return":
				case "ret":
					$bReturn	= true;
					break;

				case "color":
					$bColor	= true;
					break;

				case "firephp":
					$bFirePHP	= true;
					break;

				case "noscreen":
					$bScreen 	= false;
					break;

				case "file":
					$bFile		= true;
					break;

				default:
					$cName 	= $mOptions[$i];
					break;
			}
		}
	} else { //options are just a string
		//Since this can be anything
		switch ($mOptions) {
			case "email":
				$bEmail	= true;
				break;

			case "console":
				$bConsole	= true;
				break;

			case "return":
			case "ret":
				$bReturn	= true;
				break;

			case "color":
				$bColor		= true;
				break;

			case "firephp":
				$bFirePHP	= true;
				break;

			case "noscreen":
				$bScreen	= false;
				break;

			case "file":
				$bFile		= true;
				break;

			default:
				$cName	= $mOptions;
				break;
		}
	}


	//get the output reader object
	$oReader = new oReader($mString);

	if ($bEmail) {		$oReader->bEmail	= true; }
	if ($bConsole) {	$oReader->bConsole	= true; }
	if ($bColor) {		$oReader->bColor	= true; }
	if ($bReturn) {		$oReader->bReturn	= true; }
	if ($bFirePHP) {	$oReader->bFirePHP	= true; }
	if ($cName) {		$oReader->cName		= $cName; }
	if (!$bScreen) {	$oReader->bScreen	= false; }
	if ($bFile) {		$oReader->bFile		= true; }

	//is there a fire level given
	if ($cFireLevel) { $oReader->cLevel = $cFireLevel; }

	if (!$bReturn) {
		echo $oReader->doOutput();
	} else {
		return $oReader->doOutput();
	}
}

/**
 * Set the error handler, so that if anything happens it throws an exception,
 * and informs the admin, instead of spitting out rubbish to the user
 */
function errorHandler($errno, $errstr, $errfile, $errline) {
	//clear the buffers
	while (ob_get_level()) { ob_end_clean(); }
	$oError		= false;

	$cError	 = $errstr . "<br />";
	$cError	.= "In File: " . $errfile . "<br />";
	$cError .= "On Line: " . $errline . "<br />";
	$cError .= "Error No: " . $errno;

	//now go through and actually show the error
	switch ($errno) {
		case E_USER_ERROR:
		case E_ERROR:
		case E_COMPILE_ERROR:
		case E_CORE_ERROR:
			$cError	= "Error: " . $cError;
			break;

		case E_USER_WARNING:
		case E_WARNING:
		case E_COMPILE_WARNING:
		case E_CORE_WARNING:
		case E_RECOVERABLE_ERROR:
			$cError	= "Warning: " . $cError;
			break;

		case E_USER_NOTICE:
		case E_NOTICE:
		case E_DEPRECATED:
			$cError	= "Notice: " . $cError;
			break;

		case E_PARSE:
		case E_STRICT:
			$cError	= "Parse Error: " . $cError;
			break;

		default:
			printRead(array($errstr, $errno, $errfile, $errline), array("Something went very wrong", "die"));
			break;
	}

	//skip unfixables
	if (strstr($cError, "Unknown on line 0")) { return false; }

	//create the exception object
	$oError	= new Spanner($cError, $errno);

	//print a nice error
	if (is_object($oError)) { $oError->showNiceMessage(false, true); }

	return false;
}

/**
 * exceptionHandler()
 *
 * @param object $oException
 * @return null
 */
function exceptionHandler($oException) {
	$iError	= $oException->getCode();
	$cError	= $oException->getMessage();

	//now throw it to my handler
	if (class_exists("Spanner")) {
		if (!$iError) { $iError = 1234567890; } //completlly out there
		if (!$cError) { $cError = "Unknown"; }

		//see if i can thrown spanner
		try {
			new Spanner($cError, $iError);
		} catch(Exception $oError) {
			//clean teh buffers
			while (ob_get_level()) { ob_end_clean(); }

			//wow something went really wrong
			printRead($oError, "Exception happened and spanner callable, but normal exception thrown");
		}
	} else {
		printRead($oException, "Exception happened and spanner not callbable");
	}

	//make sure they are defentlly all gone
	while (ob_get_level()) { ob_end_clean(); }

	printRead($oException, "Something has gone very wrong if we have thrown an exception this far down");

	//since die doesnt work
	return false;
}

/**
 * assertionHandler()
 *
 * @param string $cFile
 * @param int $iLine
 * @param string $cCode
 * @return null
 */
function assertionHandler($cFile, $iLine, $cCode) {
	throw new Spanner("Assertion Failed - Code[ " . $cCode . " ] - Line[ " . $iLine . "] - File[ " . $cFile . " ]");
}
<?php
include_once "screws.php"; //include the autoloader
include_once "hammer.fn.php"; //to include the standard hammer functons

/**
 * Spanner
 *
 * @package
 * @author Keloran
 * @copyright Copyright (c) 2008
 * @version $Id: spanner.php 506 2010-01-07 15:53:59Z keloran $
 * @access public
 */
class Spanner extends Exception {
	private $mContact	= false;
	private $cMessage	= false;
	private $cAddress	= false;

	/**
	 * Error::__construct()
	 *
	 * @param string $cMessage
	 * @param int $iErrNo
	 * @param string $cFile
	 * @param int $iLine
	 */
	public function __construct($cMessage = null, $iErrNo = null, $oError = null) {
		parent::__construct($cMessage, $iErrNo);
		if ($iErrNo)	{ $this->code = $iErrNo; }

		//previous
		if ($oError) {
			$this->cPrevious = get_class($oError);
		} else {
			$this->cPrevious = get_class($this);
		}

		$this->cMessage = $cMessage;

		//get the config
		if (function_exists("Config")) {
			$this->mContact = Config("contact");

			$aHead		= Config("head");
			$this->cAddress	= $aHead['address'];
		} else {
			include_once("hammer.php");
			$this->mContact	= Hammer::getConfigStat("email");
			$this->cAddress	= Hammer::getConfigStat("address");
		}

		//Since we now need to actually do something with this
		$this->catchIt();
	}

	/**
	 * Spanner::catchIt()
	 *
	 * @return null
	 */
	public function catchIt() {
		$cReturn	= false;
		$bNice		= false;

		//Since this dictates what happens
		switch($this->code) {
			case 1:
				$this->showSpecificMessage($this->cMessage); //this is also needed becasue im forcing the failure
				$bNice = false;
				break;

			case 2:
				if (strstr($this->cMessage, "Too many")) {
					$this->showSpecificMessage("We are having scaling issues, please try again in a few hours");
					$bNice = false;
					break;
				}

			//this can spam the system
			case 8:
			case 200:
			case 100:
				$bNice = false;
				$this->saveMessage($this->code);
				break;

			case 1001:
				$this->showNiceMessage(true);
				break;

			default:
				if (defined("TERM")) { //Terminal
					$cReturn = $this->showMessage(true); //This is still needed to solve the terminal problem
					$bNice = false;
				} else if (defined("DEV")) { //Development
					if ($this->mContact) {
						$this->sendMessage($this->code);
						$cReturn = $this->showMessage(false);
					} else {
						$cReturn = $this->showMessage(false);
					}
				} else { //Live
					$bBugs = false;
					if (defined("bugs")) { $bBugs = true; }
					if (defined("Bugs")) { $bBugs = true; }
					if (defined("BUGS")) { $bBugs = true; }

					if ($bBugs && function_exists("mail")) { //you want to send to the bug reporter and you have mail working
						$this->sendMessage($this->code, true);
						$bNice = true;
					} else { //nothing so show me
						$cReturn = $this->showMessage(true); //show the real message
					}
				}
				break;
		} // switch

		//nice message
		if ($bNice) { $cReturn = $this->showNiceMessage(); }

		echo $cReturn;
	}

	/**
	 * Error::setMessage()
	 *
	 * @param string $message
	 * @return
	 */
	public function setMessage($cMessage) {
		$this->message 	= $cMessage;
		$this->cMessage	= $cMessage;
	}

	/**
	 * Error::sendMessage()
	 *
	 * @desc This sends the message, to the user defined in the config
	 * @param int iErrNo Error number
	 * @param bool dicate the if to send to bugs
	 * @return
	 */
	private function sendMessage($iErrNo, $bBugs = false) {
		$cTitle		= "An error occoured in " . $this->cAddress . ":" . $iErrNo;
		$cFrom		= "error@" . $this->cAddress;
		$cMessage	= $this->showMessage();
		$mContact	= $this->mContact;

		//I really dont want to send this to bug tracker
		$bNoSend	= isset($_GET['nobugs']) ? $_GET['nobugs'] : false;

		if (!$bNoSend) {
			$oSend = new Email_Send();

			if ($bBugs) {
				$aSend = array(
					"to"		=> "bugs@bugfixs.com",
					"title"		=> $cTitle,
					"html"		=> $cMessage,
					"text"		=> $cMessage,
					"from"		=> $cFrom,
				);
				$oSend->compose($aSend);
			} else {

				if (is_array($mContact)) {
					$iContacts = count($mContact);
					foreach ($mContact AS $contact) {
						$cTo        = addslashes($contact);
						$aSend		= array(
							"to"		=> $cTo,
							"title"		=> $cTitle,
							"html"		=> $cMessage,
							"text"		=> $cMessage,
							"from"		=> $cFrom,
						);
						$oSend->compose($aSend);
					}
				} else {
					$cTo 	= addslashes($mContact);
					$aSend 	= array(
						"to"	=> $cTo,
						"title"	=> $cTitle,
						"html"	=> $cMessage,
						"text"	=> $cMessage,
						"from"	=> $cFrom,
					);
					$oSend->compose($aSend);
				}
			}
		}
	}

	/**
	 * Spanner::saveMessage()
	 *
	 * @param int $iErrNo
	 * @return null
	 */
	private function saveMessage($iErrNo) {
		$cMessage = $this->showMessage();
		file_put_contents("/tmp/" . time() . "_error.message", $cMessage);
	}

	/**
	 * Error::showNiceMessage()
	 *
	 * @desc this is the message the end users see
	 * @param bool $bHeader This turns off the header of 404 being sent
	 * @return
	 */
	public function showNiceMessage($bOffline = false, $bSyntax = false) {
		$cMessage	 = "<section id=\"exceptiond\">\n";
		$cMessage	.= "<header>A Problem happened</header>\n";
		$cMessage	.= "<article>\n";
		$cMessage	.= "<p>A problem happened, please try again in '30 Minutes'</p>\n";
		$cMessage	.= "<p>Site Admin has been informed</p>\n";
		$cMessage	.= "<p>You will be redirected to the homepage</p>\n";
		$cMessage	.= "<p><a href=\"#\" onclick=\"history.go(-1)\">Back</a></p>\n";
		$cMessage	.= "</article>\n";
		$cMessage	.= "</section>\n";

		//clear the buffers
		while(ob_get_level()) { ob_end_clean(); }

		//get the site address
		$cSite = "Unknown Hammer Site";
		if (isset($_SERVER['HTTP_HOST'])) {
			$cAddy = $_SERVER['HTTP_HOST'];
			if (substr($cAddy, 0, 4) == "www.") { $cAddy = substr($cAddy, 4); }
			$cSite = $cAddy;
		}

		//not a syntax error
		if (!$bSyntax) {
			//now put the offline page
			ob_start();
				header("HTTP/1.1 404 File not Found");

				//is stuff offline, e.g. database, it also redirects to homepage for normal stuffs,
				//hopefulyl wont go into infinite loop
				if ($bOffline) {
					header("refresh: 5; url=http://hammer.develbox.info/offline.html?site=" . $cSite);
				} else {
					header("refresh 5; url=/");
				}

				echo $cMessage;
			ob_end_flush();
			die();
		} else {
			$cMessage  = "<section id=\"exceptiond\">\n";
			$cMessage .= "<header>An Error Happened</header>\n";
			$cMessage .= "<article>\n";
			$cMessage .= $this->showSpecificMessage(true);
			$cMessage .= "</article>\n";
			$cMessage .= "</section>\n";

			//now make the style for it
			$cMessage .= "<style>\n";
			$cMessage .= "body { width: 90%; }\n";
			$cMessage .= "section { width: 50%; margin: 2% 10% 2% 25%; background: #000000; color: #FFFFFF; border: thin solid #FFFFFF; }\n";
			$cMessage .= "header { width: 99%; text-align: center; font-weight: bold; }\n";
			$cMessage .= "article { width: 99%; padding: 5%; }\n";
			$cMessage .= "</style>\n";

			echo $cMessage;
			die();
		}
	}

	/**
	 * Error::showSpecificMessage()
	 *
	 * @return
	 */
	private function showSpecificMessage($bReturn = false) {
		$cMessage = $this->getMessage();

		//return the error
		if ($bReturn) { return $cMessage; }

		//no return given so echo it
		echo $cMessage;
	}

	/**
	 * Error::showMessage()
	 *
	 * @desc this is the message that gets sent to the developer
	 * @return
	 */
	private function showMessage($bConsole = false) {
		$cMessage	 = "<section id=\"exceptiond\">\n";
		$cMessage	.= "<p>An exception happened in <br />" . $this->getFile() . ".</p>\n";
		$cMessage	.= "<p>On line <br />" . $this->getLine() . ".</p>\n";
		$cMessage	.= "<p>The whole message is <br />" . $this->getMessage() . "</p>\n";
		$cMessage	.= "<p>The exception code is <br />" . $this->getCode() . "</p>\n";
		$cMessage	.= "<p>A Trace is <br />" . nl2br($this->getTraceAsString()) . "</p>\n";
		$cMessage	.= "<p>A Printed trace is <br />" . nl2br(print_r($this->getTrace(), true)) . "</p>\n";

		//5.2 specific
		if (function_exists("error_get_last")) { 		$cMessage .= "<p>Last Error array <br />" . nl2br(print_r(error_get_last(), true)) . "</p>\n"; }
		if (function_exists("memory_get_peak_usage")) { $cMessage .= "<p>Memory Usage<br />" . nl2br(print_r(memory_get_peak_usage(), true)) . "</p>\n"; }

		$cMessage .= "<p>Function Arguments <br />" . nl2br(print_r(func_get_args(), true)) . "</p>\n";

		//Since not all systems have the remote addr, e.g. googlebot
		$cVisitor = visitorIP();
		if ($cVisitor) { $cMessage .= "<p>It was triggered by " . $cVisitor. "</p>\n"; }

		//class previous
		if (isset($this->cPrevious)) { $cMessage .= "<p>Previous: " . $this->cPrevious . "</p>\n"; }

	    $cMessage .= "<p>Full REQUEST is . " . print_r($_REQUEST, true) . "</p>\n";
		$cMessage .= "<p>Full GET is " . print_r($_GET, true) . "</p>\n";
		$cMessage .= "<p>Full POST is " . print_r($_POST, true) . "</p>\n";

		if (isset($_FILES)) { $cMessage .= "<p>Full FILES is " . print_r($_FILES, true) . "</p>\n"; }
		$cMessage .= "<p>Full SERVER is " . print_r($_SERVER, true) . "</p>\n";

	    if (isset($_SERVER['HTTP_REFERER'])) {
			$cMessage .= "<p>Referer: " . print_r($_SERVER['HTTP_REFERER'], true) . "</p>\n";
		} else {
			$cMessage .= "<p>Direct link, god knows where they came from</p>\n";
        }

        //put the date
        $cMessage	.= "<p>Date: " . date("d/m/Y H:i", time()) . "</p>\n";
		$cMessage	.= "</section>\n";

		//remove the brs and replace with newlines
		if ($bConsole) {
			$cMessage = str_replace("<br />", "\n", $cMessage);
			$cMessage = str_replace("\n\n", "\n", $cMessage);
		}

		return $cMessage;
	}

	/**
	 * Spanner::getMessages()
	 *
	 * @return string
	 */
	public function getMessages() {
		return "wow somet really went wrong";
	}
}


//this is here becasue its part of debugging,
//which in most cases would have thrown a spanner
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
			$oError = new Spanner("Error: " . $cError, $errno);
			break;

		case E_USER_WARNING:
		case E_WARNING:
		case E_COMPILE_WARNING:
		case E_CORE_WARNING:
		case E_RECOVERABLE_ERROR:
			$oError = new Spanner("Warning: " . $cError, $errno);
			break;

		case E_USER_NOTICE:
		case E_NOTICE:
		case E_DEPRECATED:
			$oError = new Spanner("Notice: " . $cError, $errno);
			break;

		case E_PARSE:
		case E_STRICT:
			$oError = new Spanner("Parse Error: " . $cError, $errno);
			break;

		default:
			printRead(array($errstr, $errno, $errfile, $errline), "Something went very wrong");
			break;
	}

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
		die();
	} else {
		printRead($oException, "Exception happened and spanner not callbable");
		die();
	}

	//make sure they are defentlly all gone
	while (ob_get_level()) { ob_end_clean(); }

	printRead($oException, "Something has gone very wrong if we have thrown an exception this far down");
	die();
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

/**
* These are defined at the end so that they work basiclly
*/
set_exception_handler("exceptionHandler");
set_error_handler("errorHandler");

/** Assertions **/
assert_options(ASSERT_ACTIVE, 1);
assert_options(ASSERT_WARNING, 0);
assert_options(ASSERT_BAIL, 0);
assert_options(ASSERT_QUIET_EVAL, 0);
assert_options(ASSERT_CALLBACK, "assertionHandler");

//if there is an noerror defined
if (defined("noError")) {
	restore_error_handler();
	restore_exception_handler();
	assert_options(ASSERT_CALLBACK, null);
}


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
		if (isset($_SERVER['REMOTE_ADDR'])) {
		    $cMessage .= "<p>It was triggered by " . $_SERVER['REMOTE_ADDR'] . "</p>\n";
		}

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
	#$aFile		= debug_backtrace();
	#$cReturn	= print_r($mString, 1);
	$bReturn	= false;
	$bColor		= false;
	$bConsole	= false;
	$bEmail		= false;
	$bColored	= false;
	$bFirePHP	= false;

	//get the output reader object
	$oReader = new oReader($mString);

	//Show the methods of the class your trying diagnose
	#if (is_object($mString)) { $cReturn .= print_r(get_class_methods($mString), 1); }

	//are hte options an array
	if (is_array($mOptions)) {
		for ($i = 0; $i < count($mOptions); $i++) {
			//Since this can be anything
			switch ($mOptions[$i]) {
				case "email":
					$oReader->bEmail	= true;
					break;

				case "console":
					$oReader->bConsole	= true;
					break;

				case "return":
				case "ret":
					$oReader->bReturn	= true;
					break;

				case "color":
					$oReader->bColor	= true;
					break;

				case "firephp":
					$oReader->bFirePHP	= true;
					break;

				default:
					$oReader->cName 	= $mOptions[$i];
					break;
			}
		}
	} else { //options are just a string
		//Since this can be anything
		switch ($mOptions) {
			case "email":
				$oReader->bEmail	= true;
				break;

			case "console":
				$oReader->bConsole	= true;
				break;

			case "return":
			case "ret":
				$oReader->bReturn	= true;
				break;

			case "color":
				$oReader->bColor	= true;
				break;

			case "firephp":
				$oReader->bFirePHP	= true;
				break;

			default:
				$oReader->cName	= $mOptions;
				break;
		}
	}

	//is there a fire level given
	if ($cFireLevel) { $oReader->cLevel = $cFireLevel; }

	//now send to reader
	$oReader->doOutput();

	/**
	//if there is color choice
	if ($bColor) {
		//different color modes depending on whats in it
		if ((strstr($cReturn, "Array")) || (strstr($cReturn, "Object ("))) { //PHP highlight
			$bAdded	= false;

			//add the php tag if needed
			if (!strstr($cReturn, "<?php")) {
				$bAdded = true;
				$cReturn	= "<?php " . $cReturn;
			}

			//now highlight the stuff
			$cReturn	= highlight_string($cReturn, true);

			//if added strip the <?php bit, only if added
			if ($bAdded) {
				$cStart	= substr($cReturn, 0, 35);
				$cRest	= substr($cReturn, 79);

				//the rest plus the start tag
				$cReturn = $cStart .= $cRest;
			}

		//XML highlught
		} else if (strstr($cReturn, "<?xml")) {
			$cReturn	= xml_highlight($cReturn);

		//SQL highlight
		} else if ((stristr($cReturn, "SELECT")) && (stristr($cReturn, "FROM"))) {
			$cReturn	= sql_highlight($cReturn);
		}

		$cCoded		= $cReturn;
	} else {
		if ($bFirePHP || $bConsole) {
			$cCoded		= $cReturn;
		}else {
			//turn it into new lines
			$cReturn	= str_replace(" ", "&nbsp;", $cReturn);
			$cReturn	= preg_replace("{[\t]+}", "&nbsp;&nbsp;&nbsp;&nbsp;", $cReturn);
			$cCoded		= nl2br($cReturn);
		}
	}

	//split the lines and remove anything that might need protecting
	$aLines 	= explode("\n", $cCoded);
	$iLines		= count($aLines);
	$cFixed		= "";
	for ($i = 0; $i < $iLines; $i++) {
		if (strstr($aLines[$i], "[hostname]")) {
			$cFixed .= hideProtected($aLines[$i]);
		} else if (strstr($aLines[$i], "[username]")) {
			$cFixed .= hideProtected($aLines[$i]);
		} else if (strstr($aLines[$i], "[password]")) {
			$cFixed .= hideProtected($aLines[$i]);
		} else if (strstr($aLines[$i], "[database]")) {
			$cFixed .= hideProtected($aLines[$i]);
		} else {
			$cFixed .= $aLines[$i];
		}
	}

	//add the header and merge the stripped content
	if ($bEmail) {
		$cCode	= $cFixed;
	} else {
		$cCode	 = "<b>printRead called by: " . $aFile[0]['file'] . "</b><br />";
		$cCode	.= "<b>on line: " . $aFile[0]['line'] . "</b><br />";

		//k have to fix it at this point for browsers, since <br /> gets turned into <br&nbsp;/>
		$cFixed	= str_replace("<br&nbsp;/>", "<br />", $cFixed);
		$cCode	.= $cFixed;
	}

	//start hte code to make it nice
	$cReturn = "<code>";

	//If there a title then bold it
	if (isset($cName)) { $cReturn .= "<b><u>" . ucwords($cName) . "</u></b><br />"; }

	//close the code to make it nice
	$cReturn .= $cCode;
	$cReturn .= "</code><br />";

	//Console remove all the tags since not in use for console, and firephp
	if ($bConsole || $bFirePHP) {
		$cReturn_a = str_replace("<br />", "\n", $cReturn);
		$cReturn_a = strip_tags($cReturn_a);

		if ($bFirePHP) {
			FirePHP($cReturn_a);
		}
	}

	//return or echo
	if ($bReturn) {
		//Console or not
		if ($bConsole) {
			return $cReturn_a;
		} else {
			return $cReturn;
		}
	} else {
		if ($bConsole) {
			echo $cReturn_a;
		} else {
			echo $cReturn;
		}
	}
	*/
}

/**
 * firephp()
 *
 * @return firephp stuff
 */
function FirePHP() {
	$oInstance 	= FirePHP::getInstance(true);
	$aArgs		= func_get_args();
	return call_user_func_array(array($oInstance, "fb"), $aArgs);
}

/**
 * xml_highlight()
 *
 * @desc This is used to highlight xml for printRead
 * @param string $cXML
 * @return string
 */
function xml_highlight($cXML) {
	$cRegex		= '`(<([a-z]+)([^>]*)>)(.*?)(</\2>)`is';
	$cReplace	= "\1\n\t\4\5";

	$cXML	= preg_replace_callback($cRegex, xml_parse_highlight($cXML), $cXML);

	//Special Chars
	$cXML	= htmlspecialchars($cXML);

	// debug FF00FF

	//Tag <> and values
	$cXML = preg_replace("#&lt;([/]*?)(.*)([\s]*?)&gt;#sU", "<font color=\"#0000FF\">&lt;\\1\\2\\3&gt;</font>", $cXML);

	//Attribute name
	$cXML = preg_replace("#&lt;([\?])(.*)([\?])&gt;#sU", "<font color=\"#800000\">&lt;\\1\\2\\3&gt;</font>", $cXML);

	//Tag Start
	$cXML = preg_replace("#&lt;([^\s\?/=])(.*)([\[\s/]|&gt;)#iU", "&lt;<font color=\"#808000\">\\1\\2</font>\\3", $cXML);

	//Tag End
	$cXML = preg_replace("#&lt;([/])([^\s]*?)([\s\]]*?)&gt;#iU", "&lt;\\1<font color=\"#808000\">\\2</font>\\3&gt;", $cXML);

	//Attribute values
	$cXML = preg_replace("#([^\s]*?)\=(&quot;|')(.*)(&quot;|')#isU", "<font color=\"#800080\">\\1</font>=<font color=\"#D14769\">\\2\\3\\4</font>", $cXML);

	//CDATA
	$cXML = preg_replace("#&lt;(.*)(\[)(.*)(\])&gt;#isU", "&lt;\\1<font color=\"#800080\">\\2\\3\\4</font>&gt;", $cXML);

	//Find the start of the tag, and then find the end of it, so that I can seperate it properlly

	//New Line
	$cXML = preg_replace("#&gt;</font><font color=\"\#0000FF\">&lt;(.*)#isU", "&gt;</font><font color=\"#0000FF\"><br />&nbsp;&nbsp;&lt;\\1", $cXML);
	$cXML = preg_replace("#<br />&nbsp;&nbsp;&lt;/#isU", "<br />&lt;/", $cXML);
	$cXML   = preg_replace("{[\t]+}", "&nbsp;&nbsp;&nbsp;&nbsp;", $cXML);

	return nl2br($cXML);
}

/**
 * xml_parse_highlight()
 *
 * @param string $cString
 * @return string
 */
function xml_parse_highlight($cString) {
	$cRegex		= '`(<([a-z]+)([^>]*)>)(.*?)(</\2>)`is';
	$cReplace	= "\1\n\t\4\5";

	return preg_replace($cRegex, $cReplace, $cString);
}

/**
 * sql_highlight()
 *
 * @param string $cSQL
 * @return string
 */
function sql_highlight($cSQL) {
	$cStart = "<font color=\"#800000\">";
	$cEnd	= "</font>";

	//SELECT, WHERE
	$cSQL 	= str_ireplace("SELECT", "<font color=\"#0000FF\">SELECT</font>", $cSQL);
	$cSQL	= str_ireplace("WHERE", "<font color=\"#0000FF\">\nWHERE</font>", $cSQL);
	$cSQL 	= str_ireplace("(SELECT", "<font color=\"#0000FF\">(\nSELECT</font>", $cSQL);

	//AND, LIKE
	$cSQL	= str_ireplace("LIKE", "<font color=\"#0000FF\">LIKE</font>", $cSQL);
	$cSQL	= str_ireplace("AND", "<font color=\"#0000FF\">\nAND</font>", $cSQL);

	//FROM, JOIN, LEFT JOIN, RIGHT JOIN
	$cSQL	= str_ireplace('FROM', "<font color=\"#0000FF\">\nFROM</font>", $cSQL);
	$cSQL	= str_ireplace('JOIN', "<font color=\"#0000FF\">\nJOIN</font>", $cSQL);
	$cSQL	= str_ireplace('LEFT JOIN', "<font color=\"#0000FF\">\nLEFT JOIN</font>", $cSQL);
	$cSQL	= str_ireplace('RIGHT JOIN', "<font color=\"#0000FF\">\nRIGHT JOIN</font>", $cSQL);

	//LIMIT, GROUP
	$cSQL	= str_ireplace('LIMIT', "<font color=\"#800080\">\nLIMIT</font>", $cSQL);
	$cSQL	= str_ireplace('GROUP', "<font color=\"#800080\">\nGROUP</font>", $cSQL);

	//BY, AS
	$cSQL	= str_ireplace('BY', "<font color=\"#800080\">BY</font>", $cSQL);
	$cSQL	= str_ireplace('AS', "<font color=\"#800080\">AS</font>", $cSQL);

	//Parenthesses
	$cSQL	= str_ireplace('(', "<font color=\"#800080\">(</font>", $cSQL);
	$cSQL	= str_ireplace(')', "<font color=\"#800080\">)</font>", $cSQL);


	$cFinal	= $cStart . $cSQL . $cEnd;
	return nl2br($cFinal);
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


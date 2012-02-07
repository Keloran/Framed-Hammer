<?php
if (!function_exists("printRead")) { include HAMMERPATH . "/functions/errors.php"; } //always needed
if (!class_exists("screws")) { include "screws.php"; } //include the autoloader
if (!function_exists("visitorIP")) { include HAMMERPATH . "/functions/hammer.php"; } //to include the standard hammer functons

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
			if (!class_exists("Hammer")) { include "hammer.php"; }

			$this->mContact	= Hammer::getConfigStat("email")['email'];
			$this->cAddress	= Hammer::getConfigStat("address")['address'];
		}

		//fix for old configs
		if (is_array($this->cAddress)) {
			$cAddress 		= $this->cAddress['address'];
			$this->cAddress	= $cAddress;
		}
		if (is_array($this->mContact)) {
			$cContact		= $this->mContact['email'];
			$this->mContact	= $cContact;
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
		$cSave		= false;

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
				$bNice 		= false;
				$cSave		= $this->saveMessage($this->code);

				//is it dev, in which case tell me where the message is saved, and also show it
				if (defined("DEV")) {
					$cReturn	 = $this->showThis($cSave);
					$cReturn	.= $this->showMessage();
				}
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

		//now kill it since it will loop otherwise
		die();
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
		$cTitle			= "An error occoured in " . $this->cAddress . ":" . $iErrNo;
		$cFrom			= "error@" . $this->cAddress;
		$mContact		= $this->mContact;

		$cMessageHTML	= $this->showMessage(false);
		$cMessageText	= $this->showMessage(true);

		//I really dont want to send this to bug tracker
		$bNoSend	= isset($_GET['nobugs']) ? $_GET['nobugs'] : false;

		if (!$bNoSend) {
			$oSend = new Email_Send();

			if ($bBugs) {
				$aSend = array(
					"to"		=> "bugs@bugfixs.com",
					"title"		=> $cTitle,
					"html"		=> $cMessageHTML,
					"text"		=> $cMessageText,
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
							"html"		=> $cMessageHTML,
							"text"		=> $cMessageText,
							"from"		=> $cFrom,
						);
						$oSend->compose($aSend);
					}
				} else {
					$cTo 	= addslashes($mContact);
					$aSend 	= array(
						"to"	=> $cTo,
						"title"	=> $cTitle,
						"html"	=> $cMessageHTML,
						"text"	=> $cMessageText,
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
		$iTime		= time();
		$cMessage 	= $this->showMessage(true);
		file_put_contents("/tmp/" . $iTime . "_error.message", $cMessage);

		$cReturn	= "File Located at: /tmp/" . $iTime . "_error.message";
		return $cReturn;
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
			//open buffer
			if (!checkHeaders()) { ob_start(); }

			//now put the offline page
			#header("HTTP/1.1 404 File not Found");
			http_response_code(404);

			//is stuff offline, e.g. database, it also redirects to homepage for normal stuffs,
			//hopefulyl wont go into infinite loop
			if ($bOffline) {
				header("refresh: 5; url=http://hammer.develbox.info/offline.html?site=" . $cSite);
			} else {
				header("refresh 5; url=/");
			}

			echo $cMessage;

			//close teh buffer
			if (!checkHeaders()) { ob_end_flush(); }
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
		$cMessage = false;

		//since console messages cant see this
		if (!$bConsole) {
			$cMessage	.= "<section id=\"exceptiond\">\n";
			$cMessage	.= "<header>" . $this->getMessage() . "</header>\n";
			$cMessage	.= "<article>\n";
		}

		$cMessage	.= "<p>An exception happened in <br />" . $this->getFile() . ".</p>\n";
		$cMessage	.= "<p>On line <br />" . $this->getLine() . ".</p>\n";
		$cMessage	.= "<p>The whole message is <br />" . $this->getMessage() . "</p>\n";
		$cMessage	.= "<p>The exception code is <br />" . $this->getCode() . "</p>\n";
		$cMessage	.= "<p>A Trace is <br />" . nl2br($this->getTraceAsString()) . "</p>\n";
		$cMessage	.= "<p>A Printed trace is <br />" . nl2br(print_r($this->getTrace(), true)) . "</p>\n";

		//the last error, incase it exists
		$cMessage 	.= "<p>Last Error array <br />" . nl2br(print_r(error_get_last(), true)) . "</p>\n";

		//memory usage, and devided by 1024
		$cMessage .= "<p>Memory Usage<br />" . nl2br(print_r(memory_get_peak_usage(), true));
 		$cMessage .= " (" . nl2br(print_r(round(memory_get_peak_usage() / 1024), true)) . ")</p>\n";

		//arguments in useS
		$cMessage .= "<p>Function Arguments <br />" . nl2br(print_r(func_get_args(), true)) . "</p>\n";

		//Since not all systems have the remote addr, e.g. googlebot
		$cVisitor = visitorIP();
		if ($cVisitor) { $cMessage .= "<p>It was triggered by " . $cVisitor. "</p>\n"; }

		//class previous
		if (isset($this->cPrevious)) { $cMessage .= "<p>Previous: " . $this->cPrevious . "</p>\n"; }

		//Defined Vars
		$cMessage .= "<p>Defined Vars " . print_r(get_defined_vars(), true) . "</p>\n";

		//GPR
	    $cMessage .= "<p>Full REQUEST is " . print_r($_REQUEST, true) . "</p>\n";
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

		//since console cant see this
		if (!$bConsole) {
			$cMessage	.= "</article>\n";
			$cMessage	.= "</section>\n";
		}

		//remove the brs and replace with newlines
		if ($bConsole) {
			$cMessage = str_replace("<br />", "\n", $cMessage);
			$cMessage = str_replace("\n\n", "\n", $cMessage);
			$cMessage = str_replace(array("<p>", "</p>"), array("\n--------\n", "\n--------\n"), $cMessage);
		}

		return $cMessage;
	}

	/**
	 * Spanner::showThis()
	 *
	 * @param string $cMessage
	 * @return string
	 */
	private function showThis($cMessage) {
		$cReturn	 = "<section id=\"exceptiond\">\n";
		$cReturn	.= "<header>Specific Message</header>\n";
		$cReturn	.= "<article>" . $cMessage . "</article>\n";
		$cReturn	.= "</section>\n";
		$cReturn	.= "<hr />\n";

		return $cReturn;
	}
}

/**
* These are defined at the end so that they work basiclly
*/
set_exception_handler("exceptionHandler");
set_error_handler("errorHandler", E_ALL);

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


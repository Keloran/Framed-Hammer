<?php
/**
* The Default hammer functions
* Removed from hammerexception,
* had no buisness being in there to begin with
* @version $Id: hammer.fn.php 477 2010-01-04 12:57:54Z keloran $
*/
date_default_timezone_set('UTC');

//since 5.2 cant do late static binding
if (PHP_VERSION >= 5.3) {
	include_once("nails/nail3.php");
} else {
	include_once("nails/nail2.php");
}

/**
 * funcParam()
 *
 * @param string $cParam
 * @param array $aArray
 * @return mixed
 */
function funcParam($cParam, $aArray) {
	$mReturn	= false;

	if (isset($aArray[$cParam])) {
		$mReturn = $aArray[$cParam];
	}

	return $mReturn;
}

/**
 * sendMail()
 *
 * @param mixed $mInfo
 * @param string $cSubject
 * @param string $cContent
 * @param string $cContentText
 * @param string $cFrom
 * @param string $cFromName
 * @param string $cReturn
 * @return bool
 */
function sendMail($mInfo, $cSubject = false, $cContent = false, $cContentText = false, $cFrom = false, $cFromName = false, $cReturn = false) {
	$oSend 		= new Email_Send();
	$bReturn	= false;

	if (is_array($mInfo)) {
		$bReturn = $oSend->compose($mInfo);
	} else {
		$aSend = array(
			"to"		=> $mInfo,
			"from"		=> $cFrom,
			"fromName"	=> $cFromName,
			"return"	=> $cReturn,
			"text"		=> $cContentText,
			"html"		=> $cContent,
			"subject"	=> $cSubject,
		);
		$bReturn = $oSend->compose($aSend);
	}

	return $bReturn;
}

/**
 * sendEmail()
 *
 * @param mixed $mInfo
 * @param string $cSubject
 * @param string $cContent
 * @param string $cContentText
 * @param string $cFrom
 * @param string $cFromName
 * @param string $cReturn
 * @return bool
 */
function sendEmail($mInfo, $cSubject = false, $cContent = false, $cContentText = false, $cFrom = false, $cFromName = false, $cReturn = false) {
	$oSend 		= new Email_Send();
	$bReturn	= false;

	if (is_array($mInfo)) {
		$bReturn = $oSend->compose($mInfo);
	} else {
		$aSend = array(
			"to"		=> $mInfo,
			"from"		=> $cFrom,
			"fromName"	=> $cFromName,
			"return"	=> $cReturn,
			"text"		=> $cContentText,
			"html"		=> $cContent,
			"subject"	=> $cSubject,
		);
		$bReturn = $oSend->compose($aSend);
	}

	return $bReturn;
}

/**
* sendEmail()
*
* @desc This is a better method of sending mail instead of using mail() it sends in html and text
* @param string $cTo
* @param string $cSubject
* @param string $cContent
* @param string $cFrom
* @return bool
*/
function oldsendEmail($mInfo, $cSubject = false, $cContent = false, $cContentText = false, $cFrom = false, $cFromName = false, $cReturn = false, $bTextOnly = false) {
	$bTemplate		= false; //if your using a template this will get set to true
	$cTemplate		= false; //the template name
	$aParams		= false;
	$mAttachments	= false;

	$cBoundry	= "----Hammer_Mailer----" . md5(time());

	//is there an array for the first element, pass it through and get elements
	if (is_array($mInfo)) {
		//set this to true since your using the new method,
		//if you want html you will have added it in the array
		$bTextOnly	= true;
		foreach ($mInfo as $cKey => $mValue) {
			switch ($cKey) {
				case "to":
					$cTo = $mValue;
					break;

				case "subject":
					$cSubject = $mValue;
					break;

				//html part of the email
				case "content":
				case "html":
					$cContent	= $mValue;
					$bTextOnly	= false;
					break;

				//text of the email
				case "contentText":
				case "text":
					$cContentText = $mValue;
					break;

				//template thats to be used instead of written html
				case "template":
					$cTemplate	= $mValue;
					$bTemplate	= true;
					$bTextOnly	= false;
					break;

				//template params
				case "templateParams":
				case "params":
					$aParams = $mValue;
					break;

				//From address
				case "from":
					$cFrom	= $mValue;
					break;

				//From name e.g. site.com
				case "fromName":
				case "fromname":
					$cFromName	= $mValue;
					break;

				//is there a return address
				case "return":
				case "returnPath":
				case "returnAddress":
					$cReturn = $mValue;
					break;

				case "attachments":
					$mAttachments = $mValue;
					break;
			}
		}
	} else { //its not an array, its still the old way
		$cTo = $mInfo;
	}

	//From
	if ($cFrom) {
		if ($cFromName) { //there is a name set so has to be put in gt/lt tags
			$cHeaders       = "From: " . $cFromName . " <" . $cFrom . ">\n";
		} else { //no name set
			$cHeaders       = "From: " . $cFrom . "\n";
		}
	}

	//set the some of the headers
	$cHeaders .= "X-Mailer: Hammer\n";
	$cHeaders .= "User-Agent: Hammer\n";
	$cHeaders .= "MIME-Version: 1.0\n";

	//Is it text only
	if ($bTextOnly) {
		$cHeaders .= "Content-type: text/plain; charset=UTF-8\n";
	} else { //Headers to say its multipart
		$cHeaders .= 'Content-Type: multipart/mixed; boundary="' . $cBoundry . '"' . "\n";
	}

	//return
	if (!$cReturn) {
		if ($cFrom) {
			$cHeaders .= "Return-Path: " . $cFrom . "\n";
			$cHeaders .= "Return-path: <" . $cFrom . ">\n";
		}
	} else {
		$cHeaders .= "Return-Path: " . $cReturn . "\n";
		$cHeaders .= "Return-path: <" . $cReturn . ">\n";
	}

	if ($bTextOnly) {
		$cBody = $cContentText;
	} else {
		//Text
		$cBody  = "--" . $cBoundry . "\n";
		$cBody .= "Content-Type: text/plain; charset=UTF-8\n";
		$cBody .= "Content-Transfer-Encoding: 8bit\n\n";
		$cBody .= $cContentText . "\n";

		//HTML part
		$cBody .= "--" . $cBoundry . "\n";
		$cBody .= "Content-Type: text/html; charset=UTF-8\n";
		$cBody .= "Content-Transfer-Encoding: 8bit\n\n";

		//theres a template in place
		if ($bTemplate) {
			if ($cTemplate) {
				$oHammer	= Hammer::getHammer();
				$oTemplate	= $oHammer->getTemplate();
				$oTemplate->setTemplate($cTemplate);

				//theres some params to set
				if ($aParams) {
					foreach ($aParams as $cKey => $cValue) {
						$oTemplate->setParams($cKey, $cValue);
					}
				}

				$cBody .= $oTemplate->renderTemplate();
			} else {
				$cBody .= $cContent;
			}
		} else {
			$cBody .= "<html>\n";
   			$cBody .= "<body style=\"font-family:Verdana, Verdana, Geneva, sans-serif; font-size:12px; color:#666666;\">\n";
	   		$cBody .= $cContent;
   			$cBody .= "</body>\n";
	   		$cBody .= "</html>\n";
		}

		//Close
		$cBody .= "--" . $cBoundry . "--\n";

		//now go through the attachments
		if ($mAttachments) {
			//single attachment
			if (isset($mAttachments['name'])) {
				$cBody .= "Content-Type: ";

			//multiple attachments
			} else {
				for ($k = 0; $k < count($mAttachments); $k++) {

				}
			}
		}
	}

	//see if -f should be used or not
	$bLogin = false;
	if (defined("emailed")) {
		if (strstr($cFrom, emailed)) {
			$bLogin = true;
		}
	}

	if ($bLogin) {
		return mail($cTo, $cSubject, $cBody, $cHeaders, "-f " . $cFrom);
	} else {
		return mail($cTo, $cSubject, $cBody, $cHeaders);
	}
}

/**
 * shrinkThis()
 *
 * @desc This shrinks the content, and trys not to break the syntax so that it still passes validator
 * @param string $cString
 * @param int $iLength
 * @return string
 */
function shrinkThis($cString, $iLength) {
	$iPosBR		= stripos($cString, "<br />");

	//Start the tags
	$iPosStart_a	= stripos($cString, "<");
	$iPosEnd_a		= stripos($cString, ">");
	$iLength1_a		= (($iPosEnd1 + 1) + $iPosStart_a);
	$cStrStart		= substr($cString, $iPosStart_a, $iLength_a);

	//Get the length before the tag
	if (isset($iPosStart_a) && ($iPosStart_a > 0)) {
		$cStart_b	= substr($cString, 0, $iPosStart_a);
		$iLength_b	= strlen($cStart_b);
	}

	//The return string if the tag is after the point wanted to cut anyway
	if (isset($iLength_b) && ($iLength_b > 0)) {
		if ($iLength_b > $iLength) {
			$cReturn = substr($cString, 0, $iLength);
		} else {
			$cReturn = substr($cString, 0, $iLength_b);
		}
	}

	//End the tag
	if (isset($cStart_b)) {
		$iPosEnd_c	= stripos($cString, "</" . $cStart_b . ">");
		$iPosEnd_d	= (3 + $iLength_a);
		$iPosEnd_e	= ($iPosEnd_c + $iPosEnd_e);
	}

	//Get the length after the end of the tags
	if (isset($iPosEnd_e) && ($iPosEnd_e > 0)) {
		$cStart_c	= substr($cString, 0, $iPosEnd_e);
		$iLength_e	= strlen($cStart_c);
	}

	//If the length is less than the end of the tags, then return after tag end
	if (isset($iLength_e) && ($iLength_e > 0)) {
		if ($iLength_e > $iLength) {
			$cReturn	= substr($cString, 0, $iLength);
		}
	}

	if (isset($cReturn)) {
		$cReturn = wordwrap($cReturn, $iLength);
	} else {
		$cReturn = substr($cString, 0, $iLength);
		$cReturn = wordwrap($cReturn, $iLength);
	}

	return $cReturn . "...";
}

/**
 * ob_process()
 *
 * @desc This doesnt really work and as such isnt called by anything
 * @param string $cBuffer
 * @return
 */
function ob_process($cBuffer) {
	$cReturn	= false;

	$cReturn	= $cBuffer;
	header("Content-Type: text/html; charset=UTF-8");

	//Remove the extra spaces that are not needed
	$cReturn = trim(preg_replace('/\s+/', ' ', $cReturn));

	// add styled double quotes
	$cReturn = preg_replace('/"(?=[^>]*<)([^"]*)"(?=[^>]*<)/u', '&#8220;\\1&#8221;', $cReturn);

	// add styled apostrophes
	$cReturn = preg_replace("/'(?=[^>]*<)/i", "&#8217;", $cReturn);

	// add ellipses
	$cReturn = str_replace('...', '&#8230;', $cReturn);

	// encode ampersands
	$cReturn = str_replace('&', '&amp;', $cReturn);

	return $cReturn;
}

/**
* Hammer()
*
* @desc this starts the site and calls the right objects
* @param string $cSite This is the name of the site, it isnt really used atm
* @param array $aFilter The filters e.g. page/action/choice/others
* @param bool $bNoInstall set this if you want all files to be set to no install
*
* @return string
*/
function Hammer($cSite, $aFilter = false, $aOptions = null) {
	$cReturn	= false;

	//options
	$cStructure	= false;
	$bNoInstall	= false;

	//see if this fixes the slash-stack fault
	removeEndSlash();

	//go through the options and set them
	if ($aOptions) {
		foreach ($aOptions as $cKey => $cValue) {
			switch ($cKey) {
				case "noInstall":
					$bNoInstall = $cValue;
					break;

				case "structure":
					$cStructure = $cValue;
					break;
			}
		}
	}

	//xhprofiler
	if (function_exists("xhprof_enable") && (defined("profile"))) {
    		xhprof_enable(XHPROF_FLAGS_CPU + XHPROF_FLAGS_MEMORY);
	}

	//set a filter if its missign, but still tell them, since they are doign it wrong
	if (!$aFilter) { $aFilter = array("page", "action", "choice"); }

	//Since we havent yet setup the site we want to send them to this
	if (file_exists(SITEPATH . "/setup.php")) {
		$bConfig = false;

		//configs
		if (file_exists(SITEPATH . "/config.xml")) { 		$bConfig = true; }
		if (file_exists(SITEPATH . "/config.live.xml")) {	$bConfig = true; }
		if (file_exists(SITEPATH . "/config.dev.xml")) {	$bConfig = true; }

		//if there isnt a config
		if (!$bConfig) {
			if ((isset($_SERVER['SCRIPT_NAME'])) && (strstr($_SERVER['SCRIPT_NAME'], "/setup/"))) {
				header("Location: /setup.php");
				die();
			}
		}
	}

	//Try the loader
	try {
		if (!class_exists("Hammer")) { include_once("hammer.php"); }

		$oHammer = Hammer::getHammer($cSite, $aFilter);
	} catch (Spanner $e) {
		$aError = array(
			"Couldn't do the loader",
			$cSite,
			$aFilter,
			$_GET,
		);
		$cError = printRead($aError, "ret");

		throw new Spanner($cError, 100);
		die();
	}

	//Start the session
	if (!session_id()) { session_start(); }

	//set the address
	if (isset($oHammer)) { $oHammer->setAddress($aFilter); }

	//do the try/catch of the modules
	try {
		//User
		try {
			$oUser		= $oHammer->getUser();
		} catch (User_Exception $e) {
			throw new Spanner($e->getMessage(), 699);
		}

		//Session
		try {
			$oSession	= $oHammer->getSession();
			$oSession->lastLogin();
			$oSession->setLogin();
		} catch (Session_Exception $e) {
			throw new Spanner($e->getMessage(), 899);
		}

		//Admin
		try {
			$oAdmin	= $oHammer->getAdmin();
			$oAdmin->secureLogin();
		} catch (Admin_Exception $e) {
			throw new Spanner($e->getMessage(), 999);
		}

		//Since this IP is banned, send them to google
		if (isset($_SERVER['REMOTE_ADDR'])) {
			if (!is_null($oAdmin)) {
				if ($oAdmin->getBannedIP($_SERVER['REMOTE_ADDR'])) {
					$oHammer->sendLocation("http://www.google.com");
				}
			}
		}

		//Organics
		try {
			$oOrganic = $oHammer->getNails("Organic");
			$oOrganic->markOrganic();
		} catch (Organic_Exception $e) {
			throw new Spanner($e->getMessage(), 1099);
		}

		//Cache
		$cCached		= false;
		try {
			$oCache		= $oHammer->getCache();
			$cCached	= $oCache->getItem();
		} catch (Cache_Exception $e) {
			throw new Spanner($e->getMessage(), 1199);
		}

		//is there anything in the cache
		if (!$cCached) {
			//Template
			try {
				$oTemplate	= $oHammer->getTemplate();
				$cReturn	= $oTemplate->getStructure($cStructure);
				$oCache->addItem($cReturn);
			} catch (Template_Exception $e) {
				throw new Spanner($e->getMessage(), 599);
			}
		} else {
			$cReturn = $cCached;
		}
	} catch (Spanner $e) {
		new Spanner($e->getMessage(), $e->getCode());
	} catch (Exception $e) {
		new Spanner($e->getMessage(), $e->getCode());
	}

	//destroy the session
	if (session_id()) {
		//make sure the headers havent already been sent
		if (!headers_sent()) {
			session_regenerate_id();
			session_destroy();
		}
	}

	//now use the profile and display the result
	if (function_exists("xhprof_disable")) {
 		if (defined("profile")) {
			$mProf = xhprof_disable();

			include_once(HAMMERPATH . "/tests/xhprof_lib/utils/xhprof_lib.php");
 			include_once(HAMMERPATH . "/tests/xhprof_lib/utils/xhprof_runs.php");

			$oProf = new XHProfRuns_Default();
 			$iRunID = $oProf->save_run($mProf, "xhprof");

			$cReturn .= "<div \"profiled\">run=" . $iRunID . "&source=xhprof</div>\n";
		}
	}

	return $cReturn;
}

/**
 * hammerHash() //renamed to fix a conflict
 *
 * @desc a more secure hash method than sha1 although it will fallback to sha1 if has isnt installed
 * @param string $cString
 * @return
 */
function hammerHash($cString, $iStrength = false) {
	if (function_exists("hash")) { //has isnt installed so revert to sha1
		if ($iStrength) {
			switch ($iStrength) {
				case 3:
					$cHash = "sha512";
					break;

				case 2:
					$cHash = "sha384";
					break;

				case 1:
				default:
					$cHash = "sha256";
					break;
			}
		} else {
			$cHash = "sha256";
		}

		$cReturn	= hash($cHash, $cString);
	} else {
		$cReturn	= sha1(uniqid(rand() . $cString));
	}

	return $cReturn;
}

/**
* stribet()
*
* @desc This finds the bits between the start and end inside a string
* @param string $cString The whole string
* @param string $cStart The starter part to find
* @param string $cEnd The ender
* @example stribet(tester@beep>, @, >) returns beep
* return string
*/
function stribet($cString, $cStart, $cEnd) {
	$cString	= strtolower($cString);
	$cStart		= strtolower($cStart);
	$cEnd		= strtolower($cEnd);

	$iStart		= strpos($cString, $cStart) + 1;
	$cReturn	= substr($cString, $iStart);

	$iEnd		= strpos($cReturn, $cEnd);
	$cReturn	= substr($cReturn, 0, $iEnd);

	return $cReturn;
}

/**
* setAddress()
*
* @desc This is a global for setting the address so that it can be used by pretty much everything
*
* @param array $aFilters This sets teh order/name of the variable
* @return array
*/
function getAddress($aFilters = false, $cSiteAddress = false) {
	$oScrewDriver = new Screwdriver($aFilters, $cSiteAddress);

	return $oScrewDriver->finalAddress();
}

/**
 * getBrowserCap()
 *
 * @return mixed
 */
function getBrowserCap() {
	$bGetBrowser	= false;
	$mGetBrowser	= false;
	$mReturn	= false;

	//do we actually have browser cap, otherwise dont bother trying
	if (ini_get("browscap")) {
		ob_start();
			$mGetBrowser = get_browser();
			$bGetBrowser = true;
		ob_end_clean();
	}

	//get_browser doesnt work on this server
	if (!$bGetBrowser) { return $mReturn; }

	$oBrowser	= $mGetBrowser;
	$cBrowser	= strtolower($oBrowser->browser);
	switch ($cBrowser) {
		case "chrome":
		case "webkit":
		case "safari":
			$mBrowser = "safari";
			break;

		case "iphone":
		case "mobile-safari":
			$mBrowser = array("safari", "iphone");
			break;

		case "ie":
		case "internet explorer":
			$mBrowser[] = "ie";

			switch ($oBrowser->majorver) {
				case 6:
					$mBrowser[] ="ie6";
					break;

				case 7:
					$mBrowser[] = "ie7";
					break;

				case 8:
					$mBrowser[] = "ie8";
					break;

			}
			break;
	}

	return $mReturn;
}

/**
 * getBrowser()
 *
 * @return array
 */
function getBrowser($cSpecific = null) {
	$mBrowser 	= false;
	$mGetBrowser	= getBrowserCap();

	//this is a better method
	if ($mGetBrowser) { return $mGetBrowser; }

	if (isset($_SERVER['HTTP_USER_AGENT'])) {
		$cBrowser = $_SERVER['HTTP_USER_AGENT'];

		if (stristr($cBrowser, "MobileIE")) {
			$mBrowser = array("ie", "mobileie");
		} else if (stristr($cBrowser, "MSIE 7") || stristr($cBrowser, "MSie 7")) {
			$mBrowser = array("ie", "ie7");
		} else if (stristr($cBrowser, "MSIE 8") || stristr($cBrowser, "MSie 8")) {
			$mBrowser = array("ie", "ie8");
		} else if (stristr($cBrowser, "MSIE 9") || stristr($cBrowser, "MSie 9")) {
			$mBrowser = "ie9";
		} else if (stristr($cBrowser, "MSIE") || stristr($cBrowser, "MSie")) {
			$mBrowser = array("ie", "ie6");
		} else if (stristr($cBrowser, "Opera")) {
			$mBrowser = "opera";
		} else if (stristr($cBrowser, "iPhone")) {
			$mBrowser = "iphone";
		}

		//Since we might have already got the iphone
		if (stristr($cBrowser, "KHTML")) {
			if (stristr($cBrowser, "Safari")) {
				if ($mBrowser) {
					$mBrowser = array("webkit", "iphone");
				} else {
					$mBrowser = "webkit";
				}
			} else if (stristr($cBrowser, "Konqueror")) {
				$mBrowser = "khtml";
			}
		} else if (stristr($cBrowser, "Gecko")) {
			$mBrowser = "gecko";
		}
	} else {
		$mBrowser = false;
	}

	//specific browser specified
	if ($cSpecific) {
		if (is_array($mBrowser)) {
			if (in_array($cSpecific, $mBrowser)) {
				$mBrowser	= true;
			} else {
				$mBrowser	= false;
			}
		} else {
			if ($cSpecific == $mBrowser) {
				$mBrowser = true;
			} else {
				$mBrowser = false;
			}
		}
	}

	return $mBrowser;
}

/**
 * getNailed()
 *
 * @param string $cNail
 * @param object $oNail
 * @param mixed $mParams
 * @return object
 */
function getNailed($cNail, $oNail, $mParams = null) {
	return getNail_Version($cNail, $oNail, $mParams);
}

/**
* removeEndSlash()
* @desc Remove the closing slash since it causes problems
* @return null
*/
function removeEndSlash() {
	//variables
	if (isset($_GET['variables'])) {
		if (substr($_GET['variables'], -1) == "/") {
			$_GET['variables'] = substr($_GET['variables'], 0, (strlen($_GET['variables']) - 1));
		}
	}

	//hvars alternative to variables
	if (isset($_GET['hvars'])) {
		if (substr($_GET['hvars'], -1) == "/") {
			$_GET['hvars'] = substr($_GET['hvars'], 0, (strlen($_GET['hvars']) - 1));
		}
	}

	//the same way most frameworks work
	if (isset($_SERVER['REQUEST_URI'])) {
		if (substr($_SERVER['REQUEST_URI'], -1) == "/") {
			$_SERVER['REQUEST_URI'] = substr($_SERVER['REQUEST_URI'], 0, (strlen($_SERVER['REQUEST_URI']) -1));
		}
	}
}


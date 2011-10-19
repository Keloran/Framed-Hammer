<?php
/**
 * The Default hammer functions
 * Removed from hammerexception,
 * had no buisness being in there to begin with
 * @version $Id: hammer.fn.php 477 2010-01-04 12:57:54Z keloran $
 */
date_default_timezone_set('UTC');

/**
 * getNail_Version()
 *
 * @desc Since this is 5.4 only, include it in fn file
 * @param string $cNail
 * @param string $oNail
 * @param mixed $mParams
 * @return object
 */
function getNail_Version($cNail, $oNail, $mParams = null) {
	if (is_callable(array($cNail, "getInstance"))) {
		return $cNail::getInstance($oNail, $mParams);
	} else {
		return new $cNail($oNail, $mParams);
	}
}

//is spanner included
if (!function_exists("printRead")) { include HAMMERPATH . "/spanner.php"; }

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
	if (!function_exists("removeEndSlash")) { include HAMMERPATH . "/functions/address.php"; }

	//default of false
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
	if (function_exists("xhprof_enable") && (defined("profile"))) { xhprof_enable(XHPROF_FLAGS_CPU + XHPROF_FLAGS_MEMORY); }

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
		if (!class_exists("Hammer")) { include HAMMERPATH . "/hammer.php"; }

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
	if (session_status() !== 2) { session_start(); }

	//set the address
	if (isset($oHammer)) { $oHammer->setAddress($aFilter); }

	//do the try/catch of the modules
	try {
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

			//Since this IP is banned, send them to google
			$cVisitor = visitorIP();
			if ($cVisitor) {
				if (strstr($cVisitor, ",")) {
					$aVisitor = explode(",", $cVisitor);
					for ($i = 0; $i < count($aVisitor); $i++) {
						if ($oAdmin->getBannedIP(trim($aVisitor[$i]))) { $oHammer->sendLocation("http://www.google.com"); }
					}
				} else {
					if ($oAdmin->getBannedIP(trim($cVisitor))) { $oHammer->sendLocation("http://www.google.com"); }
				}
			}

			//the user isnt banned so go through secure incase of page
			$oAdmin->secureLogin();
		} catch (Admin_Exception $e) {
			throw new Spanner($e->getMessage(), 999);
		}

		//Organics
		try {
			$oOrganic = $oHammer->getNails("Organic");
			$oOrganic->markOrganic();
		} catch (Organic_Exception $e) {
			throw new Spanner($e->getMessage(), 1099);
		}

		//Cache
		try {
			$oCache		= $oHammer->getCache();
			$cCached	= $oCache->getItem();

			//is there anything in the cache
			if (!$cCached) {
				//Template
				try {
					$oTemplate	= $oHammer->getTemplate();
					$cReturn	= $oTemplate->getStructure($cStructure); //now load the actual site
					$oCache->addItem($cReturn);
				} catch (Template_Exception $e) {
					throw new Spanner($e->getMessage(), 599);
				}
			} else {
				$cReturn = $cCached;
			}
		} catch (Cache_Exception $e) {
			throw new Spanner($e->getMessage(), 1199);
		}
	} catch (Spanner $e) {
		new Spanner($e->getMessage(), $e->getCode());
	} catch (ErrorException $e) {
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

			include HAMMERPATH . "/tests/xhprof_lib/utils/xhprof_lib.php";
			include HAMMERPATH . "/tests/xhprof_lib/utils/xhprof_runs.php";

			$oProf = new XHProfRuns_Default();
			$iRunID = $oProf->save_run($mProf, "xhprof");

			$cReturn .= "<div \"profiled\">run=" . $iRunID . "&source=xhprof</div>\n";
		}
	}

	return $cReturn;
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
 * visitorIP()
 *
 * @return string
 */
function visitorIP() {
	if (isset($_SERVER['HTTP_CLIENT_IP'])) { //check ip from share internet
		$cIP = $_SERVER['HTTP_CLIENT_IP'];
	} else if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) { //to check ip is pass from proxy
		$cIP = $_SERVER['HTTP_X_FORWARDED_FOR'];
	} else if (isset($_SERVER['REMOTE_ADDR'])) { //is it standard method
		$cIP = $_SERVER['REMOTE_ADDR'];
	} else {
		$cIP = false;
	}

	//Check to make sure its not "unknown"
	if ($cIP == "unknown") { $cIP = false; }

	return $cIP;
}

/**
 * checkHeaders()
 *
 * @todo -c proberlly should change default to true actually
 * @return bool
 */
function checkHeaders() {
	$bReturn	= false;
	if (function_exists("headers_sent")) { $bReturn	= headers_sent(); }

	return $bReturn;
}
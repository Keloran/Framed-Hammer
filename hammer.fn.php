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

//is it 5.4 in which case traits exist
if (PHP_VERSION <= 5.3) {
	$cPath = HAMMERPATH . "/nails/traits/";

	$aFiles = false;
	if ($pHandle_a = opendir($cPath)) {
		while (false !== ($mFile_a = readdir($pHandle_a))) {
			//skips
			if ($mFile_a == "templates") { continue; }
			if ($mFile_a == ".") { continue; }
			if ($mFile_a == "..") { continue; }

			//set the path
			$mFile_a = $cPath . $mFile_a;

			//go into the folder
			if (is_dir($mFile_a)) {
				if ($pHandle_b = opendir($mFile_a)) {
					while(false !== ($mFile_b = readdir($pHandle_b))) {
						//skips
						if ($mFile_b == ".") { continue; }
						if ($mFile_b == "..") { continue; }
						if (!strpos($mFile_b, "fn")){ continue; } //its proberlly a trait file

						//Only files left
						$cPath_b = $mFile_a . "/" . $mFile_b;
						if (is_file($cPath_b)) {
							$aFiles[] = $cPath_b;
						}
					}
					closedir($pHandle_b);
				}
			}
		}
		closedir($pHandle_a);
	}

	//now include the new files
	if ($aFiles) {
		for($i = 0; $i < count($aFiles); $i++) {
			include_once($aFiles[$i]);
		}
	}
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


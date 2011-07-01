<?php
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

function mobileBrowser($mBrowser = false) {
	if (!$mBrowser) { $mBrowser = getBrowser(); }

	$bReturn	= false;

	//is mbrowser an array
	if (is_array($mBrowser)) {
		if (in_array("android", $mBrowser)) {
			$bReturn	= true;
		} else if (in_array("iphone", $mBrowser)) {
			$bReturn	= true;
		} else if (in_array("ipad", $mBrowser)) {
			$bReturn	= true;
		}
	} else {
		switch($mBrowser) {
			case "android":
			case "iphone":
			case "ipad":
				$bReturn = true;
				break;
		} // switch
	}

	return $bReturn;
}

function IEBrowser($mBrowser = false) {
	if (!$mBrowser) { $mBrowser = getBrowser(); }

	$bReturn = false;

	printRead($mBrowser);
}
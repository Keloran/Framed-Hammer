<?php
/**
 * Browser
 *
 * @package
 * @author Keloran
 * @copyright Copyright (c) 2011
 * @version $Id$
 * @access public
 */
trait Browser {
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
			$mGetBrowser = get_browser();
			$bGetBrowser = true;
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
			case "ipad":
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
		$mBrowser 		= false;
		$mGetBrowser	= $this->getBrowserCap();

		//this is a better method
		if ($mGetBrowser) { return $mGetBrowser; }

		//older method
		if (isset($_SERVER['HTTP_USER_AGENT'])) {
			$cBrowser = $_SERVER['HTTP_USER_AGENT'];

			//see if its a mobile device first
			$bMobile	= $this->mobileBrowser($cBrowser, true);

			//Internet Explorer
			if (preg_match("`(ie 7)`i", $cBrowser)) {
				$mBrowser	= array("ie", "ie7");
			} else if (preg_match("`(ie 8)`i", $cBrowser)) {
				$mBrowser	= array("ie", "ie8");
			} else if (preg_match("`(ie 9)`i", $cBrowser)) {
				$mBrowser	= array("ie", "ie9");
			} else if (preg_match("`(msie)`i", $cBrowser)) {
				$mBrowser	= array("ie", "ie6");

			} else if (preg_match("`(firefox)`i", $cBrowser)) { //Firefox
				$mBrowser = "firefox";

			} else if (preg_match("`(ie 7)`i", $cBrowser)) { //Safari
				$mBrowser = "safari";

			} else if (preg_match("`(opera)`i", $cBrowser)) { //opera
				$mBrowser = "opera";
			}


			//if its a mobile device, tell me what it is
			if ($bMobile) { $mBrowser = $this->mobileBrowser($cBrowser); }
		} else {
			$mBrowser = false;
		}

		//specific browser specified
		if ($cSpecific) {
			if ($mBrowser) {
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
		}

		printRead($mBrowser);die();

		return $mBrowser;
	}

	/**
	 * mobileBrowser()
	 *
	 * @param mixed $mBrowser
	 * @return bool
	 */
	function mobileBrowser($mBrowser = false, $bBool = false) {
		if (!$mBrowser) { $mBrowser = $this->getBrowser(); }

		$cReturn	= $mBrowser;
		$bReturn	= false;

		if (preg_match("`(android)`i", $mBrowser)) {
			$cReturn	= "android";
			$bReturn	= true;
		} else if (preg_match("`(iphone)`i", $mBrowser)) {
			$cReturn	= "iphone";
			$bReturn	= true;
		} else if (preg_match("`(ipad)`i", $mBrowser)) {
			$cReturn	= "ipad";
			$bReturn	= true;
		} else if (preg_match("`(MobileIE)`i", $mBrowser)) {
			$cReturn	= "mobileie";
			$bReturn	= true;
		}

		//do we only want to test for, not yet return
		if ($bBool) { return $bReturn; }

		return $cReturn;
	}

	/**
	 * IEBrowser()
	 *
	 * @param mixed $mBrowser
	 * @return bool
	 */
	function IEBrowser($mBrowser = false) {
		return $this->getBrowser("ie");
	}
}
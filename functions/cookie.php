<?php
/**
 * getCookie()
 *
 * @param string $cCookie
 * @return bool
 */
function getCookie($cCookie = null) {
	//Cookies
	if ($cCookie) {
		if (isset($_COOKIE[$cCookie])) { return $_COOKIE[$cCookie]; }
	}

	return false;
}

/**
 * createCookie()
 *
 * @param string $cName
 * @param mixed $mValue
 * @param bool $bForever
 * @param int $iTimeLimit
 * @return null
 */
function createCookie($cName, $mValue, $bForever = false, $iTimeLimit = false) {
	$cServer = "."; //incase there really is nothing

	//Host in server
	if (isset($_SERVER['HTTP_HOST'])) { $cServer = $_SERVER['HTTP_HOST']; }

	//Origin in server
	if (isset($_SERVER['HTTP_ORIGIN'])) {
		$cOrigin = $_SERVER['HTTP_ORIGIN'];
		if (strstr($cOrigin, "http")) { $cOrigin = substr($cOrigin, 7); }

		$cServer = $cOrigin;
	}

	//is the page actually a https
	$bSecure = false;
	if (isset($_SERVER['HTTPS'])) { $bSecure = true; }

	$cServer = "." . $cServer;

	if ($bForever) {
		$iTime  = time() + 2147483647;
	} else {
		if ($iTimeLimit) { //This can allow you to give a timelimit, e.g. 5, will give a timelimit of 5 secnds
			$iTime  = time() + ($iTimeLimit * 60);
		} else {
			$iTime  = time() + 3600;
		}
	}

	if (defined("DEV")) {
		setcookie($cName, $mValue, $iTime, "/");
	} else {
		setcookie($cName, $mValue, $iTime, "/", $cServer, $bSecure);
	}
}

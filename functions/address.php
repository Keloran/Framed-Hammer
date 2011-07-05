<?php

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

/**
 * getParam()
 *
 * @param mixed $mName
 * @return string
 */
function getParam($mName) {
	$cReturn	= false;

	if (isset($_GET[$mName])) {
		$this->cOther	= $_GET[$mName];
		$cReturn		= $_GET[$mName];
	}

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
<?php
/**
 * Address
 *
 * @package
 * @author Keloran
 * @copyright Copyright (c) 2011
 * @version $Id$
 * @access public
 */
trait Address {
	/**
	 * getParam()
	 *
	 * @param mixed $mName
	 * @return string
	 */
	function getParam($mName) {
		$cReturn	= false;
		if (isset($_GET[$mName])) { $cReturn = $_GET[$mName]; }

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
	 * getPost()
	 *
	 * @param string $mName
	 * @return mixed
	 */
	function getPost($mName) {
		$cReturn	= false;
		if (isset($_POST[$mName])) { $cReturn = $_POST[$mName]; }

		return $cReturn;
	}
}
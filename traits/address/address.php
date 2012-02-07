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

	/**
	 * makeSEO()
	 *
	 * @param string $cString
	 * @return string
	 */
	function makeSEO($cString) {
		$aPattern = array(
			//And
			"&amp;",

			//Spaces
			" ",
			"\s",
			"%20"
		);

		$aReplace = array(
			//And
			"and",

			//Spaces
			"_",
			"_",
			"_"
		);

		return strtolower(str_replace($aPattern, $aReplace, $cString));
	}

	/**
	 * unSEO()
	 *
	 * @param string $cString
	 * @return string
	 */
	function unSEO($cString) {
		$aPattern = array(
			//And
			"and",

			//Spaces
			"_",
			"%20",
		);

		$aReplace = array(
			//And
			"&amp;",

			//Spaces
			" ",
			" ",
		);

		return strtolower(str_replace($aPattern, $aReplace, $cString));
	}

	/**
	 * removeQueryVar()
	 *
	 * @param string $cUrl
	 * @param string $cKey
	 * @return string
	 */
	function removeQueryVar($cUrl, $cKey) {
		$cUrl = preg_replace('/(.*)(?|&)' . $cKey . '=[^&]+?(&)(.*)/i', '$1$2$4', $cUrl . '&');
		$cUrl = substr($cUrl, 0, -1);
		return $cUrl;
	}
}
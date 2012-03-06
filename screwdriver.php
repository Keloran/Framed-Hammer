<?php
/**
 * Screwdriver
 *
 * @package
 * @author keloran
 * @copyright Copyright (c) 2011
 * @version $Id$
 * @access public
 */
class Screwdriver {
	use Address;

	//get|set
	private $aData;

	//the filters
	private $aFilters	= array(
			"page",
			"action",
			"choice"
	);

	//the original get data
	private $aGET		= false;
	private $iGET		= false;

	//the number of parameters
	private $iParam		= 1;

	//the number of filters
	private $iFilters	= 0;

	//can you use filter_input
	private $bFilter	= false;

	//the final result of getting the address
	public $aAddress	= false;
	public $aReturn		= false;

	/**
	 * Screwdriver::__construct()
	 *
	 */
	public function __construct($aFilters = false, $cSiteAddress = false) {
		$cSant		= false;
		$cFilt		= false;

		//params
		$jParams	= 1;
		$cName		= false;
		$cName_b	= false;
		$cName_c	= false;
		$cOriginal	= false;

		//can we use filter input, so use it
		if (function_exists("filter_input")) {
			$this->bFilter	= true;
		} else {
			throw new Spanner("You need filter support to use Hammer", 999);
		}

		//are there variables
		if (isset($_GET['variables'])) {
			$cGET		= removeQueryVar($_GET['variables'], "variables");
			$this->aGET	= explode("/", $cGET);
		} else if (isset($_GET['hvars'])) {
			$cGET		= removeQueryVar($_GET['hvars'], "hvars");
			$this->aGET	= explode("/", $cGET);
		} else if (isset($_SERVER['REQUEST_URI'])) {
			$this->aGET	= explode("/", $_SERVER['REQUEST_URI']);
		}

		//get hte number of variables
		$this->iGET	= count($this->aGET);

		/**
		 * if there is http: there is a missing / at the end, so we need to reset
		 * the array to item 3, since thats actually where the start of the real address is
		 */
		if ($this->aGET[0] == "http:") {
			for ($i = 3; $i < $this->iGET; $i++) {
				$aGET_a[]	= $this->aGET[$i];
			}

			//reset it, and get the real count
			$this->aGET	= $aGET_a;
			$this->iGET	= count($this->aGET);
		}

		$this->aReturn['getCount']	= $this->iGET;
		$this->aReturn['fullGet']	= $this->aGET;
		$this->aReturn['realGet']	= $_GET;

		//if no filters are given then set them ourselves
		if ($aFilters) { $this->aFilters = $aFilters; }
		$this->iFilters = count($this->aFilters);

		$this->aReturn['aFilters']	= $this->aFilters;
		$this->aReturn['iFilters']	= $this->iFilters;

		//set the number of params above the standard
		$this->iParam	= (count($this->aFilters) + 1);

		//now actually create the address
		$this->setAddress();

		//now get the brand, or language
		$this->getPrefix($cSiteAddress);
	}

	/**
	 * Screwdriver::getPrefix()
	 *
	 * @param string $cSiteAddress
	 * @return null
	 */
	private function getPrefix($mSiteAddress = false) {
		$cSiteAddress = false;

		// Get the language
		if ($mSiteAddress) {
			if (isset($_SERVER['SERVER_NAME'])) {
				$cServerName	= $_SERVER['SERVER_NAME'];

				//just incase
				if (is_array($mSiteAddress)) {
					$cSiteAddress = $mSiteAddress['address'];
				} else {
					$cSiteAddress = $mSiteAddress;
				}

				$cPattern		= '`([a-z]+).' . $cSiteAddress . '`is';
				preg_match($cPattern, $cServerName, $aMatches);
				if (isset($aMatches[1])) {
					if (strlen($aMatches[1]) >= 3) {
						$aReturn['cBrand']	= filter_var($aMatches[1], FILTER_SANITIZE_URL);

						//Since www isnt a brand
						if ($aReturn['cBrand'] == "www") {
							$aReturn['cBrand']	= false;
						}
					} else {
						$aReturn['cLang']	= filter_var($aMatches[1], FILTER_SANITIZE_URL);
					}
				}
			}
		}

		$this->aReturn['cAddress']	= $cSiteAddress;
	}

	/**
	 * Screwdriver::setAddress()
	 *
	 * @return null
	 */
	private function setAddress() {
		$jParams	= 1;
		$iFiltered	= 0;
		$cName		= false;
		$cName_b	= false;
		$cName_c	= false;
		$cOriginal	= false;
		$iExtras	= 0;

		//go through the params
		for ($i = 0; $i < $this->iGET; $i++) { //the full amount of params
			if (is_numeric($this->aGET[$i])) { //if the param is numeric, then its an iParam
				$cName_b	= "iParam" . $jParams;
				$cName_c	= "param" . $jParams;

				//now if there is iItem set the param name to iPage
				if (isset($this->aReturn['iItem'])) {
					$cName = "iPage";
				} else {
					$cName = "iItem";
				}

				//filters
				$cSant	= FILTER_SANITIZE_NUMBER_INT;
				$cFilt	= FILTER_VALIDATE_INT;
			} else { //its a string, so make it a cParam
				$cName_b = "cParam" . $jParams;
				$cName_c = "param" . $jParams;

				//now go through the filters that are set and create the name otherwise
				foreach ($this->aFilters as $cFilter) {
					$cName = "c" . ucfirst($cFilter);
					if (isset($this->aReturn[$cName])) {
						$iFiltered++;
						continue;
					} else {
						break;
					}
				}

				//filters
				$cSant = FILTER_SANITIZE_URL;
				$cFilt = FILTER_VALIDATE_URL;
			}

			//now add the new ones
			if ($this->iFilters !== $jParams) {
				$this->iParam 	= $jParams;
				$iExtras		= $jParams;
				if ($iFiltered >= ($this->iFilters + 1)) { $jParams++; }
			}

			//filter the items
			$cOriginal = filter_var($this->aGET[$i], $cSant);

			//now go through and create the param names
			if (isset($this->aReturn[$cName])) {
				if ($cSant == FILTER_SANITIZE_NUMBER_INT) {
					$cName = "iParam" . $this->iParam;
				} else {
					$cName = "cParam" . $this->iParam;
				}

				$this->iParam++;
			}

			//seos
			$cSEOName	= $cName 	. "_seo";
			$cParamSEO	= $cName_b	. "_seo";
			$cNonSEO	= $cName_c	. "_seo";

			//uglys
			$cUgly		= $cName 	. "_ugly";
			$cParamUgly	= $cName_b	. "_ugly";
			$cNonUgly	= $cName_c	. "_ugly";

			//Make things seo friendly, only on filter
			$cSEO		= $this->makeSEO($cOriginal);

			//SEO Unfriendly
			$cUnSEO	= $cOriginal;

			//add the items
			$this->aReturn[$cName] 		= $cOriginal;
			$this->aReturn[$cSEOName]	= $cSEO;
			$this->aReturn[$cUgly]		= $cUnSEO;

			//now the extra ones
			$this->aReturn[$cName_b]	= $cOriginal;
			$this->aReturn[$cParamSEO]	= $cSEO;
			$this->aReturn[$cParamUgly]	= $cUnSEO;

			//now non type specific names
			$this->aReturn[$cName_c]	= $cOriginal;
			$this->aReturn[$cNonSEO]	= $cSEO;
			$this->aReturn[$cNonUgly]	= $cUnSEO;
		}

		$this->aReturn['iParams'] 	= $this->iParam;
		$this->aReturn['jParams']	= $jParams;
		$this->aReturn['iExtras']	= $iExtras;

		$this->iParam++;
	}

	/**
	 * Screwdriver::finalAddress()
	 *
	 * @return array
	 */
	public function finalAddress() {
		return $this->aReturn;
	}
}

//address stuff, which might or might not be used

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


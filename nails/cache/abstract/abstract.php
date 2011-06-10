<?php
/**
 * Cache_Abstract
 *
 * @package
 * @author Keloran
 * @copyright Copyright (c) 2011
 * @version $Id$
 * @access public
 */
class Cache_Abstract {
	private $aData;
	private $oNails;

	/**
	 * Cache_Abstract::__get()
	 *
	 * @param string $cName
	 * @return mixed
	 */
	public function __get($cName) {
		if (isset($this->aData[$cName])) { return $this->aData[$cName]; }

		return false;
	}

	/**
	 * Cache_Abstract::__set()
	 *
	 * @param string $cName
	 * @param mixed $mValue
	 * @return null
	 */
	public function __set($cName, $mValue) {
		$this->aData[$cName] = $mValue;
	}

	/**
	 * Cache_Abstract::getParams()
	 *
	 * @param string $cSep
	 * @return string
	 */
	protected function getParams($cSep) {
		$cReturn	= false;
		$iExtra		= isset($this->extraParams) ? ($this->extraParams - 1) : 0;

		if ($iExtra) {
			//get the extra params
			for ($i = 0; $i < $iExtra; $i++) {
				$cParam	= "cParam" . $i;
				$iParam	= "iParam" . $i;

				//cParams
				if (isset($this->$cParam)) { $cReturn .= $this->cParam . $cSep; }
				if (isset($this->$iParam)) { $cReturn .= $this->iParam . $cSep; }
			}
		}

		//remove teh extra ||s
		if ($cReturn) { $cReturn = substr($cReturn, 0, (strlen($cReturn) - 2)); }

		return $cReturn;
	}

	/**
	 * Cache_Abstract::setParams()
	 *
	 * @param mixed $mParams
	 * @return null
	 */
	protected function setParams($mParams) {
		if (is_array($mParams)) {
			foreach ($mParams as $mKey => $mValue) {
				$this->$mKey = $mValue;
			}
		}
	}

	/**
	 * Cache_Abstract::ignoreList()
	 *
	 * @param strng $cUrl
	 * @return bool
	 */
	protected function ignoreList($cUrl) {
		//ignore list, since these things are going to change to often
		$aIgnoreStandard = array(
			"admin",
			"user",
			"webmail",
			"users"
		);

		//get the extra ones as specified by the user
		$cIgnoreExtra	= $this->oNails->getConfig("cacheIgnore");
		$aIgnoreExtra	= array();

		//might have a different seperator
		if (strstr($cIgnoreExtra, "|")) { //pipe delimited
			$aIgnoreExtra	= explode("|", $cIgnoreExtra);
		} else if (strstr($cIgnoreExtra, ",")) { //comma delimited
			$aIgnoreExtra	= explode(",", $cIgnoreExtra);
		} else if (strstr($cIgnoreExtra, ":")) { //colon delimited
			$aIgnoreExtra	= explode(":", $cIgnoreExtra);
		} else if (strstr($cIgnoreExtra, ";")) { //semi-colon delimited
			$aIgnoreExtra	= explode(";", $cIgnoreExtra);
		}

		//now merge them so that the full list can be ignored
		$aIgnore	= array_merge($aIgnoreStandard, $aIgnoreExtra);

		//is the url in the list
		if (in_array($cUrl, $aIgnore)) { return false; }

		return true;
	}

	/**
	 * Cache_Abstract::getItemName()
	 *
	 * @return string
	 */
	protected function getItemName() {
		$cKey		= false;
		$cSep		= "||";

		//if it isnt in ignore list
		if ($this->ignoreList($this->cPage)) {
			$cKey	 = $this->cAddress	. $cSep;
			$cKey	.= $this->cPage		. $cSep;
			$cKey	.= $this->cAction	. $cSep;
			$cKey	.= $this->cChoice	. $cSep;
			$cKey	.= $this->iItem		. $cSep;
			$cKey	.= $this->iPage		. $cSep;
			$cKey	.= $this->getParams($cSep);

			//add the userid
			$cKey	.= "userid=" . $this->iUserID;
		}

		return $cKey;
	}


}
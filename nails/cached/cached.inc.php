<?php
/**
 * Cached
 *
 * @package
 * @author keloran
 * @copyright Copyright (c) 2010
 * @version $Id$
 * @access public
 */
class Cached implements Nails_Interface {
	static $oCached;

	private $oCache;
	private $oNails;

	private $aData;
	private $iUserID;
	private $bUseCache;

	/**
	 * Cached::getInstance()
	 *
	 * @param Nails $oNails
	 * @param mixed $mParams
	 * @return object
	 */
	public static function getInstance(Nails $oNails, $mParams = false) {
		if (is_null(self::$oCached)) {
			self::$oCached = new Cached($oNails, $mParams);
		}

		return self::$oCached;
	}

	/**
	 * Cached::__construct()
	 *
	 * @param Nails $oNails
	 * @param mixed $mParams
	 */
	private function __construct(Nails $oNails, $mParams = false) {
		//get the cache setting
		$this->bUseCache = false;
		$cCache	= $oNails->getConfig("cacheSetting");
		if ($cCache == "on") {
			//always turn off on login, and register page
			switch ($oNails->cPage){
				case "login":
				case "register":
					$this->bUseCache	= false;
					break;

				default:
					$this->bUseCache 	= true;
					break;
			} // switch
		}

		//should we even use cache
		if ($this->bUseCache) {
			//does memcache actually exist
			if (function_exists("memcache_connect")) {
				$this->oCache	= new Memcache;
				$this->oCache->addServer("localhost");
			}
		}

		$this->oNails	= $oNails;

		//get the userid
		$oUser				= $oNails->getUser();
		$this->iUserID		= $oUser->getUserID();

		$this->setParams($mParams);
	}

	/**
	 * Cached::addItem()
	 *
	 * @param string $cItem
	 * @return string
	 */
	public function addItem($cItem, $iTime = null) {
		$cSep		= "||";
		$cKey		= false;
		$bWorked	= false;

		if ($iTime) {
			$iTime		= (time() + ($iTime * 3600));
		} else {
			$iTime		= (time() + 259200);
		}

		//if it isnt in the ignore list
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


		//do we even bother if cache is turned off
		if ($this->bUseCache) {
			//if there is a key, and there is a cache
			if ($this->oCache && $cKey) {
				$bWorked	= $this->oCache->set($cKey, $cItem, MEMCACHE_COMPRESSED, $iTime);
			}

			//worked came back as false so update instead
			if ($bWorked) {
				$this->oCache->replace($cKey, $cItem, MEMCACHE_COMPRESSED, $iTime);
			}
		}
	}

	/**
	 * Cached::getParams()
	 *
	 * @param string $cSep
	 * @return string
	 */


	/**
	 * Cached::getItem()
	 *
	 * @return string
	 */
	public function getItem() {
		$cReturn	= false;
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

		//cant do this if cache isnt in use
		if ($this->bUseCache) {
			if ($this->oCache && $cKey) { $cReturn = $this->oCache->get($cKey); }
		}

		return $cReturn;
	}

	/**
	 * Cached::ignoreList()
	 *
	 * @return string
	 */
	private function ignoreList($cUrl) {
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
	 * Cached::__set()
	 *
	 * @param string $cKey
	 * @param mixed $cValue
	 * @return null
	 */
	public function __set($cKey, $mValue) {
		$this->aData[$cKey]	= $mValue;
	}

	/**
	 * Cached::__get()
	 *
	 * @param string $cKey
	 * @return mixed
	 */
	public function __get($cKey) {
		$mReturn	= false;

		if (isset($this->aData[$cKey])) {
			$mReturn	= $this->aData[$cKey];
		}

		return $mReturn;
	}

	/**
	 * Cached::setParams()
	 *
	 * @param mixed $mParams
	 * @return null
	 */


	/**
	 * Cached::getStats()
	 *
	 * @return mixed
	 */
	public function getStats() {
		$mReturn	= false;

		//cant do this cache isnt in use
		if ($this->bUseCache) {
			if ($this->oCache) { $mReturn	= $this->oCache->getExtendedStats(); }
		}

		return $mReturn;
	}

	/**
	 * Cached::wipeAll()
	 *
	 * @return null
	 */
	public function wipeAll() {
		//cant do this if cache isnt in use
		if ($this->bUseCache) {
			if ($this->oCache) { $this->oCache->flush(); }
		}
	}
}
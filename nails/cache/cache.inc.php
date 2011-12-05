<?php
/**
 * Cache
 *
 * @package
 * @author Keloran
 * @copyright Copyright (c) 2011
 * @version $Id$
 * @access public
 */
class Cache extends Cache_Abstract {
	static $oCached;
	private $oCache;

	/**
	 * Cache::getInstance()
	 *
	 * @param Nails $oNails
	 * @param mixed $mParams
	 * @return object
	 */
	public static function getInstance(Nails $oNails, $mParams = false) {
		if (is_null(self::$oCached)) {
			self::$oCached = new Cache($oNails, $mParams);
		}

		return self::$oCached;
	}

	/**
	 * Cache::__construct()
	 *
	 * @param Nails $oNails
	 * @param mixed $mParams
	 */
	private function __construct(Nails $oNails, $mParams = false) {
		//get the cache setting
		$this->bUseCache	= false;
		$cCache				= $oNails->getConfig("cacheSetting");
		if ($cCache == "on") { $this->bUseCache = true; }

		//Nails
		$this->oNails		= $oNails;

		//should we even use cache
		if ($this->bUseCache) {
			//do we have cacheType
			$cCacheType	= $oNails->getConfig("cacheType");
			if ($cCacheType == "file") {
				$this->oCache	= new Cache_File($this->oNails);
			} else {
				//does memcache actually exist
				if (function_exists("memcache_connect")) {
					$this->oCache	= new Cache_Memory($this->oNails);
				} else { //memcache doesnt exist, and no cachetype given, so use files
					$this->oCache	= new Cache_File($this->oNails);
				}
			}

			$this->oNails	= $oNails;

			//get the userid
			$oUser				= $oNails->getUser();
			$this->iUserID		= $oUser->getUserID();

			$this->setParams($mParams);
			$this->setTime();
		}
	}

	/**
	 * Cache::addItem()
	 *
	 * @param string $cItem
	 * @param int $iItem
	 * @return mixed
	 */
	public function addItem($cItem, $iTime = false) {
		if ($this->oCache) {
			return $this->oCache->addItem($cItem, $iTime = false);
		}

		return false;
	}

	/**
	 * Cache::getItem()
	 *
	 * @return mixed
	 */
	public function getItem() {
		if ($this->oCache) {
			return $this->oCache->getItem();
		}

		return false;
	}

	/**
	 * Cache::setTime()
	 *
	 * @return null
	 */
	public function setTime() {
		$iTime	= $this->oNails->getConfig("cacheTime");

		if (defined("CACHETIME")) {
			$this->iTime = time() - CACHETIME;
		} else if ($iTime) {
			$this->iTime = time() - $iTime;
		} else {
			$this->iTime = time() - 3600;
		}
	}
}

/**
 * Cache_Exception
 *
 * @package
 * @author keloran
 * @copyright Copyright (c) 2011
 * @version $Id$
 * @access public
 */
class Cache_Exception extends Exception {}
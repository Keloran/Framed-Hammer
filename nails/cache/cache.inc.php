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
			self::$oCached = new Cached($oNails, $mParams);
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
				$this->oCache	= new Cache_Memory($oNails);

				$this->oCache	= new Memcache;
				$this->oCache->addServer("localhost");
			} else {
				$this->oCache	= new Cache_File($oNails);
			}
		}

		$this->oNails	= $oNails;

		//get the userid
		$oUser				= $oNails->getUser();
		$this->iUserID		= $oUser->getUserID();

		$this->setParams($mParams);
	}
}
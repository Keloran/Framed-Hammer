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
class Cache_Memory extends Cache_Abstract {
	private $oCache;

	/**
	 * Cached::__construct()
	 *
	 * @param Nails $oNails
	 * @param mixed $mParams
	 */
	public function __construct() {
		$this->oCache	= new Memcache();
		$this->oCache->addServer("localhost");
	}

	/**
	 * Cached::addItem()
	 *
	 * @param string $cItem
	 * @return string
	 */
	public function addItem($cItem, $iTime = null) {
		$cKey		= $this->addItemName();
		$bWorked	= false;

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
	 * Cached::getItem()
	 *
	 * @return string
	 */
	public function getItem() {
		$cReturn	= false;
		$cKey		= $this->getItemName();

		//cant do this if cache isnt in use
		if ($this->bUseCache) {
			if ($this->oCache && $cKey) { $cReturn = $this->oCache->get($cKey); }
		}

		return $cReturn;
	}

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
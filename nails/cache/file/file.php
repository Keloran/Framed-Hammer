<?php
/**
 * Cache
 *
 * @package Cache
 * @author Keloran
 * @copyright Copyright (c) 2010
 * @version $Id: cache.inc.php 3588 2011-04-14 09:08:04Z keloran $
 * @access public
 */
class Cache_File extends Cache_Abstract {
	private $cCache;
	private $bCached;

	/**
	 * Cache::__construct()
	 *
	 * @param Nails $oNails
	 */
	function __construct(Nails $oNails) {
		$cCache			= $this->getItemName();
		$cCache			= str_replace(":", "_", $cCache);
		$this->cFile	= $cCache . ".cache";
		$this->oNails	= $oNails;
	}

	/**
	 * Cache::endCache()
	 *
	 * @return
	 */
	public function endCache() {
		$this->oNails		= false;
		$this->bUseCache	= false;
	}

	/**
	 * Cache::getCache()
	 *
	 * @return string
	 */
	public function getCache() {
		$pCache		= fopen($this->cCache, "r");
		$cReturn	= fread($pCache, 4096);
		fclose($pCache);

		return $cReturn;
	}

	/**
	 * Cache::setCache()
	 *
	 * @param string $cCache
	 * @return null
	 */
	public function setCache($cCache) {
		if (!defined("CACHE")) { return false; }

		$this->writeCache($cCache);
	}

	/**
	 * Cache_File::addItem()
	 *
	 * @param string $cCache
	 * @param int $iTime
	 * @return null
	 */
	public function addItem($cCache, $iTime = false) {
		if (file_exists($this->cFile)) {
			if (filemtime($this->cFile) <= $this->iTime) {
				$pCache	= fopen($this->cCache, "w");
				fwrite($pCache, $cCache);
				fclose($pCache);
			}
		}
	}

	/**
	 * Cache_File::getItem()
	 *
	 * @return string
	 */
	public function getItem() {
		$cKey		= $this->getItemName();
		$pCache		= fopen($this->cFile, "r");
		$cReturn	= fRead($this->cFile);
		fclose($pCache);

		return $cReturn;
	}
}
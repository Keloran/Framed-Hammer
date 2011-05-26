<?php
/**
 * Cache
 *
 * @package
 * @author Keloran
 * @copyright Copyright (c) 2010
 * @version $Id: cache.inc.php 3588 2011-04-14 09:08:04Z keloran $
 * @access public
 */
class Cache implements Nails_Interface {
	private $oNails;

	private $cPage;
	private $cAction;
	private $cChoice;
	private $cCache;
	private $iExtras;

	public $bUseCache;

	private static $oCache;

	/**
	 * Cache::__construct()
	 *
	 * @param Nails $oNails
	 */
	function __construct(Nails $oNails) {
		$this->oNails	= $oNails;

		if (defined("CACHE")) {
			$this->cPage 		= $oNails->cPage;
			$this->cAction		= $oNails->cAction;
			$this->cChoice		= $oNails->cChoice;
			$this->iExtras		= $oNails->extraParams;
		}
	}

	/**
	 * Cache::getInstance()
	 *
	 * @param Nails $oNails
	 * @return object
	 */
	static function getInstance(Nails $oNails) {
		if (is_null(self::$oCache)) {
			self::$oCache	= new Cache($oNails);
		}

		return self::$oCache;
	}

	/**
	 * Cache::startCache()
	 *
	 * @return
	 */
	function startCache() {
		if (!defined("CACHE")) { return false; }

		//name of the cache file
		$cCacheFile	= CACHE;

		$cCacheName	= false;
		if ($this->cPage) { 	$cCacheName	.= $this->cPage; }
		if ($this->cAction) {	$cCacheName	.= "_" . $this->cAction; }
		if ($this->cChoice) {	$cCacheName .= "_" . $this->cChoice; }

		//lots of subpages
		if ($this->iExtras) {
			for ($i = 0; $i < $this->iExtras; $i++) {
				$cCacheName	.= "_" . $this->cParam . $i;
			}
		}

		//its the front page
		if (!$cCacheName) {
			$cCacheFile .= "frontPage";
		} else {
			$cCacheFile .= $cCacheName;
		}

		$cCacheFile .= ".cache";

		//set the cachefile
		$this->cCache	= $cCacheFile;

		//time
		if (defined("CACHETIME")) {
			$iTime = time() - CACHETIME;
		} else {
			$iTime = time() - 3600;
		}

		//the file exists so use that instead
		if (file_exists($cCacheFile)) {
			if (filemtime($cCacheFile) >= $iTime) {
				$this->bUseCache = true;
			}
		}
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
	 * Cache::writeCache()
	 *
	 * @param string $cCache
	 * @return null
	 */
	private function writeCache($cCache) {
		$pCache	= fopen($this->cCache, "w");
		fwrite($pCache, $cCache);
		fclose($pCache);
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
}
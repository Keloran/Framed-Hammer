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
	static $oCached;
	private $oCache;

	/**
	 * Cached::__construct()
	 *
	 * @param Nails $oNails
	 * @param mixed $mParams
	 */
	public function __construct(Nails $oNails, $mParams = false) {
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
	private function getParams($cSep) {
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
	private function setParams($mParams) {
		if (is_array($mParams)) {
			foreach ($mParams as $mKey => $mValue) {
				$this->$mKey = $mValue;
			}
		}
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
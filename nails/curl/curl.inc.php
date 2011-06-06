<?php
class Curl {
	const iTimeout = 3;

	static $oCurl 		= null;

	private $pMc;
	private $msgs;
	private $running;
	private $aRequests		= array();
	private $aResponses		= array();
	private $aProperties	= array();

	private $oNails;

	/**
	 * Curl::__construct()
	 *
	 */
	public function __construct() {
		$this->pMc 		= curl_multi_init();
		$this->aProperties	= array(
			'code'  => CURLINFO_HTTP_CODE,
			'time'  => CURLINFO_TOTAL_TIME,
			'length'=> CURLINFO_CONTENT_LENGTH_DOWNLOAD,
			'type'  => CURLINFO_CONTENT_TYPE
		);
	}

	/**
	 * Curl::addCurl()
	 *
	 * @param string $cCh
	 * @return object
	 */
	public function addCurl($cCh) {
		$cKey 					= (string)$cCh;
		$this->aRequests[$cKey] = $cCh;
		$pRes 					= curl_multi_add_handle($this->pMc, $cCh);

		// (1)
		if($pRes === CURLM_OK || $pRes === CURLM_CALL_MULTI_PERFORM) {
			do {
				$pMrc = curl_multi_exec($this->pMc, $bActive);
			} while ($pMrc === CURLM_CALL_MULTI_PERFORM);

			return new Curl_Manager($cKey);
		} else {
			return $pRes;
		}
	}

	/**
	 * Curl::getResult()
	 *
	 * @param string $cKey
	 * @return mixed
	 */
	public function getResult($cKey = null) {
		if($cKey != null) {

			if(isset($this->aResponses[$cKey])) { return $this->aResponses[$cKey]; }

			$bRunning = null;
			do {
				$pResp 	= curl_multi_exec($this->pMc, $bRunningCurrent);
				if($bRunning !== null && $bRunningCurrent != $bRunning) {
					$this->storeResponses($cKey);

					if(isset($this->aResponses[$cKey])) { return $this->aResponses[$cKey]; }
				}
				$bRunning = $bRunningCurrent;
			} while($bRunningCurrent > 0);
		}

		return false;
	}

	/**
	 * Curl::storeResponses()
	 *
	 * @return null
	 */
	private function storeResponses() {
		while($aDone = curl_multi_info_read($this->pMc)) {
			$cKey 								= (string)$aDone['handle'];
			$this->aResponses[$cKey]['data']	= curl_multi_getcontent($aDone['handle']);

			foreach($this->aProperties as $cName => $cConst) {
				$this->aResponses[$cKey][$cName] = curl_getinfo($aDone['handle'], $cConst);
				curl_multi_remove_handle($this->pMc, $aDone['handle']);
			}
		}
	}

	/**
	 * Curl::getInstance()
	 *
	 * @return object
	 */
	static public function getInstance() {
		if(self::$oCurl == null) {
			self::$oCurl = new Curl();
		}

		return self::$oCurl;
	}
}
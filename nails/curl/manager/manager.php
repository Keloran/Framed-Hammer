<?php
/**
 * Curl_Manager
 *
 * @package Curl
 * @author Keloran
 * @copyright Copyright (c) 2011
 * @version $Id$
 * @access public
 */
class Curl_Manager {
	private $cKey;
	private $oCurl;

	/**
	 * Curl_Manager::__construct()
	 *
	 * @param string $cKey
	 */
	function __construct($cKey) {
		$this->cKey = $cKey;
		$this->oCurl = Curl::getInstance();
	}

	/**
	 * Curl_Manager::__get()
	 *
	 * @param string $cName
	 * @return mixed
	 */
	function __get($cName) {
		$aResponses = $this->oCurl->getResult($this->cKey);
		return $aResponses[$cName];
	}
}
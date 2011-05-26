<?php
/**
 * Twitter_JSON
 *
 * @package
 * @author Keloran
 * @copyright Copyright (c) 2011
 * @version $Id$
 * @access public
 */
class Twitter_JSON {
	private $oResp;

	/**
	 * Twitter_JSON::__construct()
	 *
	 * @param object $oResp
	 */
	public function __construct($oResp) {
		$this->oResp = $oResp;
	}

	/**
	 * Twitter_JSON::__get()
	 *
	 * @param string $cName
	 * @return mixed
	 */
	public function __get($cName) {
		$this->cResponseText	= $this->oResp->data;
		$this->aResponse		= (array)json_decode($this->cResponseText, 1);
		foreach($this->aResponse as $cKey => $mValue) {
			$this->$cKey = $mValue;
		}

		return $this->$cName;
	}
}
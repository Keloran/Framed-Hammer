<?php
/**
 * Oauth_Response
 *
 * @package
 * @author Keloran
 * @copyright Copyright (c) 2011
 * @version $Id$
 * @access public
 */
class Oauth_Response {
	private $oResponse;

	/**
	 * Oauth_Response::__construct()
	 *
	 * @param object $oResp
	 */
	public function __construct($oResp) {
		$this->oResponse = $oResp;
	}

	/**
	 * Oauth_Response::__get()
	 *
	 * @param string $cName
	 * @return mixed
	 */
	public function __get($cName) {
		if($this->oResponse->code < 200 || $this->oResponse->code > 299) { return false; }

		parse_str($this->oResponse->data, $aResult);
		foreach($aResult as $cKey => $mValue) {
			$this->$cKey = $mValue;
		}

		return $aResult[$cName];
	}
}
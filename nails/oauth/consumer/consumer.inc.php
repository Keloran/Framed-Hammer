<?php
/**
 * Oauth_Consumer
 *
 * @package
 * @author keloran
 * @copyright Copyright (c) 2010
 * @version $Id$
 * @access public
 */
class Oauth_Consumer {
	public $cKey;
	public $cSecret;
	public $cCallBack;

	/**
	 * Oauth_Consumer::__construct()
	 *
	 * @param string $cKey
	 * @param string $cSecret
	 * @param string $cCallBack
	 */
	function __construct($cKey, $cSecret, $cCallBack = null) {
		$this->cKey			= $cKey;
		$this->cSecret		= $cSecret;
		$this->cCallBack	= $cCallBack;
	}

	function __toString() {
		return "OAuthConsumer[key=" . $this->cKey . ", secret=" . $this->cSecret . "]";
	}
}
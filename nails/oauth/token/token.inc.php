<?php
/**
 * Oauth_Token
 *
 * @package
 * @author keloran
 * @copyright Copyright (c) 2010
 * @version $Id$
 * @access public
 */
class Oauth_Token {
	public $cKey;
	public $cSecret;

	/**
	 * Oauth_Token::__construct()
	 *
	 * @param string $cKey
	 * @param string $cSecret
	 */
	function __construct($cKey, $cSecret) {
		$this->cKey		= $cKey;
		$this->cSecret	= $cSecret;
	}

	private function to_string() {
		$cReturn	 = "oauth_token=" . Oauth_Util::urlEncodeRFC3986($this->cKey);
		$cReturn	.= "&auth_token_secret=" . Oauth_Util::urlEncodeRFC3986($this->cSecret);

		return $cReturn;
	}
}
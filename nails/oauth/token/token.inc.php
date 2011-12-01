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
class oauth_Token {
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

	/**
	 * Oauth_Token::to_string()
	 *
	 * @return string
	 */
	private function to_string() {
		$cReturn	 = "oauth_token=" . oauth_Util::urlEncodeRFC3986($this->cKey);
		$cReturn	.= "&auth_token_secret=" . oauth_Util::urlEncodeRFC3986($this->cSecret);

		return $cReturn;
	}

	/**
	 * Oauth_Token::__toString()
	 *
	 * @return string
	 */
	function __toString() {
		return $this->to_string();
	}
}
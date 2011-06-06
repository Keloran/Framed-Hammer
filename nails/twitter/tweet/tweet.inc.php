<?php
/**
 * Twitter_Tweet
 *
 * @package
 * @author Keloran
 * @copyright Copyright (c) 2011
 * @version $Id$
 * @access public
 */
class Twitter_Tweet extends OAuth {
	const TWITTER_SIGNATURE_METHOD		= 'HMAC-SHA1';
	public $cRequestTokenUrl 		= 'http://twitter.com/oauth/request_token';
	public $cAccessTokenUrl 			= 'http://twitter.com/oauth/access_token';
	public $cAuthorizeUrl 			= 'http://twitter.com/oauth/authorize';
	public $cApiUrl 					= 'http://twitter.com';

	private $oAuth	= false;

	/**
	 * Twitter_Tweet::__call()
	 *
	 * @param string $cName
	 * @param array $aParams
	 * @return object
	 */
	public function __call($cName, $aParams = null) {
		$aParts  = explode('_', $cName);
		$cMethod = strtoupper(array_shift($aParts));
		$cParts  = implode('_', $aParts);
		$cUrl    = $this->cApiUrl . '/' . preg_replace('/[A-Z]|[0-9]+/e', "'/'.strtolower('\\0')", $cParts) . '.json';

		$aArgs	= false;
		if(!empty($aParams)) { $aArgs = array_shift($aParams); }

		return new Twitter_JSON(call_user_func(array($this, 'httpRequest'), $cMethod, $cUrl, $aArgs));
	}

	/**
	 * Twitter_Tweet::__construct()
	 *
	 * @param string $cConsumerKey
	 * @param string $cConsumerSecret
	 * @param string $cOauthToken
	 * @param string $cOauthTokenSecret
	 */
	public function __construct($cConsumerKey = null, $cConsumerSecret = null, $cOauthToken = null, $cOauthTokenSecret = null) {
		parent::__construct($cConsumerKey, $cConsumerSecret, self::TWITTER_SIGNATURE_METHOD);
		$this->setToken($cOauthToken, $cOauthTokenSecret);
	}
}
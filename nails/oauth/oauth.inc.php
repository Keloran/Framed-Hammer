<?php
/**
 * Oauth
 *
 * @package
 * @author Keloran
 * @copyright Copyright (c) 2011
 * @version $Id$
 * @access public
 */
class OAuth {
	public $fVersion = '1.0';

	public $cRequestTokenUrl;
	public $cAccessTokenUrl;
	public $cAuthorizeUrl;
	public $cConsumerKey;
	public $cConsumerSecret;
	public $cToken;
	public $cTokenSecret;
	public $cSignatureMethod;

	private $pCh;

	/**
	 * Oauth::getAccessToken()
	 *
	 * @return object
	 */
	public function getAccessToken() {
		$oResp 		= $this->httpRequest('GET', $this->cAccessTokenUrl);
		$oReturn	= new Oauth_Response($oResp);

		return $oReturn;
	}

	/**
	 * Oauth::getAuthorizationUrl()
	 *
	 * @return string
	 */
	public function getAuthorizationUrl() {
		$cRetval = "{$this->cAuthorizeUrl}?";
		$oToken = $this->getRequestToken();
		return $this->cAuthorizeUrl . '?oauth_token=' . $oToken->oauth_token;
	}

	/**
	 * Oauth::getRequestToken()
	 *
	 * @return object
	 */
	public function getRequestToken() {
		$oResp = $this->httpRequest('GET', $this->cRequestTokenUrl);
		return new Oauth_Response($oResp);
	}

	/**
	 * Oauth::httpRequest()
	 *
	 * @param string $cMethod
	 * @param string $cUrl
	 * @param string $aParams
	 * @return mixed
	 */
	public function httpRequest($cMethod = null, $cUrl = null, $aParams = null) {
		if(empty($cMethod) || empty($cUrl)) { return false;}

		if(empty($aParams['oauth_signature'])) { $aParams = $this->prepareParameters($cMethod, $cUrl, $aParams); }

		switch($cMethod) {
			case 'GET':
				return $this->httpGet($cUrl, $aParams);
				break;
			case 'POST':
				return $this->httpPost($cUrl, $aParams);
				break;
		}
	}

	/**
	 * Oauth::setToken()
	 *
	 * @param string $cToken
	 * @param string $cSecret
	 * @return
	 */
	public function setToken($cToken = null, $cSecret = null) {
		//$aParams = func_get_args();
		$this->cToken 		= $cToken;
		$this->cTokenSecret	= $cSecret;
	}

	/**
	 * Oauth::encode()
	 *
	 * @param string $cString
	 * @return string
	 */
	public function encode($cString) {
		return rawurlencode(utf8_encode($cString));
	}

	/**
	 * Oauth::addOAuthHeaders()
	 *
	 * @param pointer $cCh
	 * @param string $cUrl
	 * @param array $aHeaders
	 * @return null
	 */
	protected function addOAuthHeaders($cUrl, $aHeaders) {
		$aHeader		= array('Expect:');
		$aUrlParts		= parse_url($cUrl);
		$cOauth			= 'Authorization: OAuth realm="' . $aUrlParts['path'] . '",';

		//go through the headers and set the headers to a single line
		foreach($aHeaders as $cName => $mValue) {
			$cOauth .= "{$cName}=\"{$mValue}\",";
		}
		$aHeader[] = substr($cOauth, 0, -1);
		curl_setopt($this->pCh, CURLOPT_HTTPHEADER, $aHeader);
	}

	/**
	 * Oauth::generateNonce()
	 *
	 * @return string
	 */
	protected function generateNonce() {
		// for unit testing
		if(isset($this->cNonce)) { return $this->cNonce; }

		return md5(uniqid(rand(), true));
	}

	/**
	 * Oauth::generateSignature()
	 *
	 * @param string $cMethod
	 * @param string $cUrl
	 * @param array $aParams
	 * @return string
	 */
	protected function generateSignature($cMethod = null, $cUrl = null, $aParams = null) {
		if(empty($cMethod) || empty($cUrl)) { return false; }

		// concatenating
		$cConcatenatedParams = '';
		foreach($aParams as $cKey => $cValue) {
			$cValue 				 = $this->encode($cValue);
			$cConcatenatedParams	.= "{$cKey}={$cValue}&";
		}
		$cConcatenatedParams = $this->encode(substr($cConcatenatedParams, 0, -1));

		// normalize url
		$cNormalizedUrl = $this->encode($this->normalizeUrl($cUrl));
		$method			= $this->encode($cMethod); // don't need this but why not?

		$cSignatureBaseString = "{$cMethod}&{$cNormalizedUrl}&{$cConcatenatedParams}";

		$cReturn = $this->signString($cSignatureBaseString);

		return $cReturn;
	}

	/**
	 * Oauth::httpGet()
	 *
	 * @param string $cUrl
	 * @param array $aParams
	 * @return object
	 */
	protected function httpGet($cUrl, $aParams = null) {
		if(count($aParams['request']) > 0) {
			$cUrl .= '?';
			foreach($aParams['request'] as $cKey => $cValue) {
				$cUrl .= "{$cKey}={$cValue}&";
			}
			$cUrl = substr($cUrl, 0, -1);
		}
		$this->pCh = curl_init($cUrl);
		$this->addOAuthHeaders($cUrl, $aParams['oauth']);
		curl_setopt($this->pCh, CURLOPT_RETURNTRANSFER, true);
		$oResp  = $this->oCurl->addCurl($this->pCh);

		return $oResp;
	}

	/**
	 * Oauth::httpPost()
	 *
	 * @param string $cUrl
	 * @param array $aParams
	 * @return object
	 */
	protected function httpPost($cUrl, $aParams = null) {
		$this->pCh = curl_init($cUrl);
		$this->addOAuthHeaders($cUrl, $aParams['oauth']);
		curl_setopt($this->pCh, CURLOPT_POST, 1);
		curl_setopt($this->pCh, CURLOPT_POSTFIELDS, http_build_query($aParams['request']));
		curl_setopt($this->pCh, CURLOPT_RETURNTRANSFER, true);
		$oResp  = $this->oCurl->addCurl($this->pCh);
		return $oResp;
	}

	/**
	 * Oauth::normalizeUrl()
	 *
	 * @param string $cUrl
	 * @return string
	 */
	protected function normalizeUrl($cUrl = null) {
		$cReturn		= false;

		$aUrlParts		= parse_url($cUrl);
		$cScheme		= strtolower($aUrlParts['scheme']);
		$cHost  		= strtolower($aUrlParts['host']);
		$iPort 			= (isset($aUrlParts['port']) ? intval($aUrlParts['port']) : 80);

		$cReturn = "{$cScheme}://{$cHost}";

		//get port
		if($iPort > 0 && ($cScheme === 'http' && $iPort !== 80) || ($cScheme === 'https' && $iPort !== 443)) { 	$cReturn .= ":{$iPort}"; }

		$cReturn .= $aUrlParts['path'];
		if(!empty($aUrlParts['query'])) { $cReturn .= "?{$aUrlParts['query']}"; }

		return $cReturn;
	}

	/**
	 * Oauth::prepareParameters()
	 *
	 * @param string $cMethod
	 * @param string $cUrl
	 * @param array $aParams
	 * @return array
	 */
	protected function prepareParameters($cMethod = null, $cUrl = null, $aParams = null) {
		if(empty($cMethod) || empty($cUrl)) { return false; }

		$aOauth['oauth_consumer_key']		= $this->cConsumerKey;
		$aOauth['oauth_token'] 				= $this->cToken;
		$aOauth['oauth_nonce'] 				= $this->generateNonce();
		$aOauth['oauth_timestamp'] 			= !isset($this->iTimestamp) ? time() : $this->iTimestamp; // for unit test
		$aOauth['oauth_signature_method']	= $this->cSignatureMethod;
		$aOauth['oauth_version'] 			= $this->fVersion;

		// encoding
		array_walk($aOauth, array($this, 'encode'));
		if(is_array($aParams)) { array_walk($aParams, array($this, 'encode')); }
		$aEncodedParams = array_merge($aOauth, (array)$aParams);

		// sorting
		ksort($aEncodedParams);

		// signing
		$aOauth['oauth_signature'] = $this->encode($this->generateSignature($cMethod, $cUrl, $aEncodedParams));

		//return array
		$aReturn	= array('request' => $aParams, 'oauth' => $aOauth);

		return $aReturn;
	}

	/**
	 * Oauth::signString()
	 *
	 * @param string $cString
	 * @return string
	 */
	protected function signString($cString = null) {
		$cReturn = false;
		switch($this->cSignatureMethod) {
			case 'HMAC-SHA1':
				$cKey 		= $this->encode($this->cConsumerSecret) . '&' . $this->encode($this->cTokenSecret);
				$cReturn	= base64_encode(hash_hmac('sha1', $cString, $cKey, true));
				break;
		}

		return $cReturn;
	}

	/**
	 * Oauth::__construct()
	 *
	 * @param string $cConsumerKey
	 * @param string $cConsumerSecret
	 * @param string $cSignatureMethod
	 */
	public function __construct($cConsumerKey, $cConsumerSecret, $cSignatureMethod = 'HMAC-SHA1') {
		$this->cConsumerKey			= $cConsumerKey;
		$this->cConsumerSecret		= $cConsumerSecret;
		$this->cSignatureMethod		= $cSignatureMethod;
		$this->oCurl				= Curl::getInstance();
	}
}
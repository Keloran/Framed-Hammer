<?php
class Twitter implements Nails_Interface {
	private $oUser		= false;
	private $oNails		= false;
	private $oTwit		= false;

	//login details
	private $cUsername			= false;
	private $cPassword			= false;
	private $cConsumerKey		= "SmdcqSqTA4gRU8heJftdg";
	private $cConsumerSecret	= "GI4lY0NH9VXj86toje2FwIlfEEyeS4vryXECQNLaI";

	//tokens
	private $aAccessToken	= false;
	private $aRequestToken	= false;

	private static $oXML;
	private static $oTwitter;

	/**
	 * Twitter::getInstance()
	 *
	 * @return object
	 */
	static function getInstance(Nails $oNails) {
		if (is_null(self::$oTwitter)) {
			self::$oTwitter = new Twitter($oNails);
		}

		return self::$oTwitter;
	}

	/**
	* Twitter::getUsername()
	*
	* @return string
	*/
	public function getUsername() {
		return $this->cTwitterUsername;
	}

	/**
	 * Twitter::__construct()
	 *
	 */
	function __construct(Nails $oNails) {
		$this->oNails	= $oNails;
		$this->oUser	= $this->oNails->getUser();
		#$this->oTwit	= new Twitter_Tweet($this->cConsumerKey, $this->cConsumerToken);

		//Get the config
		$aConfig				= $this->oNails->getConfig("twitter");
		printRead($aConfig);die();

		$this->cUsername		= $aConfig['username'];
		$this->cPassword		= $aConfig['password'];

		if ($this->oNails->checkInstalled("twitter") == false) {
			$this->install();
		}

		if ($this->oNails->checkVersion("twitter", "1.5") == false) {
			//1.1
			$cSQL	= "ALTER TABLE `twitter` ADD COLUMN `iFollowers` INT NOT NULL DEFAULT 0";
			$this->oNails->updateVersion("twitter", "1.1", $cSQL);

			//1.2
			$cSQL	= "ALTER TABLE `twitter` ADD COLUMN `iUpdates` INT NOT NULL DEFAULT 0";
			$this->oNails->updateVersion("twitter", "1.2", $cSQL);

			//1.3
			$cSQL	= "ALTER TABLE `twitter` ADD COLUMN `iFollowing` INT NOT NULL DEFAULT 0";
			$this->oNails->updateVersion("twitter", "1.3", $cSQL);

			//1.4
			$this->oNails->updateVersion("twitter", "1.4", false, "Moved to using XML to store Twitter locally");

			//1.5
			$this->oNails->updateVersion("twitter", "1.5", false, "Moved to use oAuth");
		}
	}

	/**
	 * Twitter::install()
	 *
	 * @return null
	 */
	private function install() {
		//since admins can post tweets
		$this->oNails->addGroups("postTweet");
		$this->oNails->addAbility("admin", "postTweet");

		//now add the version to the xml so that it can be checked
		$this->oNails->addVersion("twitter", "1.0");

		$this->oNails->sendLocation("install");
	}

	/**
	 * Twitter::getFeedFile()
	 *
	 * @return array
	 */
	private function getFeedFile($cType) {
		if (!$this->cUsername) { return false; }

		//Get the XML from Twitter
		$oCurl = curl_init();
		curl_setopt($oCurl, CURLOPT_URL, "http://api.twitter.com/1/statuses/" . $cType . ".xml");
		curl_setopt($oCurl, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($oCurl, CURLOPT_HEADER, 0);
		curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($oCurl, CURLOPT_USERPWD, $this->cUsername . ':' . $this->cPassword);
		curl_setopt($oCurl, CURLOPT_POST, false);

		// Lets Send the Data
		$aData = curl_exec($oCurl);

		return $aData;
	}

	/**
	 * Twitter::getFeed()
	 *
	 * @return object
	 */
	private function getFeed($cType) {
		if (file_exists(SITEPATH . "/twitter/" . $cType . ".xml")) {
			if (filemtime(SITEPATH . "/twitter/" . $cType . ".xml") <= (time() - 3600)) {
				$aData	= $this->getFeedFile($cType);
				file_put_contents(SITEPATH . "/twitter/" . $cType . ".xml", $aData);
			} else {
				$aData	= file_get_contents(SITEPATH . "/twitter/" . $cType . ".xml");
			}
		} else {
			$aData	= $this->getFeedFile($cType);
			file_put_contents(SITEPATH . "/twitter/" . $cType . ".xml", $aData);
		}

		//parse the feed
		try {
			$oCheck	= @simplexml_load_string($aData);
			if ($oCheck) {
				$oXML	= simplexml_load_string($aData);
			} else {
				$oXML	= false;
			}
		} catch (Spanner $e) {
			throw new Spanner($e->getMessage(), 800);
		}

		return $oXML;
	}

	/**
	 * Twitter::getTweets()
	 *
	 * @param int $iFetch
	 * @return array
	 */
	public function getTweets($iFetch = false) {
		if (!$iFetch) { $iFetch = 5; }
		$aReturn	= false;

		$oXML		= $this->getFeed("user_timeline");
		if ($oXML) {
			$aReturn	= $this->parseFeed($oXML, $iFetch);
		}

		return $aReturn;
	}

	/**
	 * Twitter::getMentions()
	 *
	 * @param int $iFetch
	 * @return array
	 */
	public function getMentions($iFetch = false) {
		if (!$iFetch) { $iFetch = 5; }
		$aReturn	= false;

		$oXML		= $this->getFeed("mentions");
		if ($oXML) {
			$aReturn	= $this->parseFeed($oXML, $iFetch);
		}

		return $aReturn;
	}

	/**
	 * Twitter::getReply()
	 *
	 * @param int $iTweet
	 * @return string
	 */
	public function getReply($iTweet) {
		$cReturn	= false;
		$aMentions	= $this->getMentions();
		$iMentions	= count($aMentions);

		//go through the mentions
		for ($i = 0; $i < $iMentions; $i++) {
			if ($aMentions[$i]['id'] == $iTweet) {
				$cReturn	= $aMentions[$i]['screen_name'];
			}
		}

		//if it can be found add the @ to it
		if ($cReturn) {
			$cReturn = "@" . trim($cReturn);
		}

		return $cReturn;
	}

	/**
	 * Twitter::parseFeed()
	 *
	 * @param object $oXML pass the simplexml object
	 * @param int $iFetch the number of elements to return
	 * @return array
	 */
	private function parseFeed(SimpleXMLElement $oXML, $iFetch) {
		$i			= 0;
		$aReturn	= false;

		//The number of results
		$iTweets	= count($oXML->status);
		if ($iTweets <= $iFetch) {
			$iReturn = $iTweets;
		} else {
			$iReturn = $iFetch;
		}

		//get the results
		for ($i = 0; $i < $iReturn; $i++) {
			$aReturn[$i]['text']			= (string)$oXML->status[$i]->text[0];
			$aReturn[$i]['id']				= (string)$oXML->status[$i]->id[0];
			$aReturn[$i]['dated']			= (string)$oXML->status[$i]->created_at[0];
			$aReturn[$i]['posts']			= (string)$oXML->status[$i]->user->statuses_count[0];
			$aReturn[$i]['screen_name']		= (string)$oXML->status[$i]->user->screen_name[0];
		}

		return $aReturn;
	}

	/**
	 * Twitter::postTweet()
	 *
	 * @param string $cMessage
	 * @return object
	 */
	public function postTweet($cMessage) {
		$oReturn	= false;

		//some how they managed to get this far, but dont want non-posters to post
		if ($this->oUser->canDoThis("postTweet")) {
			$oCurl = curl_init();
			curl_setopt($oCurl, CURLOPT_URL, 'http://twitter.com/statuses/update.xml');
			curl_setopt($oCurl, CURLOPT_POST, 1);
			curl_setopt($oCurl, CURLOPT_POSTFIELDS, "status=" . $cMessage);
			curl_setopt($oCurl, CURLOPT_FOLLOWLOCATION, 1);
			curl_setopt($oCurl, CURLOPT_HEADER, 0);
			curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($oCurl, CURLOPT_USERPWD, $this->cTwitterUsername . ':' . $this->cTwitterPassword);
			curl_setopt($oCurl, CURLOPT_USERAGENT, "Hammer/2.0 (+http://www.framedhammer.com)");

			// Lets Send the Data
			$oReturn = curl_exec($oCurl);
		}

		return $oReturn;
	}

	/**
	 * Twitter::getFollowers()
	 *
	 * @return string
	 */
	public function getFollowers() {
		$iFollowers	= false;

		$oXML = $this->getFeed("user_timeline");
		if (!$oXML) { return false; }

		//Add the followers
		if (isset($oXML->status[0])) {
			$iFollowers = (string)$oXML->status[0]->user->followers_count[0];
			$iFollowing	= (string)$oXML->status[0]->user->friends_count[0];
		} else {
			$iFollowers	= 0;
		}

		return $iFollowers;
	}

	/**
	 * Twitter::getFollowing()
	 *
	 * @return string
	 */
	public function getFollowing() {
		$iFollowing	= false;
		$oXML		= $this->getFeed("user_timeline");
		if (!$oXML) { return false; }

		//get how many your following
		if (isset($oXML->status[0])) {
			$iFollowing	= (string)$oXML->status[0]->user->following[0];
		} else {
			$iFollowing	= 0;
		}

		return $iFollowing;
	}

	/**
	 * Twitter::authIt()
	 *
	 * @return null
	 */
	private function authIt() {
		if (!class_exists("OAuth")) { return false; }

		$cAddress				= $this->oNails->cSiteAddy;
		$cCallBack				= "http://" . $cAddress . "/admin/twitter/";

		$oAuth					= new OAuth($this->cConsumerKey, $this->cConsumerSecret, OAUTH_SIG_METHOD_HMACSHA1, OAUTH_AUTH_TYPE_URI);
		$this->aRequestToken	= $oAuth->getRequestToken("https://api.twitter.com/oauth/request_token", $cCallBack);

		$oAuth->setToken($this->aRequestToken['oauth_token'], $this->aRequestToken['oauth_token_secret']);
		$this->aAccessToken		= $oAuth->getAccessToken("https://api.twitter.com/oauth/access_token");
	}

	/**
	 * Twitter::login()
	 *
	 * @return
	 */
	public function login() {
		$cReturn	= false;

		if (!$this->oNails->getSession("twitter_token")) {
			#$cReturn = $this->oTwit->getAuthorizationUrl();
		} else {

		}

		return $cReturn;
	}
}

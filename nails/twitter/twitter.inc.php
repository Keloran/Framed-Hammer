<?php
/**
 * Twitter
 *
 * @package
 * @author Keloran
 * @copyright Copyright (c) 2011
 * @version $Id$
 * @access public
 */
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
	 * Twitter::__construct()
	 *
	 * @param Nails $oNails
	 */
	private function __construct(Nails $oNails) {
		$this->oNails	= $oNails;
		$this->oUser	= $oNails->getUser();
		$this->oTwit	= new Twitter_Tweet($this->cConsumerKey, $this->cConsumerSecret);

		//check installed
		if ($this->oNails->checkInstalled("twitter") == false) { $this->install(); }

		//check version
		if ($this->oNails->checkVersion("twitter", 1.6) == false) {
			//1.1
			$cSQL	= "CREATE TABLE IF NOT EXISTS `twitter` (
				`iTweetID` INT NOT NULL AUTO_INCREMENT,
				`cTweet` VARCHAR(150),
				PRIMARY KEY (`iTweetID`))";
			$this->oNails->updateVersion("twitter", 1.1, $cSQL, "Create table");

			//1.2
			$cSQL	= "ALTER TABLE `twitter` ADD COLUMN `iFollowers` INT NOT NULL DEFAULT 0";
			$this->oNails->updateVersion("twitter", 1.2, $cSQL, "Add Followers");

			//1.3
			$cSQL	= "ALTER TABLE `twitter` ADD COLUMN `iUpdates` INT NOT NULL DEFAULT 0";
			$this->oNails->updateVersion("twitter", 1.3, $cSQL, "Add Updates");

			//1.4
			$cSQL	= "ALTER TABLE `twitter` ADD COLUMN `iFollowing` INT NOT NULL DEFAULT 0";
			$this->oNails->updateVersion("twitter", 1.4, $cSQL, "Add Following");

			//1.5
			$this->oNails->updateVersion("twitter", 1.5, false, "Moved to using XML to store Twitter locally");

			//1.6
			$this->oNails->updateVersion("twitter", 1.6, false, "Moved to use oAuth");
		}
	}

	/**
	 * Twitter::install()
	 *
	 * @return nulll
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
	 * Twitter::login()
	 *
	 * @return string
	 */
	public function login() {
		$cReturn	= false;

		if (!$this->oNails->getSession("twitterToken")) {
			$cReturn = $this->oTwit->getAuthorizationUrl();
		}

		return $cReturn;
	}

	/**
	 * Twitter::setUserToken()
	 *
	 * @param string $cToken
	 * @return null
	 */
	public function setUserToken($cToken) {
		//set the token for the user
<<<<<<< HEAD
=======
		#$this->oUser->setSetting("twitterToken", $cToken);
>>>>>>> c0c66965fad63221c98f14c695de9a95e55161f3
		$this->oNails->setSession("twitterToken", $cToken);

		//set the token in the twitter object
		$this->oTwit->setToken($cToken);

		//set the access token
		$oToken		= $this->oTwit->getAccessToken();
		$this->oTwit->setToken($oToken->oauth_token, $oToken->oauth_token_secret);

		//set the session token
		$this->oNails->setSession("auth_token", $oToken->oauth_token);
		$this->oNails->setSession("auth_token_secret", $oToken->oauth_token_secret);
<<<<<<< HEAD
=======

		#$this->setInfo();
>>>>>>> c0c66965fad63221c98f14c695de9a95e55161f3
	}

	/**
	 * Twitter::setInfo()
	 *
	 * @return array
	 */
	public function setInfo() {
		$oInfo	= $this->oTwit->get_accountVerify_credentials();
		$oInfo->response;

		//response
		$aReturn				= array();
		$aReturn['username']	= $oInfo->screen_name;
		$aReturn['pic']			= $oInfo->profile_image_url;

		return $aReturn;
	}
}
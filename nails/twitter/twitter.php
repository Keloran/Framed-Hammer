<?php
/**
 * Twitter
 *
 * @package
 * @author keloran
 * @copyright Copyright (c) 2011
 * @version $Id$
 * @access public
 */
class Twitter implements Nails_Interface {
	private $iUserID	= false;
	private $oNails		= false;

	private $cKey		= "SmdcqSqTA4gRU8heJftdg";
	private $cSecret	= "GI4lY0NH9VXj86toje2FwIlfEEyeS4vryXECQNLaI";

	private static $oTwitter;

	public $oDB			= false;
	public $oImage		= false;
	public $oUser		= false;
	public $oAuth		= false;

	/**
	 * Twitter::__construct()
	 *
	 */
	private function __construct(Nails $oNails) {
		$oNails->getNails("Twitter_Install");
		$this->oNails	= $oNails;

		$this->oUser	= $this->oNails->getUser();
		$this->iUserID	= $this->oUser->getUserID();

		$this->oDB		= $this->oNails->getDatabase();

		$oAuth	= new OAuth($this->cKey, $this->cSecret, OAUTH_SIG_METHOD_HMACSHA1, OAUTH_AUTH_TYPE_URI);
		$oAuth->enableDebug();

		$this->oAuth	= $oAuth;
	}

	/**
	 * Twitter::getInstance()
	 *
	 * @return
	 */
	static function getInstance(Nails $oNails) {
		if (is_null(self::$oTwitter)) {
			self::$oTwitter = new Twitter($oNails);
		}

		return self::$oTwitter;
	}

	/**
	 * Twitter::save()
	 *
	 * @param mixed $aRecord
	 * @return null
	 */
	public function save($aRecord) {
		$cScreenName	= $aRecord['username'];
		$iState			= $aRecord['state'];
		$cToken			= $aRecord['token'];
		$cSecret		= $aRecord['secret'];
		$aRecord		= false;

		if ($this->iUserID) {
			$aRecord[]	= $cScreenName;
			$aRecord[]	= $iState;
			$aRecord[]	= $cToken;
			$aRecord[]	= $cSecret;
			$aRecord[]	= $this->iUserID;

			$this->oDB->write("REPLACE INTO twitter (username, state, token, secret, iUserID) VALUES (?, ?, ?, ?, ?)", $aRecord);
		}
	}

	/**
	 * Twitter::update()
	 *
	 * @param array $aRecord
	 * @return
	 */
	public function update($aRecord) {
		$cTweet			= $aRecord['tweet'];
		$iReTweet		= $aRecord['reTweet'];
		$cScreeName		= $aRecord['screenName'];
		$iTweetID		= $aRecord['tweetID'];
		$aUpdate		= false;

		if ($this->iUserID) {
			$aUpdate[]	= $cTweet;
			$aUpdate[]	= $this->iUserID;
			$aUpdate[]	= $iTweetID;
			$aUpdate[]	= $iReTweet;
			$aUpdate[]	= $cScreenName;
			$this->oDB->write("INSERT INTO twitter_tweets (cTweet, iUserID, iTweetID, iReTweet, cScreeName) VALUES (?, ?, ?, ?, ?)", $aUpdate);

			$aUpdate	= false;
			$aUpdate[]	= $aRecord['followers'];
			$aUpdate[]	= $aRecord['following'];
			$aUpdate[]	= $aRecord['description'];
			$aUpdate[]	= $aRecord['image'];
			$aUpdate[]	= $aRecord['location'];
			$aUpdate[]	= $this->iUserID;
			$this->oDB->write("UPDATE twitter_details SET iFollowers = ?, iFollowing = ?, cDescription = ?, cImage = ?, cLocation = ? WHERE iUserID = ? LIMIT 1", $aUpdate);

			$this->touch();
		}
	}

	/**
	 * Twitter::load()
	 *
	 * @param bool $bAll
	 * @return
	 */
	public function load($bAll = false) {
		$aReturn	= false;

		if ($this->iUserID) {
			$this->oDB->read("SELECT username, state, token, secret FROM twitter WHERE iUserID = ? LIMIT 1", $this->iUserID);
			if ($this->oDB->nextRecord()) {
				$aReturn['username']	= $this->oDB->f('username');
				$aReturn['state']		= $this->oDB->f('state');
				$aReturn['token']		= $this->oDB->f('token');
				$aReturn['secret']		= $this->oDB->f('secret');
			}
		}

		return $aReturn;
	}

	/**
	 * Twitter::touch()
	 *
	 * @return null
	 */
	public function touch() {
		if ($this->iUserID) {
			$this->oDB->write("UPDATE twitter SET mtime = CURRENT_TIMESTAMP WHERE iUserID = ? LIMIT 1", $this->iUserID);
		}
	}

	/**
	 * Twitter::remove()
	 *
	 * @return null
	 */
	public function remove() {
		if ($this->iUserID) {
			$this->oDB->write("DELETE FROM twitter WHERE iUserID = ? LIMIT 1", $this->iUserID);
		}
	}

	/**
	 * Twitter::getDetails()
	 *
	 * @return null
	 */
	public function getDetails() {
		$aDetails	= $this->load();
		if ($aDetails['state'] == 0) { //need to auth
			$aRequest	= $this->oAuth->getRequestToken("https://api.twitter.com/oauth/request_token");

			$aNewDetails['username']	= $this->oUser->getUsername();
			$aNewDetails['state']		= 1;
			$aNewDetails['token']		= $aRequest['oauth_token'];
			$aNewDetails['secret']		= $aRequest['oauth_token_secret'];
			$this->save($aNewDetails);

			$this->oNails->sendLocation("https://api.twitter.com/oauth/authorize?oauth_token=" . $aRequest['oauth_token']);
		} else if ($aDetails['state'] == 1) { //the call back from twitter
			printRead($this->oNails);
		}

		//stage 2 authorized
		$this->oAuth->setToken($aDetails['token'], $aDetails['secret']);
		$this->oAuth->fetch("https://api.twitter.com/1/account/verify_credentials.json");
		$oJSON	= json_decode($this->oAuth->getLastResponse());

		printRead($oJSON);die();

		//add the elements to the insert array
		$aNewDetails['screenName']	= (string)$oJSON->user->screen_name;
		$aNewDetails['description']	= (string)$oJSON->user->description;
		$aNewDetails['location']	= (string)$oJSON->user->location;
		$aNewDetails['followers']	= (int)$oJSON->user->followers_count;
		$aNewDetails['following']	= (int)$oJSON->user->friends_count;
		$aNewDetails['image']		= (string)$oJSON->user->profile_image_url_https;
		$aNewDetails['tweet']		= (string)$oJSON->status->text;

		//is it retweet, in which case i want the retweet info not the default info
		$iReTweet					= 0;
		$aNewDetails['reTweet']		= $iReTweet	= (int)$oJSON->retweeted;
		if ($iReTweet) {
			$aNewDetails['tweet']		= (string)$oJSON->retweeted_status->text;
			$aNewDetails['screenName']	= (string)$oJSON->retweeted_status->screen_name;
		}

		$this->update($aNewDetails);
	}

	public function getLatestTweets() {
		$iMinus	= 36000;

		$this->oDB->read("SELECT cTweet, ");


		$aDetails	= $this->load();

		$this->oAuth->setToken($aDetails['token'], $aDetails['secret']);
		$this->oAuth->fetch("https://api.twitter.com/1/statuses/user_timeline.json?include_entities=true&include_rts=true&count=5&screen_name=" . $aDetails['username']);
		$oJSON	= json_decode($this->oAuth->getLastResponse());



		printRead($oJSON);die();
	}
}

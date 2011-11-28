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
	private function save($aRecord) {
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
	private function update($aRecord) {
		if ($this->iUserID) {
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
	 * Twitter::addTweet()
	 *
	 * @param array $aRecord
	 * @return null
	 */
	private function addTweet($aRecord) {
		$cTweet			= $aRecord['tweet'];
		$iReTweet		= $aRecord['reTweet'];
		$cScreeName		= $aRecord['screenName'];
		$iTweetID		= $aRecord['id'];

		if ($this->iUserID) {
			$aUpdate[]	= $cTweet;
			$aUpdate[]	= $this->iUserID;
			$aUpdate[]	= $iTweetID;
			$aUpdate[]	= $iReTweet;
			$aUpdate[]	= $cScreenName;
			$this->oDB->write("INSERT INTO twitter_tweets (cTweet, iUserID, iTweetID, iReTweet, cScreeName) VALUES (?, ?, ?, ?, ?)", $aUpdate);
		}
	}

	/**
	 * Twitter::load()
	 *
	 * @param bool $bAll
	 * @return
	 */
	private function load($bAll = false) {
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
	private function touch() {
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

		$aNewDetails['description']	= (string)$oJSON->description;
		$aNewDetails['following']	= (int)$oJSON->friends_count;
		$aNewDetails['followers']	= (int)$oJSON->followers_count;
		$aNewDetails['image']		= (string)$oJSON->profile_image_url_https;
		$aNewDetails['location']	= (string)$oJSON->location;
		$this->update($aNewDetails);
	}

	/**
	 * Twitter::getLatest()
	 *
	 * @return array
	 */
	private function getLatest() {
		$aReturn	= false;
		$j			= 0;

		if ($this->iUserID) {
			$aDetails	= $this->load();

			$this->oAuth->setToken($aDetails['token'], $aDetails['secret']);
			$this->oAuth->fetch("https://api.twitter.com/1/statuses/user_timeline.json?include_entities=true&include_rts=true&count=5&screen_name=" . $aDetails['username']);
			$oJSON	= json_decode($this->oAuth->getLastResponse());

			//go through the tweets and get only info we want
			for ($i = 0; $i < count($oJSON); $i++) {
				$aReturn[$j]['tweet']	= (string)$oJSON[$i]->text;

				$iTweet	= (int)$oJSON[$i]->id;
				if (!$iTweet) { $iTweet = (double)$oJSON[$i]->id; }
				$aReturn[$j]['id']		= $iTweet;

				$aReturn[$j]['reTweet']	= (int)$oJSON[$i]->retweet_count;

				//is it a retweet or normal
				if ($oJSON[$i]->retweeted_status) {
					$aReturn[$j]['screenName']	= (string)$oJSON[$i]->retweeted_status->user->screen_name;
					$aReturn[$j]['tweet']		= (string)$oJSON[$i]->retweeted_status->text;
				} else {
					$aReturn[$j]['screenName']	= (string)$oJSON[$i]->user->screen_name;
				}

				$j++;
			}
		}

		printRead($aReturn);die();

		return $aReturn;
	}

	/**
	 * Twitter::getLatestTweets()
	 *
	 * @return array
	 */
	public function getLatestTweets() {
		$i			= 0;
		$j			= 0;
		$aReturn	= false;
		$aTweets	= false;
		$aTweetIDs	= false;

		//see if we need to pull new data
		$iMinus		= 36000;
		$iStatus	= 0;
		$this->oDB->read("SELECT mtime FROM twitter WHERE iUserID = ? LIMIT 1", $this->iUserID);
		if ($this->oDB->nextRecord()) { $iStatus = strtotime($this->oDB->f('mtime')); }

		//get teh latest 5 tweetids
		$this->oDB->read("SELECT iTweetID FROM twitter_tweet WHERE iUserID = ? LIMIT 5", $this->iUserID);
		while ($this->oDB->nextRecord()) { $aTweetIDs[]	= $this->oDB->f('iTweetID'); }

		//do we need todo an update
		if ($iStatus >= (time() - $iMinus)) {
			$this->getDetails();
			$aLatest	= $this->getLatest();

			//add an new tweets
			for ($i = 0; $i < count($aLatest); $i++) {
				$iTweet	= $aLatest[$i]['id'];
				if (in_array($iTweet, $aTweetIDs)) {
					continue;
				} else {
					$this->addTweet($aLatest[$i]);
				}
			}
		}

		//get the latest 5 tweets from table
		$this->oDB->read("
			SELECT cTweet, iFollowers, iFollowing, cImage, cScreenName, iReTweet, iTweetID
			FROM twitter_tweet
			JOIN twitter_details ON (twitter_tweet.iUserID = twitter_details.iUserID
			WHERE twitter_tweet.iUserID = ?
			ORDER BY twitter_details.iTweetID
			LIMIT 5", $this->iUserID);
		while ($this->oDB->nextRecord()) {
			$aTweets[$j]['tweet']		= $this->oDB->f('cTweet');
			$aTweets[$j]['id']			= $this->oDB->f('iTweetID');
			$aTweets[$j]['followers']	= $this->oDB->f('iFollowers');
			$aTweets[$j]['following']	= $this->oDB->f('iFollowing');
			$aTweets[$j]['image']		= $this->oDB->f('cImage');
			$aTweets[$j]['name']		= $this->oDB->f('cScreenName');
			$aTweets[$j]['retweets']	= $this->oDB->f('iReTweet');
			$j++;
		}

		printRead($aTweets);die();

		return $aTweets;
	}
}

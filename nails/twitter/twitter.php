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
		if ($this->iUserID) {
			$aRecord[] = $this->iUserID;
			$this->oDB->write("REPLACE INTO twitter (username, state, token, secret, description, iUserID) VALUES (?, ?, ?, ?, ?, ?)", $aRecord);
		}
	}

	/**
	 * Twitter::update()
	 *
	 * @param array $aRecord
	 * @return
	 */
	public function update($aRecord) {
		if ($this->iUserID) {
			$aRecord[] = $this->iUserID;
			$this->oDB->write("UPDATE twitter SET username = ?, status = ?, description = ?, location = ?, followers = ? WHERE iUserID = ? LIMIT 1", $aRecord);
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
			$this->oDB->read("SELECT username, state, token, secret, description FROM twitter WHERE iUserID = ? LIMIT 1", $this->iUserID);
			if ($this->oDB->nextRecord()) {
				$aReturn['username']	= $this->oDB->f('username');
				$aReturn['state']		= $this->oDB->f('state');
				$aReturn['token']		= $this->oDB->f('token');
				$aReturn['secret']		= $this->oDB->f('secret');
				$aReturn['description']	= $this->oDB->f('description');
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

	public function getDetails() {
		$oAuth	= new OAuth($this->cKey, $this->cSecret, OAUTH_SIG_METHOD_HMACSHA1, OAUTH_AUTH_TYPE_URI);
		$oAuth->enableDebug();

		$aDetails	= $this->load();
		if ($aDetails['state'] == 0) { //need to auth
			$aRequest	= $oAuth->getRequestToken("https://api.twitter.com/oauth/request_token");

			$aNewDetails[]	= $this->oUser->getUsername();
			$aNewDetails[]	= 1;
			$aNewDetails[]	= $aRequest['oauth_token'];
			$aNewDetails[]	= $aRequest['oauth_token_secret'];
			$aNewDetails[]	= "New Auth";
			$this->save($aNewDetails);

			$this->oNails->sendLocation("https://api.twitter.com/oauth/authorize?oauth_token=" . $aRequest['oauth_token']);
		} else if ($aDetails['state'] == 1) { //the call back from twitter
			printRead($this->oNails);
		}

		//stage 2 authorized
		$oAuth->setToken($aDetails['token'], $aDetails['secret']);
		$oAuth->fetch("https://api.twitter.com/1/account/verify_credentials.json");
		$oJSON	= json_decode($oAuth->getLastResponse());

		$aNewDetails[]	= (string)$oJSON->screen_name;
		$aNewDetails[]	= (string)$oJSON->status->text;
		$aNewDetails[]	= (string)$oJSON->description;
		$aNewDetails[]	= (string)$oJSON->location;
		$aNewDetails[]	= (int)$oJSON->followers_count;
		$this->update($aNewDetails);
	}
}

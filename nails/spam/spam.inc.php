<?php
class Spam {
	private static $oSpam;
	private $oNails;
	private $oDB;

	private $cIP;
	private $cEmail;

	private $cResponse;
	private $oResponse;

	/**
	 * Spam::__construct()
	 *
	 * @param Nails $oNails
	 */
	public function __construct(Nails $oNails) {
		$this->oNails 	= $oNails;
		$this->oDB		= $oNails->getDatabase();

		//check its installed, since might aswell put this stuff into a database
		if ($this->oNails->checkInstalled("hammer_spam") == false) {
			$this->install();
		}
	}

	/**
	 * Spam::install()
	 *
	 * @return null
	 */
	private function install() {
		$this->oNails->addTable("
			CREATE TABLE IF NOT EXISTS `hammer_spam` (
				`iSpamID` INT NOT NULL AUTO_INCREMENT,
				`cEmail` TEXT NOT NULL,
				`iIP` INT NOT NULL,
				PRIMARY KEY (`iSpamID`))
			ENGINE=InnoDB");

		$this->oNails->addVersion("hammer_spam", "1.0");

		$this->oNails->sendLocation("install");
	}

	/**
	 * Spam::getInstance()
	 *
	 * @param Nails $oNails
	 * @return object
	 */
	public static function getInstance(Nails $oNails) {
		if (is_null(self::$oSpam)) { self::$oSpam = new Spam($oNails); }

		return self::$oSpam;
	}

	/**
	 * Spam::checkSpam()
	 *
	 * @param string $cIP
	 * @return bool
	 */
	public function checkSpam($mSpam) {
		$bMySpam	= false;
		$bSpam		= false;

		if (strstr($mSpam, "@")) {
			$this->cEmail	= $mSpam;
		} else {
			//its a long so convert to ip, will be converted back for checking in db
			if (strstr($mSpam, ".")) { $mSpam = long2ip($mSpam); }

			$this->cIP		= $mSpam;
		}

		//check my spam list
		$bMySpam = $this->getMySpam();

		//if its not in my spam list, check the spamlist
		if (!$bMySpam) {
			$this->getJSON();
			$bSpam = $this->getJSONResult();
		}

		//now if its spam, add to my spam list
		if ($bSpam) { $this->addSpam(); }

		return $bSpam;
	}

	/**
	 * Spam::addSpam()
	 *
	 * @return null
	 */
	private function addSpam() {
		if ($this->cIP) {
			$this->oDB->write("INSERT INTO hammer_spam (iIP) VALUES (?)", ip2long($this->cIP));
		} else if ($this->cEmail) {
			$this->oDB->write("INSERT INTO hammer_spam (cEmail) VALUES (?)", $this->cEmail);
		}
	}

	/**
	 * Spam::getResult()
	 *
	 * @return bool
	 */
	private function getJSONResult() {
		$bSpam	= false;

		if ($this->oResponse) {
			$oIP	= $this->oResponse->ip;
			if ($oIP) {
				$bSpam = $oIP->appears;
			}
		}

		return $bSpam;
	}

	/**
	 * Spam::getMySpam()
	 *
	 * @return bool
	 */
	private function getMySpam() {
		$bSpam	= false;

		//its an ip to test
		if ($this->cIP) {
			$this->oDB->read("SELECT TRUE FROM hammer_spam WHERE iIP = ? LIMIT 1", ip2long($this->cIP));
			if ($this->oDB->nextRecord()) { $bSpam = true; }

		//its an email to test
		} else if ($this->cEmail) {
			$this->oDB->read("SELECT TRUE FROM hammer_spam WHERE cEmail = ? LIMIT 1", $this->cEmail);
			if ($this->oDB->nextRecord()) { $bSpam = true; }
		}

		return $bSpam;
	}

	/**
	 * Spam::getJSON()
	 *
	 * @return null
	 */
	private function getJSON() {
		if ($this->cIP) {
			$cURL = "http://www.stopforumspam.com/api?f=json&ip=" . $this->cIP;
		} else if ($this->cEmail) {
			$cURL = "http://www.stopforumspam.com/api?f=json&email=" . $this->cEmail;
		}

		//get the contents
		$pCURL = curl_init();
		curl_setopt($pCURL,CURLOPT_URL,$cURL);
		curl_setopt($pCURL,CURLOPT_CONNECTTIMEOUT,2);
		curl_setopt($pCURL,CURLOPT_RETURNTRANSFER,1);
		$cData = curl_exec($pCURL);
		curl_close($pCURL);

		$this->oResponse = json_decode($cData);
	}

	/**
	 * Spam::debug()
	 *
	 * @return null
	 */
	public function debug() {
		return $this;
	}
}

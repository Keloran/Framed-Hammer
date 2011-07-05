<?php
/**
 * Admin
 *
 * this is to do basic admin details
 * e.g. last time the site was indexed, or tried to be hacked
 *
 * @author your name
 * @tag value
 */
class Admin implements Nails_Interface {
	//Browser Trait
	use Traits_Browser;

	static $oAdmin;

	private $oNails	= false;
	private $oDB	= false;
	private $oUser	= false;

	/**
	 * Admin::__construct()
	 *
	 * @param Nails $oNails
	 */
	private function __construct(Nails $oNails) {
		$this->oNails = $oNails;

		//check its installed
		if ($this->oNails->checkInstalled("admin") == false) {
			$this->install();
		}

		//upgrade
		if ($this->oNails->checkVersion("admin", "1.1") == false) {
			//1.1
			$cSQL	= "CREATE TABLE admin_contest (`iContestID` INT NOT NULL AUTO_INCREMENT, `dDated` DATETIME, `iUserID` INT NOT NULL, `cContest` VARCHAR(150), PRIMARY KEY(`iContestID`), INDEX(`iUserID`))";
			$this->oNails->updateVersion("admin", "1.1", $cSQL);
		}

		$this->oDB 		= $this->oNails->getDatabase();
		$this->oUser	= $this->oNails->getUser();
	}

	/**
	 * Admin::__destruct()
	 *
	 */
	public function __destruct() {
		$this->oNails	= null;
		$this->oDB		= null;
		$this->oUser	= null;
	}

	/**
	 * Admin::install()
	 *
	 * @return null
	 */
	private function install() {
		$this->oNails->addGroups("admin");
		$this->oNails->addAbility("admin", "Admin");

		$this->oNails->addVersion("admin", "1.0");
	}

	/**
	 * Admin::getInstance()
	 *
	 * @param bool $bStandAlone
	 * @return object
	 */
	static function getInstance(Nails $oNails) {
		if (is_null(self::$oAdmin)) {
			self::$oAdmin	= new Admin($oNails);
		}

		return self::$oAdmin;
	}

	/**
	 * Admin::getLastHack()
	 *
	 * @return array
	 */
	public function getLastHack() {
		$aReturn	= false;

		$this->oDB->read("SELECT tsDate, COUNT(tsDate) as counted FROM users_sessions_robots WHERE cRobotName = ? GROUP BY cRobotName ORDER BY iSessionID DESC LIMIT 1", "Hacking Attempt");
		if ($this->oDB->nextRecord()) {
			$aReturn['date']	= date("d/m/Y h:s", $this->oDB->f('tsDate'));
			$aReturn['count']	= $this->oDB->f('counted');
		}

		return $aReturn;
	}

	/**
	 * Admin::getLastIndex()
	 *
	 * @param string $cRobot
	 * @return string
	 */
	public function getLastIndex($cRobot) {
		$cReturn = false;

		$this->oDB->read("SELECT tsDate FROM users_sessions_robots WHERE cRobotName = ? LIMIT 1", $cRobot);
		if ($this->oDB->nextRecord()) {
			$cReturn	= date("d/m/Y h:s", $this->oDB->f('tsDate'));
		} else {
			$cReturn	= "Not Indexed Yet";
		}

		return $cReturn;
	}

	/**
	 * Admin::getLastRobots()
	 *
	 * @return array
	 */
	public function getLastRobots() {
		$aReturn	= array();
		$i			= 0;

		$this->oDB->read("SELECT tsDate, cRobotName, cRobotSite FROM users_sessions_robots WHERE cRobotName NOT LIKE ? ORDER BY iSessionID DESC LIMIT 10", "Hacking Attempt");
		while ($this->oDB->nextRecord()) {
			$aReturn[$i]['date']	= date("d/m/Y h:s", $this->oDB->f('tsDate'));
			$aReturn[$i]['name']	= ucfirst($this->oDB->f("cRobotName"));
			$aReturn[$i]['site']	= $this->oDB->f("cRobotSite");
			$i++;
		}

		return $aReturn;
	}

	/**
	 * Admin::secureLogin()
	 *
	 * @param bool $bSecure This is if your site has https
	 * @return null
	 */
	public function secureLogin($bSecure = null) {
		//Logged in
		$bLogged	= $this->getCookie("userCookie");
		$iAdminLogged	= $this->getCookie("adminLogged");

		$cAdminLoc	= $this->oNails->getConfig("adminLocation"); //Get the admin location, e.g. you could put it in /secure/
		$cAdminPage	= $cAdminLoc ? $cAdminLoc : "admin"; //there is a loc set, or revert to default

		//since its the admin page we are on
		if ($cAdminPage == $this->oNails->cPage) {
			if (!defined("admininsecure")) {
				$bAdminExists	= file_exists(PAGES . "/" . $cAdminPage . "/secure.php"); //helps if there is a secure location

				//are they actually logged in at all
				if (!$bLogged) { $this->oNails->sendLocation("/login/"); }

				//does the page actually exist
				if ($bAdminExists) {
					//Since you have activity within the 5min gap
					$iOld	= time() - 240;
					if ($iAdminLogged < $iOld) {
						if ($iAdminLogged) {
							createCookie("adminLogged", time(), false, 5, $bSecure);
						}
					}

					//Since we know that the person isnt re-checked and we want them to be
					if (!$iAdminLogged) {
						if ($this->oNails->cAction !== "secure") { //since your on secure we dont want to send into an infinite loop
							if ($bLogged) { //since your already logged in then show it
								$this->oNails->sendLocation("/" . $cAdminPage . "/secure/");
							}
						}
					}
				} else {
					$this->insecureAdmin($bSecure);
				}
			} else {
				$this->insecureAdmin($bSecure);
			}
		}
	}

	/**
	 * Admin::insecureAdmin()
	 *
	 * @return null
	 */
	private function insecureAdmin($bSecure = null) {
		$bLogged = $this->getCookie("userCookie");

		if ($bLogged) {
			createCookie("adminLogged", time(), false, 5, $bSecure);
		} else {
			if ($this->oNails->cPage !== "login") {
				$this->oNails->sendLocation("/login/");
			}
		}
	}

	/**
	 * Admin::banIP()
	 *
	 * @param string $cIP
	 * @return null
	 */
	public function banIP($cIP) {
		$cIP	= ip2long($cIP);
		$this->oDB->write("INSERT INTO users_banned (cBannedIP) VALUES (?)", $cIP);
	}

	/**
	 * Admin::getBannedIP()
	 *
	 * @param string $cIP
	 * @return bool
	 */
	public function getBannedIP($cIP) {
		$cIP = ip2long($cIP);

		$this->oDB->read("SELECT iBannedID FROM users_banned WHERE cBannedIP = ? LIMIT 1", $cIP);
		if ($this->oDB->nextRecord()) {
			return true;
		}

		return false;
	}

	/**
	 * Admin::contestBan()
	 *
	 * @param string $cEmail
	 * @return null
	 */
	public function contestBan($cEmail) {
		//always validate before sanitizing
		if (!filter_var($cEmail, FILTER_VALIDATE_EMAIL)) { return false; }

		$oUser	= $this->oNails->getUser();

		$iUserID	= $oUser->getUserID();
		$cSan   	= filter_var($cEmail, FILTER_SANITIZE_EMAIL);

		$aInsert	= array($cSan, $iUserID);
		$this->oDB->write("INSERT INTO admin_contest (dDated, cContest, iUserID) VALUES (NOW(), ?, ?)", $aInsert);
	}
}

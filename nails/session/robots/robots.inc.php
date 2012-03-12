<?php
/**
 * Session_Robots
 *
 * @package
 * @author Keloran
 * @copyright Copyright (c) 2010
 * @version $Id$
 * @access public
 */
class Session_Robots {
	//Traits
	use Browser;

	static $oRobots;

	public $cError;

	private $oNails;
	private $oDB;

	/**
	 * Session_Robots::__construct()
	 *
	 * @param Nails $oNails
	 */
	private function __construct(Nails $oNails) {
		$this->oNails	= $oNails;
		$this->oDB		= $oNails->getDatabase();
	}

	/**
	 * Session_Robots::getInstance()
	 *
	 * @param Nails $oNails
	 * @return object
	 */
	static function getInstance(Nails $oNails) {
		if (is_null(self::$oRobots)) {
			self::$oRobots	= new Session_Robots($oNails);
		}

		return self::$oRobots;
	}

	/**
	 * Session_Robots::getRobotDetails()
	 *
	 * @return array
	 */
	public function getRobotDetails() {
		$aInsert	= false;

		if (isset($_SERVER['HTTP_USER_AGENT'])) {
			$cBrowser = $_SERVER['HTTP_USER_AGENT'];

			//Get the robotname and
			$cPattern	= "`(\()(.+?)((?:http://)([a-zA-Z0-9\-_.\/]+))(\))`is";
			$cMatch		= preg_match_all($cPattern, $cBrowser, $aMatches);

			if (isset($aMatches[3][0])) {
				$cRobotName = $aMatches[2][0];
				$cRobotSite = $aMatches[3][0];
				$aInsert	= array($cRobotName, $cRobotSite);
			} else {
				$cRobotName = "";
			}


			switch($cRobotName) {
				//Google
				case "GoogleBot":
				case "googlebot":
				case "Googlebot":
				case "Googlebot/2.1; +":
				case "compatible; Googlebot/2.1; +":
				case "DoCoMo":
					$aInsert = array("Google", "http://www.google.com");
					break;

					//Msn | Bing | Live
				case "MsnBot":
				case "MSNBOT":
				case "msnbot":
					$aInsert = array("Bing", "http://www.bing.com");
					break;

					//Yahoo
				case "Yahoo":
				case "yahoo":
				case "compatible; Yahoo! Slurp3/0":
					$aInsert = array("Yahoo", "http://www.yahoo.com");
					break;

					//Twiceler
				case "twiceler":
				case "Twiceler":
				case "twiceler-0.9":
				case "Twiceler-0.9":
					$aInsert = array("Twiceler", "http://www.cuil.com/info/webmaster_info/");
					break;

					//Majestic
				case "MJ12":
				case "mj12":
				case "mj12bot":
					$aInsert = array("Majestic", $cRobotSite);
					break;

					//Alexa
				case "ia_archiver":
					$aInsert = array("Alexa", "http://www.alexa.com");
					break;
			}

			if (isset($aInsert)) {
				$aInsert[]	= print_r($_SERVER['HTTP_USER_AGENT'], true);
			} else if (strstr($cBrowser, "msn")) {
				$aInsert = array("MSN", "http://www.bing.com", print_r($_SERVER['HTTP_USER_AGENT'], true));
			}
		} else {
			if (isset($_SERVER['REMOTE_HOST'])) {
				switch ($_SERVER["REMOTE_HOST"]) {
					case "aremysitesup.com":
						$aInsert = array("AMSU", "http://www.aremysitesup.com", "Are My Sites Up");
						break;

					case "pingdom.com":
						$aInsert = array("PingDom", "http://www.pingdom.com", "PingDom");

					default:
						$aInsert = array("Unknown", print_r($_SERVER['REMOTE_HOST'], true), "UnKnown");
						break;
				}

				return $aInsert;
			} else {
				$aInsert	= array("Hacking Attempt", print_r($_SERVER, true), "UnKnown");
			}
		}

		return $aInsert;
	}

	/**
	 * Session_Robots::addRobot()
	 *
	 * @return null
	 */
	public function addRobot() {
		$aRobot	= $this->getRobotDetails();
		if ($aRobot) {
			//its from bing/msn
			if (isset($aRobot[2])) {
				if (strstr($aRobot[2], "msn.com")) { $aRobot[0] = "MSN"; }
			} else { //nothing set
				$aRobot[2] = "UnKnown";
			}

			if (isset($aRobot[1])) {
				//now insert it
				$this->oDB->write("INSERT INTO users_sessions_robots (cRobotName, tsDate, cRobotSite, cFullRobotString) VALUES (?, UNIX_TIMESTAMP(), ?, ?)", $aRobot);
			}
		} else {
			$this->addVistor();
		}
	}

	/**
	 * Session_Robots::addVistor()
	 *
	 * @return null
	 */
	public function addVistor() {
		$aWrite	= array($_SERVER['HTTP_USER_AGENT'], time());
		$this->oDB->write("INSERT INTO users_sessions_visitors (cUserAgent, tsDate) VALUES (?, ?)", $aWrite);
	}
}

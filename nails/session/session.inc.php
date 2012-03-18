<?php
/**
 * Session
 *
 * @package
 * @author Max
 * @copyright Copyright (c) 2008
 * @version $Id: session.inc.php 508 2010-01-08 12:15:40Z keloran $
 * @access public
 */
class Session {
	//Traits
	use Cookie;

	public $tsLogin		= false;
	public $tsLastLogin 	= false;
	public $iUserID		= false;
	public $cError		= false;
	public $cSited		= false;

	private $oUser;
	private $oRobot;
	private $oDB;

	static $oSession;

	/**
	 * Session::__construct()
	 *
	 */
	private function __construct(Nails $oNails) {
		#$oNails->getNails("Session_Install");

		$this->oNails	= $oNails;
		$this->oDB		= $oNails->getDatabase();

		//ther is no user id so try and retrive it
		if ($this->iUserID == false) {
			$this->oUser		= $this->oNails->getUser();
			$this->iUserID		= $this->oUser->getUserID();
			$this->cUserName	= $this->oUser->getUserName();
		}

		if (Hammer::$cWebSite) {
			$this->cSited	= $this->oNails->cSite;
		}

        $this->tsLastLogin = $this->getCookie("lastVisit");

        //get the robot nail
        $this->oRobot		= Session_Robots::getInstance($oNails);
	}

	/**
	 * Session::__destruct()
	 *
	 */
	public function __destruct() {
		$this->oUser	= null;
		$this->oRobot	= null;
		$this->oDB		= null;
	}

	/**
	 * Session::getInstance()
	 * @param Nails $oNails
	 * @return object
	 */
	static function getInstance(Nails $oNails) {
		if (is_null(self::$oSession)) {
			self::$oSession = new Session($oNails);
		}

		return self::$oSession;
	}

	/**
	 * Session::lastLogin()
	 *
	 * @return
	 */
	public function lastLogin() {
		$cVisitor = visitorIP();

		if ($this->iUserID) {
            $this->oDB->read("SELECT tsDate FROM users_sessions WHERE iUserID = ? ORDER BY iSessionID DESC LIMIT 1", $this->iUserID);
            if ($this->oDB->nextRecord()) {
                if ($this->oDB->f('tsDate') < (time() - 1000)) {
                    $aEscape = array($this->iUserID, session_id(), "Old Session", ip2long($cVisitor), $_SERVER['HTTP_USER_AGENT']);

                    $this->oDB->write("INSERT INTO users_sessions (iUserID, cLastSessionID, tsDate, cReason, cIP, cBrowser) VALUES (?, ?, UNIX_TIMESTAMP(), ?, ?, ?)", $aEscape);
                    $this->createCookie("lastVisit", time(), true);
                    $this->tsLastLogin = time();
                } else {
                    $this->createCookie("lastVisit", time(), true);
	                $this->tsLastLogin = time();
                }
            } else {
                $aEscape = array($this->iUserID, session_id(), "New Session", ip2long($cVisitor), $_SERVER['HTTP_USER_AGENT']);

                $this->oDB->write("INSERT INTO users_sessions (iUserID, cLastSessionID, tsDate, cReason, cIP, cBrowser) VALUES (?, ?, UNIX_TIMESTAMP(), ?, ?, ?)", $aEscape);
                $this->createCookie("lastVisit", time(), true);
                $this->tsLastLogin = time();
            }

			$this->addAction("ReVisit");

			//generate a new sessionid
			session_regenerate_id();

			//Add Vistor
			$this->oRobot->addVistor();

            return $this->tsLastLogin;
		} else {
			$this->oRobot->addRobot();

			return false;
		}
	}

	/**
	 * Session::setLogin()
	 *
	 * @return
	 */
	public function setLogin() {
	        if (!$this->getCookie("userLogin")) {
        	    $this->createCookie("userLogin", time());
		    	$this->tsLogin = time();

		    	if ($this->iUserID) {
		    		$cVisitor = visitorIP();
					$aEscape = array($this->iUserID, session_id(), "Login", ip2long($cVisitor), $_SERVER['HTTP_USER_AGENT']);
					$this->oDB->write("INSERT INTO users_sessions(iUserID, cLastSessionID, tsDate, cReason, cIP, cBrowser) VALUES (?, ?, UNIX_TIMESTAMP(), ?, ?, ?)", $aEscape);
		    	}
	        } else {
        	    $this->tsLogin = $this->getCookie("userLogin");
	        }

		//regenerate the id again
		session_regenerate_id();

		return $this->tsLogin;
	}

	/**
	 * Session::addAction()
	 *
	 * @param string $cAction
	 * @param string $cSite This is if your doing stuff in cli really
	 * @return null
	 */
	public function addAction($cAction, $cSite = false, $iUserID = null) {
		if (!$iUserID) {
			$iUserID	= $this->iUserID	? $this->iUserID : "0";
		} else {
			//Add the userid if given, since its not included normally
			$cAction .= $iUserID;
		}

		$dDate		= date("d-m-Y H:i:s");

		//Very long winded way of doing this
		if (isset($this->oNails->cSited)) {
			if ($this->oNails->cSited) {
				$cWebSite = $this->oNails->cSited;
			}
		} else if ($cSite) {
			$cWebSite	= $cSite;
		} else {
			if (isset($_SERVER['SERVER_NAME'])) {
				$cWebSite = $_SERVER['SERVER_NAME'];
			} else {
				if (isset($_SERVER['SERVER_NAME'])) {
					$cWebSite = $_SERVER['SERVER_NAME'];
				} else {
					$cWebSite	= "Default";
				}
			}
		}

		//now get the username
		if ($iUserID) {
			if (!$this->oUser) { $this->oUser = $this->oNails->getUser(); }
			$this->cUsername	= $this->oUser->getUsername($iUserID);
			$this->iUserID		= $iUserID;
		}

		//Now create the log entry itself to be done with later
		$cLog	 = "Site: " . $cWebSite . " :: ";

		//if user
		if ($iUserID) {
			$cLog	.= "User: " . $this->cUsername . " (" . $this->iUserID . ") :: ";
			$iLog	= LOG_INFO;
		} else {
			$cLog	.= "**Non-User**";
			$iLog	= LOG_WARNING;
		}

		//the action itself
		$cLog	.= "Action: " . $cAction . " :: ";

		//todays date
		$cLog	.= "Date: " . $dDate . " :: ";

		//Visotr IP
		$cAddress	= visitorIP();

		//has this user got an ip
		if ($cAddress) {
			//see if we can get a hostname
			if (function_exists("gethostbyaddr")) {
				if (defined("host-detail")) {
					$cLog .= "Address: " . gethostbyaddr($cAddress) . " (" . $cAddress . ") :: ";
				} else {
					$cLog .= "Address: " . $cAddress . " :: ";
				}
			} else {
				$cLog	.= "Address: " . $cAddress . " :: ";
			}

			$cLog	.= "IP2Long: " . ip2long($cAddress) . " :: ";
		}

		//has this user got a user-agent
		if (isset($_SERVER['HTTP_USER_AGENT'])) { $cLog .= "UserAgent: " . $_SERVER['HTTP_USER_AGENT'] . " :: "; }

		//Query String
		if (isset($_SERVER['QUERY_STRING'])) { $cLog .= "Query String: " . $_SERVER['QUERY_STRING'] . " :: "; }

		//add a seperate line for the end
		$cLog .= "\n";

		//Logging might want to log to both
		if (defined("Logging")) {
			switch(Logging) {
				case "Both":
					$this->logIt($cLog, $iLog, "Console");
					$this->logIt($cLog, $iLog, "Database");
					break;
				case "Database":
					$this->logIt($cLog, $iLog, "Database");
					break;
				default:
					$this->logIt($cLog, $iLog, "Console");
					break;
			}
		} else {
			$this->logIt($cLog, $iLog, "Console");
		}
	}

	/**
	 * Session::logIt()
	 *
	 * @param string $cLog
	 * @param int $iLog
	 * @param string $cType
	 * @return null
	 */
	public function logIt($cLog, $iLog, $cType) {
		if ($cType == "Database") {
			$cVisitor = visitorIP();
			if ($cVisitor) {
				$aInsert = array($this->iUserID, session_id(), $cLog, ip2long($cVisitor), $_SERVER['HTTP_USER_AGENT']);
			} else {
				$aInsert = array(0, session_id(), $cLog, 0, 0);
			}

			$this->oDB->write("INSERT INTO user_sessions (iUserID, cLastSessionID, tsDate, cReason, cIP, cBrowser) VALUES (?, ?, UNIX_TIMESTAMP, ?, ?, ?)", $aInsert);
		} else if ($cType == "Console") {
			//check the operating system
			if (isset($_SERVER['PATH'])) {
				if (strstr($_SERVER['PATH'], "/usr/bin")) { //Linux
					$iLogFile	= LOG_LOCAL0;
				} else {
					$iLogFile	= LOG_USER;
				}
			} else {
				$iLogFile	= LOG_SYSLOG; //no idea on the OS
			}

			$cSite	= $this->oNails->cSited;
			if (!$cSite) { $cSite = "unknown"; }

			$cLocation	 = HAMMERPATH . "/logs/";
			$cLocation	.= $cSite . ".log";

			//since they all do this, make it more effecient
			/**
			openlog("HammerLog", LOG_PID | LOG_PERROR, $iLogFile);
				syslog($iLog, $cLog);
			closelog();
			*/
			error_log($cLog, 3, $cLocation);
		}
	}

	/**
	 * Session::getVisitors()
	 *
	 * @param int $iStart
	 * @param int $iEnd
	 * @return array
	 */
	public function getVisitors($iStart = false, $iEnd = false) {
		$aData		= false;
		$aReturn	= false;
		$i			= 0;

		if (!$iStart) { $iStart = strtotime("yesterday"); }
		if (!$iEnd) { $iEnd	= time(); }

		$aRead	= array($iStart, $iEnd);
		$this->oDB->read("
			SELECT
				COUNT(*) AS visitors,
				FROM_UNIXTIME(tsDate, '%d/%m/%Y') AS dated,
				tsDate
			FROM users_sessions_visitors
			WHERE (tsDate BETWEEN (?) AND (?))
			GROUP BY dated DESC
		", $aRead);
		while ($this->oDB->nextRecord()) {
			$aData[$i]['visitors']	= $this->oDB->f('visitors');
			$aData[$i]['day']		= date("l", $this->oDB->f('tsDate'));
			$aData[$i]['date']		= $this->oDB->f("dated");

			if ($i == 0) {
				$aData[$i]['diff']		= ($aData[$i]['visitors'] - 0);
			} else {
				$aData[$i]['diff']		= ($aData[$i]['visitors'] - $aData[$i - 1]['visitors']);
			}
			$i++;
		}

		//add the blank data if needed
		$iDays  = (($iEnd - $iStart) / 86400);
		$iInit  = $iStart;
		for ($i = 0; $i < $iDays; $i++) {
			if ($i == 0) {
				$tsDate	= $iInit;
			} else {
				$tsDate	= ($iInit + ($i * 86400));
			}

			$dDate = date("d/m/Y", $tsDate);

			//add the default info
			$aFinal[$i]['visitors']	= 0;
			$aFinal[$i]['day']		= date("l", $tsDate);
			$aFinal[$i]['date']		= $dDate;
			$aFinal[$i]['diff']		= 0;

			//make sure there is something for that date
			for ($j = 0; $j < count($aData); $j++) {
				if ($dDate == $aData[$j]['date']);

				$aFinal[$i] = $aData[$j];
				break;
			}
		}

		return $aFinal;
	}
}

/**
 * Session_Exception
 *
 * @package
 * @author keloran
 * @copyright Copyright (c) 2011
 * @version $Id$
 * @access public
 */
class Session_Exception extends Exception { }
<?php
/**
 * User
 *
 * @package
 * @author Max
 * @copyright Copyright (c) 2008
 * @version $Id: user.inc.php 457 2009-12-26 20:45:50Z keloran $
 * @access public
 */
class User implements Nails_Interface {
	use Security, Cookie;

	public $iUserID     = false;
	public $iGroupID    = false;
	public $cError		= false;
	public $aConfig     = false;

	static $oUser		= false;

	//decouple
	private $oNails	= false;
	private $oDB	= false;
	private $cPage	= false;

	//Set to logged in
	public $bLogged = false;

    /**
     * Constructor
     * @param object $oNails
     * @access protected
     */
    private function __construct(Nails $oNails, $bNoInstall = null) {
		$this->oNails	= $oNails;
		$this->oDB		= $this->oNails->getDatabase();
		$this->cPage	= $oNails->cPage;

    	//installed
    	if ($this->oNails->checkInstalled("users") == false) {
			$this->install();
		}

    	//update
		if ($this->oNails->checkVersion("users", 1.6) == false) {
			//1.1
			printRead("1.1 Start");
			$cSQL	= "CREATE TABLE IF NOT EXISTS `users_special_privs` (`iSpecialID` INT NOT NULL AUTO_INCREMENT, `cAllowed` VARCHAR(50) NOT NULL, `iUserID` INT NOT NULL, PRIMARY KEY (`iSpecialID`), INDEX (`iUserID`))";
			$this->oNails->updateVersion("users", 1.1, $cSQL, "Added the special Privs table");
			printRead("1.1 End");

			//1.2
			printRead("1.2 Start");
			$this->oNails->updateVersion("users", 1.2, false, "De-Coupled");
			printRead("1.2 End");

			//1.3
			printRead("1.3 Start");
			$cSQL	= "ALTER TABLE `users` ADD COLUMN `bTemp` BOOL NOT NULL";
			$this->oNails->updateVersion("users", 1.3, $cSQL, "Added Temp flag for passwords");
			printRead("1.3 End");

			//1.4
			printRead("1.4 Start");
			$cSQL 	= "CREATE TABLE IF NOT EXISTS `users_notify` (`iNotifyID` INT NOT NULL AUTO_INCREMENT, `iUserID` INT NOT NULL, `cMessage` TEXT, PRIMARY KEY(`iNotifyID`), INDEX(`iUserID`))";
			$this->oNails->updateVersion("users", 1.4, $cSQL, "Added Notifications");
			printRead("1.4 End");

			//1.5
			printRead("1.5 Start");
			$mSQL	= array(
				"ALTER TABLE `users` CHANGE COLUMN `cPassword` `cPassword` VARCHAR(65) NOT NULL",
				"ALTER TABLE `users` CHANGE COLUMN `cRegisterHash` `cRegisterHash` VARCHAR(65) NOT NULL",
				"ALTER TABLE `users` CHANGE COLUMN `cLoginHash` `cLoginHash` VARCHAR(65) NOT NULL"
			);
			$this->oNails->updateVersion("users", 1.5, $mSQL, "Updated MD5 fields to 65 chars");
			printRead("1.5 End");

			//1.6
			printRead("1.6 Start");
			$mSQL = array(
				"ALTER TABLE `users` ADD COLUMN `bDeleted` BOOL DEFAULT 0",
				"ALTER TABLE `users` ADD COLUMN `bBanned` BOOL DEFAULT 0"
			);
			$this->oNails->updateVersion("users", 1.6, $mSQL, "Added Deleted and Banned flags");
			printRead("1.6 End");
    	}

		//Cookie
        $this->cCookie = $this->getCookie("userCookie");
        if ($this->cCookie) {
        	$this->iUserID  = $this->getID();
        	$this->iGroupID = $this->getGroupID();
        	$this->bLogged	= true;

			//Is this user actually banned
        	$cGroup = $this->getGroupName();
			if ($cGroup == "banned") {
				switch ($this->cPage) {
					case "login":
					case "banned":
						break;

					default:
						$this->oNails->sendLocation("/banned/");
						break;
				}
			}
        }
    }

	/**
	 * User::__destruct()
	 *
	 */
	public function __destruct() {
		$this->oNails	= false;
		$this->oDB		= false;
	}

    /**
     * User::getInstance()
     *
     * @param object $oNails
     * @param bool $bNoInstall dont want installer to happen, e.g. the setup file running
     * @return object
     */
    static function getInstance(Nails $oNails, $bNoInstall = null) {
    	if (!self::$oUser) {
    		self::$oUser = new User($oNails, $bNoInstall);
    	}

    	return self::$oUser;
    }

    /**
     * User::install()
     *
     * @return null
     */
    private function install() {
    	//Create the groups table
    	printRead("users_groups");
    	$this->oNails->addTable("
			CREATE TABLE IF NOT EXISTS `users_groups` (
				`iGroupID` INT NOT NULL AUTO_INCREMENT,
				`cGroup` VARCHAR(50) NOT NULL,
				PRIMARY KEY (`iGroupID`)
			) ENGINE = InnoDB");

		//Create the users table and its index`s
    	printRead("users");
		$this->oNails->addTable("
			CREATE TABLE IF NOT EXISTS `users` (
				`iUserID` INT NOT NULL AUTO_INCREMENT,
				`tsDate` INT NOT NULL,
				`cUsername` VARCHAR(150) NOT NULL,
				`cPassword` VARCHAR(65) NOT NULL,
				`cEmail` VARCHAR(250) NOT NULL,
				`cUserImage` VARCHAR(50) NOT NULL,
				`iGroupID` INT NOT NULL,
				`cRegisterHash` VARCHAR(65) NOT NULL,
				`cLoginHash` VARCHAR(65) NOT NULL,
				`cLastIP` VARCHAR(15) NOT NULL,
				PRIMARY KEY (`iUserID`),
				INDEX (`iGroupID`),
				INDEX (`tsDate`)
			) ENGINE = InnoDB");

    	printRead("users_settings");
    	$this->oNails->addTable("
			CREATE TABLE IF NOT EXISTS `users_settings` (
				`iSettingID` INT NOT NULL AUTO_INCREMENT,
				`iUserID` INT NOT NULL,
				`cSettingName` VARCHAR(50) NOT NULL,
				`cSettingValue` VARCHAR(50) NOT NULL,
				PRIMARY KEY (`iSettingID`),
				INDEX (`iUserID`),
				FOREIGN KEY (`iUserID`)
					REFERENCES `users` (`iUserID`)
					ON DELETE CASCADE
					ON UPDATE CASCADE
			) ENGINE = InnoDB");

		//Install the groups
    	printRead("Add Version");
    	$this->oNails->addVersion("users", "1.0");

    	printRead("Add Groups");
		$this->addInstallGroups();
    }

	/**
	 * User::addInstallGroups()
	 *
	 * @return null
	 */
	private function addInstallGroups() {
		$aEscape = array(
			"Non-Registered",
			"Non-Confirmed",
			"Registered",
			"Banned",
			"Admin"
		);
		$this->oDB->write("INSERT INTO `users_groups` (cGroup) VALUES (?), (?), (?), (?), (?)", $aEscape);

		//add the groups
		$this->oNails->addGroups("install");
		$this->oNails->addAbility("Admin", "install");

		//check it
		$aGroups = false;
		$this->oDB->read("SELECT cGroup FROM users_groups");
		while($this->oDB->nextRecord()){ $aGroups[] = $this->oDB->f("cGroup"); }

		if (count($aGroups) == 5) {
			$this->oNails->addVersion("users_groups", "1.0");
			$this->oNails->sendLocation("install");
		} else {
			printRead("Stuff has gone wrong with the install of groups");
			die();
		}
	}

	/**
	* User::checkPassword()
	*
	* @desc this is mainly used for admin area
	* @param string $cPassword
	* @return bool
	*/
	function checkPassword($cPassword) {
		$bReturn	= false;
		$cUsername	= $this->getUsername();

		if ($cUsername) {
			$aCheck	= array($cUsername, $cPassword);
			$this->oDB->read("SELECT iUserID FROM users WHERE cUsername = ? AND cPassword = MD5(?) LIMIT 1", $aCheck);
			if ($this->oDB->nextRecord()) {
				$bReturn	= true;
			}
		}

		return $bReturn;
	}

	/**
	 * User::updatePassword()
	 *
	 * @param string $cPassword
	 * @return null
	 */
	public function updatePassword($cPassword) {
		$cUsername	= $this->getUsername();

		if ($cUsername) {
			$aUpdate = array($cPassword, $cUsername);
			$this->oDB->write("UPDATE users SET cPassword = MD5(?) WHERE cUsername = ? LIMIT 1", $aUpdate);
		}

		return false;
	}

    /**
     * User::getUserID()
     *
     * @desc This gets the users id based on either there cookie, or the hash of a cookie
     * @param string $cCookie
     * @param bool $bIsHash Old method not really used anymore
     * @return mixed
     */
    public function getUserID($cCookie = false, $bIsHash = false) {
        if ($bIsHash) {
            $cCookieHash = $cCookie;
        } else {
        	//if there is a userid already set (e.g. by login and hasnt created a cookie yet)
        	if ($this->iUserID) {
        		return $this->iUserID;
        	} else {
        		if ($cCookie) {
	            	$cCookieHash	= $this->getCookie($cCookie);
	        	} else {
	        		$cCookieHash	= $this->cCookie;
	        	}
        	}
        }

    	//get the ip
    	$cVisitor = visitorIP();

    	//is there a cookie hash
        if ($cCookieHash) {
        	$aCheck = array($cCookieHash, ip2long($cVisitor));
            $this->oDB->read("SELECT iUserID FROM users WHERE cLoginHash = ? AND cLastIP = ? LIMIT 1", $aCheck);
            if ($this->oDB->nextRecord()) {
                $this->iUserID = $this->oDB->f('iUserID');
            }

            return $this->iUserID;
        } else if ($this->cCookie) {
        	$aCheck = array($this->cCookie, ip2long($cVisitor));
            $this->oDB->read("SELECT iUserID FROM users WHERE cLoginHash = ? AND cLastIP = ? LIMIT 1", $aCheck);
            while ($this->oDB->nextRecord()) {
                $this->iUserID = $this->oDB->f('iUserID');
            }

            return $this->iUserID;
        } else {
            return false;
        }
    }

    /**
     * User::getID()
     *
     * @param string $cCookie
     * @param bool $bIsHash
     * @return mixed
     */
    public function getID($cCookie = null, $bIsHash = null) {
    	return $this->getUserID($cCookie, $bIsHash);
    }

    /**
     * User::getUserGroupID()
     *
     * @desc Whats hte users group id, not really used better to use canDoThis()
     * @return int
     */
    public function getUserGroupID() {
  		if ($this->iUserID) {
            $this->oDB->read("SELECT iGroupID FROM users WHERE iUserID = ? LIMIT 1", $this->iUserID);
            if ($this->oDB->nextRecord()) {
                $this->iGroupID = $this->oDB->f('iGroupID');
            }
        } else {
            $this->iGroupID = 1;
        }

        return $this->iGroupID;
    }

    /**
    * User::whatGroupID
    *
    * @desc This is to get what a groupid is based on the name
    * @param string $cName
    * @return int
    */
    public function whatGroupID($cName) {
    	$iReturn	= false;

    	$this->oDB->read("SELECT iGroupID FROM users_groups WHERE cGroup LIKE ? LIMIT 1", $cName);
    	if ($this->oDB->nextRecord()) {
    		$iReturn	= $this->oDB->f("iGroupID");
    	}

    	return $iReturn;
    }

    /**
     * User::getGroupID()
     *
     * @desc This is a pointer to getUserGroupID to make it more standardized
     * @return bool
     */
    public function getGroupID() {
    	return $this->getUserGroupID();
    }

	/**
	 * User::getUserGroupName()
	 *
	 * @desc Gets the groups name, better to use perms canDoThis()
	 * @return string
	 */
	public function getUserGroupName() {
		if ($this->iUserID) {
			if (!$this->iGroupID) { $this->getUserGroupID(); }

			$this->oDB->read("SELECT cGroup FROM users_groups WHERE iGroupID = ? LIMIT 1", $this->iGroupID);
			if ($this->oDB->nextRecord()){
				return ucfirst($this->oDB->f('cGroup'));
			}
		}

		return false;
	}

	/**
	 * User::getGroupName()
	 *
	 * @desc this is a shortcut to usergroupname since its in the user class
	 *  so why do i need to call it get user%
	 *  and lowercase it
	 * @return string
	 */
	public function getGroupName() {
		return strtolower($this->getUserGroupName());
	}

	/**
	 * User::checkExists()
	 *
	 * @param string $cUsername
	 * @return false
	 */
	private function checkExists($cUsername) {
		$bReturn	= false;

		$this->oDB->read("SELECT iUserID FROM users WHERE cUsername = ? LIMIT 1", $cUsername);
		if ($this->oDB->nextRecord()) {
			$bReturn = true;
		}

		return $bReturn;
	}

    /**
     * User::register()
     *
     * @todo Change MD5 to SHA, this should really be normalized
     * @desc This is used to register people
     * @param string $cUsername
     * @param string $cPassword
     * @param string $cPassword2
     * @param string $cEmail
     * @return bool
     */
    public function register($cUsername, $cPassword, $cPassword2, $cEmail) {
		$bExists = $this->checkExists($cUsername);

		//does the username already exist
		if ($bExists) {
			$this->cError .= "Sorry that username has been taken<br />";
			return false;
		}

		//do the passwords match
		if ($cPassword !== $cPassword2) {
			$this->cError .= "Sorry passwords don't match<br />";
			return false;
		}

        $cRegisterHash = $this->genHash($cEmail);
        $cLoginHash = $this->genHash($cUsername);

        $aEscape = array($cUsername, $cPassword, $cEmail, $cRegisterHash, $cLoginHash);
		$this->oDB->write("
			INSERT INTO users
				(cUsername, cPassword, cEmail, tsDate, cRegisterHash, cLoginHash, iGroupID)
			VALUES
				(?, MD5(?), ?, UNIX_TIMESTAMP(), ?, ?, 2)", $aEscape);
		$iUserID = $this->oDB->insertID();

		$this->sendConfirm($cEmail, $cRegisterHash, $iUserID, true);

		return true;
    }

	/**
	* User::addUser()
	* @desc This is to add a user to the site and tell them there details
	* @todo Should this really be in the admin lib
	* @param string $cName What username do want to give them
	* @param string $cEmail waht is tehre email so we can email them there details
	* @param int $iLength how long should the email be
	* @return null
	*/
    public function addUser($cName, $cEmail, $iLength = 8) {
    	$cPass 		= $this->genPassword($iLength);
		$cHash		= $this->genHash($cEmail);
		$aInsert	= array($cName, $cPass, $cEmail, $cHash, $cHash);

		$this->oDB->write("INSERT INTO users (cUsername, cPassword, cEmail, tsDate, cLoginHash, cRegisterHash, iGroupID, bTemp) VALUES (?, MD5(?), ?, UNIX_TIMESTAMP(), ?, ?, 3, 1)", $aInsert);
		$iUserID	= $this->oDB->insertID();

		$this->sendRegister($cEmail, $cName, $cPass);
	}

	/**
	 * User::login()
	 *
	 * @todo Change MD5 to SHA
	 *
	 * @param array $mDetails
	 * @param string $cPassword
	 * @param bool $bInternal
	 * @return string
	 */
	public function login($mDetails, $cPassword = false, $bInternal = false) {
		$cUsername	= $mDetails		?: false;
		$cPassword	= $cPassword	?: false;
		$bEmail		= false;
		$cHash		= false;

		if (is_array($mDetails)) {
			foreach ($mDetails as $cKey => $mValue) {
				switch($cKey){
					case "login":
						$cUsername = $mValue;
						break;

					case "password":
						$cPassword = $mValue;
						break;

					case "email":
						$bEmail = $mValue;
						break;

				} // switch
			}
		} else {
			$cUsername = $mDetails;
		}

		//details
		$aEscape 	= array($cUsername, $cPassword);

		//email login
		if ($bEmail) {
			$this->oDB->read("SELECT iUserID, iGroupID FROM users WHERE cEmail = ? AND cPassword = MD5(?) LIMIT 1", $aEscape);
		} else { //username login
			$this->oDB->read("SELECT iUserID, iGroupID FROM users WHERE cUsername = ? AND cPassword = MD5(?) LIMIT 1", $aEscape);
		}

		if ($this->oDB->nextRecord()) {
			$iGroupID	= $this->oDB->f('iGroupID');
			$iUserID	= $this->oDB->f('iUserID');

			//set the userdetails for login
			$this->iUserID		= $iUserID;
			$this->cUsername	= $cUsername;
			$cVisitor			= visitorIP();

			$aUpdate = array(ip2long($cVisitor), $iUserID);
			$this->oDB->write("UPDATE users SET cLastIP = ? WHERE iUserID = ? LIMIT 1", $aUpdate);

			if ($iGroupID >= 3) {
				//generate a new login hash to stop cookie stealing,
				//also generate a new session hash, to stop session stealing
				session_regenerate_id();
				$cNewHash	= session_id();
				$cHash		= $this->genHash($cNewHash);

				if (!$bInternal) { $this->oNails->createCookie("userCookie", $cHash, true); }

				$aUpdate = array($cHash, $iUserID);
				$this->oDB->write("UPDATE users SET cLoginHash = ? WHERE iUserID = ? LIMIT 1", $aUpdate);

				//Set the action
				$oSession = $this->oNails->getSession();
				$oSession->addAction("Login: ", false, $iUserID);
			}

			//banned
			if ($this->getGroupName() == "banned") { $this->oNails->sendLocation("/banned/"); }
		} else {
			$oSession 	= $this->oNails->getSession();
			$cLog		= print_r(array(
				"Username"	=> $cUsername,
				"Password"	=> $cPassword
			), true);
			$oSession->logIt($cLog, 1, "Console");
		}

		return $cHash;
	}

	/**
	* User::getPassword()
	*
	* @desc This is used by createUser
	* @param int $iLength This is used to state how long the password should be
	* @return string
	*/
    private function genPassword($iLength) {
    	$cRand = "";
    	$aLetters	= range("a", "z");
    	$aNumbers	= range(0, 9);
    	$aContent	= array_merge($aLetters, $aNumbers);

		for ($i = 0; $i < $iLength; $i++) {
			$iRand = array_rand($aContent);
			$cRand .= $aContent[$iRand];
		}

		return $cRand;
	}

	/**
	* User::isTemp()
	*
	* @desc This is to check if the password is temp and needs to be changed
	* @return bool
	*/
	public function isTemp() {
		$this->oDB->read("SELECT bTemp FROM users WHERE iUserID = ? AND bTemp = 1 LIMIT 1", $this->iUserID);
		if ($this->oDB->nextRecord()) {
			return true;
		} else {
			return false;
		}
	}

    /**
     * User::sendConfirm()
     *
     * @param string $cEmail
     * @param string $cRegisterHash
     * @param int $iUserID
     * @return null
     */
    public function sendConfirm($cEmail, $cRegisterHash, $iUserID) {
		$oHammer	= Hammer::getHammer();
		$oHead		= $oHammer->getHead();

		$cAddress	= $oHammer->getConfig("address", $oHammer->getConfigKey());
	    	$aTitle		= $oHammer->getconfig("title");
    		$cTitle		= $aTitle[0];

		$cTitle = "Confirmation email from " . $oHead->aHead['title'];
		$cMessage = "You will need to confirm your email address by clicking on the following link\n<br />";
		$cMessage .= "<a href=\"http://" . $_SERVER['HTTP_HOST'] . "/login/confirm/" . $cRegisterHash . "/\">Here</a>\n<br />";
		$cMessage .= "or by going to http://" . $_SERVER['HTTP_HOST'] . "/login/confirm/" . $cRegisterHash . "/\n<br />";
		$cMessage .= "thanks";

		if (defined("DEV") && defined("NOMAIL")) {
			throw new Spanner($cMessage, 600);
		} else {
			sendEmail($cEmail, $cTitle, $cMessage, "Admin", "admin@" . $cAddress);
		}
    }

	/**
	* User::sendRegister()
	*
	* @desc This sends the user the details for the account
	* @todo This might be better in admin lib, i needs normalizng
	* @param string $cEmail Email of the user that needs the details sending
	* @param string $cUsername The username the user has been given
	* @param string $cPassword the temp password the user has been given
	* @return null
	*/
    public function sendRegister($cEmail, $cUsername, $cPassword) {
		$oHammer	= Hammer::getHammer();
		$oHead		= $oHammer->getHead();

    	$cTitle		= "Your Account Details For " . $oHead->aHead['title'];
		$cMessage	= "You have been registered, on <a href=\"http://" . $_SERVER['HTTP_HOST'] . "\">" . $oHead->aHead['title'] . "</a>\n<br />";
		$cMessage	.= "Your username is " . $cUsername . "\n<br />";
		$cMessage	.= "Your temporary password is " . $cPassword . "\n<br />";
		$cMessage	.= "To Login and change the password goto <a href=\"http://" . $_SERVER['HTTP_HOST'] . "/login\">" . $oHead->aHead['title'] . "</a>";

		if (defined("DEV") && defined("NOMAIL")) {
			throw new Spanner($cMessage, 601);
		} else {
			sendEmail($cEmail, $cTitle, $cMessage, "Admin", "admin@" . $oHead->aHead['address']);
		}
	}

    /**
     * User::getUsername()
     *
     * @return mixed
     */
    public function getUsername($iUserID = null) {
    	$cReturn	= false;

    	//a userid has been given so we are trying to find details
    	if (!$iUserID) { $iUserID = $this->iUserID; }

    	if ($iUserID) {
			$this->oDB->read("SELECT cUsername FROM users WHERE iUserID = ? LIMIT 1", $iUserID);
        	if ($this->oDB->nextRecord()) {
            	$cReturn = $this->oDB->f('cUsername');
        	}
    	}

    	return $cReturn;
    }

    /**
     * User::getUserLimit()
     *
     * @return int
     */
    public function getUserLimit() {
		if (!$this->iUserID) { return 10; }

		if ($this->getCookie("userLimit")) {
			return $this->getCookie("userLimit");
		} else {
			$iLimit = $this->getSetting("userLimit");
			if ($iLimit) {
				$this->createCookie("userLimit", $iLimit, true);
				return $iLimit;
			} else {
				return 10;
			}
		}
	}

	/**
	 * User::setGroup()
	 *
	 * @param int $iGroupID
	 * @param int $iUserID
	 * @return
	 */
	public function setGroup($iGroupID, $iUserID) {
		$aUpdate	= array($iGroupID, $iUserID);
		$this->oDB->write("UPDATE users SET iGroupID = ? WHERE iUserID = ? LIMIT 1", $aUpdate);
	}

	/**
	* User::addSpecial()
	*
	* @desc Add a special ability to a user so if User A is in Group G and needs to do something like addnews, but doesnt need the perm of delete
	* @todo Should this really be in the admin lib
	* @param string $cAllowed What is the permision they need
	* @param int $iUserID What is the id of the user that needs the perm
	* @return bool
	*/
    public function addSpecial($cAllowed, $iUserID) {
    	$cAllowed = "b" . ucfirst($cAllowed);

		if ($this->canDoThis("addAbilitys")) {
			$aInsert = array($cAllowed, $iUserID);
			$this->oDB->write("INSERT INTO users_special_privs (cAllowed, iUserID) VALUES (?, ?)", $aInsert);
			if ($this->oDB->insertID()) {
				return true;
			} else {
				return false;
			}
		}
	}

	/**
	 * User::canDoThis()
	 *
	 * @param string $cAllowed
	 * @return bool
	 */
	public function canDoThis($cAllowed) {
		if (!$this->iGroupID) { return false; }

		$cCheck		= $cAllowed;
		$cAllowed	= "b" . ucfirst($cAllowed);
		$this->oDB->read("SELECT TRUE FROM users_groups WHERE " . $cAllowed . " = 1 AND iGroupID = ? LIMIT 1", $this->iGroupID);
		if ($this->oDB->nextRecord()) { return true; }

		//special check
		if (!$this->iUserID) { return false; }
		$aSelect = array($this->iUserID, $cAllowed);
		$this->oDB->read("SELECT TRUE FROM users_special_privs WHERE iUserID = ? AND cAllowed = ? LIMIT 1", $aSelect);
		if ($this->oDB->nextRecord()) { return true; }

		return false;
	}

	/**
	* User::isUserGroup();
	*
	* @desc this is an easier way of checking if the user is in a specific group
	* @param string $cName
	* @return bool
	*/
	public function isUserGroup($cName) {
		if (!$this->iGroupID) { return false; }

		$aSelect = array(ucfirst($cName), $this->iGroupID);
		$this->oDB->read("SELECT ? FROM users_groups WHERE iGroupID = ? LIMIT 1", $aSelect);
		if ($this->oDB->nextRecord()) {
			return true;
		}

		return false;
	}

	/**
	 * User::getUserImage()
	 *
	 * @return mixed
	 */
	public function getUserImage() {
		if (!$this->iUserID) { return false; }

		$this->oDB->read("SELECT cUserImage FROM users WHERE iUserID = ? LIMIT 1", $this->iUserID);
		if ($this->oDB->nextRecord()) {
		    $cReturn = $this->oDB->f('cUserImage');
		}

		if ($cReturn) {
			return $cReturn;
		} else {
			return false;
		}
	}

    /**
     * User::getAllUsers()
     *
     * @desc This is used to give a list of all the users, mainly used for admin
     * @todo Should this really be in the admin area
     * @return mixed
     */
    public function getAllUsers($aNot = null) {
    	$aReturn	= false;
		$cNot		= false;
		if ($aNot) {
			$cNot 	= " WHERE ";
			$i		= 0;
			foreach ($aNot as $cName => $cValue) {
				if ($i !== 0) { $cNot .= "AND "; }
				$cNot .= $cName . " = " . $cValue . " ";
				$i++;
			}
		}

        $this->oDB->read("SELECT cUsername, iUserID, tsDate FROM users " . $cNot . " ORDER BY iUserID DESC");
        $i = 0;
        while ($this->oDB->nextRecord()) {
            $aReturn[$i]['iUserID']     = $this->oDB->f('iUserID');
            $aReturn[$i]['cUsername']   = $this->oDB->f('cUsername');
            $aReturn[$i]['tsDate']		= $this->oDB->f('tsDate');
            $i++;
        }

        return $aReturn;
    }

    /**
     * User::getGroups()
     *
     * @return mixed
     */
    public function getGroups() {
        $this->oDB->read("SELECT cGroup, iGroupID FROM users_groups ORDER BY iGroupID DESC");
        $i = 0;
        while ($this->oDB->nextRecord()) {
            $aReturn[$i]['cGroup']      = $this->oDB->f('cGroup');
            $aReturn[$i]['iGroupID']    = $this->oDB->f('iGroupID');
            $i++;
        }

        if (isset($aReturn)) {
            return $aReturn;
        } else {
            return false;
        }
    }

    /**
     * User::forgotDetails()
     *
     * @param string $cEmail
     * @return
     */
    public function forgotDetails($cEmail) {
        $this->oDB->read("SELECT cUsername FROM users WHERE cEmail = ? LIMIT 1", $cEmail);
        if ($this->oDB->nextRecord()) {
            $cNewPass 	= $this->genHash($this->oDB->f('cUsername'));
        	$cUsername	= $this->oDB->f('cUsername');

            $aEscape = array($cNewPass, $cUsername);
            $this->oDB->write("UPDATE users SET cPassword = MD5(?) WHERE cUsername = ? LIMIT 1", $aEscape);

        	$cSiteTitle	= $this->oNails->getConfig("title", $this->oNails->getConfigKey());
        	$cAddress	= $this->oNails->getConfig("address", $this->oNails->getConfigKey());

            $cTitle     = "Login Details for " . $this->oNails->getConfig("title", $this->oNails->getConfigKey());
            $cMessage   = "Your password has been reset to a " . $cNewPass . "\n";
            $cMessage   .= "You can change this now, by going to settings\n";

			sendEmail($cEmail, $cTitle, $cMessage, $cMessage, "admin@" . $cAddress, $cSiteTitle . " Admin");
        }
    }

	/**
	 * User::getSetting()
	 *
	 * @param string $cSetting
	 * @return
	 */
	public function getSetting($cSetting) {
		if (!$this->iUserID) { return false; }

		$aRead = array($cSetting, $this->iUserID);
		$this->oDB->read("SELECT cSettingValue FROM users_settings WHERE cSettingName = ? AND iUserID = ? LIMIT 1", $aRead);
		while($this->oDB->nextRecord()) {
			$cReturn = $this->oDB->f('cSettingValue');
		} // while

		if (isset($cReturn)) {
			return $cReturn;
		} else {
			return false;
		}
	}

	/**
	 * User::setSetting()
	 *
	 * @param string $cSetting
	 * @param string $cValue
	 * @return bool
	 */
	public function setSetting($cSetting, $cValue) {
		$cOldSetting	= $this->getSetting($cSetting);
		$aWrite			= array($this->iUserID, $cSetting);
		$aInsert		= array($this->iUserID, $cSetting, $cValue);

		if (!is_null($cOldSetting)) {
			$this->oDB->write("DELETE FROM users_settings WHERE iUserID = ? AND cSettingName = ?", $aWrite);
			$this->oDB->write("INSERT INTO users_settings (iUserID, cSettingName, cSettingValue) VALUES (?, ?, ?)", $aInsert);
		} else {
			$this->oDB->write("INSERT INTO users_settings (iUserID, cSettingName, cSettingValue) VALUES (?, ?, ?)", $aInsert);
		}

		return true;
	}

	/**
	* User::getUserDetails();
	*
	* @param int $iUserID
	* @return mixed
	*/
	public function getUserDetails($iUserID = null) {
		$iUserID	= $iUserID ? $iUserID : $this->getUserID();
		if (!$iUserID) { return false; }

		$this->oDB->read("
			SELECT users.cUsername, users.cUserImage, users_groups.cGroup, users.iGroupID, users.cEmail
			FROM users
			LEFT JOIN users_groups ON users.iGroupID = users_groups.iGroupID
			WHERE users.iUserID = ?
			LIMIT 1", $iUserID);
		if ($this->oDB->nextRecord()) {
			$aReturn["name"] 	= $this->oDB->f('cUsername');
			$aReturn["group"]	= $this->oDB->f('cGroup');
			$aReturn['groupid']	= $this->oDB->f('iGroupID');
			$aReturn['email']	= $this->oDB->f('cEmail');

			if ($this->oDB->f('cUserImage')) {
				$aReturn["image"]	= $this->oDB->f('cUserImage');
			} else {
				$aReturn["image"]	= "default.jpg";
			}

			return $aReturn;
		} else {
			return false;
		}
	}

	/**
	 * User::confirmUser()
	 *
	 * @param string $cCode
	 * @return bool
	 */
	public function confirmUser($cCode) {
		$bReturn	= false;

		$this->oDB->read("SELECT iUserID, iGroupID FROM users WHERE cRegisterHash = ? LIMIT 1", $cCode);
		if ($this->oDB->nextRecord()) {
			$iGroupID	= $this->oDB->f('iGroupID');
			$iUserID	= $this->oDB->f('iUserID');

			if ($iGroupID == 2) {
				$iNewID	= $this->whatGroupID("Registered");

				$aUpdate = array($iNewID, $iUserID);
				$this->oDB->write("UPDATE users SET iGroupID = ? WHERE iUserID = ? LIMIT 1", $aUpdate);

				$bReturn = true;
			}
		}

		return $bReturn;
	}
}

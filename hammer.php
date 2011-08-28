<?php
/**
 * Hammer
 *
 * @package
 * @author Max
 * @copyright Copyright (c) 2008
 * @version $Id: hammer.php 482 2010-01-04 14:26:49Z keloran $
 * @access public
 */
class Hammer {
	//Traits
	use Address, Cookie;


	/**
	* @var object $oHammer
	*/
	public static $oHammer;

	/**
	* @var string $cSiteTitle
	*/
	public static $cSiteTitle;

	/**
	* @var string $cWebSite
	*/
	public static $cWebSite;

	/**
	* @var string $cSiteaddy
	*/
	public static $cSiteAddy;
	public static $cSite;
	public $cSited;

	/**
	* @var object $oConfig
	*/
	private static $oConfig;

	/**
	* @var string $cDatabase
	*/
	public $cDatabase;

	/**
	* @var object $oDB
	*/
	public $oDB;

	/**
	* @var array $aData
	*/
	private	$aData	= array();

	/**
	* @var array $aAddress
	*/
	private $aAddress = false;

	/**
	* @var object $oNails
	*/
	private $oNails;

	/**
	* @var string $cError
	*/
	public $cError;

	/**
	* @var array $aBaseConfig
	*/
	public $aBaseConfig	= false;

	/**
	* @var string $cTitle
	*/
	public $cTitle = false;

	/**
	* @var int $iHostLocal
	*/
	public $iHostsLocal	= false;

	/**
	* @var int $iHostRemote
	*/
	public $iHostsRemote = false;

	/** @var array $aFilters */
	private $aFilters;
	private static $aFilterd;

	/** @var string $cSkin */
	public $cSkin		= false;

	/**
 	* Hammer::__construct()
	*
    * @param string $cSite
	* @param string $cSkin
	* @param array $aFilter
	*
	* @return null
    */
	public function __construct($cSite = false, $aFilter = null) {
		//Since there is an error throw it, this is very unlikelly to ever be called
		if ($this->cError) {
			throw new Spanner($this->cError, 50);
		}

		//Set the site stuff
		//Will be used in cache eventually
		if ($cSite) {
			self::$cWebSite 	= $cSite;
			$this->cSited		= $cSite;
		}

		//do the filters exist
		if ($aFilter) {
			$this->aFilters		= $aFilter;
			self::$aFilterd		= $aFilter;
		}

		//set the address
		$this->setAddress($aFilter);
	}

    /**
	* Hammer::getHammer()
	*
	* @desc Gets the object of Hammer
	*
	* @param string $cSite Not Really used but will be
	* @param string $cSkin Used for getting different Styles
	* @param array $aFilter The filters
	*
	* @return object self::$oHammer
	*/
	public static function getHammer($cSite = null, $aFilter = null) {
		if (function_exists("Config")) {
			$aHead		= Config("head");
			$cSiteAddy	= $aHead['address'];
		} else {
			$cSiteAddy 	= self::getConfigStat("address");
		}

		//get the timezone
		$cTimeZone	= self::getConfigStat("timezone");
		if (!$cTimeZone) { $cTimeZone = "Europe/London"; }

		//set the timezone
		date_default_timezone_set($cTimeZone);

		self::$cSiteAddy 	= $cSiteAddy;
		self::$cSite		= $cSite;

		if (!$cSite) {
			if (!$cSiteAddy) {
				throw new Spanner("Edit the loader script please", 1);
			}
		}

		//get the object
		if (is_null(self::$oHammer)) {
			if ($cSite) {
				self::$oHammer = new Hammer($cSite, $aFilter);
			} else {
				self::$oHammer = new Hammer($cSiteAddy, $aFilter);
			}
		}

		//see if this fixs it
		$oHammer = self::$oHammer;
		$oHammer->setAddress($aFilter, $cSiteAddy);

		return $oHammer;
	}

	/**
	 * Hammer::rootConfig()
	 *
	 * @param string $cKey
	 * @param string $cElement
	 * @return string
	 */
	private static function rootConfig($cElement, $cKey = null) {
		$mReturn	= false;

		$oConfig 	= New XML();
		$oConfig->cRoot = "config";

		if (defined("STAGED")) {
			$oConfig->setFile("config." . STAGED);
			$oConfig->cType = STAGED;
		} else {
			$oConfig->setFile("config");
			if (defined("LIVE")) {
				$oConfig->cType = "live";
			} else {
				$oConfig->cType = "dev";
			}
		}

		//Different things require different things, of course should really just do the element stuff
		if ($cKey) {
			switch($cKey){
				case "contact":
					$mReturn = $oConfig->getElement("contact");
					break;

				case "skinSetting":
					$mReturn = $oConfig->getElement("skinSetting");
					break;

				default:
					$mReturn = $oConfig->getElement($cElement, $cKey);
					break;
			}
		} else {
			$mReturn	= $oConfig->getElement($cElement);
		}

		return $mReturn;
	}

	/**
	* Hammer::getConfigStat();
	*
	* @desc This is only for use by spanner or getHammer, dont use this manually
	* @todo Proberlyl would be a good idea to set something if want from root not dev/live
	*
	* @param string $cKey
	* @param string $cElement
	* @param string $cAttribute
	* @return mixed
	*/
	public static function getConfigStat($cElement, $cKey = null, $cAttribute = null) {
		$mReturn 	= false;

		//Dont do this
		if (function_exists("Config")) { return false; }

		//Its a root element, this is pretty much always going to work, pointless doing it again
		$mRoot	= self::rootConfig($cElement, $cKey);
		if ($mRoot) { $mReturn = $mRoot; }

		return $mReturn;
	}

	/**
	 * Hammer::setAddress()
	 *
	 * @desc This gets the addresss e.g. /page/ = ?page
	 *
	 * @param array $aFilters This is if we dont use mod_rewrite
	 * @return null
	 */
	public function setAddress($aFilters = null, $cSite = false) {
		$cSiteAddress	= isset($this->cSiteAddy) ? $this->cSiteAddy : $cSite; //This sets the address, used for language/brand
		$aFilter	= $this->aFilters ? $this->aFilters : $aFilters; //do the filters already exist

		$oScrewDriver	= new ScrewDriver($aFilter, $cSiteAddress);
		$aAddress	= $oScrewDriver->finalAddress();

		//Set the variables
		if ($aAddress) {
			foreach ($aAddress as $cName => $cValue) {
				$this->$cName	= $cValue;
			}
		}
	}

	/**
	* Hammer::debugAddress()
	*
	* @desc This is for debugging purposes only
	* @return string
	*/
	public function debugAddress() {
		$cReturn	= "<hr />";
		$cReturn	.= "Page: " . trim($this->cPage) . "<br />";
		$cReturn	.= "Action: " . $this->cAction . "<br />";
		$cReturn	.= "Choice: " . $this->cChoice . "<br />";
		$cReturn	.= "Num: " . $this->iPage . "<br />";
		$cReturn	.= "Item: " . $this->iItem . "<br />";
		$cReturn	.= "SubChoice: " . $this->cSubChoice . "<br />";
		$cReturn	.= "Others-a: " . print_r($this->aOthers, true) . "<br />";
		$cReturn	.= "Others-c: " . $this->cOthers . "<br />";
		$cReturn	.= "Others-a2: " . print_r($this->aOther, true) . "<br />";
		$cReturn	.= "More: <pre>" . print_r($this->aData, true) . "</pre><br />";
		$cReturn	.= "Address Stuff: <pre>" . print_r($this->aAddress, true) . "</pre><br />";

		return $cReturn;

	}

    /**
     * Hammer::getDatabase()
     *
     * @desc This gets the database object
     * @param array $aConfig Gets the config to be used, e.g. read/write
     * @return object
     */
    public function getDatabase($aConfig = false) {
		$oDB	= Database::getInstance($aConfig);

        return $oDB;
    }

    /**
     * Hammer::getConfigKey()
     *
     * @return string
     */
    public function getConfigKey() {
    	$cReturn = false;

		if (defined("LIVE")) {
			$cReturn = "live";
		} else if (defined("DEV")) {
			$cReturn = "dev";
		}

		return $cReturn;
    }

    /**
     * Hammer::getConfig()
     *
     * @desc This gets teh config for use inside a different process, e.g. Database usage
     * this Config() method inside shouldnt be used anymore, we should always be using xml now
     * @param string $cKey This gets the parent element e.g Database
     * @param string $cElement This gets the child, e.g. Database->Read
     * @param string $cAttribute This is used to get an attribute of the element, e.g. <title case="lower">Stuff</title>
     * @return mixed
     */
	public function getConfig($cElement = false, $cKey = false, $cAttribute = false) {
		$mReturn = false;

		//are we using the old method
		if (function_exists("Config")) {
		    //Fixes for old naming method
		    switch ($cKey) {
	    		case "databases":
	    			$cKey	= "database";
	    			break;

		    	case "skinSetting": //Since this will never exist in the old method
		    		return false;
	    			break;
		    }

			//now do we need to change the name of some things
		    switch ($cElement) {
	    		case "javascript":
	    			$cElement = "js";
	    			break;
		    }

	    	$mConfig	= Config($cKey, $cElement);

			if ($cElement) {
				if (isset($mConfig[$cElement])) {
					$mReturn	= $mConfig[$cElement];
				} else {
					$mReturn	= $mConfig;
				}
			} else {
				$mReturn	= $mConfig;
			}
		} else {
			$mReturn	= self::getConfigStat($cElement, $cKey, $cAttribute);
		}

	    return $mReturn;
    }

	/**
	 * Hammer::setConfig()
	 *
	 * @param string $cKey
	 * @param string $cValue
	 * @return null
	 */
	public function setConfig($cKey, $cValue) {
		$oConfig		= new XML();
		$oConfig->cRoot		= "config";

		if (defined("STAGED")) {
			$oConfig->setFile("config." . STAGED);
			$oConfig->cType	= STAGED;
		} else {
			$oConfig->setFile("config");

			if (defined("LIVE")) { $oConfig->cType = "live"; }
		}

		//set the element
		$oConfig->addElement($cKey, $cValue);
	}

    /**
     * Hammer::getHead()
     *
     * @desc Gets the head object
     * @param string cStyle This is to specify the style, e.g. Black
     * @return object
     */
    public function getHead($cStyle = false, $bNoInstall = null) {
    	$oHead = $this->getNail("Head", $cStyle, $bNoInstall);

    	$oHead->cTitle = $this->cTitle;

        return $oHead;
    }

	/**
	* Hammer::getNail()
	*
	* @param string $cName
	* @param mixed $mParams
	* @param bool $bNails
	* @return object
	*/
	public function getNail($cName, $mParams = false) {
		$cNail	= ucfirst($cName);

		//Since we need to send the Nails object to pretty much everything
		if (is_null($this->oNails)) {
			$this->oNails	= new Nails($this->aFilters);
		}

		return getNailed($cNail, $this->oNails, $mParams);
	}

	/**
	 * Hammer::getNails()
	 *
	 * @desc another clone of getNail for backwards compat
	 * @param string $cName
	 * @param mixed $mParams
	 * @return object
	 */
	public function getNails($cName, $mParams = false) {
		return $this->getNail($cName, $mParams);
	}

	/**
	 * Hammer::getExtension()
	 *
	 * @desc this is a clone of getNail for back compat
	 * @param string $cName
	 * @param string $mParams
	 * @return object
	 */
	public function getExtension($cName, $mParams = false) {
		return $this->getNail($cName, $mParams);
	}

    /**
     * Hammer::createCookie()
     *
     * @desc This creates teh cookies
     * @param string $cName Name of the cookie
     * @param mixed $mValue Value e.g. time
     * @param bool $bForever Does it have to last forever {well 5 years}
     * @param int $iTimeLimit Does it have a timelimit, e.g. 5mins
     * @return null
     */
    public function createCookie($cName, $mValue, $bForever = false, $iTimeLimit = false) {
    	if (isset($_SERVER['HTTP_HOST'])) {
    		$cServer = $_SERVER['HTTP_HOST'];
    	} else {
    		$cServer = $this->getConfig("address");
    	}

		//is the page actually a https
		$bSecure = false;
		if (isset($_SERVER['HTTPS'])) {
			$bSecure = true;
		}

    	$cServer = "." . $cServer;

    	if ($bForever) {
    		$iTime	= time() + 2147483647;
    	} else {
			if ($iTimeLimit) { //This can allow you to give a timelimit, e.g. 5, will give a timelimit of 5 secnds
				$iTime	= time() + ($iTimeLimit * 60);
			} else {
		    	$iTime	= time() + 3600;
			}
    	}

    	if (defined("DEV")) {
    		setcookie($cName, $mValue, $iTime, "/");
    	} else {
    		setcookie($cName, $mValue, $iTime, "/", $cServer, $bSecure);
        }
    }

    /**
     * Hammer::destroyCookie()
     *
     * @desc Destorys the cookie that matches the name
     * @param string $cName Name of the cookie
     * @return null
     */
	public function destroyCookie($cName) {
		if (isset($_SERVER['HTTP_HOST'])) {
			$cServer = $_SERVER['HTTP_HOST'];
		} else {
			$cServer	= $this->getConfig("address");
		}

		//is the page actually a https
		$bSecure = false;
		if (isset($_SERVER['HTTPS'])) {
			$bSecure = true;
		}

		$cServer = "." . $cServer;

		if (defined("DEV")) {
			setcookie($cName, "", time()-50000, "/");
		} else {
			setcookie($cName, "", time()-50000, "/", $cServer, $bSecure);
		}

        $this->sendLocation();
    }


	/**
	 * Hammer::getTemplate()
	 *
	 * @desc This is just a pointer to addTemplate use that
	 * @param string $cTemplate
	 * @return object
	 */
	public function getTemplate($cTemplate = null) {
		return $this->addTemplate($cTemplate);
	}

	/**
	 * Hammer::getTemplates()
	 *
	 * @param string $cTemplate
	 * @return string
	 */
	public function getTemplates($cTemplate = null) {
		return $this->addTemplate($cTemplate);
	}

	/**
	* Hammer:getCache()
	*
	* @return object
	*/
	public function getCache() {
		return $this->getNail("cache", $this->aData);
	}

	/**
	* Hammer::addTemplate()
	*
	* @desc Gets the template object
	* @param string $cTemplate If you already know what template your going to use, you can set it here
	* @return object
	*/
	public function addTemplate($cTemplate = null) {
		$cSkinSetting	= false;
		$cSkin			= $cSkinSetting ? $cSkinSetting : "brand";

		$this->cSkinSetting	= $cSkin;
		$this->cSiteCalled	= $this->cSited;

		//get the template object
		$oReturn	= Template::getInstance($this->aData, $this->cSited, $cSkinSetting);

		//set the template always, and then if just echo called, no errors
		$oReturn->setTemplate($cTemplate);

		return $oReturn;
	}

	/**
	 * Hammer::addForm()
	 *
	 * @return object
	 */
	public function addForm() {
		$oTemplate	= $this->addTemplate();
		$oForm		= $oTemplate->addForm();

		return $oForm;
	}

	/**
	 * Hammer::getSession()
	 *
	 * @desc Gets session object, or it gets the session cookie
	 * @param string $cName if this isset then it retrives the $_SESSION
	 * @return mixed
	 */
	public function getSession($cName = false) {
		//This is so that 2 functions dont need to be created, and can keep
		//the naming convention
		if ($cName) {
			if (isset($_SESSION[$cName])) {
				return $_SESSION[$cName];
			}

			return false;
		}

		//this returns the session if ther eisnt one set by name
		return $this->getNail("session");
	}

	/**
	 * Hammer::sendLocation()
	 *
	 * @desc Will send the user to a specified location e.g. /home
	 * @param string $cLocation
	 * @return null
	 */
	public static function sendLocation($cLocation = false, $iTime = null) {
		if ($iTime) {
			$cLoc  = "refresh: " . $iTime;
			$cLoc .= "; url=";
		} else {
			$cLoc = "Location: ";
		}

		if (!$cLocation) {
			header("HTTP/1.1 301 Moved Permanently");
			$cLoc .= "/";
		} else {
			if (is_null(self::$oHammer)) {
				$oHammer	= self::getHammer();
			} else {
				$oHammer	= self::$oHammer;
			}

			$oUser		= $oHammer->getUser();

			if ($cLocation == "refer") {
				//now we have hammer, and user
				header("HTTP/1.1 302 Found");

				//refer is login and your username is correct
				if (isset($_SERVER['HTTP_REFERER']) && $oUser->getUserID() && (strstr($_SERVER['HTTP_REFERER'], "login"))) {
					if (!strstr($_SERVER['HTTP_REFERER'], "logout")) {
						$oHammer->sendLocation();
					}

				//there is a referer, and your username could be wrong
				} else if (isset($_SERVER['HTTP_REFERER'])) {
					if (strstr($_SERVER['HTTP_REFERER'], "login")) { //your username must have been wrong
						$cLoc .= "/login/";
					} else { //nothing special
						$cLoc .= strtolower($_SERVER['HTTP_REFERER']);
					}
				}

			//Install
			} else if ($cLocation == "install") {
				header("HTTP/1.1 200 Ok");
				$cLoc .= "/";

			//Other
			} else {
				$iLength	= strlen($cLocation) - 1;
				$iPos		= strrpos($cLocation, "/");

				//the last slash is missing
				if ($iLength !== $iPos) { $cLocation .= "/"; }

				header("HTTP/1.1 303 See Other");
				$cLoc .= $cLocation;
			}
		}

		if ($iTime) { printRead($cLoc);die(); }

		//now do the location
		header($cLoc);

		die();
	}

	/**
	 * Hammer::getCurrentURL()
	 *
	 * @desc Where is the user atm not really used
	 * @return array
	 */
	public function getCurrentURL() {
		$cPattern = '/([a-zA-Z0-9-]+)(\.[a-zA-Z0-9-]+)(\.[a-zA-Z.]{2,7})/is';
		preg_match($cPattern, $_SERVER['HTTP_HOST'], $aMatches);

		return $aMatches;
	}

	/**
	* Hammer::setSession()
	*
	* @desc Sets teh session uses session cookie
	* @param string $cName name of it so that it can be retrieved
	* @param string $cValue value of the session variable
	* @todo would this be better actually in the session lib, possibly
	* but at least if its in the core you dont need to load the sesssion lib
	* if really not needed
	*
	* @return bool
	*/
	public function setSession($cName, $cValue = false) {
		//delete hte session
		if ($cValue == false) { unset($_SESSION[$cName]); }

		if (isset($_SESSION[$cName])) { //if its set your proberlly want it to return the same thing
			if ($_SESSION[$cName] == $cValue) {
				return true;
			} else { //the value isnt the same, so it needs changing
				$this->setSession($cName);
				$this->setSession($cName, $cValue);
			}
		} else { //now we know its not set, so set it to this
			$_SESSION[$cName] = $cValue;
		}

		return false;
	}

	/**
	 * Hammer::getSetup()
	 *
	 * @return object
	 */
	public function getSetup() {
		$oSetup	= Setup::getSetup();

		return $oSetup;
	}

	/** MAGIC STUFF **/

	/**
	 * Hammer::__get()
	 *
	 * @desc This is an experiment, to see how much time changes by doing it though magic
	 *
	 * @param string $cName
	 * @return string
	 */
	public function __get($cName) {
		$cReturn	= false;

		if (array_key_exists($cName, $this->aData)) {
			$cReturn =  $this->aData[$cName];
		} else if (isset($this->$cName)) {
			$cReturn = $this->$cName;
		}

		return $cReturn;
	}

	/**
	 * Hammer::__set()
	 *
	 * @desc This is an experiment
	 *
	 * @param string $cName
	 * @param string $cValue
	 * @return null
	 */
	public function __set($cName, $cValue) {
		$this->aData[$cName]	= $cValue;
	}

	/**
	 * Hammer::__isset()
	 *
	 * @param string $cName
	 * @return bool
	 */
	public function __isset($cName) {
		return isset($this->aData[$cName]);
	}

	/**
	 * Hammer::__unset()
	 *
	 * @param string $cName
	 * @return null
	 */
	public function __unset($cName) {
		unset($this->aData[$cName]);
	}

	/**
	 * Hammer::__call()
	 *
	 * @param string $cFunction
	 * @param mixed $mArgs
	 * @return object
	 */
	public function __call($cFunction, $mArgs = false) {
		$cFuncName	= $cFunction;

		if (strstr($cFunction, "get")) { $cFuncName = substr($cFunction, 3); }

		return $this->getNail($cFuncName, $mArgs);
	}
}

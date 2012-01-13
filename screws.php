<?php
//if there is no hammerpath defined must called specific so return false
if (!defined("HAMMERPATH")) { throw new Spanner("Hammer Path not defined", 99); }

//if no sitepath included make it app for other frameworks to understand
if (!defined("SITEPATH")) {
	$cPath	 = dirname(__FILE__);
	$cPath	.= "app/";
	define("SITEPATH", $cPath);
}


/**
 * Screws
 *
 * @package
 * @author keloran
 * @copyright Copyright (c) 2011
 * @version $Id$
 * @access public
 */
class Screws {
	private $cClass;
	private $cPath;
	private $aData;
	private $cOriginal;

	/**
	 * Screws::__construct()
	 *
	 */
	public function __construct() {
		spl_autoload_register(array($this, "loader"), true);
	}

	/**
	 * Screws::loader()
	 *
	 * @param mixed $cClass
	 * @return
	 */
	private function loader($cClass) {
		$this->cClass		= $cClass;
		$this->cOriginal	= $cClass;

		$this->definePaths();
		$bExists = $this->checkExists();

		//should really check if the class is inside a file thats been created
		if (!$bExists) { $bExists = class_exists($cClass, false); }

		//does the file exist
		if ($bExists) {
			try {
				include $this->cPath;
			} catch (Exception $e) {
				throw new Spanner($e->getMessage());
			}

			//now make sure not todo this in a loop
			$bSkip	= false;
			if (substr($cClass, -7) == "install") { $bSkip = true; }

			//now do the installer unless skipped
			if (!$bSkip) {
				if ($this->cClassPath) {
					try {
						$this->doInstall($cClass, $this->cClassPath);
					} catch (Exception $e) {
						throw new Spanner($e->getMessage());
					}
				}
			}
		} else {
			//so that the class not exisitng can be handled by the user/file
			throw new ErrorException($cClass . ": Class File not found", 101);
		}
	}

	/**
	 * Screws::__set()
	 *
	 * @param string $cName
	 * @param mixed $mValue
	 * @return null
	 */
	public function __set($cName, $mValue) {
		$this->aData[$cName] = $mValue;
	}

	/**
	 * Screws::__isset()
	 *
	 * @param string $cName
	 * @return bool
	 */
	public function __isset($cName) {
		$bReturn	= false;

		if (isset($this->aData[$cName])) { $bReturn = true; }
		if (isset($this->$cName)) { $bReturn = true; }

		return $bReturn;
	}

	/**
	 * Screws::__get()
	 *
	 * @param string $cName
	 * @return mixed
	 */
	public function __get($cName) {
		if (isset($this->aData[$cName])) { return $this->aData[$cName]; }

		return false;
	}

	/**
	 * Screws::definePaths()
	 *
	 * @return null
	 */
	private function definePaths() {
		$this->defineClassVars();
		$this->defineHammerPaths();

		//is there a usernails folder
		$bNails = $this->defineNailsPath();

		//if there is no usernails folder, might be legacy
		if (!$bNails) { $this->defineLibsPath(); }
	}

	/**
	 * Screws::defineClassVars()
	 *
	 * @return null
	 */
	private function defineClassVars() {
		//Set teh defaults so it doesnt complain
		$cClassName		= strtolower($this->cClass);

		//added this so it doesnt die when one doesnt exist
		$cClass_b	= false;
		$aClass		= false;

		//get the nails class
		$cClass_a	= str_replace("_", "/", $cClassName);
		$aClass		= explode("_", $cClassName);

		//so it doesnt do it on every loop
		$iClass		= count($aClass);
		$cClass_b	= $aClass[0];
		$cClass_c	= $cClassName;

		//se if its a namespace
		$cClass_d	= str_replace("\\", "/", $cClassName);

		//its not the base class (e.g. Database)
		if ($iClass >= 2) {
			$cClass_b 	 = $cClass_a;
			$cClass_b	.= "/" . end($aClass);
		} else {
			$cClass_a	= $cClassName . "/" . $cClassName;
		}

		$this->cClass_a 	= $cClass_a;
		$this->cClass_b 	= $cClass_b;
		$this->cClass_c		= $cClass_c;
		$this->cClass_d		= $cClass_d;
		$this->cClassName	= $cClassName;
	}

	/**
	 * Screws::defineHammerPaths()
	 *
	 * @return null
	 */
	private function defineHammerPaths() {
		//Hammer Class
		$this->fHammerClass_a	= HAMMERPATH	. "/nails/"	. $this->cClass_a	. ".inc.php";
		$this->fHammerClass_b	= HAMMERPATH	. "/nails/"	. $this->cClass_b	. ".inc.php";

		//without the inc bit
		$this->fHammerClass_c	= HAMMERPATH	. "/nails/" . $this->cClass_a	. ".php";
		$this->fHammerClass_d	= HAMMERPATH	. "/nails/" . $this->cClass_b	. ".php";

		//see if there is anything in namespaces
		$this->fHammerClass_e		= HAMMERPATH	. "/nails/" . $this->cClass_d	. ".php";

		//Traits
		$this->fHammerClass_Trait	= HAMMERPATH . "/traits/" . $this->cClass_a . ".php";

		//Base class, this is only for things like spanner
		$this->fBaseClass		= HAMMERPATH	. "/" 		. $this->cClassName	. ".php";

		//get the paths
		$this->fHammerClass_a_Path	= HAMMERPATH	. "/nails/" . $this->cClass_a;
		$this->fHammerClass_b_Path	= HAMMERPATH	. "/nails/" . $this->cClass_b;
		$this->fHammerClass_c_Path	= HAMMERPATH	. "/nails/" . $this->cClass_a;
		$this->fHammerClass_d_Path	= HAMMERPATH	. "/nails/" . $this->cClass_b;
		$this->fHammerClass_e_Path	= HAMMERPATH	. "/nails/" . $this->cClass_d;

		//if the dir exists
		if (is_dir(SITEPATH . "/nails")) {
			if (!defined("USERNAILS")) {
				define("USERNAILS", SITEPATH . "/nails/");
			}
		}
	}

	/**
	 * Screws::defineNailsPath()
	 *
	 * @return bool
	 */
	private function defineNailsPath() {
		$bReturn	= false;
		if (defined("USERNAILS")) {
			$bReturn = true;
		} else {
			return false;
		}

		//site class
		$this->fSiteClass_a	= USERNAILS	. $this->cClass_a	. ".inc.php";
		$this->fSiteClass_b	= USERNAILS	. $this->cClass_b	. ".inc.php";
		$this->fSiteClass_e	= USERNAILS . $this->cClass_d	. ".inc.php";

		//without the inc bit
		$this->fSiteClass_c	= USERNAILS . $this->cClass_a	. ".php";
		$this->fSiteClass_d	= USERNAILS . $this->cClass_b	. ".php";
		$this->fSiteClass_f	= USERNAILS	. $this->cClass_d	. ".php";

		//Traits
		$this->fSiteClass_Trait = SITEPATH . "/traits/" . $this->cClass_a . ".php";
		print_r($this);die();

		//get the paths
		$this->fSiteClass_a_Path	= USERNAILS . $this->cClass_a;
		$this->fSiteClass_b_Path	= USERNAILS . $this->cClass_b;
		$this->fSiteClass_c_Path	= USERNAILS . $this->cClass_d;
		$this->fSiteClass_d_Path	= USERNAILS . $this->cClass_a;
		$this->fSiteClass_e_Path	= USERNAILS . $this->cClass_b;
		$this->fSiteClass_f_Path	= USERNAILS . $this->cClass_d;

		return $bReturn;
	}

	/**
	 * Screws::defineLibsPath()
	 *
	 * @return null
	 */
	private function defineLibsPath() {
		$this->fSiteClass_a	= SITEPATH . "/libs/"	. $this->cClass_a	. ".inc.php";
		$this->fSiteClass_b	= SITEPATH . "/libs/"	. $this->cClass_b	. ".inc.php";

		//without the inc bit
		$this->fSiteClass_c	= SITEPATH . "/libs/"	. $this->cClass_a	. ".php";
		$this->fSiteClass_d	= SITEPATH . "/libs/"	. $this->cClass_b	. ".php";

		//get the paths
		$this->fSiteClass_a_Path	= SITEPATH . "/libs/" . $this->cClass_a;
		$this->fSiteClass_b_Path	= SITEPATH . "/libs/" . $this->cClass_b;
		$this->fSiteClass_c_Path	= SITEPATH . "/libs/" . $this->cClass_a;
		$this->fSiteClass_d_Path	= SITEPATH . "/libs/" . $this->cClass_b;
	}

	/**
	 * Screws::checkExists()
	 *
	 * @return bool
	 */
	private function checkExists() {
		$aRange = range("a", "f");
		$iRange	= count($aRange); //random

		//base class
		if (file_exists($this->fBaseClass)) {
			$this->cPath = $this->fBaseClass;
			return true;
		}

		//now do the site classes
		for ($i = 0; $i < $iRange; $i++) {
			$cClass	 = "fSiteClass_";
			$cClass .= $aRange[$i];
			$cPath	 = $cClass . "_Path";

			if (file_exists($this->$cClass)) {
				$this->cPath 		= $this->$cClass;
				$this->cClassPath	= $this->$cPath;
				return true;
			}
		}

		//now do the hammer classes
		for ($i = 0; $i < $iRange; $i++) {
			$cClass	 = "fHammerClass_";
			$cClass .= $aRange[$i];
			$cPath	 = $cClass . "_Path";

			if (file_exists($this->$cClass)) {
				$this->cPath 		= $this->$cClass;
				$this->cClassPath	= $this->$cPath;
				return true;
			}
		}

		//Check the traits
		if (file_exists($this->fSiteClass_Trait)) {
			$this->cPath = $this->fSiteClass_Trait;
			return true;
		} else if (file_exists($this->fHammerClass_Trait)) {
			$this->cPath = $this->fHammerClass_Trait;
			return true;
		}

		return false;
	}

	/**
	 * Screws::doInstall()
	 *
	 * @return null
	 */
	private function doInstall($cClass = false, $cPath = false) {
		$aName			= explode("_", $cClass);
		$cLast			= end($aName);
		$iLength		= strlen($cLast);

		$cPathed		= $cPath;
		$cPath			= substr($cPath, 0, -$iLength);
		if (!$cPath) { $cPath	= substr($cPathed, 0, -$iLength); }

		$cInstallFile	= $cPath . "/install/install.php";
		$cInstallClass	= $cClass . "_install";
		$cInstallPath	= $cPath . "/install/install.php";
		$cChecker	= $cPath . "/" . $cClass . "/install/install.php";

		$aDebug	= array(
			"Path"		=> $cPath,
			"Install"	=> $cInstallFile,
			"Class"		=> $cClass,
			"Installer"	=> $cClass . "_install",
			"Pathed"	=> $cPathed,
			"InstallPath"	=> $cInstallPath,
			"Name"		=> $aName,
			"Last"		=> $cLast,
			"Length"	=> $iLength,
			"nLength"	=> -$iLength,
			"checker"	=> $cChecker,
		);
		$oInstall	= false;

		if (file_exists($cInstallPath) || file_exists($cChecker)) {
			if (file_exists($cChecker)) {
				try {
					$oNails 	= new Nails();
					$oInstall	= new $cInstallClass($oNails);
				} catch (Exception $e) {
					if (!strstr($e->getMessage(), "not found")) {
						printRead($e->getMessage());
						printRead($aDebug);
						die();
					}
				}
			} else if (file_exists($cInstallPath)) {
				try {
					$oNails		= new Nails();
					$oInstall	= new $cInstallClass($oNails);
				} catch (Exception $e) {
					if (!strstr($e->getMessage(), "not found")) {
						printRead($e->getMessage());
						printRead($aDebug);
						die();
					}
				}
			}
		}
	}
}

//load the autoloader
$oLoader = new Screws();

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
				throw new Spanner($e);
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
		return $this->aData[$cName];
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

		//its not the base class (e.g. Database)
		if ($iClass >= 2) {
			for ($i = 1; $i < $iClass; $i++) {
				$cClass_b = $cClass_a . "/" . $aClass[$i];
			}
		} else {
			$cClass_a	= $cClassName . "/" . $cClassName;
		}

		$this->cClass_a 	= $cClass_a;
		$this->cClass_b 	= $cClass_b;
		$this->cClass_c		= $cClass_c;
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

		//Traits
		$this->fHammerClass_Trait	= HAMMERPATH . "/traits/" . $this->cClass_a . ".php";

		//Base class, this is only for things like spanner
		$this->fBaseClass		= HAMMERPATH	. "/" 		. $this->cClassName	. ".php";

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

		//without the inc bit
		$this->fSiteClass_c	= USERNAILS . $this->cClass_a	. ".php";
		$this->fSiteClass_d	= USERNAILS . $this->cClass_b	. ".php";

		//Traits
		$this->fSiteClass_Trait = USERNAILS . "/traits/" . $this->cClass_a . ".php";

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
	}

	/**
	 * Screws::checkExists()
	 *
	 * @return bool
	 */
	private function checkExists() {
		$aRange = range("a", "d");
		$iRange	= count($aRange); //random

		//base class
		if (file_exists($this->fBaseClass)) {
			$this->cPath = $this->fBaseClass;
			return true;
		}

		//now do the site classes
		for ($i = 0; $i < $iRange; $i++) {
			$cClass	= "fSiteClass_";
			$cClass .= $aRange[$i];
			if (file_exists($this->$cClass)) {
				$this->cPath = $this->$cClass;
				return true;
			}
		}

		//now do the hammer classes
		for ($i = 0; $i < $iRange; $i++) {
			$cClass	= "fHammerClass_";
			$cClass .= $aRange[$i];
			if (file_exists($this->$cClass)) {
				$this->cPath = $this->$cClass;
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
}

//load the autoloader
$oLoader = new Screws();

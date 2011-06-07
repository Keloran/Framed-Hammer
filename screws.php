<?php
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

		if ($bExists) {
			try {
				include($this->cPath);
			} catch(Exception $e) {
				new Spanner($e);
			}
		} else {
			throw new Spanner("Class File not found", 101);
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

		//if there is no hammerpath defined must called specific so return false
		if (!defined("HAMMERPATH")) { throw new Spanner("Hammer Path not defined", 99); }

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
				$cClass_b .= "/" . $aClass[$i]; //get the folder
				$cClass_b .= "/" . $aClass[$i]; //get the file itself

				//lots of underscores, so need to assign the final
				$cClass_c = $cClass_a . "/" . $aClass[$i];
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

		//lots of unders with/out inc
		$this->fHammerClass_e	= HAMMERPATH	. "/nails/" . $this->cClass_c	. ".inc.php";
		$this->fHammerClass_f	= HAMMERPATH	. "/nails/" . $this->cClass_c	. ".php";

		//Base class, this is only for things like spanner
		$this->fBaseClass	= HAMMERPATH	. "/" 		. $this->cClassName	. ".php";

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

		//lots of unders with/out inc
		$this->fSiteClass_e	= USERNAILS	. $this->cClass_c	. ".inc.php";
		$this->fSiteClass_f	= USERNAILS	. $this->cClass_c	. ".php";

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

		//lots of unders, with/out inc
		$this->fSiteClass_e	= SITEPATH . "/libs/"	. $this->cClass_c	. ".inc.php";
		$this->fSiteClass_f	= SITEPATH . "/libs/"	. $this->cClass_c	. ".php";
	}

	/**
	 * Screws::checkExists()
	 *
	 * @return bool
	 */
	private function checkExists() {
		$aRange = range("a", "e");
		$iRange	= count($aRange);

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

		return false;
	}
}

//load the autoloader
$oLoader = new Screws();

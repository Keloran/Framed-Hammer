<?php
/**
 * Dommed
 *
 * @package
 * @author keloran
 * @copyright Copyright (c) 2010
 * @version $Id$
 * @access public
 */
class Dommed {
	private static $oDommed;

	private $oDOM;
	private $pFile;
	private $oRoot;

	//These are to set the parent, and add the parent node if needed
	private $oParent;
	private $bWritten;
	private $cParent;

	private $cRoot;
	private $cRootExtras;
	private $aVars = array();

	/**
	 * Dommed::__construct()
	 *
	 * @param string $cFile
	 */
	public function __construct($cFile = null) {
		if ($cFile) {
			$this->setFile($cFile);
		}

		if (defined("LIVE")) {
			$this->setRoot("live");
		} else {
			$this->setRoot("dev");
		}
	}

	/**
	 * Dommed::getInstance()
	 *
	 * @return object
	 */
	public static function getInstance() {
		if (is_null(self::$oDommed)) {
			self::$oDommed = new Dommed();
		}

		return self::$oDommed;
	}

	/**
	 * Dommed::setRoot()
	 *
	 * @param string $cRoot
	 * @param string $cExtras
	 * @return null
	 */
	public function setRoot($cRoot, $cNameSpace = null, $aExtraNS = null) {
		$this->cRoot 		= $cRoot;
		$this->iExtraNS		= 0;
		$oRoot				= false;

		$this->xmlns		= $cNameSpace;

		//add the extras
		if ($aExtraNS) {
			$this->iExtraNS	= count($aExtraNS);
			$this->aExtraNS	= $aExtraNS;
		}

		//set the root
		if ($this->oDOM) {
			$oDOM	= $this->oDOM->getElementsByTagName($cRoot);
			$oRoot	= $oDOM->item(0);
		}

		$this->oRoot = $oRoot;
		return $oRoot;
	}

	/**
	 * Dommed::__set()
	 *
	 * @param string $cName
	 * @param mixed $mValue
	 * @return null
	 */
	public function __set($cName, $mValue) {
		$this->aVars[$cName] = $mValue;
	}

	/**
	 * Dommed::__get()
	 *
	 * @param string $cName
	 * @return mixed
	 */
	public function __get($cName) {
		if (isset($this->aVars[$cName])) {
			return $this->aVars[$cName];
		}

		return false;
	}

	/**
	 * Dommed::setFile()
	 *
	 * @param string $cFile
	 * @param bool $bAbsolute
	 * @return null
	 */
	public function setFile($cFile, $bAbsolute = null, $bDelete = null) {
		if (!$bAbsolute) {
			$cRealFile	= SITEPATH . "/" . $cFile . ".xml";
		} else {
			$cRealFile	= $cFile;
		}

		//since it wont save something that isnt set correctlly
		$this->cFile	= $cRealFile;

		//delete the file
		if ($bDelete) {
			//have to check it exists before trying to delete it
			if (file_exists($cRealFile)) {
				unlink($cRealFile);
			}
		}

		if (file_exists($cRealFile)) {
			$pFile			= file_get_contents($cRealFile);
			$this->oDOM		= new DomDocument("1.0", "UTF-8");
			$this->oDOM->loadXML($pFile);
		} else {
			$this->oDOM		= new DomDocument("1.0", "UTF-8");
			$this->oRoot	= $this->oDOM->createElementNS($this->xmlns, $this->cRoot);
			$this->oDOM->appendChild($this->oRoot);
		}

		//set the root if it isnt already
		if (!$this->oRoot) { $this->setRoot($cFile); }
	}

	/**
	 * Dommed::addElement()
	 *
	 * @param string $cElement
	 * @param string $cValue
	 * @param string $cParent
	 * @return null
	 */
	public function addElement($cElement, $cValue = null, $cParent = null, $bNoSkip = null, $bWriteReturn = null) {
		if (!$this->oRoot) { return false; }
		$oRoot		= $this->oRoot;
		$oParent	= false;

		//do the parent check
		if ($cParent) { $oParent = $this->setParent($cParent); }

		if (!$oParent) {
			$oDOM		= $this->oDOM->getElementsByTagName($cParent);
			$oParent	= $oDOM->item(0);
		}

		//now create the element
		$oElement	= $this->oDOM->createElement($cElement);
		$oParent->appendChild($oElement);

		//now if there is a value
		if ($cValue) {
			$oValue		= $this->oDOM->createTextNode($cValue);
			$oElement->appendChild($oValue);
		}

		$this->saveIt($bWriteReturn);
	}

	/**
	 * Dommed::setParent()
	 *
	 * @param string $cParent
	 * @param bool $bRoot
	 * @return object
	 */
	public function setParent($cParent, $bRoot = false) {
		$oParent = false;

		if ($this->cParent) {
			if ($cParent == $this->cParent) { //its the same, so nothing really needs to be done
				$oParent = $this->oParent;
			} else { //its not the same, but it might be blank also
				if ($this->cParent) { //the parent does exist, and its different so we need to write it
					$this->oDOM->appendChild($this->oParent);
				} else { //the parent doesnt actually exist, so we need to create it
					$oParent 		= $this->oDOM->createElement($cParent);
					$this->cParent	= $cParent;
					$this->oParent	= $oParent;
				}
			}
		}

		return $oParent;
	}

	/**
	 * Dommed::saveIt()
	 *
	 * @return bool
	 */
	public function saveIt($bWriteReturn = null) {
		if (!$this->oDOM) { return false; }

		//this saves it, but it saves without formatting
		$this->oDOM->preserveWhiteSpace = false;
		$this->oDOM->formatOutput		= true;

		//do we want to return it or do we want to write it
		if ($bWriteReturn) {
			return $this->oDOM->saveXML();
		} else {
			$this->oDOM->save($this->cFile);
		}

		return true;
	}

	/**
	 * Dommed::returnIt()
	 *
	 * @return string
	 */
	public function returnIt() {
		if (!$this->oDOM) { return false; }

		return $this->oDOM->saveXML();
	}
}

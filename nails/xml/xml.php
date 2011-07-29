<?php
/**
 * XML
 *
 * @package
 * @author keloran
 * @copyright Copyright (c) 2011
 * @version $Id$
 * @access public
 */
class XML {
	public static $oXML;

	private $aData;

	/**
	 * XML::__construct()
	 *
	 */
	public function __construct() {

	}

	/**
	 * XML::__set()
	 *
	 * @param string $cName
	 * @param mixed $mValue
	 * @return null
	 */
	public function __set($cName, $mValue) {
		$this->aData[$cName] = $mValue;
	}

	/**
	 * XML::__get()
	 *
	 * @param string $cName
	 * @return mixed
	 */
	public function __get($cName) {
		$mReturn = false;

		if (isset($this->aData[$cName])) { $mReturn = $this->aData[$cName]; }

		return $mReturn;
	}

	/**
	 * XML::getInstance()
	 *
	 * @return object
	 */
	public static function getInstance() {
		if (is_null(self::$oXML)) { self::$oXML = new XML(); }

		return self::$oXML;
	}

	/**
	 * XML::getElement()
	 *
	 * @param string $cElement
	 * @param string $cParent
	 * @return mixed
	 */
	public function getElement($cElement, $cParent = null) {
		$oXPath		= new DOMXPath($this->oDOM); //get the xpath object
		$mReturn	= false;

		$cPath	= "//";
		if ($cParent) { $cPath = "//" . $cParent; }

		$aElements	= $oXPath->query($cPath . $cElement);
		$iElements	= count($aElements);

		if ($iElements >= 2) {
			foreach ($aElements as $aElement) {
				$mReturn[] = $aElement->item;
			}
		} else {
			$mReturn = $aElements[0]->item;
		}

		return $mReturn;
	}

	public function addElement($cElement) {

	}

	/**
	 * XML::setFile()
	 *
	 * @param string $cFile
	 * @param bool $bAbsolute
	 * @param bool $bDelete
	 * @return null
	 */
	public function setFile($cFile, $bAbsolute = null, $bDelete = null) {
		//given the absolute path, could even be remote
		if (!$bAbsolute) {
			$cRealFile	= SITEPATH . "/" . $cFile . ".xml";
		} else {
			$cRealFile	= $cFile;
		}

		//set to the object
		$this->cFile = $cRealFile;

		//do we want to delete it, before making a new one, and does the file exist
		if ($bDelete && file_exists($cRealFile)) { unlink($cRealFile); }

		//create the dom object
		$this->oDOM	= new DomDocument("1.0", "UTF-8");

		//if there isnt a root set
		if (!$this->cRoot) { $this->cRoot = "config"; }

		//now if there is a file, which possibly there isnt now
		if (file_exists($cRealFile)) {
			$pFile		= file_get_contents($cRealFile);
			$this->oDOM->loadXML($pFile);
		} else {
			if ($this->cXMLNS) {
				$this->oRoot = $this->oDOM->createElementNS($this->cXMLNS, $this->cRoot);
			} else {
				$this->oRoot = $this->oDOM->createElement($this->cRoot);
			}

			$this->oDOM->appendChild($this->oRoot);
		}
	}

	/**
	 * XML::setRoot()
	 *
	 * @param string $cRoot
	 * @param string $cNameSpace
	 * @param array $aExtraNS
	 * @return object
	 */
	public function setRoot($cRoot, $cNameSpace = null, $aExtraNS = null) {
		$this->cRoot	= $cRoot;
		$this->iExtraNs	= 0;
		$oRoot			= false;

		$this->cXMLNS 	= $cNameSpace;

		//are there any extra namespaces
		if ($aExtraNS) {
			$this->iExtraNS	= count($aExtraNS);
			$this->aExtraNS	= $aExtraNS;
		}

		//set the root
		if ($this->oDOM) {
			$oDOM	= $this->oDOM->getElementsByTagName($cRoot);
			$oRoot	= $oDOM->item(0);
		}

		$this->oRoot	= $oRoot;
		return $oRoot;
	}
}
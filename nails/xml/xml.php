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
	private $bUpdated;

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
	 * @param bool $bReturn
	 * @param bool $bKey
	 * @return mixed
	 */
	public function getElement($cElement, $cParent = null, $bReturn = null, $bKey = false) {
		$mReturn	= false;

		//no parent given, so make it default to config
		if (!$cParent) { $cParent = $this->cRoot ?: "config"; }

		//go through the parent elements
		$oParent	= $this->oDOM->getElementsByTagName($cParent);
		$mParent	= false;
		$iParent	= $oParent->length;
		for ($i = 0; $i < $iParent; $i++) { $mParent[] = $oParent->item($i); }

		//if no parents found at this point it might be blank
		if (!$mParent) { return false; }

		//hopefully there will only be one parent
		$z = 0;
		foreach ($mParent as $mParentElem) {
			$oElem		= $mParentElem->getElementsByTagName($cElement);
			$iElements	= $oElem->length;

			//go through the elements
			for ($i = 0; $i < $iElements; $i++) {
				$mElement 	= $oElem->item($i);
				$bReal		= $this->isRealNode($mElement);

				//get the values
				if ($bReal) {
					$iChildren = $mElement->childNodes->length;

					//return the elements
					if ($bReturn) { return $mElement; }

					//go through the elements
					for ($j = 0; $j < $iChildren; $j++) {
						$mItem		= $mElement->childNodes->item($j);
						$bRealNode	= $this->isRealNode($mItem);

						if ($bRealNode) {
							$mReturn[$z] = $this->recursiveElement($mItem);
						} else {
							//it must be the only element
							$cName					= $mItem->nodeName;
							$mValue 				= $mItem->nodeValue;
							$mReturn[$z][$cName]	= $mValue;
						}
						$z++;
					}
				} else {
					//return the elements
					if ($bReturn) { return $mElement; }

					$cName	= $mElement->nodeName;
					$mValue	= $mElement->nodeValue;
					$mReturn[$z][$cName] = $mValue;
					$z++;
				}
			}
		}

		//if no return at this point return false
		if (!$mReturn) { return false; }

		//stuff must have been found
		$iCount 	= count($mReturn);
		$mReturn1	= $mReturn;
		$mReturn	= false;
		for ($i = 0; $i < $iCount; $i++) {
			foreach ($mReturn1[$i] as $cKey => $mValue) { $mReturn[$cKey] = $mValue; }
		}

		//if the key needed and its a single element
		$iCount	= count($mReturn);
		if ($iCount == 1 && $bKey) { $mReturn = $mReturn[$cElement]; }

		return $mReturn;
	}

	/**
	 * XML::isRealNode()
	 *
	 * @param object $oElement
	 * @return bool
	 */
	private function isRealNode($oElement) {
		if ($oElement->hasChildNodes()) {
			foreach ($oElement->childNodes as $oElem) {
				if ($oElem->nodeType == XML_ELEMENT_NODE) { return true; }
			}
		}

		return false;
	}

	/**
	 * XML::updateElement()
	 *
	 * @param string $cElement
	 * @param string $cValue
	 * @param string $cParent
	 * @return null
	 */
	public function updateElement($cElement, $cValue, $cParent = false) {
		$mElement	= $this->getElement($cElement, $cParent, true);

		//there is an element to update
		if (is_object($mElement)) {
			$mElement->nodeValue = $cValue;
		} else {
			$this->addElement($cElement, $cValue, $cParent);
		}

		$this->bUpdated = true;

		//save the file
		#$this->saveFile();
		if (!$mElement) {
			printRead($mElement);
			die();
		} else {
			$mElement->ownerDocument->saveXML($mElement);
		}
	}

	/**
	 * XML::recursiveElement()
	 *
	 * @param object $oElement
	 * @return array
	 */
	private function recursiveElement($oElement) {
		$mReturn	=  false;
		$bReal		= $this->isRealNode($oElement);

		if ($bReal) {
			$iLength	= $oElement->childNodes->length;
			for ($i = 0; $i < $iLength; $i++) {
				$oElem 		= $oElement->childNodes->item($i);
				$bRealNode	= $this->isRealNode($oElem);

				if ($bRealNode) {
					$mReturn			= $this->recursiveElement($oElem);
				} else {
					$cName				= $oElem->nodeName;
					$cValue				= $oElem->nodeValue;
					$mReturn[$cName]	= $cValue;
				}
			}
		} else {
			$cName				= $oElement->nodeName;
			$cValue				= $oElement->nodeValue;
			$mReturn[$cName]	= $cValue;
		}

		return $mReturn;
	}

	/**
	 * XML::addElement()
	 *
	 * @param mixed $cElement
	 * @return
	 */
	public function addElement($cElement, $cValue = false, $cParent = false) {
		$cRoot		= $this->cRoot ?: "config"; //just uncase its needed

		//is there a parent given
		if ($cParent) {
			$oParent	= $this->getElement($cParent, false, true);
		} else {
			$oParent	= $this->getElement($cRoot, false, true);
		}

		if ($cValue == "1.3") {
			printRead($oParent, "ParentO");
			printRead($cParent, "ParentC");
			printRead($cRoot, "Root");
			die();
		}

		//if no parent see if i can revert to the basic method
		if (!$oParent) {
			$oRoot 		= $this->oDOM->getElementsByTagName($cRoot);
			$oParent	= $oRoot->item(0);
		}

		//if there is still no parent die
		if (!$oParent) { printRead("Erm somet really went wrong"); }

		//if value
		if ($cValue) {
			$oElement = $this->oDOM->createElement($cElement, $cValue);
		} else {
			$oElement = $this->oDOM->createElement($cElement);
		}

		//append the element
		$oParent->appendChild($oElement);

		$this->bUpdated = true;

		//save the file
		$oParent->ownerDocument->saveXML($oParent);
		#$this->saveFile();
	}

	/**
	 * XML::saveFile()
	 *
	 * @return null
	 */
	private function saveFile() {
		//save the file
		$this->oDOM->formatOutput = true;
		$iFile	= $this->oDOM->save($this->cFile);

		if (!$iFile) {
			printRead("Something went very wrong writing the file");
			die();
		}
	}

	/**
	 * XML::setFile()
	 *
	 * @param string $cFile
	 * @param bool $bAbsolute
	 * @param bool $bDelete
	 * @return null
	 */
	public function setFile($cFile, $bAbsolute = null, $bDelete = null, $bPreserve = false) {
		//given the absolute path, could even be remote
		if ($bAbsolute) {
			$cRealFile	= $cFile;
		} else {
			$cRealFile	= SITEPATH . "/" . $cFile . ".xml";
		}

		//send to the object
		$this->cFile 	= $cRealFile;
		$this->cFiled	= $cFile;

		//do we want to delete it, before making a new one, and does the file exist
		if ($bDelete && file_exists($cRealFile)) { unlink($cRealFile); }

		//create the dom object
		$this->oDOM	= new DomDocument("1.0", "UTF-8");
		$this->oDOM->preserveWhiteSpace = $bPreserve;

		//if there isnt a root set
		if (!$this->cRoot) { $this->cRoot = "config"; }

		//now if there is a file, which possibly there isnt now
		if (file_exists($cRealFile)) {
			$this->oDOM->load($cRealFile);
		} else {
			//is it the install file
			if ($this->cFiled == "installed") { $this->bNew = true; }

			//has it got a namespace
			if ($this->cXMLNS) {
				$this->oRoot = $this->oDOM->createElementNS($this->cXMLNS, $this->cRoot);
			} else {
				$this->oRoot = $this->oDOM->createElement($this->cRoot);
			}

			$this->oDOM->appendChild($this->oRoot);
		}

		return $this->oDOM;
	}

	/**
	 * XML::getFile()
	 *
	 * @param string $cFile
	 * @param bool $bAbsolute
	 * @return object
	 */
	public function getFile($cFile, $bAbsolute = null) {
		return $this->setFile($cFile, $bAbsolute, false, false);
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

	public function __destruct() {
		if ($this->bUpdated) { $this->saveFile(); }
	}
}
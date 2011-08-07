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
	public function getElement($cElement, $cParent = null, $bReturn = null) {
		$mReturn	= false;

		//no parent given, so make it default to config
		if (!$cParent) { $cParent = "config"; }

		//go through the parent elements
		$oParent	= $this->oDOM->getElementsByTagName($cParent);
		$mParent	= false;
		$iParent	= $oParent->length;
		for ($i = 0; $i < $iParent; $i++) { $mParent[] = $oParent->item($i); }

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
		$iCount = count($mReturn);
		$mReturn1	= $mReturn;
		$mReturn	= false;
		for ($i = 0; $i < $iCount; $i++) {
			foreach ($mReturn1[$i] as $cKey => $mValue) { $mReturn[$cKey] = $mValue; }
		}

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

	public function updateElement($cElement, $cValue, $cParent = false) {
		$mElement	= $this->getElement($cElement, $cParent, true);

		//there is an element to update
		if ($mElement) {
			$oElement	= $mElement->childNodes->item(0);

			$mElement->nodeValue = $cValue;

			//save the file
			$this->oDOM->formatOutput = true;
			$this->oDOM->save($this->cFile);
		} else {
			$this->addElement($cElement, $cValue, $cParent);
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
		if ($cParent) {
			$oParent = $this->getElement($cParent, false, true);
		} else {
			$oParent = $this->getElement("config", false, true);
		}

		//if no parent then there must be a problem
		if (!$oParent) { return false; }

		//if value
		if ($cValue) {
			$oElement = $this->oDOM->createElement($cElement, $cValue);
		} else {
			$oElement = $this->oDOM->createElement($cElement);
		}

		//append the element
		$oParent->appendChild($oElement);

		//save the file
		$this->oDOM->formatOutput = true;
		$this->oDOM->save($this->cFile);
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
		$this->oDOM->preserveWhiteSpace = $bPreserve;

		//if there isnt a root set
		if (!$this->cRoot) { $this->cRoot = "config"; }

		//now if there is a file, which possibly there isnt now
		if (file_exists($cRealFile)) {
			$this->oDOM->load($cRealFile);
			#$pFile		= file_get_contents($cRealFile);
			#$this->oDOM->loadXML($pFile);
		} else {
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
}
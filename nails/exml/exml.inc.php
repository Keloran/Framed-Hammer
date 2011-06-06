<?php
/**
 * Exml
 *
 * @package
 * @author keloran
 * @copyright Copyright (c) 2010
 * @version $Id$
 * @access public
 */
class Exml {
	/** Objects **/
	private $oNails;
	private $oDB;
	private $oXML;

	/** Static Object **/
	private static $oExml;

	/** Variables **/
	private $cFile;
	private $cRoot			= "hammer";
	private $cType			= "dev";
	private $cRootExtras	= false;
	private $bRoot			= false;
	private $cPrefix		= false;

	/** Pointers **/
	private $pFile;

	//Debug
	private $i		= 0;
	private $aPath	= false;

	/**
	 * Constructor
	 */
	public function __construct($cFile = null) {
		if ($cFile) {
			$this->setFile($cFile);
		}

		if (!defined("STAGED")) {
			if (defined("LIVE")) {
				$this->setRoot("live");
			} else {
				$this->setRoot("dev");
			}
		} else {
			$this->setRoot("configs");
			$this->cType = "configs";
		}
	}

	/**
	 * Exml::getInstance()
	 *
	 * @param Nails $oNails
	 * @return object
	 */
	public static function getInstance() {
		if (is_null(self::$oExml)) {
			self::$oExml = new Exml();
		}

		return self::$oExml;
	}

	/**
	 * Exml::__set()
	 *
	 * @param string $cName
	 * @param mixed $mValue
	 * @return null
	 */
	public function __set($cName, $mValue) {
		$this->$cName = $mValue;
	}

	/**
	 * Exml::__get()
	 *
	 * @param string $cName
	 * @return mixed
	 */
	public function __get($cName) {
		return $this->$cName;
	}

	/**
	 * Exml::setRoot()
	 *
	 * @desc Sets the root element so if the root is <install> set the root to "install"
	 * @param string $cRoot
	 * @return null
	 */
	public function setRoot($cRoot, $cExtras = null) {
		$this->cRoot		= $cRoot;
		$this->cRootExtras	= $cExtras;
		$this->bRoot		= false;
	}

	/**
	 * Exml::setFile()
	 *
	 * @desc This sets the file, and opens it into a simplexml oobject
	 * @param string $cFile
	 * @param bool $bAbsolute If you are giving an absolute path set this to true
	 * @return null
	 */
	public function setFile($cFile, $bAbsolute = null) {
		if (!$bAbsolute) {
			$cRealFile	= SITEPATH . "/" . $cFile . ".xml";
		} else {
			$cRealFile	= $cFile;
		}

		//since it wont save something that isnt set correctlly
		$this->cFile	= $cRealFile;
		$this->cPrefix	= $cFile;

		if (file_exists($cRealFile)) {
			$this->oXML		= simplexml_load_file($cRealFile);
		} else {
			$cRoot		= "<" . $this->cRoot . $this->cRootExtras . "></" . $this->cRoot . ">";
			$this->oXML	= simplexml_load_string($cRoot);
		}
	}

	/**
	 * Exml::setRemoteFile()
	 *
	 * @param string $cFile
	 * @return null
	 */
	public function setRemoteFile($cFile) {
		$this->cFile	= $cFile;
		$this->pFile 	= file_get_contents($cFile);
		$this->oXML		= simplexml_load_string($this->pFile);
	}

	/**
	 * Exml::setString()
	 *
	 * @desc This loads a string as a simplexml object, e.g. for twitter where curl is used to get the string
	 * @param string $cString
	 * @return null
	 */
	public function setString($cString) {
		$this->oXML	= simplexml_load_string($cString);
	}

	/**
	 * Exml::setElementType()
	 *
	 * @desc This sets the element type, this is only really for use in hammer files, so dev/live
	 * @param string $cType dev
	 * @return null
	 */
	public function setElementType($cType) {
		$this->cType = $cType;
	}

	/**
	 * Exml::getElement()
	 *
	 * @desc This gets the element, if you set object to true it will return the object instead of a string/array
	 * @param string $cElement
	 * @param string $cParent
	 * @param bool $bObject
	 * @return mixed
	 */
	public function getElement($cElement, $cParent = null, $bObject = null) {
		$mReturn	= false;
		$cType		= $this->cType;
		$oParent	= false;

		//Is there a parent
		if ($cParent) {
			//does it exist at the root
			if (isset($this->oXML->$cParent)) {
				//does the element exist at this level
				if (isset($this->oXML->$cParent->$cElement)) {
					$mReturn	= $this->oXML->$cParent->$cElement;

				// does the element exist at the parent level on the type
				} else if (isset($this->oXML->$cType->$cParent->$cElement)) {
					$mReturn	= $this->oXML->$cType->$cParent->$cElement;

				//k it doesnt exist at all at this level, try its children
				} else {
					$oParent	= $this->oXML->$cParent;
				}
			}
		} else {
			//does the element exist at the root
			if ($this->oXML->$cElement) {
				$mReturn	= $this->oXML->$cElement;

			//does it exist at the ctype
			} else if ($this->oXML->$cType->$cElement) {
				$mReturn	= $this->oXML->$cType->$cElement;

			//run out of places to look by default, guess need to search
			} else {
				$oParent	= $this->oXML;
			}
		}

		//if parent isnt an object, return false;
		if (!is_object($oParent) && !$mReturn) { return false; }

		//no values yet so get children
		if (!$mReturn) {
			$mReturn	= $this->getChild($cElement, $oParent, $bObject);
		}

		//mreturn is going to be an object, need to make it primative
		if ($mReturn) {
			if (is_object($mReturn)) {
				if (!$bObject) {
					$aAttrs			= $this->getAttributes($mReturn);
					$mReturnReal	= $this->makePrimative($mReturn, $cElement);
					$mReturn		= false;

					//it has attributes
					if ($aAttrs) {
						$mReturn[]	= $mReturnReal;
						$mReturn[]	= $aAttrs;
					} else {
						$mReturn	= $mReturnReal;
					}
				}
			}
		}

		return $mReturn;
	}

	/**
	 * Exml::getAttributes()
	 *
	 * @param mixed $mObject
	 * @return array
	 */
	private function getAttributes($mObject) {
		$aReturn	= false;

		//it has to be an object
		if (is_object($mObject)) {
			$oAttrs	= $mObject->attributes();

			foreach ($oAttrs as $cKey => $cValue) {
				$aReturn[$cKey]	= $cValue;
			}
		}

		return $aReturn;
	}

	/**
	 * Exml::getChild()
	 *
	 * @param string $cElement
	 * @param object $oParent
	 * @return mixed
	 */
	private function getChild($cElement, $oParent, $bObject = null) {
		$mReturn	= false;

		if (!is_object($oParent)) { return false; }

		//we want the object
		if ($bObject) {
			$aParent	= (array)$oParent;
			if (isset($aParent[$cElement])) {
				return $oParent;
			}
		}

		if ($oParent->children()) {
			foreach ($oParent->children() as $oKey => $oValue) {
				//turn it into primative to see if it exists at that level
				$cValue	= (string)$oValue;
				$cKey	= (string)$oKey;

				//have we hit the it at the value level
				if ($cValue == $cElement) {
					if ($oValue->children()) {
						$mReturn = $oValue;
					} else {
						if ($bObject) {
							$mReturn = $oKey;
						} else {
							$mReturn = $cValue;
						}
					}
				}

				//have we hit it at the key level
				if ($cKey == $cElement) {
					if ($oValue->children()) {
						$mReturn = $oValue;
					} else {
						if ($bObject) {
							$mReturn = $oKey;
						} else {
							$mReturn = $cValue;
						}
					}
				}

				//we havent hit it yet, send it in a circle
				if (!$mReturn) {
					$mReturn = $this->getChild($cElement, $oValue, $bObject);
				}

				if ($mReturn) { break; }
			}
		}

		return $mReturn;
	}

	/**
	 * Exml::makePrimative()
	 *
	 * @param mixed $mElement
	 * @return mixed
	 */
	private function makePrimative($mElement, $cElement) {
		$mReturn	= false;
		$i			= 0;

		//There is more than 1 element
		if (count($mElement) >= 1) {
			//turn it into a primatives
			foreach ($mElement as $mKey => $mValue) {
				//turn each part into primative
				$cKey		= (string)$mKey;
				$cValue		= (string)$mValue;
				$aValue		= (array)$mValue;
				$mValues	= false;

				//value is an object, needs turning into a primative
				if (is_object($mValue)) {
					$mValues = $this->makePrimative($mValue, $cElement);
					$i++;
				}

				if ($cElement == $cKey) {
					if ($mValues) {
						$mReturn	= $mValues;
					} else {
						$mReturn	= $cValue;
					}
				} else {
					if ($mValues) {
						$mReturn[$cKey]	= $mValues;
					} else {
						$mReturn[$cKey]	= $cValue;
					}
				}
			}
		} else {
			$mElement	= (array)$mElement;
			if (isset($mElement[0])) {
				$mReturn	= $mElement[0];
			}
		}

		//is it a single value
		if (is_array($mReturn)) {
			if (count($mReturn) == 1) {
				if (isset($mReturn[0])) {
					$mReturn	= $mReturn[0];
				}
			}
		}

		return $mReturn;
	}

	/**
	 * Exml::saveIt()
	 *
	 * @desc This saves the file, you shouldnt need todo this, adding element/attribute does it automaticly
	 * @param bool $bSkipDOM
	 * @return bool
	 */
	public function saveIt($bSkipDOM = null) {
		if (!$this->oXML) { return false; }

		//this saves it, but it saves without formatting
		$cXML		= $this->oXML->asXML();
		$cTemp		= false;

		if (file_exists($this->cFile)) {
			$bReturn	= file_put_contents($this->cFile, $cXML);
		} else {
			$cTemp 		= tempnam(SITEPATH, $this->cPrefix);
			$bReturn	= file_put_contents($cTemp, $cXML);

			if (!rename($cTemp, $this->cFile)) {
				throw new Spanner("File Can't be Written");
			} else {
				printRead("It Write it");
				$bReturn = true;
			}
		}

		//now we have saved it, save it again but this time formatted
		if (!$bSkipDOM) {
			$oDom = new DOMDocument("1.0", "UTF-8");
			$oDom->preserveWhiteSpace	= false;
			$oDom->formatOutput			= true;
			$oDom->loadXML($cXML);
			$oDom->save($this->cFile);
		}

		return $bReturn;
	}

	/**
	 * Exml::addElement()
	 *
	 * @desc This adds an element to the tree, if you give it a parent it will add it to that element
	 * @param string $cElement
	 * @param string $cValue
	 * @param string $cParent
	 * @param bool $bSkipParent
	 * @return null
	 */
	public function addElement($cElement, $cValue = null, $cParent = null, $bSkipParent = null)  {
		$oElement	= null;
		$oParent	= null;
		if (!$bSkipParent) {
			$oParent	= $this->getElement($cParent, false, true);
			$oElement	= $this->getElement($cElement, $cParent, true);
		}

		//if no parent given
		if (!$oParent) {
			if (!$cParent) {
				if ($this->bRoot) {
					$oParent 	= $this->getElement($this->cRoot, false, true);
					$oElement	= $this->getElement($cElement, $this->cRoot, true);
				} else {
					$oParent 	= $this->getElement($this->cType, false, true);
					$oElement	= $this->getElement($cElement, $this->cType, true);
				}
			}
		}

		//there is no parent so make the parent root and create the element
		if (!$oParent) {
			$oParent	= $this->oXML;
			$oParent->addChild($cParent);

			$this->saveIt();

			$this->addElement($cElement, $cValue, $cParent);
			return false;
		}

		//the element exists so it needs replacing
		if ($oElement) {
			$this->replaceElement($cElement, $cValue, $cParent, $oElement);
		} else {
			if ($cValue) {
				$oParent->addChild($cElement, $cValue);
			} else {
				$oParent->addChild($cElement);
			}
		}

		$this->saveIt();
		$this->bRoot	= false;
		return false;
	}

	/**
	 * Exml::addElements()
	 *
	 * @param array $aElements
	 * @return null
	 */
	public function addElements($aElements) {
		foreach ($aElements as $aElement) {
			$cParent	= $aElement['parent'];
			$cElement	= $aElement['element'];
			$cValue		= $aElement['value'];

			$this->addElement($cElement, $cValue, $cParent);
		}
	}

	/**
	 * Exml::replaceElement()
	 *
	 * @param string $cElement
	 * @param string $cValue
	 * @param string $cParent
	 * @param object $oParent
	 * @param object $oElement
	 * @return null
	 */
	private function replaceElement($cElement, $cValue, $cParent, $oElement) {
		$oDom_b		= dom_import_simplexml($oElement);

		//remove the old element
		$oDom_b->parentNode->removeChild($oDom_b);

		//now add the new element
		$this->addElement($cElement, $cValue, $cParent);
	}

	/**
	 * Exml::addAttribute()
	 *
	 * @desc This adds an attribute to an element, e.g. <title case="lower"
	 * @param string $cElement
	 * @param string $cAttribute
	 * @param string $cValue
	 * @return null
	 */
	public function addAttribute($cElement, $cAttribute, $cValue) {
		$oElement	= $this->getElement($cElement, false, true);

		if (!$oElement) {
			$this->addElement($cElement);
			$this->addAttribute($cElement, $cAttribute, $cValue);
			return false;
		}

		$oElement->addAttribute($cAttribute, $cValue);
		$this->saveIt();
		return false;
	}
}

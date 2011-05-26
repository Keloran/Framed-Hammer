<?php
/**
 * Form_Abstract
 *
 * @package
 * @author keloran
 * @copyright Copyright (c) 2010
 * @version $Id$
 * @access public
 */
abstract class Form_Abstract implements Form_Interface {
	protected $aElement = array();
	protected $cName	= false;

	/**
	 * Form_Abstract::addElement()
	 *
	 * @desc This adds the element to the element array
	 * @param string $cType This sets the type of element e.g. type="textarea"
	 * @return null
	 */
	public function addElement($cType) {
		$cName	= $this->cName;
		if (isset($this->aElement[$cName])) { return false; }

		$this->aElement[$cName]['type']		= $cType;
	}

	/**
	 * Form_Abstract::getType()
	 *
	 * @return string
	 */
	public function getTyped() {
		$cName = $this->cName;
<<<<<<< HEAD
		if (isset($this->aElement[$cName])) {
			if (isset($this->aElement[$cName]['type'])) {
				return $this->aElement[$cName]['type'];
			}
		}

		return false;
=======

		return $this->aElement[$cName]['type'];
>>>>>>> c0c66965fad63221c98f14c695de9a95e55161f3
	}

	/**
	 * Form_Abstract::addHTML5()
	 *
	 * @desc This returns any HTML5 attributes
	 * @return string
	 */
	public function addHTML5() {
		$cName		= $this->cName;
		$cReturn	= false;
		$bHidden	= false;

		//does it have extraType defined
		if (isset($this->aElement[$cName]['extraType']) && ($this->aElement[$cName]['extraType'] == "hidden")) {
			$bHidden = true;
<<<<<<< HEAD
		} else if (isset($this->aElement[$cName]['type']) && ($this->aElement[$cName]['type'] == "hidden")) {
=======
		} else if ($this->aElement[$cName]['type'] == "hidden") {
>>>>>>> c0c66965fad63221c98f14c695de9a95e55161f3
			$bHidden = true;
		}

		//its not a hidden element
		if (!$bHidden) {
			//PlaceHolder
			if (isset($this->aElement[$cName]['placeholder'])) {
				$cReturn .= " placeholder=\"" . $this->aElement[$cName]['placeholder'] . "\"";
			} else {
				$cReturn .= " placeholder=\"" . $cName . "\"";
			}
		}

		//not really html5
		if (isset($this->aElement[$cName]['tabindex'])) {
			$cReturn .= " tabindex=\"" . $this->aElement[$cName]['tabindex'] . "\"";
		}

		//RegEX
		if (isset($this->aElement[$cName]['pattern'])) {
			$cReturn .= " pattern=\"" . $this->aElement[$cName]['pattern'] . "\"";
		} else if (isset($this->aElement[$cName]['regex'])) {
			$cReturn .= " pattern=\"" . $this->aElement[$cName]['regex'] . "\"";
		}

		//Require
		if (isset($this->aElement[$cName]['required'])) { $cReturn .= " required"; }

		//AutoFocus
		if (isset($this->aElement[$cName]['autofocus'])) {
			$cReturn .= " autofocus";
		} else if (isset($this->aElement[$cName]['focus'])) {
			$cReturn .= " autofocus";
		}

		//NoComplete
		if (isset($this->aElement[$cName]['nocomplete'])) { $cReturn .= " autocomplete=\"off\""; }

		//Min
		if (isset($this->aElement[$cName]['min'])) { $cReturn .= " min=\"" . $this->aElement[$cName]['min'] . "\""; }

		//Max
		if (isset($this->aElement[$cName]['max'])) { $cReturn .= " max=\"" . $this->aElement[$cName]['max'] . "\""; }

		//Step
		if (isset($this->aElement[$cName]['step'])) { $cReturn .= " step=\"" . $this->aElement[$cName]['step'] . "\""; }

		//Optimum
		if (isset($this->aElement[$cName]['optimum'])) { $cReturn .= " optimum=\"" . $this->aElement[$cName]['optimum'] . "\""; }

		//list
		if (isset($this->aElement[$cName]['list'])) {
			$cList	= $this->aElement[$cName]['list'];

			if (isset($this->aElement[$cList]['type']) && ($this->aElement[$cList]['type'] == "datalist")) {
				$cReturn .= " list=\"" . $this->aElement[$cName]['list'] . "\"";
			}
		}

		//title
		if (isset($this->aElement[$cName]['title'])) {
			$cReturn .= " title=\"" . $this->aElement[$cName]['title'] . "\"";
		}

		//Finally return it
		return $cReturn;
	}

	/**
	 * Form_Abstract::addTitle()
	 *
	 * @param string $cTitle
	 * @return object
	 */
	public function addTitle($cTitle) {
		$cName		= $this->cName;
		$this->aElement[$cName]['title']	= $cTitle;

		return $this;
	}

	/**
	 * Form_Abstract::getName()
	 *
	 * @return string
	 */
	public function getName() {
		return $this->cName;
	}

	/**
	 * Form_Abstract::setID()
	 *
	 * @param string $cID
	 * @return null
	 */
	public function setID($cID) {
		$cName = $this->cName;
		$this->aElement[$cName]['id'] = $cID;

		return $this;
	}

	/**
	 * Form_Abstract::setError()
	 *
	 * @param string $cError
	 * @return null
	 */
	public function setError($cError = null) {
		$cName = $this->cName;

		if ($cError) {
			$this->aElement[$cName]['error'] = $cError;
		}

		return $this;
	}

	/**
	 * Form_Abstract::setPlaceHolder()
	 *
	 * @param string $cPlaceHolder
	 * @return null
	 */
	public function setPlaceHolder($cPlaceHolder) {
		$cName = $this->cName;
		$this->aElement[$cName]['placeholder'] = $cPlaceHolder;

		return $this;
	}

	/**
	 * Form_Abstract::setClass()
	 *
	 * @param string $cClass
	 * @return null
	 */
	public function setClass($cClass) {
		$cName = $this->cName;
		$this->aElement[$cName]['class'] = $cClass;

		return $this;
	}

	/**
	 * Form_Abstract::setLabel()
	 *
	 * @param string $cLabel
	 * @param string $cClass
	 * @return null
	 */
	public function setLabel($cLabel, $cClass = null) {
		$cName = $this->cName;
		$this->aElement[$cName]['label'] = $cLabel;

		//there is a class
		if ($cClass) {
			$this->aElement[$cName]['labelClass'] = $cClass;
		}

		return $this;
	}

	/**
	 * Form_Abstract::addSurrowned()
	 *
	 * @param string $cElement
	 * @param string $cElementClass
	 * @param string $cElementID
	 * @return object
	 */
	public function addSurrowned($cElement = false, $cElementClass = false, $cElementID = false) {
		$cName	= $this->cName;

		$this->aElement[$cName]['surrowned'] = $cElement;

		if ($cElementClass) {	$this->aElement[$cName]['surrownedClass']	= $cElementClass; }
		if ($cElementID) {		$this->aElement[$cName]['surrownedID']		= $cElementID; }

		return $this;
	}

	/**
	 * Form_Abstract::setValue()
	 *
	 * @desc This sets the value to enter into the element value="value"
	 * @param string $cValue
	 * @return null
	 */
	public function setValue($cValue) {
		$cName = $this->cName;
		$this->aElement[$cName]['value'] = $cValue;

		return $this;
	}

	/**
	 * Form_Abstract::getError()
	 *
	 * @param bool $bElement
	 * @return string
	 */
	protected function getError($bElement = null) {
		$cName = $this->cName;
		$cReturn	= false;

		if (isset($this->aElement[$cName]['error'])) {
			$cError = $this->aElement[$cName]['error'];

			if ($bElement) {
				$cReturn = "<div class=\"formError\">" . $cError . "</div>\n";
			} else {
				$cReturn = $cError;
			}
		}

		return $cReturn;
	}

	/**
	 * Form_Abstract::createLabel()
	 *
	 * @return string
	 */
	public function createLabel($cName) {
<<<<<<< HEAD
=======
		#$cName		= $this->cName;
>>>>>>> c0c66965fad63221c98f14c695de9a95e55161f3
		$cReturn	= false;
		$cLabel		= false;

		//there is an element with this name
		if (isset($this->aElement[$cName])) {
			//label
			if (isset($this->aElement[$cName]['label'])) {
				$cLabel = $this->aElement[$cName]['label'];
			}

			//title
			if (isset($this->aElement[$cName]['title']) && (!$cLabel)) {
				$cLabel = $this->aElement[$cName]['title'];
			}

			//placeholder
			if (isset($this->aElement[$cName]['placeholder']) && (!$cLabel)) {
				$cLabel = $this->aElement[$cName]['placeholder'];
			}

			//is search set
			if (isset($this->aElement[$cName]['search'])) {
				$cLabel = false;
			}

			//now is the type hidden
			if (isset($this->aElement[$cName]['extraType']) && ($this->aElement[$cName]['extraType'] == "hidden")) {
				$cLabel = false;
			}
		}

		//there is a label
		if ($cLabel) {
			$cLabel	= ucwords($cLabel);

			//is there an error
			if (isset($this->aElement[$cName]['error'])) {
				$cLabel = "*" . $cLabel . "*:";
			} else if (isset($this->aElement[$cName]['required'])) {
				$cLabel = "*" . $cLabel . "*:";
			} else {
				$cLabel .= ":";
			}

			//now start the actual creation
			$cReturn = "<label for=\"" . $cName . "\"";

			//is there a label class
			if (isset($this->aElement[$cName]['labelClass'])) {
				$cReturn .= " class=\"" . $this->aElement[$cName]['labelClass'] . "\"";
			}

			//is there a label id
			if (isset($this->aElement[$cName]['labelid'])) {
				$cReturn .= " id=\"" . $this->aElement[$cName]['labelid'] . "\"";
			} else {
				$cReturn .= " id=\"label" . $cName . "\"";
			}

			//close the label
			$cReturn .= ">" . $cLabel . "</label>\n";
		}

		return $cReturn;
	}

	/**
	 * Form_Abstract::startSurrowned()
	 *
	 * @return string
	 */
	public function startSurrowned() {
		$cName		= $this->cName;
		$cReturn	= false;

		if (isset($this->aElement[$cName])) {
			if (isset($this->aElement[$cName]['surrowned'])) {
				if ($this->aElement[$cName]['surrowned']) {
					$cReturn = "<" . $this->aElement[$cName]['surrowned'];

					//does it have a class
					if (isset($this->aElement[$cName]['surrownedClass'])) {
						if ($this->aElement[$cName]['surrownedClass']) {
							$cReturn .= " class=\"" . $this->aElement[$cName]['surrownedClass'] . "\" ";
						}
					}

					//does it have an id
					if (isset($this->aElement[$cName]['surrownedID'])) {
						if ($this->aElement[$cName]['surrownedID']) {
							$cReturn .= " id=\"" . $this->aElement[$cName]['surrownedID'] . "\" ";
						}
					}

					$cReturn .= ">\n";
				}
			}
		}

		return $cReturn;
	}

	/**
	 * Form_Abstract::closeSurrowned()
	 *
	 * @return string
	 */
	public function closeSurrowned() {
		$cName		= $this->cName;
		$cReturn	= false;

		if (isset($this->aElement[$cName])) {
			if (isset($this->aElement[$cName]['surrowned'])) {
				if ($this->aElement[$cName]['surrowned']) {
					$cReturn = "</" . $this->aElement[$cName]['surrowned'] . ">\n";
				}
			}
		}

		return $cReturn;
	}

	/**
	 * Form_Abstract::addExtras()
	 *
	 * @desc This adds any extra stuff to the element, e.g. bbCode
	 * @param mixed $mExtras
	 * @return null
	 */
	abstract public function addExtras($mExtras);

	/**
	 * Form_Abstract::addError()
	 *
	 * @param string $cError
	 * @return string
	 */
	public function addError() {
		$cReturn	= false;
		$cName		= $this->cName;
		$cError		= false;

		//get the error
		if (isset($this->aElement[$cName]['error'])) { $cError = $this->aElement[$cName]['error']; }

		//if an error exists
		if ($cError) {
			$cReturn	 = "<div id=\"" . $cName . "error\" class=\"form_error\">";
			$cReturn	.= $cError;
			$cReturn 	.= "</div>";
		}

		return $cReturn;
	}

	/**
	 * Form_Abstract::createElement()
	 *
	 * @desc This creates the element after all the properties have been set
	 * @return string
	 */
	abstract protected function createElement();

	/**
	 * Form_Abstract::getValue()
	 *
	 * @param bool $bFile
	 * @return mixed
	 */
	public function getValue($bFile = false) {
		$cType 	= $this->getTyped();
		$cName	= $this->cName;

		//its a file (proberlly gona be set anyway by typed)
		if ($bFile) { $cType = "file"; }

		$oValue = new Form_Value();
		$oValue->setName($cName)
			->setTyped($cType);

		return $oValue->getValue();
	}
}
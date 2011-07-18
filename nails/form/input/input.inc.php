<?php
/**
 * Form_Input
 *
 * @package
 * @author keloran
 * @copyright Copyright (c) 2010
 * @version $Id: input.inc.php 3389 2011-03-02 11:33:32Z keloran $
 * @access public
 */
class Form_Input extends Form_Abstract {
	public $cFormElementType	= "input";

	/**
	 * Form_Input::__construct()
	 *
	 * @param string $cName
	 */
	public function __construct($cName = false) {
		if ($cName) {
			if (!isset($_FORM[$cName])) {
				$this->cName = $cName;
				$this->addElement("input");
			}
		}
	}

	/**
	 * Form_Input::addExtras()
	 *
	 * @desc This sets the type, e.g. password
	 * @param mixed $mExtras
	 * @return null
	 */
	public function addExtras($mExtras) {
		$cName = $this->cName;

		if ($mExtras) {
			if (is_array($mExtras)) {
				if (isset($mExtras[0])) {
					for ($i = 0; $i < count($mExtras); $i++) {
						foreach ($mExtras[$i] as $cKey => $cValue) {
							$this->aElement[$cName][$cKey] = $cValue;
						}
					}
				}
			}
		}

		return $this;
	}

	/**
	 * Form_Input::setType()
	 *
	 * @param string $cType
	 * @return object
	 */
	public function setTyped($cType) {
		$cName = $this->cName;

		$this->aElement[$cName]['extraType'] = $cType;

		return $this;
	}

	/**
	* Form_Input::setSearch()
	*
	* @param bool $bSearch
	* @return object
	*/
	public function setSearch($bSearch = false) {
		if ($bSearch) {
			$cName = $this->cName;
			$this->aElement[$cName]['search']	= true;
		}

		return $this;
	}

	/**
	 * Form_Input::createElement()
	 *
	 * @return string
	 */
	public function createElement() {
		$cName	= $this->cName;

		//create teh surrowned if one needed
		$cReturn = $this->startSurrowned($cName);

		//create the label
		$cReturn .= $this->createLabel($cName);

		//create teh input
		$cReturn .= "<input ";
		$cReturn .= " type=\"" . $this->aElement[$cName]['extraType'] . "\" ";

		//has a class
		if ($this->aElement[$cName]['class']) { $cReturn .= " class=\"" . $this->aElement[$cName]['class'] . "\""; }

		//has an id
		if ($this->aElement[$cName]['id']) { $cReturn .= " id=\"" . $this->aElement[$cName]['id'] . "\""; }

		//set its name and close the opener
		$cReturn .= " name=\"" . $cName . "\"";

		//has a value set
		if (isset($this->aElement[$cName]['value'])) {
			if ($this->aElement[$cName]['value']) { //it might be set, but it could be blank
				$cReturn .= " value=\"" . $this->aElement[$cName]['value'] . "\"";
			}
		}

		//is it a checkbox and is it checked
		if (isset($this->aElement[$cName]['checked'])) {
			if ($this->aElement[$cName]['checked']) {
				$cReturn .= " checked=\"checked\" ";
			}
		}

		//if autofocus
		if (isset($this->aElement[$cName]['autofocus'])) {
			$cReturn .= " autofocus ";
		}

		//HTML5
		$cReturn .= $this->addHTML5();

		//close the element
		$cReturn .= " />\n";

		//Errors
		$cReturn .= $this->addError();

		//close the sorrowned
		$cReturn .= $this->closeSurrowned($cName);

		return $cReturn;
	}

	/**
	 * Form_Input::validate()
	 *
	 * @param string $cType
	 * @return string
	 */
	public function validate($cType = "text") {
		$mReturn	= false;

		switch($cType) {
			case "email":
				$mReturn = $this->validateEmail();
				break;

			case "number":
				$mReturn = $this->validateNumber();
				break;

			default:
				$mReturn = $this->validateText();
				break;
		}

		return $mReturn;
	}

	/**
	 * Form_Input::validateEmail()
	 *
	 * @return string
	 */
	private function validateEmail() {
		$cReturn	= false;

		//Might aswell use filter var if its avalible, less resource-hungry
		if (function_exists("filter_var")) {
			$cReturn	= filter_var($this->getValue(), FILTER_VALIDATE_EMAIL);
		} else {
			$cPattern = "([\\w-+]+(?:\\.[\\w-+]+)*@(?:[\\w-]+\\.)+[a-zA-Z]{2,7})";
			if (preg_match($cPattern, $this->getValue())) {
				$cReturn = $this->getValue();
			}
		}

		return $cReturn;
	}

	/**
	 * Form_Input::validateText()
	 *
	 * @return string
	 */
	private function validateText() {
		$cReturn	= false;
		$cInput		= $this->getValue();

		//It doesnt have anything
		if (!isset($cInput[0])) { return false; }

		//Use filter var
		if (function_exists("filter_var")) {
			$aFilters	= array(FILTER_FLAG_ENCODE_HIGH, FILTER_FLAG_ENCODE_LOW, FILTER_FLAG_ENCODE_AMP);
			$cReturn	= filter_var($cInput, FILTER_SANITIZE_STRING, $aFilters);
		} else {
			if (preg_match("`([a-zA-Z0-9\-_]+)`is", $cInput)) {
				$cReturn = addslashes($cInput);
			}
		}

		return $cReturn;
	}

	/**
	 * Form_Input::validateNumber()
	 *
	 * @return string
	 */
	private function validateNumber() {
		$cReturn	= false;
		$cInput		= $this->getValue();

		//its not actually got any chars
		if (!isset($cInput[0])) { return false; }

		if (function_exists("filter_var")) {
			if (filter_var($cInput, FILTER_VALIDATE_INT)) {
				$cReturn	= filter_var($cInput, FILTER_SANITIZE_NUMBER_INT);
			}
		} else {
			if (preg_match("`([0-9\s]+)`is", $cInput)) {
				$cReturn = addslashes($cInput);
			}
		}

		return $cReturn;
	}


}

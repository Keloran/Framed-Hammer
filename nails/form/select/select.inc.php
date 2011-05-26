<?php
/**
 * Form_Select
 *
 * @package
 * @author keloran
 * @copyright Copyright (c) 2010
 * @version $Id$
 * @access public
 */
class Form_Select extends Form_Abstract {
	public $cFormElementType = "select";

	/**
	 * Form_Select::__construct()
	 *
	 * @param string $cName
	 */
	public function __construct($cName) {
		$this->cName = $cName;
		$this->addElement("select");
	}

	/**
	 * Form_Select::addExtras()
	 *
	 * @param string $cName
	 * @param bool $bBBCode
	 * @return null
	 */
	public function addExtras($mExtras) {
		$cName = $this->cName;

		foreach ($mExtras as $cKey => $cValue) {
			$this->aElement[$cName]['options'][$cKey] = $cValue;
		}

		return $this;
	}

	/**
	 * Form_Select::createElement()
	 *
	 * @return string
	 */
	public function createElement() {
		$cName	= $this->cName;

		$cReturn = $this->createLabel($cName);

		$cReturn .= "<select name=\"" . $cName . "\" ";

		//has a class
		if ($this->aElement[$cName]['class']) { $cReturn .= "class=\"" . $this->aElement[$cName]['class'] . "\" "; }

		//has an id
		if ($this->aElement[$cName]['id']) { $cReturn .= "id=\"" . $this->aElement[$cName]['id'] . "\" "; }

<<<<<<< HEAD
=======
		#$cReturn .= "error=\"" . $this->getError($cName) . "\"";

>>>>>>> c0c66965fad63221c98f14c695de9a95e55161f3
		//HTML5
		$cReturn .= $this->addHTML5();
		$cReturn .= ">";

		//get the values
		$cReturn .= $this->getOptions();

		//close the element
		$cReturn .= "</select>\n";

		//Errors
		$cReturn .= $this->addError();

<<<<<<< HEAD
=======
		//has it got an error
		#$cReturn .= $this->getError($cName, true);

>>>>>>> c0c66965fad63221c98f14c695de9a95e55161f3
		return $cReturn;
	}

	/**
	 * Form_Select:getOptions()
	 *
	 * @return string
	 */
	private function getOptions() {
		$cReturn	= false;
		$cName		= $this->cName;


		if (isset($this->aElement[$cName]['options'])) {
			if (is_array($this->aElement[$cName]['options'])) {
				$cReturn = "<option>Please Select One</option>\n";

				foreach ($this->aElement[$cName]['options'] as $cKey => $cValue) {
					if (strstr($cValue, "||")) {
						$iPos 		= strpos($cValue, "||");
						$cValue		= substr($cValue, 0, $iPos);
						$cSelected	= "selected=\"selected\"";
					} else {
						$cSelected	= "";
					}

					$cReturn .= "<option value=\"" . $cValue . "\"" . $cSelected . ">" . $cKey . "</option>";
				}
			}
		} else {
			$cReturn .= "<option>Please Select One</option>\n";
		}

		return $cReturn;
	}
}

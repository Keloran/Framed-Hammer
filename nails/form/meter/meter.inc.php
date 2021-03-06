<?php
/**
 * Form_Meter
 *
 * @package Form
 * @author keloran
 * @copyright Copyright (c) 2010
 * @version $Id: Meter.inc.php 1159 2010-05-10 12:17:11Z keloran $
 * @access public
 */
class Form_Meter extends Form_Abstract {
	public $cFormElementType = "meter";

	/**
	 * Form_Meter::__construct()
	 *
	 * @param string $cName
	 */
	public function __construct($cName) {
		$this->cName = $cName;
		$this->addElement("Meter");
	}

	/**
	 * Form_Meter::addExtras()
	 *
	 * @desc This sets the type, e.g. password
	 * @param mixed $mExtras
	 * @return null
	 */
	public function addExtras($mExtras) {
		$cName = $this->cName;

		foreach ($mExtras as $cKey => $cValue) {
			$this->aElement[$cName][$cKey] = $cValue;
		}

		return $this;
	}

	/**
	 * Form_Meter::createElement()
	 *
	 * @return string
	 */
	public function createElement() {
		$cName	= $this->cName;

		//label
		$cReturn = $this->createLabel($cName);

		//Meter meter
		$cReturn .= "<meter ";

		//has a class
		if ($this->aElement[$cName]['class']) { $cReturn .= " class=\"" . $this->aElement[$cName]['class'] . "\""; }

		//has an id
		if ($this->aElement[$cName]['id']) { $cReturn .= " id=\"" . $this->aElement[$cName]['id'] . "\""; }

		//there is an error
		$cError = $this->getError($cName);
		if ($cError) { $cReturn .= " error=\"" . $cError . "\""; }

		//set its name and close the opener
		$cReturn .= "name=\"" . $cName . "\"";

		//HTML5
		$cReturn .= $this->addHTML5();

		//close the element
		$cReturn .= " />\n";

		//the type of meter, e.g. cm
		$cType = $this->aElement[$cName]['meterType'];

		//has a value set
		if (isset($this->aElement[$cName]['value'])) { $cReturn .= $this->aElement[$cName]['value'] . $cType; }

		//close
		$cReturn .= "</meter>";

		//has it got an error
		$cReturn .= $this->getError($cName, true);

		return $cReturn;
	}
}
<?php
/**
 * Form_Progress
 *
 * @package
 * @author keloran
 * @copyright Copyright (c) 2010
 * @version $Id: Progress.inc.php 1159 2010-05-10 12:17:11Z keloran $
 * @access public
 */
class Form_Progress extends Form_Abstract {
	public $cFormElementType = "progress";

	/**
	 * Form_Progress::__construct()
	 *
	 * @param string $cName
	 */
	public function __construct($cName) {
		$this->cName = $cName;
		$this->addElement("progress");
	}

	/**
	 * Form_Progress::addExtras()
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
	 * Form_Progress::createElement()
	 *
	 * @return string
	 */
	public function createElement() {
		$cName	= $this->cName;

		//label
		$cReturn = $this->createLabel($cName);

		//progress meter
		$cReturn .= "<progress ";

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

		//has a value set
		if (isset($this->aElement[$cName]['value'])) { $cReturn .= $this->aElement[$cName]['value'] . "%"; }

		//close
		$cReturn .= "</progress>";

		//has it got an error
		$cReturn .= $this->getError($cName, true);

		return $cReturn;
	}
}
<?php
/**
 * Form_File
 *
 * @package
 * @author keloran
 * @copyright Copyright (c) 2010
 * @version $Id: file.inc.php 3382 2011-03-02 11:04:47Z keloran $
 * @access public
 */
class Form_File extends Form_Abstract {
	public $cFormElementType = "file";

	/**
	 * Form_File::__construct()
	 *
	 * @param string $cName
	 */
	public function __construct($cName) {
		if ($this->cName !== $cName) {
			$this->cName = $cName;
			$this->addElement("input");
		}
	}

	/**
	 * Form_File::addExtras()
	 *
	 * @desc This sets the type, e.g. password
	 * @return null
	 */
	public function addExtras($mExtras) {
		$cName = $this->cName;

		return $this;
	}

	/**
	 * Form_File::createElement()
	 *
	 * @return string
	 */
	public function createElement() {
		$cName	= $this->cName;

		$cReturn = $this->createLabel($cName);

		$cReturn .= "<input type=\"file\"";

		//has a class
		if ($this->aElement[$cName]['class']) { $cReturn .= "class=\"" . $this->aElement[$cName]['class'] . "\" "; }

		//has an id
		if ($this->aElement[$cName]['id']) { $cReturn .= "id=\"" . $this->aElement[$cName]['id'] . "\" "; }

<<<<<<< HEAD
=======
		#$cReturn .= "error=\"" . $this->getError($cName) . "\"";

>>>>>>> c0c66965fad63221c98f14c695de9a95e55161f3
		//set its name and close the opener
		$cReturn .= "name=\"" . $cName . "\"";

		//HTML5
		$cReturn .= $this->addHTML5();

		//close the element
		$cReturn .= " />\n";

		//Errors
		$cReturn .= $this->addError();

<<<<<<< HEAD
=======
		//has it got an error
		#$cReturn .= $this->getError($cName, true);

>>>>>>> c0c66965fad63221c98f14c695de9a95e55161f3
		return $cReturn;
	}
}
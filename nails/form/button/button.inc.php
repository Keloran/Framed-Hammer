<?php
/**
 * Form_Button
 *
 * @package
 * @author keloran
 * @copyright Copyright (c) 2010
 * @version $Id: button.inc.php 2506 2010-09-16 10:15:40Z keloran $
 * @access public
 */
class Form_Button extends Form_Abstract {
	public $cFormElementType = "button";

	/**
	 * Form_Button::__construct()
	 *
	 * @param string $cName
	 */
	public function __construct($cName) {
		$this->cName = $cName;
		$this->addElement("button");
	}

	/**
	 * Form_Button::addExtras()
	 *
	 * @desc This sets the type, e.g. password
	 * @return null
	 */
	public function addExtras($mExtras) {
		$cName = $this->cName;
		$this->aElement[$cName]['buttonType'] = $mExtras;

		return $this;
	}

	/**
	 * Form_Button::createElement()
	 *
	 * @return string
	 */
	public function createElement() {
		$cName = $this->cName;

		//button type should be reset if its abort
		if ($this->aElement[$cName]['buttonType'] == "abort") {
			$this->aElement[$cName]['buttonType'] = "reset";
		}

		$cReturn	 = "<button ";
		$cReturn	.= " type=\"" . $this->aElement[$cName]['buttonType'] . "\" ";
		$cReturn	.= " id=\"" . $this->aElement[$cName]['id'] . "\" ";

		if ($this->aElement[$cName]['class']) { $cReturn .= " class=\"" . $this->aElement[$cName]['class'] . "\" "; }

		$cReturn	.= ">" . $this->aElement[$cName]['label'] . "</button>\n";

		return $cReturn;
	}
}
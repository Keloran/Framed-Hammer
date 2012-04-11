<?php
/**
 * Form_TextArea
 *
 * @package Form
 * @author keloran
 * @copyright Copyright (c) 2010
 * @version $Id$
 * @access public
 */
class Form_TextArea extends Form_Abstract {
	public $cFormElementType 	= "textarea";
	public $cFormElementName	= false;
	public $cBBCodeOptions		= "{singleLine: true}";
	public $bBBCode				= false;

	/**
	 * Form_TextArea::__construct()
	 *
	 * @param string $cName
	 */
	function __construct($cName) {
		$this->cName = $cName;
		$this->addElement("textarea");

		$this->cFormElementName	= $cName;
	}

	/**
	 * Form_TextArea::addExtras()
	 *
	 * @param string $cName
	 * @param bool $bBBCode
	 * @return null
	 */
	public function addExtras($bBBCode) {
		$cName = $this->cName;

		if ($bBBCode) {
			$this->aElement[$cName]['bbCode'] = true;
		}

		return $this;
	}

	/**
	 * Form_TextArea::createElement()
	 *
	 * @return string
	 */
	public function createElement() {
		$cName	= $this->cName;

		//bbcode options
		if (isset($this->aElement[$cName]['bbCode_Options'])) {
			if ($this->aElement[$cName]['bbCode_Options']) {
				$this->cBBCodeOptions = json_encode($this->aElement[$cName]['bbCode_Options']);
			}
		}

		//surrowned
		if (!isset($this->aElement[$cName]['bbCode'])) {
			$cReturn 		= $this->startSurrowned($cName);
		} else {
			$cReturn = false;
		}

		//label
		$cReturn .= $this->createLabel($cName);

		if (isset($this->aElement[$cName]['bbCode'])) {
			$this->bBBCode	= true;
			$cReturn .= "<div id=\"bbContainer_" . $this->aElement[$cName]['id'] . "\">\n<div id=\"" . $this->aElement[$cName]['id'] . "bbCode\" class=\"bbCode\"></div>\n";
			$cReturn .= $this->startSurrowned($cName);
		}

		$cReturn .= "<textarea ";

		//has a class
		if ($this->aElement[$cName]['class']) { $cReturn .= " class=\"" . $this->aElement[$cName]['class'] . "\""; }

		//has an id
		if ($this->aElement[$cName]['id']) {
			$cReturn .= " id=\"" . $this->aElement[$cName]['id'] . "\"";
			$this->cFormElementName	= $this->aElement[$cName]['id'];
		}

		//html5
		$cReturn .= $this->addHTML5();

		//set its name and close the opener
		$cReturn .= " name=\"" . $cName . "\">";

		//has a value set
		if (isset($this->aElement[$cName]['value'])) { $cReturn .= $this->aElement[$cName]['value']; }


		//close the element
		$cReturn .= "</textarea>\n";

		if (isset($this->aElement[$cName]['bbCode'])) {
			$cReturn .= $this->closeSurrowned();
			$cReturn .= "</div>";
		}

		//Errors
		$cReturn .= $this->addError();

		//close surrowned
		if (!isset($this->aElement[$cName]['bbCode'])) {
			$cReturn .= $this->closeSurrowned();
		}

		return $cReturn;
	}

	/**
	 * Form_TextArea::validate()
	 *
	 * @return
	 */
	public function validate() {

	}
}

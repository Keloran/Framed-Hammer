<?php
/**
 * Form_Element
 *
 * @package
 * @author keloran
 * @copyright Copyright (c) 2010
 * @version $Id$
 * @access public
 */
class Form_Element extends Form_Abstract {
	private $cElement	= false;
	public $cFormElementType = "element";

	/**
	 * Constructor
	 */
	public function __construct($cName) {
		$this->cName = $cName;
		$this->addElement("element");
	}

	/**
	 * Form_Element::addExtras()
	 *
	 * @param mixed $mExtras
	 * @return object
	 */
	public function addExtras($mExtras) {
		$cName	= $this->cName;

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
	 * Form_Element::setElement()
	 *
	 * @param string $cElement
	 * @return object
	 */
	public function setElement($cElement) {
		$cName			= $this->cName;

		$this->cElement = $cElement;

		//self closing or not
		switch ($cElement) {
			case "br":
			case "hr":
			case "img":
				$this->addExtras(array("selfClosing" => true));
				break;

			default:
				$this->addExtras(array("selfClosing" => false));
				break;
		}

		return $this;
	}

	/**
	 * Form_Element::createElement()
	 *
	 * @return string
	 */
	public function createElement() {
		$cName		= $this->cName;
		$cReturn	= "<"; //open the tag

		//set the type
		$cReturn .= $this->cElement;

		//does it have a class
		if (isset($this->aElement[$cName]['class'])) { $cReturn .= " class=\"" . $this->aElement[$cName]['class'] . "\" "; }

		//does it have an id
		if (isset($this->aElement[$cName]['id'])) { $cReturn .= " id=\"" . $this->aElement[$cName]['id'] . "\" "; }

		//if self closing is missing
		if (!isset($this->aElement[$cName]['selfClosing'])) { $this->aElement[$cName]['selfClosing'] = false; }

		//is it a self closer
		if ($this->aElement[$cName]['selfClosing']) {
			$cReturn .= " /";
		} else {
			if (isset($this->aElement[$cName]['value'])) {
				$cReturn .= ">";
				$cReturn .= $this->aElement[$cName]['value'];
			} else {
				$cReturn .= ">";
			}

			$cReturn .= "</" . $this->cElement;
		}

		$cReturn .= ">"; //close the tag

		return $cReturn;
	}
}

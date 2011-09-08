<?php
/**
 * Validate_Email
 *
 * @package
 * @author keloran
 * @copyright Copyright (c) 2011
 * @version $Id$
 * @access public
 */
class Validate_Email {
	public $mValue;
	private $mPreValue;

	/**
	 * Validate_Text::__construct()
	 *
	 */
	function __construct() {

	}

	/**
	 * Validate_Text::validate()
	 *
	 * @param mixed $mEntry
	 * @return mixed
	 */
	public function validate($mEntry) {
		$this->mPrevalueValue	= $mEntry;
		$this->mValue			= $this->doValidate();

		return $this;
	}

	/**
	 * Validate_Text::doValidate()
	 *
	 * @return mixed
	 */
	private function doValidate() {
		$cReturn	= false;
		$cInput		= $this->mPrevalueValue;

		//It doesnt have anything
		if (!isset($cInput[0])) { return false; }

		//Use filter var
		if (function_exists("filter_var")) {
			$cReturn	= filter_var($cInput, FILTER_VALIDATE_EMAIL);
		} else {
			$cPattern = "([\\w-+]+(?:\\.[\\w-+]+)*@(?:[\\w-]+\\.)+[a-zA-Z]{2,7})";
			if (preg_match($cPattern, $cInput)) {
				$cReturn = $cInput;
			}
		}

		return $cReturn;
	}
}
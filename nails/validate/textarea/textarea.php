<?php
/**
 * Validate_TextArea
 *
 * @package
 * @author keloran
 * @copyright Copyright (c) 2011
 * @version $Id$
 * @access public
 */
class Validate_TextArea {
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
			$aFilters	= array(FILTER_FLAG_ENCODE_HIGH, FILTER_FLAG_ENCODE_LOW, FILTER_FLAG_ENCODE_AMP);
			$cReturn	= filter_var($cInput, FILTER_SANITIZE_STRING, $aFilters);
		} else {
			if (preg_match("`([a-zA-Z0-9\-_]+)`is", $cInput)) {
				$cReturn = addslashes($cInput);
			}
		}

		return $cReturn;
	}
}
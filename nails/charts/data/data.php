
<?php
class Charts_Data {
	var $cDesc 	= false;
	var $iValue	= false;
	var $cExtra	= false;

	/**
	 * Constructor
	 * @access protected
	 */
	function __construct($iValue, $cDesc = false, $cExtra = false) {
		$this->cDesc 	= $cDesc;
		$this->iValue	= $iValue;
		$this->cExtra	= $cExtra;
	}
}
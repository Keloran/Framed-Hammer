
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
		$this->cExtra	= $cExtra;
		$this->cColor	= $this->createColors();

		//if value is 0 set it to infinitlly small, so it can be divided by
		if ($iValue == 0) { $iValue = 0.1; }
		$this->iValue	= $iValue;
	}

	/**
	 * Charts_Data::createColors()
	 *
	 * @desc This returns a random color
	 * @return string
	 */
	private function createColors() {
		$cReturn	= "#";
		$iRange		= range(0, 9);
		$cRange		= range("A", "F");
		$aRange		= array_merge($iRange, $cRange);
		$iCount		= count($aRange) - 1;

		for ($i = 0; $i < 6;  $i++) {
			$iRand		 = rand(0, $iCount);
			$cReturn	.= $aRange[$iRand];
		}

		return $cReturn;
	}
}

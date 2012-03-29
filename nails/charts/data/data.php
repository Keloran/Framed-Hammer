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
		$this->iValue	= $iValue; //the set to 0 wasnt needed, it was in the divide that was needed
		$this->createRGB(); //get the rgb
		$this->createColorDebug(); //create the debug
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

	/**
	 * Charts_Data::createRGB()
	 *
	 * @return null
	 */
	private function createRGB() {
		$cColor	= $this->cColor;

		//remove # from start
		$cColor	= substr($cColor, 1);

		//now get red
		$cRed	= hexdec($cColor[0] . $cColor[1]);

		//now get green
		$cGreen	= hexdec($cColor[2] . $cColor[3]);

		//now get blue
		$cBlue	= hexdec($cColor[4] . $cColor[5]);

		//now set to object
		$this->cColorRed 	= $cRed;
		$this->cColorGreen	= $cGreen;
		$this->cColorBlue	= $cBlue;
	}

	/**
	 * Charts_Data::createColorDebug()
	 *
	 * @return null
	 */
	private function createColorDebug() {
		$cRed	= dechex($this->cColorRed);
		$cGreen	= dechex($this->cColorGreen);
		$cBlue	= dechex($this->cColorBlue);

		$aColorDebug = array(
			"Red"	=> $cRed,
			"Green"	=> $cGreen,
			"Blue"	=> $cBlue
		);
		$this->aColorDebug = $aColorDebug;
	}
}

<?php
/**
 * Charts_Bar
 *
 * @package
 * @author Max
 * @copyright Copyright (c) 2008
 * @version $Id$
 * @access public
 */
class Charts_SVG_BarV {
	var $cTitle     = false;
	var $aOptions	= false;
	var $aData		= false;

	/**
	 * Constructor
	 * @access protected
	 */
	function __construct(){
	}

	/**
	 * Charts_Bar::renderChart()
	 *
	 * @return string
	 */
	function renderChart() {
		$iX = isset($this->aOptions["iX"])	? $this->aOptions["iX"]	: 0;
		$iY = isset($this->aOptions["iY"])	? $this->aOptions["iY"]	: 0;

		$aData = $this->aData;

		$cOutput  = "<defs>\n";
		$cOutput .= "<filter id='flt'>\n";
		$cOutput .= "<feGaussianBlur in='SourceAlpha' stdDeviation='0.5' result='blur' />\n";

		$cOutput .= "<feSpecularLighting in='blur' surfaceScale='5' specularConstant='0.5' specularExponent='10' result='specOut' style='lighting-color: #FFF'>\n";
		$cOutput .= "<fePointLight x='-5000' y='-5000' z='5000' />\n";
		$cOutput .= "</feSpecularLighting>\n";

		$cOutput .= "<feComposite in='specOut' in2='SourceAlpha' operator='in' result='specOut2' />\n";
		$cOutput .= "<feComposite in='SourceGraphic' in2='SpecOut2' operator='arithmetic' k1='0' k2='1' k3='1' k4='0' />\n";
		$cOutput .= "</filter>\n";
		$cOutput .= "</defs>\n";

		$iCount = 0;
		$iBarY	= $iY;

		foreach ($aData as $oObject) {
			$cColor 	= $oObject->cColor;
			$iHeight	= $oObject->iPercent * 2;
			$iPercent	= number_format($oObject->iPercent, 2, ",", ".");

			//Filter
			$cFilter 	= false;
			if (isset($this->aOptions['filter'])) { $cFilter = " style='filter: url(" . $this->aOptions['filter'] . ")' "; }

			if (isset($this->aOptions["bAnimated"])) {
				$cOutput .= "<rect x='" . $iX . "' y='" . $iBarY . "' width='15' height='0' fill='" . $cColor . "'" . $cFilter . " transform='rotate(-180 100 100)'>\n";
				$cOutput .= "<animate attributeName='height' attributeType='XML' begin='0s' dur='1s' fill='freeze' from='0' to='" . $iHeight . "' />\n";
				$cOutput .= "</rect>\n";
			} else {
				$cOutput .= "<rect x='" . $iX . "' y='" . $iBarY . "' width='15' height='" . $iHeight . "' fill='" . $cColor . "'" . $cFilter . " transform='rotate(-180 100 100)' />\n";
			}

			$iX = $iX + 27;
			$iCount++;
		}

		return $cOutput;
	}
}

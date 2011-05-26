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
class Charts_SVG_BarH {
	var $cTitle     = false;
	var $aOptions	= false;
	var $aData		= false;
	var $aColors	= false;

	/**
	 * Constructor
	 * @access protected
	 */
	function __construct(){
	}

	/**
	 * Charts_Bar::getColor()
	 *
	 * @param mixed $iID
	 * @return
	 */
	function getColor($iID) {
		$cColor = $this->aColors[$iID % count($this->aColors)];

		return $cColor;
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
		$iBarX	= $iX + 100;
		$iDescX	= $iX + 200;

		foreach ($aData as $oObject) {
			$cColor 	= $this->getColor($iCount);
			$iTextY		= $iY + 11;
			$iWidth		= $oObject->iPercent;
			$iPercent	= number_format($oObject->iPercent, 2, ",", ".");

			if (isset($this->aOptions["bAnimated"])) {
				$cOutput .= "<rect x='" . $iBarX . "' y='" . $iY . "' width='0' height='15' fill='" . $cColor . "' style='filter: url(#flt)'>\n";
				$cOutput .= "<animate attributeName='width' attributeType='XML' begin='0s' dur='1s' fill='freeze' from='0' to='" . $iWidth . "' />\n";
				$cOutput .= "</rect>\n";

				$cOutput .= "<text x='" . $iX . "' y='" . $iTextY . "' style='font-size: 12px; text-anchor: right;'>" . $oObject->cDesc . "</text>\n";

				$cOutput .= "<text x='" . $iDescX . "' y='" . $iTextY . "' style='font-size: 12px; text-anchor: right; visibility: hidden;'>" . $oObject->iValue . " [" . $iPercent . "%]\n";
				$cOutput .= "<animate attributeName='visibility' attributeType='CSS' begin='1s' dur='0.1s' fill='freeze' from='hidden' to='visible' calcMode='discrete' />\n";
				$cOutput .= "</text>\n";
			} else {
				$cOutput .= "<rect x='" . $iBarX . "' y='" . $iY . "' width='" . $iWidth . "' height='15' fill='" . $cColor . "' style='filter: url(#flt)' />\n";
				$cOutput .= "<text x='" . $iX . "' y='" . $iTextY . "' style='font-size: 12px; text-anchor: right;'>" . $oObject->cDesc . "</text>\n";
				$cOutput .= "<text x='" . $iDescX . "' y='" . $iTextY . "' style='font-size: 12px; text-anchor: right;'>" . $oObject->iValue . " [" . $iPercent . "%]</text>\n";
			}

			$iY = $iY + 27;
			$iCount++;
		}

		return $cOutput;
	}
}
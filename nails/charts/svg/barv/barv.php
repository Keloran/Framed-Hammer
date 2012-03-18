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

		$cOutput  = "<defs>";
		$cOutput .= "<filter id='flt' filterUnits='userSpaceOnUse' x='0' y='0' width='1200' height='1200'>";
		$cOutput .= "<feGaussianBlur in='SourceAlpha' stdDeviation='0.5' result='blur' />";

		$cOutput .= "<feSpecularLighting in='blur' surfaceScale='5' specularConstant='0.5' specularExponent='10' result='specOut' style='lighting-color: #FFF'>";
		$cOutput .= "<fePointLight x='-5000' y='-5000' z='5000' />";
		$cOutput .= "</feSpecularLighting>";

		$cOutput .= "<feComposite in='specOut' in2='SourceAlpha' operator='in' result='specOut2' />";
		$cOutput .= "<feComposite in='SourceGraphic' in2='SpecOut2' operator='arithmetic' k1='0' k2='1' k3='1' k4='0' />";
		$cOutput .= "</filter>";
		$cOutput .= "</defs>";

		$iCount = 0;
		$iBarY	= $iY;

		foreach ($aData as $oObject) {
			$cColor 	= $oObject->cColor;
			$iHeight	= $oObject->iPercent * 2;
			$iPercent	= number_format($oObject->iPercent, 2, ",", ".");

			//Filter
			$cFilter 	= false;
			if (isset($this->aOptions['filter'])) { $cFilter = " filter='url(" . $this->aOptions['filter'] . ")' "; }

			if (isset($this->aOptions["bAnimated"])) {
				$cOutput .= "<rect x='" . $iX . "' y='" . $iBarY . "' width='15' height='0' fill='" . $cColor . "'" . $cFilter . " transform='rotate(-180 100 100)'>";
				$cOutput .= "<animate attributeName='height' attributeType='XML' begin='0s' dur='1s' fill='freeze' from='0' to='" . $iHeight . "' />";
				$cOutput .= "</rect>\n";

				//description of the bar
				$cOutput .= "<text x='" . $iX . "' y='" . $iTextY . "' style='font-size: 12px; text-anchor: right;' fill='" . $cFontColor . "'>" . $oObject->cDesc . "</text>";

				$cOutput .= "<text x='" . $iDescX . "' y='" . $iTextY . "' style='font-size: 12px; text-anchor: right; visibility: hidden;' fill='" . $cFontColor . "'> " . $oObject->iValue . " [" . $iPercent . "%]";
				$cOutput .= "<animate attributeName='visibility' begin='1s' fill='freeze' from='hidden' to='visible' calcMode='discrete' />";
				$cOutput .= "<set attributeName='visibility' from='hidden' to='visible' begin='graph" . $iY . ".mouseover' end='graph" . $iY . ".mouseout' />";
				$cOutput .= "</text>";
			} else {
				$cOutput .= "<rect x='" . $iX . "' y='" . $iBarY . "' width='15' height='" . $iHeight . "' fill='" . $cColor . "'" . $cFilter . " transform='rotate(-180 100 100)' />";
				$cOutput .= "<text x='" . $iX . "' y='" . $iTextY . "' style='font-size: 12px; text-anchor: right;' fill='" . $cFontColor . "'>" . $oObject->cDesc . "</text>";
			}

			$iX = $iX + 27;
			$iCount++;
		}

		return $cOutput;
	}
}

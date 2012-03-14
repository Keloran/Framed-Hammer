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
	public $cTitle		= false;
	public $aOptions	= false;
	public $aData		= false;

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

		$aData 		= $this->aData;

		$cOutput  = "<defs>\n";
		$cOutput .= "<filter id='flt' filterUnits='userSpaceOnUse' x='0' y='0' width='1200' height='1200'>\n";
		$cOutput .= "<feGaussianBlur in='SourceAlpha' stdDeviation='0.5' result='blur' />\n";

		$cOutput .= "<feSpecularLighting in='blur' surfaceScale='5' specularConstant='0.5' specularExponent='10' result='specOut' style='lighting-color: #FFF'>\n";
		$cOutput .= "<fePointLight x='-5000' y='-5000' z='5000' />\n";
		$cOutput .= "</feSpecularLighting>\n";

		$cOutput .= "<feComposite in='specOut' in2='SourceAlpha' operator='in' result='specOut2' />\n";
		$cOutput .= "<feComposite in='SourceGraphic' in2='SpecOut2' operator='arithmetic' k1='0' k2='1' k3='1' k4='0' />\n";
		$cOutput .= "</filter>\n";
		$cOutput .= "</defs>\n";


		$iBarX	= ($iX + 100);
		$iDescX	= ($iX + 200);

		//Filter
		$cFilter 	= false;
		if (isset($this->aOptions['filter'])) { $cFilter = " filter='url(" . $this->aOptions['filter'] . ")' "; }

		//set the font color
		$cFontColor		= $this->aOptions['fontcolor'];

		//go through the data
		foreach ($aData as $oObject) {
			$cColor 	= $oObject->cColor;
			$iTextY		= ($iY + 11);
			$iWidth		= $oObject->iPercent;
			$iPercent	= number_format($oObject->iPercent, 2, ",", ".");

			if (isset($this->aOptions["bAnimated"])) {
				//the bar itself
				$cOutput .= "<rect x='" . $iBarX . "' y='" . $iY . "' width='0' height='15' fill='" . $cColor . "'" . $cFilter . " id='graph" . $iY . "'>\n";
				$cOutput .= "<animate attributeName='width' attributeType='XML' begin='0s' dur='1s' fill='freeze' from='0' to='" . $iWidth . "' />\n";
				$cOutput .= "</rect>\n";

				//description of the bar
				$cOutput .= "<text x='" . $iX . "' y='" . $iTextY . "' style='font-size: 12px; text-anchor: right;' fill='" . $cFontColor . "'>" . $oObject->cDesc . "</text>\n";

				$cOutput .= "<text x='" . $iDescX . "' y='" . $iTextY . "' style='font-size: 12px; text-anchor: right; visibility: hidden;' fill='" . $cFontColor . "'> " . $oObject->iValue . " [" . $iPercent . "%]\n";
				$cOutput .= "<animate attributeName='visibility' begin='1s' fill='freeze' from='hidden' to='visible' calcMode='discrete' />\n";
				$cOutput .= "<set attributeName='visibility' from='hidden' to='visible' begin='graph" . $iY . ".mouseover' end='graph" . $iY . ".mouseout' />\n";
				$cOutput .= "</text>\n";
			} else {
				$cOutput .= "<rect x='" . $iBarX . "' y='" . $iY . "' width='" . $iWidth . "' height='15' fill='" . $cColor . "'" . $cFilter . "/>\n";
				$cOutput .= "<text x='" . $iX . "' y='" . $iTextY . "' style='font-size: 12px; text-anchor: right;' fill='" . $cFontColor . "'>" . $oObject->cDesc . "</text>\n";
				$cOutput .= "<text x='" . $iDescX . "' y='" . $iTextY . "' style='font-size: 12px; text-anchor: right;' fill='" . $cFontColor . "'>" . $oObject->iValue . " [" . $iPercent . "%]</text>\n";
			}

			$iY = ($iY + 27);
		}

		return $cOutput;
	}
}
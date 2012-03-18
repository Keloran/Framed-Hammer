<?php
/**
 * Charts_Pie
 *
 * @package
 * @author Max
 * @copyright Copyright (c) 2008
 * @version $Id$
 * @access public
 */
class Charts_SVG_Pie {
	var $cTitle     = false;
	var $aOptions	= false;
	var $aData		= false;

	/**
	 * Charts_Pie::__construct()
	 *
	 */
	function __construct() {
	}

	/**
	 * Charts_Pie::renderChart()
	 *
	 * @return
	 */
	function renderChart(){
		$iCX		= isset($aOptions["iCX"])	? $aOptions["iCX"]	: 150;
		$iCY		= isset($aOptions["iCY"])	? $aOptions["iCY"]	: 150;
		$iR			= isset($aOtions["iR"])		? $aOptions["iR"]	: 150;
		$iX1		= $iCX;
		$iY1		= $iCY - $iR;
		$iAlpha		= 0;
		$cOutput	= "";
		$iCount		= 0;

		$aData	= $this->aData;

		//set the font color
		$cFontColor		= $this->aOptions['fontcolor'];

		foreach ($aData as $oObject){
			$iAlpha = ($iAlpha + (($oObject->iPercent / 100) * (2 * M_PI)));

			$iX2 = $iCX + ($iR * sin($iAlpha));
			$iY2 = $iCY - ($iR * cos($iAlpha));

			$iOver180	= $oObject->iPercent > 50 ? "1" : "0";
			$cColor 	= $oObject->cColor;

			$iPercent	= number_format($oObject->iPercent, 2, ",", ".");

			$cOutput .= "<path d='M" . $iCX . "," . $iCY;
			$cOutput .= " L" . $iX1 . "," . $iY1;
			$cOutput .= " A" . $iR . "," . $iR;
			$cOutput .= " 0 " . $iOver180 . ",1 ";
			$cOutput .= $iX2 . "," . $iY2 . " Z' ";
			$cOutput .= "id='graph" . $iX2 . "' ";
			$cOutput .= "fill='" . $cColor . "' opacity='0.6' />";

			//description of the bar
			$cOutput .= "<text x='0' y='0' style='font-size: 12px; text-anchor: right; visibility: hidden;' fill='" . $cFontColor . "'> " . $oObject->iValue . " [" . $iPercent . "%]";
			$cOutput .= "<set attributeName='visibility' from='hidden' to='visible' begin='graph" . $iX2 . ".mouseover' end='graph" . $iX2 . ".mouseout' />";
			$cOutput .= "<set attributeName='x' from='0' to='" . $iCX . "' begin='graph" . $iX2 . ".mouseover' end='graph" . $iX2 . ".mouseout' />";
			$cOutput .= "</text>";

			$iX1 = $iX2;
			$iY1 = $iY2;
			$iCount++;
		}

		if (isset($this->aOptions["legend"])) {
			$iX = $iCX + $iR * 1.2;
			$iY = $iCY - $iR;

			$this->aOptions["bLegend"]["iX"] = $iX;
			$this->aOptions["bLegend"]["iY"] = $iY;
		}

		return $cOutput;
	}
}
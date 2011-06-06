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
	var $aColors	= false;

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
		$aColors = $this->aColors;

		foreach ($aData as $oObject){
			$iAlpha = $iAlpha + ($oObject->iPercent / 100 * (2 * M_PI));

			$iX2 = $iCX + ($iR * sin($iAlpha));
			$iY2 = $iCY - ($iR * cos($iAlpha));

			$iOver180 = $oObject->iPercent > 50 ? "1" : "0";

			$cColor = $this->getColor($iCount);

			$cOutput .= "<path d='M" . $iCX . "," . $iCY;
			$cOutput .= " L" . $iX1 . "," . $iY1;
			$cOutput .= " A" . $iR . "," . $iR;
			$cOutput .= " 0 " . $iOver180 . ",1 ";
			$cOutput .= $iX2 . "," . $iY2 . " Z' ";
			$cOutput .= "fill='" . $cColor . "' opacity='0.6' />\n\n";

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

	/**
	 * Charts_Pie::getColor()
	 *
	 * @param int $iID
	 * @return
	 */
	function getColor($iID) {
		$cColor = $this->aColors[$iID % count($this->aColors)];

		return $cColor;
	}
}
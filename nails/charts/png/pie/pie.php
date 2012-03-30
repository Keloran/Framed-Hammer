<?php
/**
 * Charts_PNG_Pie
 *
 * @package Charts
 * @author Keloran
 * @copyright Copyright (c) 2008
 * @version $Id$
 * @access public
 */
class Charts_PNG_Pie {
	var $imImage	= false;
	var $iWidth		= 450;
	var $iHeight	= 450;
	var $aData		= false;
	var $aColors	= false;
	var $iShadow	= 10;

	/**
	 * Charts_PNG_Pie::__construct()
	 *
	 */
	function __construct() {
		$this->imImage = imagecreatetruecolor($this->iWidth, $this->iHeight);
		imagecolorallocate($this->imImage, 255, 255, 255);
	}

	/**
	 * Charts_PNG_Pie::renderChart()
	 *
	 * @return string
	 */
	function renderChart() {
		$aData	= $this->aData;
		$i		= 0;
		$iStop	= 0;

		//Set the background to white
		$imBack	= imagecolorallocate($this->imImage, 255, 255, 255);
		ImageFilledRectangle($this->imImage, 0, 0, 450, 450, $imBack);

		//Get black
		$imBlack    = imagecolorallocate($this->imImage, 0, 0, 0);

		//Get the total
		$iTotal = 0;
		foreach ($aData as $oObject) { $iTotal += $oObject->iValue; }

		//Draw the circle
		foreach ($aData as $oObject) {
			//color
			$cColor			= $oObject->cColor;
			$iColR			= $oObject->cColorRed;
			$iColG 			= $oObject->cColorGreen;
			$iColB 			= $oObject->cColorBlue;
			$imPartColor	= ImageColorAllocate($this->imImage, $iColR, $iColG, $iColB);

			//$iDegree		= (($oObject->iValue / $iTotal) * 360);
			$iDegree		= ($oObject->iPercent * 360);
			imagefilledarc($this->imImage, 225, 225, 450, 450, $iStop, ($iStop + $iDegree), $imPartColor, IMG_ARC_PIE);

			$iStop 		= ($iStop + $iDegree);
			$iStopX		= round(225 + (225 * cos($iStop * (M_PI / 180))));
			$iStopY		= round(225 + (225 * sin($iStop * (M_PI / 180))));
			$i++;
		}

		imageantialias($this->imImage, true);
		return $this->imImage;
	}
}

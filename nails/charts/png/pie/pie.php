<?php

/**
 * Charts_PNG_Pie
 *
 * @package
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
	 * Charts_PNG_Pie::getColor()
	 *
	 * @param int $iID
	 * @return string
	 */
	function getColor($iID) {
		$cColor = $this->aColors[$iID % count($this->aColors)];

		return $cColor;
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

		//set total to 1 if 0, avoid DbZ
		if ($iTotal === 0) { $iTotal = 1; }

		//Draw the circle
		$i = 0;
		foreach ($aData as $oObject) {
			//color
			$cColor			= $this->getColor($i);
			$iColR			= hexdec(substr($cColor, 1, 2));
			$iColG			= hexdec(substr($cColor, 3, 2));
			$iColB			= hexdec(substr($cColor, 5, 2));
			$imPartColor	= ImageColorAllocate($this->imImage, $iColR, $iColG, $iColB);

			$iDegree		= ($oObject->iValue / $iTotal) * 360;
			imagefilledarc($this->imImage, 225, 225, 450, 450, $iStop, ($iStop + $iDegree), $imPartColor, IMG_ARC_PIE);

			$iStop 		= $iStop + $iDegree;
			$iStopX		= round(225 + (225 * cos($iStop * (M_PI / 180))));
			$iStopY		= round(225 + (225 * sin($iStop * (M_PI / 180))));
			$i++;
		}

		imageantialias($this->imImage, true);
		return $this->imImage;
	}
}

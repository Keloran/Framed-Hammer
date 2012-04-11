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
		$aData		= $this->aData;
		$i			= 0;
		$iStart		= 0;
		$iEnd		= 0;
		$iEndCheck	= 0;
		$iEndAngle	= 0;

		//Set the background to white
		$imBack	= imagecolorallocate($this->imImage, 255, 255, 255);
		ImageFilledRectangle($this->imImage, 0, 0, 450, 450, $imBack);

		//Get black
		$imBlack    = imagecolorallocate($this->imImage, 0, 0, 0);

		//Get the total
		$iTotal = 0;
		foreach ($aData as $oObject) { $iTotal += $oObject->iValue; }

		$aDebug	= array();

		//Draw the circle
		foreach ($aData as $oObject) {
			//color
			$cColor			= $oObject->cColor;
			$iColR			= $oObject->cColorRed;
			$iColG 			= $oObject->cColorGreen;
			$iColB 			= $oObject->cColorBlue;
			$imPartColor	= ImageColorAllocate($this->imImage, $iColR, $iColG, $iColB);

			//maths for angle
			$iDegree		= ($oObject->iRawPercent * 360);
			$iEnd			= $iDegree;
			$iEndCheck		= ($iEnd += $iStart);
			$iEndAngle		= ($iDegree += $iStart);

			//debug before any corrections
			$aDebug[$i]['degree']	= $iDegree;
			$aDebug[$i]['start']	= $iStart;
			$aDebug[$i]['end']		= $iEndCheck;
			$aDebug[$i]['data']		= $oObject;
			$i++;

			//correct for 360/360, or 0 0
			if (($iStart == 360) && ($iEndCheck == 360)) { continue; }
			if (($iStart == 0) && ($iEndCheck == 0)) { continue; }

			//actually draw something
			imagefilledarc($this->imImage, 225, 225, 450, 450, $iStart, 360, $imPartColor, IMG_ARC_PIE);

			//start of next angle
			$iStart = $iDegree;
		}

		$oReader			= new oReader($aDebug);
		$oReader->bScreen 	= false;
		$oReader->bFile 	= true;
		$oReader->doOutput();

		imageantialias($this->imImage, true);
		return $this->imImage;
	}
}

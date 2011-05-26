<?php

class Charts_PNG_Create {
	var $aData		= false;
	var $aColors	= false;

	/**
	 * Charts_PNG_Create::__construct()
	 *
	 */
	function __construct() {
	}

	/**
	 * Charts_PNG_Create::getColor()
	 *
	 * @param int $iID
	 * @return string
	 */
	function getColor($iID) {
		$cColor = $this->aColors[$iID % count($this->aColors)];

		return $cColor;
	}

	/**
	 * Charts_PNG_Create::makeLegend()
	 *
	 * @param string $cContent
	 * @return string
	 */
	function makeLegend() {
		$aData	= $this->aData;

		//load the font
<<<<<<< HEAD
=======
		#$iFont	= imageloadfont(dirname(__FILE__) . "/courier8.gdf");
>>>>>>> c0c66965fad63221c98f14c695de9a95e55161f3
		$iFont	= 2;

		//get the legend width
		$iFontWidth = imagefontwidth($iFont);
		$iMaxWidth	= 0;
		foreach ($aData as $oObject) {
			$iLength 	= strlen($oObject->cDesc . " - " . $oObject->cExtra . $oObject->iValue . " (" . $oObject->iPercent . "%)") + 5;
			$iWidth		= $iLength * $iFontWidth;

			$iMaxWidth = max($iWidth, $iMaxWidth);
		}

		// Legend Box
		$i = 0;
		$iLegWidth		= $iMaxWidth;
		$iLegHeight		= count($aData) * (ImageFontHeight($iFont) + 2) + 2;

		$imLegendImage	= ImageCreateTrueColor($iLegWidth, $iLegHeight);
		$imBack			= imagecolorallocate($imLegendImage, 255, 255, 255);
		ImageFill($imLegendImage, 0, 0, $imBack);

		$imBorderColor	= ImageColorAllocate($imLegendImage, 155, 155, 155);
		$imBoxColor		= ImageColorAllocate($imLegendImage, 255, 255, 255);
		$imTextColor	= ImageColorAllocate($imLegendImage, 000, 000, 000);

		ImageFilledRectangle($imLegendImage, 0, 0, $iLegWidth, $iLegHeight, $imBoxColor);
		ImageRectangle($imLegendImage, 0, 0, $iLegWidth - 1, $iLegHeight - 1, $imBorderColor);

		$iBoxWidth  = ImageFontWidth($iFont);
		$iBoxHeight = ImageFontHeight($iFont) - 5;
		$yOffset	= 2;

		foreach($aData as $oObject) {
			$iPiePart		= $oObject->cExtra . round($oObject->iValue, 2);
			$iPiePart100	= $oObject->iPercent;

			$cColor		= $this->getColor($i);
			$iColR		= hexdec(substr($cColor, 1, 2));
			$iColG 		= hexdec(substr($cColor, 3, 2));
			$iColB 		= hexdec(substr($cColor, 5, 2));
			$imPartColor= ImageColorAllocate($imLegendImage, $iColR, $iColG, $iColB);

			ImageFilledRectangle($imLegendImage, 5, $yOffset + 2, 5 + $iBoxWidth, $yOffset + $iBoxHeight + 2, $imPartColor);
			ImageRectangle($imLegendImage, 5, $yOffset + 2, 5 + $iBoxWidth, $yOffset + $iBoxHeight + 2, $imBorderColor);

			$cText = $oObject->cDesc . " - " . $iPiePart . " (" . $iPiePart100 . "%)";
			ImageString($imLegendImage, $iFont, '20', $yOffset, $cText, $imTextColor);
			$yOffset = $yOffset + 15;
			$i++;
		}

		return $imLegendImage;
	}

	/**
	 * Charts_PNG_Create::createOutput()
	 *
	 * @param string $cImage
	 * @return
	 */
	function createOutput($cImage) {
		return $cImage;
	}
}

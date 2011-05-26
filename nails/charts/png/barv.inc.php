<?php
/**
 * Charts_PNG_BarV
 *
 * @package
 * @author Max
 * @copyright Copyright (c) 2008
 * @version $Id$
 * @access public
 */
class Charts_PNG_BarV {
	var $imImage	= false;
	var $iWidth		= 450;
	var $iHeight	= 450;
	var $aData		= false;
	var $aColors	= false;

	/**
	 * Charts_PNG_BarV::__construct()
	 *
	 */
	function __construct() {
		$this->imImage = imagecreatetruecolor($this->iWidth, $this->iHeight);
		imagecolorallocate($this->imImage, 255, 255, 255);
	}

	/**
	 * Charts_PNG_BarV::getColor()
	 *
	 * @param int $iID
	 * @return string
	 */
	function getColor($iID) {
		$cColor = $this->aColors[$iID % count($this->aColors)];

		return $cColor;
	}

	/**
	 * Charts_PNG_BarV::renderChart()
	 *
	 * @return string
	 */
	function renderChart() {
		$i		= 0;
		$aData	= $this->aData;
		$iY		= 0;
		$iMaxV	= 0;

		foreach($aData as $oObject) {
			$iMaxV = max(($oObject->iPercent * 3), $iMaxV);
		}

		$imBack	= imagecolorallocate($this->imImage, 255, 255, 255);
		ImageFilledRectangle($this->imImage, 0, 0, 450, 450, $imBack);
		$iWidth	= 450 / count($aData);

		foreach ($aData as $oObject) {
			$cColor			= $this->getColor($i);
			$iColR			= hexdec(substr($cColor, 1, 2));
			$iColG			= hexdec(substr($cColor, 3, 2));
			$iColB			= hexdec(substr($cColor, 5, 2));
			$imPartColor	= ImageColorAllocate($this->imImage, $iColR, $iColG, $iColB);

			$iX1	= $i * $iWidth;
			$iY1	= 450 - ($oObject->iPercent * 5);
			$iX2	= (($i + 1) * $iWidth) - 5;
			$iY2	= 450;

			imagefilledrectangle($this->imImage, $iX1, $iY1, $iX2, $iY2, $imPartColor);
			$i++;
		}

		return $this->imImage;
	}
}

<?php
/**
 * Charts_PNG_BarH
 *
 * @package Charts
 * @author Keloran
 * @copyright Copyright (c) 2008
 * @version $Id$
 * @access public
 */
class Charts_PNG_BarH {
	var $imImage	= false;
	var $iWidth		= 450;
	var $iHeight	= 450;
	var $aData		= false;
	var $aColors	= false;

	/**
	 * Charts_PNG_BarH::__construct()
	 *
	 */
	function __construct() {
		$this->imImage = imagecreatetruecolor($this->iWidth, $this->iHeight);
		imagecolorallocate($this->imImage, 255, 255, 255);
	}

	/**
	 * Charts_PNG_BarH::renderChart()
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
		$iHeight	= 450 / count($aData);

		foreach ($aData as $oObject) {
			#$cColor			= $this->getColor($i);
			$cColor			= $oObject->cColor;
			$iColR			= hexdec(substr($cColor, 1, 2));
			$iColG			= hexdec(substr($cColor, 3, 2));
			$iColB			= hexdec(substr($cColor, 5, 2));
			$imPartColor	= ImageColorAllocate($this->imImage, $iColR, $iColG, $iColB);

			$iY1	= $i * $iHeight;
			$iX2	= $oObject->iPercent * 5;
			$iY2	= (($i + 1) * $iHeight) - 5;

			imagefilledrectangle($this->imImage, 0, $iY1, $iX2, $iY2, $imPartColor);

			$iY = $iY + 25;
			$i++;
		}

		return $this->imImage;
	}
}

<?php
/**
 *
 * Charts_SVG_Create
 * @package
 * @author Keloran
 * @copyright Copyright (c) 2008
 * @version $Id$
 * @access public
 */
class Charts_SVG_Create {
	public $aOptions;

	/**
	 *
	 * Constructor
	 * @access protected
	 */
	function __construct() {
	}

	/**
	 * Charts_SVG_Create::makeLegend()
	 *
	 * @param string $cContent
	 * @param string $cFontColor
	 * @return
	 */
	function makeLegend($cContent, $cFontColor = 'black') {
		$iX 		= isset($this->aOptions["iX"]) 		? $this->aOptions["iX"] + 305 	: 305;
		$iY 		= isset($this->aOptions["iY"]) 		? $this->aOptions["iY"] 		: 0;
		$iWidth 	= isset($this->aOptions["iWidth"]) 	? $this->aOptions["iWidth"] 	: 150;
		$iHeight 	= isset($this->aOptions["iHeight"]) 	? $this->aOptions["iHeight"] 	: 200;
		$cFontColor	= isset($this->aOptions['fontcolor'])	? $this->aOptions['fontcolor']	: 'black';

		$cSVG = "<rect x='" . $iX . "' y='" . $iY . "' width='" . $iWidth . "' height='" . $iHeight . "' fill='none' stroke='black' />\n";

		$iY 	= ($iY + 5);
		$iX 	= ($iX + 5);
		$iCount = 0;
		$aData 	= $this->aData;
		$jY		= ($iY + 5);
		$jX		= ($iX + 5);
		$jCount	= count($this->aData);

		foreach ($aData as $oObject) {
			$iTextY = ($iY + 15);
			$iTextX = ($iX + 20);

			//set to side
			if ($jCount >= 15) {
				if (($iCount % 20 == 0)) {
					$jX	= ($jX + 210);

					$iTextY	= ($jY + 15);
					$iTextX = $jX;

					$jY += 20;

					$iX	= $jX;
					$iY	= $jY;
				}
			}

			$cColor = $oObject->cColor;
			$cSVG .= "<rect x='" . $iX . "' y='" . $iY . "' width='15' height='15' fill='" . $cColor . "' stroke='black' />\n";
			$cSVG .= "<text x='" . $iTextX . "' y='" . $iTextY . "' font-size='12' fill='" . $cFontColor . "'>" . $oObject->cDesc . " - " . $oObject->iPercentLegend . "% (" . $oObject->iValue . ")</text>\n";

			$iY += 20;
			$iCount++;
		}

		$cReturn = $cContent . $cSVG;

		return $cReturn;
	}

	/**
	 * Charts_SVG_Create::createOutput()
	 *
	 * @param string $cSVG
	 * @return
	 */
	function createOutput($cSVG) {
		$cData  = "<?xml version='1.0' encoding='UTF-8' ?>\n";
		$cData .= "<!DOCTYPE svg PUBLIC \"-//W3C//DTD SVG 1.1//EN\" \"http://www.w3.org/Graphics/SVG/1.1/DTD/svg11.dtd\">\n";
		$cData .= "<svg xmlns='http://www.w3.org/2000/svg' version='1.1'>";
		$cData .= $cSVG;
		$cData .= "</svg>";

		return $cData;
	}
}

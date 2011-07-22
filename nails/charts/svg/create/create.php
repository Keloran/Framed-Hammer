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
	var $aColors	= false;

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
		$iX = isset($this->aOptions["iX"]) 				? $this->aOptions["iX"] + 305 	: 305;
		$iY = isset($this->aOptions["iY"]) 				? $this->aOptions["iY"] 		: 0;
		$iWidth = isset($this->aOptions["iWidth"]) 		? $this->aOptions["iWidth"] 	: 150;
		$iHeight = isset($this->aOptions["iHeight"]) 	? $this->aOptions["iHeight"] 	: 200;

		$cSVG = "<rect x='" . $iX . "' y='" . $iY . "' width='" . $iWidth . "' height='" . $iHeight . "' fill='none' stroke='black' />\n";

		$iY = $iY + 5;
		$iX = $iX + 5;
		$iCount = 0;
		$aColors = $this->aColors;
		$aData = $this->aData;

		foreach ($aData as $oObject) {
			$iTextY = $iY + 15;
			$iTextX = $iX + 20;
			$cColor = $aColors[$iCount % count($aColors)];
			$cSVG .= "<rect x='" . $iX . "' y='" . $iY . "' width='15' height='15' fill='" . $cColor . "' stroke='black' />\n";
			$cSVG .= "<text x='" . $iTextX . "' y='" . $iTextY . "' font-size='12' fill='" . $cFontColor . "'>" . $oObject->cDesc . " - " . $oObject->iPercent . "% (" . $oObject->iValue . ")</text>\n";
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
		$cData = "<?xml version='1.0' encoding='iso-8859-1' ?>\n";
		$cData .= "<svg xmlns='http://www.w3.org/2000/svg'>\n\n";
		$cData .= $cSVG;
		$cData .= "</svg>\n";

		return $cData;
	}
}

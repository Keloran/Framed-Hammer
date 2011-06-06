<?php
/**
 * Charts_SWF_BarV
 *
 * @package
 * @author Max
 * @copyright Copyright (c) 2008
 * @version $Id$
 * @access public
 */
class Charts_SWF_BarH {
	var $aData		= false;
	var $aColors	= false;

	/**
	 * Charts_SWF_BarV::__construct()
	 *
	 */
	function __construct() {
	}

	/**
	 * Charts_SWF_BarV::getColor()
	 *
	 * @param mixed $iID
	 * @return
	 */
	function getColor($iID) {
		$cColor = $this->aColors[$iID % count($this->aColors)];

		return $cColor;
	}

	/**
	 * Charts_SWF_BarV::renderChart()
	 *
	 * @return
	 */
	function renderChart() {
		$i			= 1;
		$aData		= $this->aData;
		$iMax		= count($aData);

		//Get Max Value
		$iMax	= 0;
		foreach ($aData as $oObject) {
			$iMax = max($oObject->iValue, $iMax);
		}

		$cReturn = "{";

		//Title
		$cReturn .= "\"title\": {";
		$cReturn .= "\"text\": \"Horizontal\"";
		$cReturn .= "},";

		$cReturn .= "\"elements\": [";
		$cReturn .= "{";
		$cReturn .= "\"type\": \"hbar\", ";
		$cReturn .= "\"colour\": \"#9933CC\", ";
		$cReturn .= "\"animate\": true, ";
		$cReturn .= "\"tip\": \"#val# of #total#\", ";

		$cReturn .= "\"values\": [";

		//Values
		$cValues = "";
		foreach ($aData as $oObject) {
			$cValues .= "{";
			$cValues .= "\"left\": 0,";
			$cValues .= "\"right\": \"" . $oObject->iValue . "\"";

			if ($i == $iMax) {
				$cValues .= "}";
			} else {
				$cValues .= "},";
			}

			$i++;
		}

		$iValue		= strlen($cValues);
		$cLastChar	= substr($cValues, ($iValue - 1));
		if ($cLastChar == ",") {
			$cValues = substr($cValues, 0, ($iValue - 1));
		}

		$cReturn .= $cValues;

		$cReturn .= "]";
		$cReturn .= "}";
		$cReturn .= "],";

		$cReturn .= "\"y_axis\": {";
		$cReturn .= "\"offset\": 1,";
		$cReturn .= "\"labels\": [";

		//Labels
		$i = 1;
		$cLabels = "";
		foreach ($aData as $oObject) {
			$cLabels .= "\"" . $oObject->cDesc . "\"";

			if ($i == $iMax) {
				$cLabels .= "";
			} else {
				$cLabels .= ",";
			}

			$i++;
		}

		$iLabels	= strlen($cLabels);
		$cLastChar	= substr($cLabels, ($iLabels - 1));
		if ($cLastChar == ",") {
			$cLabels = substr($cLabels, 0, ($iLabels - 1));
		}

		$cReturn .= $cLabels;

		$cReturn .= "]";
		$cReturn .= "},";

		$cReturn .= "\"x_axis\": {";
		$cReturn .= "\"offset\": false";

		/*
		   $cReturn .= "\"offset\": false,";
		   $cReturn .= "\"labels\": {";
		   $cReturn .= "\"labels\": [";

		   $cX = "";
		   for ($i = 0; $i < $iMax; $i++) {
		   if ($i % 10 == 0) {
		   $cX .= $i . ", ";
		   }
		   }

		   $iX			= strlen($cX);
		   $cLastChar	= substr($cX, ($iX - 1));
		   if ($cLastChar == ",") {
		   $cX = substr($cX, 0, ($iX - 1));
		   }

		   $cReturn .= $cX;
		   $cReturn .= "]";
		*/

		$cReturn .= "}";
		$cReturn .= "}";
		$cReturn .= "}";

		return $cReturn;
	}
}
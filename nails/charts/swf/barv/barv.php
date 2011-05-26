<<<<<<< HEAD
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
class Charts_SWF_BarV {
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

		$cReturn = "{ ";

		//Title
		$cReturn .= "\"title\": { ";
		$cReturn .= "\"text\": \"Vertical\" ";
		$cReturn .= "}, ";

		$cReturn .= "\"elements\": [ ";
		$cReturn .= "{ ";
		$cReturn .= "\"type\": \"bar_3d\", ";
		$cReturn .= "\"animate\": true, ";
		$cReturn .= "\"tip\": \"#val# of #total#\", ";

		//Values
		$cReturn .= "\"values\": [ ";
		$cValues = "";
		foreach ($aData as $oObject) {
			$cValues .= $oObject->iValue;

			if ($i == $iMax) {
				$cValues .= " ";
			} else {
				$cValues .= ", ";
			}

			$i++;
		}

		$iValues	= strlen($cValues);
		$cLastChar	= substr($cValues, ($iValues - 1));
		if ($cLastChar == ",") {
			$cValues = substr($cValues, 0, ($iValues - 1));
		}

		$cReturn .= $cValues;

		$cReturn .= "], ";
		$cReturn .= "\"colour\": \"#D54C78\" ";
		$cReturn .= "} ";
		$cReturn .= "], ";
		$cReturn .= "\"x_axis\": { ";
		$cReturn .= "\"3d\": 5, ";
		$cReturn .= "\"colour\": \"#909090\", ";

		//Labels
		$i = 1;
		$cReturn .= "\"labels\": [ ";
		$cLabels = "";
		foreach ($aData as $oObject) {
			$cLabels .= "\"" . $oObject->cDesc . "\"";

			if ($i == $iMax) {
				$cLabels .= " ";
			} else {
				$cLabels .= ", ";
			}

			$i++;
		}

		$iLabels	= strlen($cLabels);
		$cLastChar	= substr($cLabels, ($iLabels - 1));
		if ($cLastChar == ",") {
			$cLabels = substr($cLabels, 0, ($iLabels - 1));
		}

		$cReturn .= $cLabels;

		$cReturn .= "] ";
		$cReturn .= "} ";
		$cReturn .= "} ";

		return $cReturn;
	}
=======
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
class Charts_SWF_BarV {
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

		$cReturn = "{ ";

		//Title
		$cReturn .= "\"title\": { ";
		$cReturn .= "\"text\": \"Vertical\" ";
		$cReturn .= "}, ";

		$cReturn .= "\"elements\": [ ";
		$cReturn .= "{ ";
		$cReturn .= "\"type\": \"bar_3d\", ";
		$cReturn .= "\"animate\": true, ";
		$cReturn .= "\"tip\": \"#val# of #total#\", ";

		//Values
		$cReturn .= "\"values\": [ ";
		$cValues = "";
		foreach ($aData as $oObject) {
			$cValues .= $oObject->iValue;

			if ($i == $iMax) {
				$cValues .= " ";
			} else {
				$cValues .= ", ";
			}

			$i++;
		}

		$iValues	= strlen($cValues);
		$cLastChar	= substr($cValues, ($iValues - 1));
		if ($cLastChar == ",") {
			$cValues = substr($cValues, 0, ($iValues - 1));
		}

		$cReturn .= $cValues;

		$cReturn .= "], ";
		$cReturn .= "\"colour\": \"#D54C78\" ";
		$cReturn .= "} ";
		$cReturn .= "], ";
		$cReturn .= "\"x_axis\": { ";
		$cReturn .= "\"3d\": 5, ";
		$cReturn .= "\"colour\": \"#909090\", ";

		//Labels
		$i = 1;
		$cReturn .= "\"labels\": [ ";
		$cLabels = "";
		foreach ($aData as $oObject) {
			$cLabels .= "\"" . $oObject->cDesc . "\"";

			if ($i == $iMax) {
				$cLabels .= " ";
			} else {
				$cLabels .= ", ";
			}

			$i++;
		}

		$iLabels	= strlen($cLabels);
		$cLastChar	= substr($cLabels, ($iLabels - 1));
		if ($cLastChar == ",") {
			$cLabels = substr($cLabels, 0, ($iLabels - 1));
		}

		$cReturn .= $cLabels;

		$cReturn .= "] ";
		$cReturn .= "} ";
		$cReturn .= "} ";

		return $cReturn;
	}
>>>>>>> c0c66965fad63221c98f14c695de9a95e55161f3
}
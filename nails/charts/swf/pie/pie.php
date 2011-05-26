<<<<<<< HEAD
<?php
class Charts_SWF_Pie {
	var $aData;
	var $aColors;

	/**
	 * Constructor
	 * @access protected
	 */
	function __construct() {
	}

	/**
	 * Charts_SWF_Pie::getColor()
	 *
	 * @param int $iID
	 * @return
	 */
	function getColor($iID) {
		$cColor = $this->aColors[$iID % count($this->aColors)];

		return $cColor;
	}

	/**
	 * Charts_SWF_Pie::renderChart()
	 *
	 * @return
	 */
	function renderChart() {
		$i		= 1;
		$aData	= $this->aData;
		$iMax	= count($aData);

		$cReturn = "{ ";

		//Title
		$cReturn .= "\"title\": {";
		$cReturn .= "\"text\": \"Pie\"";
		$cReturn .= " }, ";

		$cReturn .= "\"elements\": [ ";
		$cReturn .= "{ ";
		$cReturn .= "\"type\": \"pie\",";

		//Colors
		$cReturn .= "\"colours\": [ ";
		$cColors = "";
		foreach ($aData as $oObject) {
			$cColor = $this->getColor($i);
			$cColors .= "\"" . $cColor . "\"";

			if ($i == $iMax) {
				$cColors .= "";
			} else {
				$cColors .= ",";
			}

			$i++;
		}

		$iColors	= strlen($cColors);
		$cLastChar	= substr($cColors, ($iColors - 1));
		if ($cLastChar == ",") {
			$cColors = substr($cColors, 0, ($iColors - 1));
		}

		$cReturn .= $cColors;

		$cReturn .= " ], ";

		$cReturn .= "\"alpha\": 0.6, ";
		$cReturn .= "\"border\": 2, ";
		$cReturn .= "\"start-angle\": 35, ";
		$cReturn .= "\"animate\": true, ";
		$cReturn .= "\"tip\": \"#val# of #total# <br>#percent# of 100%\", ";

		//Values
		$i = 1;
		$cReturn .= "\"values\": [ ";
		$cValues = "";
		foreach ($aData as $oObject) {
			$cValues .= "{ \"value\": " . $oObject->iValue . ", ";
			$cValues .= "\"text\": \"" . $oObject->cDesc . "\" }";

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

		$cReturn .= "] ";
		$cReturn .= "} ";
		$cReturn .= "], ";
		$cReturn .= "\"x_axis\": null ";
		$cReturn .= "}";

		return $cReturn;
	}
}
=======
<?php
class Charts_SWF_Pie {
	var $aData;
	var $aColors;

	/**
	 * Constructor
	 * @access protected
	 */
	function __construct() {
	}

	/**
	 * Charts_SWF_Pie::getColor()
	 *
	 * @param int $iID
	 * @return
	 */
	function getColor($iID) {
		$cColor = $this->aColors[$iID % count($this->aColors)];

		return $cColor;
	}

	/**
	 * Charts_SWF_Pie::renderChart()
	 *
	 * @return
	 */
	function renderChart() {
		$i		= 1;
		$aData	= $this->aData;
		$iMax	= count($aData);

		$cReturn = "{ ";

		//Title
		$cReturn .= "\"title\": {";
		$cReturn .= "\"text\": \"Pie\"";
		$cReturn .= " }, ";

		$cReturn .= "\"elements\": [ ";
		$cReturn .= "{ ";
		$cReturn .= "\"type\": \"pie\",";

		//Colors
		$cReturn .= "\"colours\": [ ";
		$cColors = "";
		foreach ($aData as $oObject) {
			$cColor = $this->getColor($i);
			$cColors .= "\"" . $cColor . "\"";

			if ($i == $iMax) {
				$cColors .= "";
			} else {
				$cColors .= ",";
			}

			$i++;
		}

		$iColors	= strlen($cColors);
		$cLastChar	= substr($cColors, ($iColors - 1));
		if ($cLastChar == ",") {
			$cColors = substr($cColors, 0, ($iColors - 1));
		}

		$cReturn .= $cColors;

		$cReturn .= " ], ";

		$cReturn .= "\"alpha\": 0.6, ";
		$cReturn .= "\"border\": 2, ";
		$cReturn .= "\"start-angle\": 35, ";
		$cReturn .= "\"animate\": true, ";
		$cReturn .= "\"tip\": \"#val# of #total# <br>#percent# of 100%\", ";

		//Values
		$i = 1;
		$cReturn .= "\"values\": [ ";
		$cValues = "";
		foreach ($aData as $oObject) {
			$cValues .= "{ \"value\": " . $oObject->iValue . ", ";
			$cValues .= "\"text\": \"" . $oObject->cDesc . "\" }";

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

		$cReturn .= "] ";
		$cReturn .= "} ";
		$cReturn .= "], ";
		$cReturn .= "\"x_axis\": null ";
		$cReturn .= "}";

		return $cReturn;
	}
}
>>>>>>> c0c66965fad63221c98f14c695de9a95e55161f3

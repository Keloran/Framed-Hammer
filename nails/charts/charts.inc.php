<?php
/**
 * Chart
 *
 * @package
 * @author Max
 * @copyright Copyright (c) 2008
 * @version $Id: charts.inc.php 63 2009-09-22 09:06:11Z keloran $
 * @access public
 */
class Charts {
	var $aPreData 	= false;

	var $aColors	= array(
		"#F82321",
		"#F8F121",
		"#48F821",
		"#F8AF21",
		"#21F899",
		"#218FF8",
		"#C021F8",
		"#5221F8",
		"#000000",
		"#0A3861",
		"#3e7fee",
		"#626c8c",
		"#9e32a8",
		"#e0443a",
		"#c9706e",
		"#7fbd2a",
		"#e36413",
		"#1b992e",
		"#2301c0",
		"#6a2713",
		"#acb13c",
		"#6b3a3c",
		"#da7ca9",
		"#316413",
		"#b49ea8",
	);

	var $iChartType	= 1; //1 = Pie, 2 = BarH, 3 = BarV
	var $cContent	= false;
	var $aOptions	= false;
	var $aData		= false;
	var $cType		= "PNG"; //Default type of render

	var $oCreate	= false;
	var $oType		= false;

	static $oCharts;
	private $oNails;

	/**
	 * Constructor
	 * @access protected
	 * @param string $cType
	 */
	function __construct(Nails $oNails, $cType = false) {
		$this->oNails	= $oNails;

		$cBrowser	= getBrowser();

		switch($cBrowser){
			case "ie6":
			case "ie7":
			case "ie8":
			case "gecko":
				$this->cType = "SWF";
				break;

			case "khtml":
			case "opera":
				$this->cType = "SVG";
				break;

			case "iphone":
			case "mobileie":
			default:
				$this->cType = "PNG";
				break;
		} // switch

		$oUser 		= $oNails->getUser();
		$cSetType	= $cType ? $cType : $oUser->getSetting("graphType");
		if ($cSetType) { $this->cType = $cSetType; }

		//do the upgrade
		if ($oNails->checkVersion("charts", "1.0") == false) {
			//1.0
			$oNails->addVersion("charts", "1.0");
		}
	}

	/**
	 * Charts::getInstance()
	 *
	 * @param string $cType
	 * @return object
	 */
	static function getInstance(Nails $oNails, $cType = false) {
		if (is_null(self::$oCharts)) {
			self::$oCharts = new Charts($oNails, $cType);
		}

		return self::$oCharts;
	}

	/**
	 * Chart::setData()
	 *
	 * @param array $aData
	 * @return
	 */
	function setData(){
		$aTmp = $this->aData;
		$iSum = 0;

		//since there might not be any data
		if (!$this->aPreData) { return false; }


		foreach ($this->aPreData as $oObject){
			$iSum += $oObject->iValue;
		}

		foreach ($this->aPreData as $cKey => $oObject){
			$oObject->iPercent	= round(($oObject->iValue / $iSum) * 100, 2);
			$this->aData[$cKey]	= $oObject;
		}

		return $this->aData;
	}

	/**
	 * Chart::addColor()
	 *
	 * @param string $cColor
	 * @return
	 */
	function addColor($cColor) {
		$this->aColors[] = $cColor;
	}

	/**
	 * Chart::createChart()
	 *
	 * @param array $aOptions
	 * @param int $cType
	 * @return
	 */
	function createChart($aOptions = false, $cType = false){
		if ($aOptions) { $this->aOptions = $aOptions; }

		//it shouldnt be an array
		if (is_array($this->cType)) { $this->cType = $this->cType[0]; }

		//Type
		switch($cType) {
			case "barv":
				$this->aOptions["bLegend"]	= "true";
				$cType		= "Charts_" . $this->cType . "_BarV";
				break;
			case "barh":
				$cType 		= "Charts_" . $this->cType . "_BarH";
				break;
			case "pie":
			default:
				$this->aOptions['bLegend'] = "true";
				$cType 		= "Charts_" . $this->cType . "_Pie";

				break;
		} // switch
		$cCreate		= "Charts_" . $this->cType . "_Create";
		$this->oCreate	= new $cCreate();
		$this->oType	= new $cType();

		if ($this->cType == "SVG") {
			$this->aOptions['bAnimated'] = "true";
		}

		$this->setData();
		$this->oType->aData 	= $this->aData;
		$this->oCreate->aData	= $this->aData;

		$this->oType->aOptions	= $this->aOptions;

		$this->oType->aColors	= $this->aColors;
		$this->oCreate->aColors	= $this->aColors;

		$cContent = $this->oType->renderChart();

		$this->cContent = $cContent;
		return $cContent;
	}

	/**
	 * Charts::createLegend()
	 *
	 * @desc this is for ones that require a legend be made outside of the main graph (e.g. PNG)
	 * @return
	 */
	function createLegend() {
		return $this->oCreate->makeLegend();
	}

	/**
	 * Charts::renderChart()
	 *
	 * @return
	 */
	function renderChart($bLegend = false) {
		if ($bLegend) {
			$cReturn	= $this->oCreate->makeLegend($this->cContent);
			$cReturn 	= $this->oCreate->createOutput($cReturn);
		} else {
			$cReturn 	= $this->oCreate->createOutput($this->cContent);
		}

		return $cReturn;
	}

}

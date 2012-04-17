<?php
/**
 * Chart
 *
 * @package Charts
 * @author Keloran
 * @copyright Copyright (c) 2008
 * @version $Id: charts.inc.php 63 2009-09-22 09:06:11Z keloran $
 * @access public
 */
class Charts {
	//Traits
	use Browser;

	public $aPreData 	= false;

	public $iChartType	= 1; //1 = Pie, 2 = BarH, 3 = BarV
	public $cContent	= false;
	public $aOptions	= false;
	public $aData		= false;
	public $cType		= "PNG"; //Default type of render

	public $oCreate		= false;
	public $oType		= false;

	static $oCharts;
	private $oNails;

	/**
	 * Constructor
	 * @access protected
	 * @param string $cType
	 */
	function __construct(Nails $oNails, $cType = false) {
		$this->oNails	= $oNails;

		$cBrowser	= $this->getBrowser();

		switch($cBrowser) {
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
		if (!is_object($oUser)) { $oUser = new User(); }

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
	 * Charts::addData()
	 *
	 * @param object $aData
	 * @return null
	 */
	public function addData(Charts_Data $oData) {
		$this->aPreData[]	= $oData;

		return $this;
	}

	/**
	 * Chart::setData()
	 *
	 * @param array $aData
	 * @return
	 */
	public function setData() {
		$aTmp 	= $this->aData;
		$iTotal = 0;

		//since there might not be any data
		if (!$this->aPreData) { return false; }

		//count the total values
		foreach ($this->aPreData as $oObject){ $iTotal += $oObject->iValue; }

		//set sum to 1 if acutally its 0, todo divide
		if ($iTotal == 0) { $iTotal = 100; }

		//go through the data and make it percentage
		foreach ($this->aPreData as $cKey => $oObject){
			$iValue = $oObject->iValue;
			if ($iValue == 0) {
				$iValue = 0.0001;
			} else {
				$iValue = ($iValue - 0.0001);
			}

			$oObject->iPercentLegend	= round(($iValue / $iTotal) * 100, 2);
			$oObject->iPercent			= round(($iValue / $iTotal) * 100, 3);
			$oObject->iRawPercent		= round(($iValue / $iTotal), 3);

			$this->aData[$cKey]	= $oObject;
		}

		return $this->aData;
	}

	/**
	 * Chart::createChart()
	 *
	 * @param array $aOptions
	 * @param int $cType
	 * @return
	 */
	public function createChart($aOptions = false, $cType = false){
		if ($aOptions) { $this->aOptions = $aOptions; }

		//it shouldnt be an array
		if (is_array($this->cType)) { $this->cType = $this->cType[0]; }

		//set teh font color
		if (!isset($this->aOptions['fontcolor'])) { $this->aOptions['fontcolor'] = 'black'; }

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

		if ($this->cType == "SVG") { $this->aOptions['bAnimated'] = "true"; }

		//give the data to the graphs
		$this->setData();
		$this->oType->aData 	= $this->aData;
		$this->oCreate->aData	= $this->aData;

		//send the options to the graphs
		$this->oType->aOptions		= $this->aOptions;
		$this->oCreate->aOptions	= $this->aOptions;

		//set teh content
		$cContent 		= $this->oType->renderChart();
		$this->cContent = $cContent;

		return $cContent;
	}

	/**
	 * Charts::createLegend()
	 *
	 * @desc this is for ones that require a legend be made outside of the main graph (e.g. PNG)
	 * @return
	 */
	public function createLegend($pImage = false) {
		return $this->oCreate->makeLegend($pImage);
	}

	/**
	 * Charts::renderChart()
	 *
	 * @param bool $bLegend
	 * @param string $cFontColor default black
	 * @return
	 */
	public function renderChart($bLegend = false, $cFontColor = 'black') {
		if ($bLegend) {
			if ($this->cType == "PNG") {
				$cReturn 	= $this->oCreate->createOutput($this->cContent);
				$cReturn	= $this->oCreate->makeLegend($cReturn, $cFontColor);
			} else {
				$cReturn	= $this->oCreate->makeLegend($this->cContent, $cFontColor);
				$cReturn 	= $this->oCreate->createOutput($cReturn);
			}
		} else {
			$cReturn 	= $this->oCreate->createOutput($this->cContent);
		}

		return $cReturn;
	}

}

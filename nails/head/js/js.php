<?php
/**
 * head_js
 *
 * @package
 * @author keloran
 * @copyright Copyright (c) 2011
 * @version $Id$
 * @access public
 */
class head_js {
	//Traits
	#use Browser;

	private $aData;
	private $oNails;

	//jQuery
	public $bJSFramework			= false;
	public $bJSFrameworkUI			= false;

	public $cJSFrameworkVersion			= "1.7.1";
	public $cJSFrameworkSubVersion		= "1.7";
	public $cJSFrameworkName			= "jquery";
	public $cJSFrameworkUIVersion		= "1.8.16";
	public $cJSFrameworkMobileName		= "mobile";
	public $cJSFrameworkMobileVersion 	= "1.0";

	public $bJS			= true;

	/**
	 * __construct()
	 *
	 * @param Nails $oNails
	 * @return null
	 */
	function __construct(Nails $oNails) {
		$this->oNails	= $oNails;

		$aGet		= $this->oNails->getConfig("address", "head");
		$cAddress	= $aGet['address'];

		$aGet				= $this->oNails->getConfig("js", "head");
		$aResource['js'] 	= $aGet['js'];

		$aGet	= $this->oNails->getConfig("js", "resourceDomains");
		$cJS	= $aGet['js'];
		if ($cCSS) { $aResource['resource']	= ($cJS . $cAddress); }

		$this->aResource = $aResource;
	}

	/**
	 * head_js::__set()
	 *
	 * @param string $cName
	 * @param mixed $mValue
	 * @return null
	 */
	public function __set($cName, $mValue) {
		$this->aData[$cName]	= $mValue;
	}

	/**
	 * head_js::__get()
	 *
	 * @param string $cName
	 * @return mixed
	 */
	public function __get($cName) {
		$mReturn	= false;

		if (isset($this->aData[$cName])) { $mReturn = $this->aData[$cName]; }

		return $mReturn;
	}

	/**
	 * head_js::__isset()
	 *
	 * @param string $cName
	 * @return bool
	 */
	public function __isset($cName) {
		if (isset($this->aData[$cName])) { return true; }
		return false;
	}

	/**
	 * head_js::getJS()
	 *
	 * @return
	 */
	public function getJS() {
		$aHead	= $this->aHead;
		$aJS	= false;

		$aJSReturn	= false;
		if (isset($this->aResource['js'])) { $aJSReturn = $this->aResource['js']; }
		if (is_array($aJSReturn)) {
			foreach ($aJSReturn as $iJSNum => $cJSName) { $aJS[] = $cJSName; }
		}

		//Folder could be js, or javascript
		$cFolder = is_dir(SITEPATH . "/javascript/") ? "/javascript/" : "/js/";

		//get the returned js
		$cJS 	= $this->cJS;

		//is there a resource domian set
		if (isset($this->aResource['resource'])) { $cFolder = "http://" . $this->aResource['js'] . "/"; }

		//theres quite a few of them
		if ($aJS) {
			for($i = 0; $i < count($aJS); $i++) {
				if ($this->bJSFramework) {
					$cJSPath	= $cFolder . $this->cJSFrameworkName . "." . $aJS[$i] . ".js";

					//append the jquery[framework] part to the name, since all js files should start with the framework,
					//e.g. jquery.menu.js, in loader all you put is menu if your turning on google framework loader
					if (file_exists(SITEPATH . $cJSPath)) { $cJS .= "<script type=\"text/javascript\" src=\"" . $cJSPath . "\"></script>\n"; }
				} else {
					$cJS .= "<script type=\"text/javascript\" src=\"" . $cFolder . $aJS[$i] . ".js\"></script>\n";
				}
			}
		}

		return $cJS;
	}

	/**
	 * head_js::addJS()
	 *
	 * @param string $cJS
	 * @param bool $bExternal
	 * @return null
	 */
	public function addJS($cJS, $bExternal = false) {
		if ($bExternal) {
			if ($this->cDocType == "html5") {
				$this->cJS .= "<script src=\"" . $cJS . "\"</script>\n";
			} else {
				$this->cJS .= "<script type=\"text/javascript\" src=\"" . $cJS . "\"</script>\n";
			}
		} else {
			$cFolder	= is_dir(SITEPATH . "/javascript/") ? "/javascript/" : "/js/";
			if (isset($this->aResource['resource'])) { $cFolder = "http://" . $this->aResource['resource'] . "/"; }

			if ($this->cDocType == "html5") {
				$this->cJS .= "<script src=\"" . $cFolder . $cJS . ".js\"></script>\n";
			} else {
				$this->cJS .= "<script type=\"text/javascript\" src=\"" . $cFolder . $cJS . ".js\"></script>\n";
			}
		}
	}

	/**
	 * head_js::addJSExtras()
	 *
	 * @param string $cJS
	 * @return null
	 */
	public function addJSExtras($cJS) {
		$this->cJS .= "<script type=\"text/javascript\">" . $cJS . "</script>\n";
	}

	/**
	 * head_js::addFrameworkJS()
	 *
	 * @param string $cJS
	 * @return null
	 */
	public function addFrameworkJS($cJS) {
		if ($this->cJSFrameworkName) {
			$cJS	= $this->cJSFrameworkName . "." . $cJS;

			//Path could be js, or javascript
			$cFolder	= is_dir(SITEPATH . "/javascript/") ? "/javascript/" : "/js/";
			if (isset($this->aResource['resource'])) { $cFolder = "http://" . $this->aResource['resource'] . "/"; }

			$this->cJS .= "<script type=\"text/javascript\" src=\"" . $cFolder . $cJS . ".js\"></script>\n";
		}

		return false;
	}

	/**
	 * head_js::loadJSFramework()
	 *
	 * @return string
	 */
	public function loadJSFramework() {
		//if its not hosted locally load the google version
		if (file_exists(SITEPATH . "/js/"  . $this->cJSFrameworkName . ".js")) {
			$cFolder = "/js/";
			if (isset($this->aResource['resource'])) { $cFolder = "http://" . $this->aResource['resource'] . "/"; }

			$cReturn = "<script type=\"text/javascript\" src=\"" . $cFolder . $this->cJSFrameworkName . ".js\"></script>\n";
		} else if (file_exists(SITEPATH . "/js/" . $this->cJSFrameworkName . ".min.js")) {
			$cFolder = "/js/";
			if (isset($this->aResource['resource'])) { $cFolder = "http://" . $this->aResource['resource'] . "/"; }

			$cReturn = "<script type=\"text/javascript\" src=\"" . $cFolder . $this->cJSFrameworkName . ".min.js\"></script>\n";
		} else {
			if (isset($_SERVER['SERVER_PORT']) && ($_SERVER['SERVER_PORT'] == 443)) {
				$cHTTP = "https";
			} else {
				$cHTTP = "http";
			}

			$cPath  = $cHTTP . "://ajax.googleapis.com/ajax/libs/";
			$cPath .= $this->cJSFrameworkName . "/";
			$cPath .= $this->cJSFrameworkVersion . "/";
			$cPath .= $this->cJSFrameworkName . ".min.js";

			$cReturn	 = "<script src=\"" . $cPath . "\" type=\"text/javascript\"></script>\n";

			//now do we want the ui aswell
			if ($this->bJSFrameworkUI) {
				$cReturn .= "<script src=\"" . $cHTTP;
				$cReturn .= "://ajax.googleapis.com/ajax/libs/";
				$cReturn .= $this->cJSFrameworkName . "ui/";
				$cReturn .= $this->cJSFrameworkUIVersion . "";
				$cReturn .= $this->cJSFrameworkName . "-ui";
				$cReturn .= ".min.js\" type=\"text/javascript\"></script>\n";
			}

			//are we a mobile version
			if ($this->bMobile) {
				$cReturn	.= "<script src=\"http://code.jquery.com/";
				$cReturn	.= $this->cJSFrameworkMobileName . "/";
				$cReturn	.= $this->cJSFrameworkMobileVersion . "/";
				$cReturn	.= $this->cJSFrameworkName . "." . $this->cJSFrameworkMobileName;
				$cReturn	.= "-" . $this->cJSFrameworkMobileVersion;
				$cReturn	.= ".min.js\" type=\"text/javascritpt\"></script>\n";
			}
		}

		return $cReturn;
	}

	public function setJSFramework($cName = false, $cVersion = false, $bUI = false) {
		$cJSName		= $cName 	?: $this->cJSFrameworkName;
		$cJSVersion		= $cVersion	?: $this->cJSFrameworkVersion;
		$cJSSubVersion	= $cVersion	?: $this->cJSFrameworkSubVersion;

		$this->cJSFrameworkName			= $cJSName;
		$this->cJSFrameworkVersion		= $cJSVersion;
		$this->bJSFramework				= true;
		$this->cJSFrameworkSubVersion	= $cJSSubVersion;

		//is the ui set
		if ($bUI) { $this->bJSFrameworkUI = true; }
	}

	public function addFrameworkCSS($cName) {
		$oCSS	= $this->oNails->getNails("Head_CSS");

		$cReturn = false;
		if ($this->bJSFrameworkUI) {
			$cLocation  = "http://ajax.googleapis.com/ajax/libs/";
			$cLocation .= $this->cJSFrameworkName . "ui/";
			$cLocation .= $this->cJSFrameworkUIVersion .= "/";
			$cLocation .= "themes/" . $cName . "/";

			$cNamed		= $this->cJSFrameworkName . "-ui.css";
			$oCSS->addCSS($cNamed, $cLocation);
		}

		//if its a mobile
		if ($this->bMobile) {
			$cLocation	 = "http://code.jquery.com/";
			$cLocation	.= $this->cJSFrameworkMobileName . "/";
			$cLocation	.= $this->cJSFrameworkMobileVersion . "/";

			$cNamed	 = $this->cJSFrameworkName . ".";
			$cNamed	.= $this->cJSFrameworkMobileName . "-";
			$cNamed	.= $this->cJSFrameworkMobileVersion;
			$cNamed	.= ".min.css";
			$oCSS->addCSS($cNamed, $cLocation);
		}
	}

	/**
	 * head_js::fullLoad()
	 *
	 * @return string
	 */
	public function fullLoad() {
		$cReturn	= false;

		if ($this->bJS) {
			//jQuery
			if ($this->bJSFramework) {  $cReturn .= $this->loadJSFramework(); }

			//get custom js
			$cReturn .= $this->getJS();
		}

		return $cReturn;
	}
}
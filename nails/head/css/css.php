<?php
/**
 * head_css
 *
 * @package
 * @author nails
 * @copyright Copyright (c) 2011
 * @version $Id$
 * @access public
 */
class head_css {
	//Traits
	use Browser;

	private $aData;
	private $oNails;

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

		$aGet				= $this->oNails->getConfig("css", "head");
		$aResource['css'] 	= $aGet['css'];

		$aGet	= $this->oNails->getConfig("css", "resourceDomains");
		$cCSS	= $aGet['css'];
		if ($cCSS) { $aResource['resource']	= ($cCSS . $cAddress); }

		$this->aResource = $aResource;
	}

	/**
	 * head_css::__set()
	 *
	 * @param string $cName
	 * @param mixed $mValue
	 * @return null
	 */
	public function __set($cName, $mValue) {
		$this->aData[$cName]	= $mValue;
	}

	/**
	 * head_css::__get()
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
	 * head_css::__isset()
	 *
	 * @param string $cName
	 * @return bool
	 */
	public function __isset($cName) {
		if (isset($this->aData[$cName])) { return true; }
		return false;
	}

	/**
	 * head_css::getBrowserCSS()
	 *
	 * @param mixed $mBrowser
	 * @param string $cStyled
	 * @return string
	 */
	private function getBrowserCSS($mBrowser, $cStyled = false) {
		$cCSS		= "";

		//The stylefolder can be stylesheets, or css
		$cFolder_a	= SITEPATH . "/css/";
		$cFolder_b	= "/css/";

		if (is_dir(SITEPATH . "/stylesheet/")) {
			$cFolder_a	=  SITEPATH . "/stylesheet/";
			$cFolder_b	= "/stylesheet/";
		}

		//if there is a resource domain
		if (isset($this->aResource) && isset($this->aResource['resource'])) { $cFolder_b = "http://" . $this->aResource['resource'] . "/"; }

		//if there are multiple browser css'
		if (is_array($mBrowser)) {
			for ($i = 0; $i < count($mBrowser); $i++) {
				if ($cStyled) {
					if (file_exists($cFolder_a . $cStyled . "/" . $mBrowser[$i] . ".css")) {
						$cCSS .= "<link type=\"text/css\" rel=\"stylesheet\" href=\"" . $cFolder_b . $cStyled . "/" . $mBrowser[$i] . ".css	\" />\n";
					}
				} else {
					if (file_exists($cFolder_a . $mBrowser[$i] . ".css")) {
						$cCSS .= "<link type=\"text/css\" rel=\"stylesheet\" href=\"" . $cFolder_b . $mBrowser[$i] . ".css\" />\n";
					}
				}
			}

			//This is to get it to render in chrome-frame, since this is mainly only useful for html5
			if (($this->cDocType == "html5") && ($mBrowser[0] == "ie")) {
				header("X-UA-Compatible: chrome=1");
				$cCSS .= "<meta http-equiv=\"X-UA-Compatible\" content=\"chrome=1\" />\n"; //Whilst not really a css tag, its easier to stick it here for validation reasons
			}
		} else {
			if ($cStyled) {
				if (file_exists($cFolder_a . $cStyled . "/" . $mBrowser . ".css")) {
					$cCSS .= "<link type=\"text/css\" rel=\"stylesheet\" href=\"" . $cFolder_b . $cStyled . "/" . $mBrowser . ".css	\" />\n";
				}
			} else {
				if (file_exists($cFolder_a . $mBrowser . ".css")) {
					$cCSS .= "<link type=\"text/css\" rel=\"stylesheet\" href=\"" . $cFolder_b . $mBrowser . ".css\" />\n";
				}
			}
		}

		return $cCSS;
	}

	/**
	 * head_css::getAddedCSS()
	 *
	 * @return string
	 */
	private function getAddedCSS() {
		$cReturn	= "";

		if ($this->aAddedCSS) {
			foreach ($this->aAddedCSS as $aCSS) {
				if (strstr($aCSS['location'], "http")) {
					if ($this->cDocType == "html5") {
						$cReturn .= "<link rel=\"stylesheet\" href=\"" . $aCSS['location'] . $aCSS['file'] . "\" />\n";
					} else {
						$cReturn .= "<link type=\"text/css\" rel=\"stylesheet\" href=\"" . $aCSS['location'] . $aCSS['file'] . "\" />\n";
					}
				} else {
					$cFolder = "/css/";
					if (isset($this->aResource) && isset($this->aResource['resource'])) { $cFolder = "http://" . $this->aResource['resource'] . "/"; }

					if ($this->cDocType == "html5") {
						$cReturn .= "<link rel=\"stylesheet\" href=\"" . $cFolder . $aCSS['location'] . $aCSS['file'] . ".css\" />\n";
					} else {
						$cReturn .= "<link type=\"text/css\" rel=\"stylesheet\" href=\"" . $cFolder . $aCSS['location'] . $aCSS['file'] . ".css\" />\n";
					}
				}
			}
		}

		return $cReturn;
	}

	/**
	 * head_css::getCSS()
	 *
	 * @param string $cFile
	 * @param string $cStyle
	 * @return string
	 */
	public function getCSS($cFile = false, $cStyle = false) {
		$cCSS_a = false;
		if (isset($this->cCSS)) { if ($this->cCSS) { $cCSS_a = $this->cCSS; }} //this is to avoid a memory loss when apache segfaults

		//set the css
		$mConfigCSS = false;
		if (isset($this->aResource['css'])) { $mConfigCSS = $this->aResource['css']; }
		$mCSS		= ($cFile ? $cFile : ($cCSS_a ? $cCSS_a : $mConfigCSS));
		$cTrue		= false;
		$cPath		= false;

		//set the default
		$cCSS	= "";

		//the folder can be css, or stylesheet
		$cFolder_a	= SITEPATH . "/css/";
		$cFolder_b	= "/css/";

		if (is_dir(SITEPATH . "/stylesheet/")) {
			$cFolder_a	= SITEPATH . "/stylesheet/";
			$cFolder_b	= "/stylesheet/";
		}

		//is there a resource domain set
		if (isset($this->aResource) && isset($this->aResource['resource'])) { $cFolder_b = "http://" . $this->aResource['resource'] . "/"; }

		//is it a single css or multiple
		if (!is_array($mCSS)) {
			//A file has been set which overwrites any styles
			if (strstr($mCSS, ".css")) {
				$cPath	= $mCSS;
				$cTrue	= SITEPATH . $mCSS;
			} else {
				$cPath	= $cFolder_b . $mCSS . ".css";
				$cTrue	= $cFolder_a . $mCSS . ".css";
			}
		} else {
			foreach ($mCSS as $cFontName => $cStyleSheet) {
				if (!strstr($cStyleSheet, ".css")) {
					$cStyleSheet	.= ".css";
				}

				if (file_exists($cFolder_a . $cStyleSheet)) {
					$cCSS	.= "<link type=\"text/css\" rel=\"stylesheet\" href=\"" . $cFolder_b . $cStyleSheet . "\" />\n";
				}
			}
		}

		//This odd
		$this->cCSS = $cPath;

		//Styles
		if ($this->cStyled) {
			$cStyled = $this->cStyled;
		} else {
			$cStyled = $cStyle;
		}

		if (file_exists($cTrue)) {
			if (file_exists($cFolder_a . "style.css")) {
				$cCSS .= "<link type=\"text/css\" rel=\"stylesheet\" href=\"" . $cFolder_b . "/style.css\" />\n";
				$cCSS .= "<link type=\"text/css\" rel=\"stylesheet\" href=\"" . $this->cCSS . "\" />\n";
			} else {
				$cCSS .= "<link type=\"text/css\" rel=\"stylesheet\" href=\"" . $this->cCSS . "\" />\n";
			}
		} else {
			if ($cStyled && file_exists($cFolder_b . $cStyled . "/style.css")) {
				$cCSS .= "<link type=\"text/css\" rel=\"stylesheet\" href=\"" . $cFolder_b . $cStyled . "/style.css\" />\n";
			} else {
				$cCSS .= "<link type=\"text/css\" rel=\"stylesheet\" href=\"" . $cFolder_b . "/style.css\" />\n";
			}
		}

		// Here so it overrides the default style
		$mBrowser = $this->getBrowser();
		$cCSS .= $this->getBrowserCSS($mBrowser, $cStyled);

		//a page structure overrides the css
		$cCSS .= $this->getAddedCSS();

		return $cCSS;
	}

	/**
	 * head_css::addCSS()
	 *
	 * @param string $cCSS
	 * @param string $cLocation
	 * @return null
	 */
	public function addCSS($cCSS, $cLocation = null) {
		if (strstr($cCSS, ".css")) { $cCSS = substr($cCSS, -4); }

		if ($cLocation) { if (!strstr($cLocation, "/")) { $cLocation .= "/"; }}

		$iNum	= count($this->aAddedCSS);
		if ($iNum) { $iNum++; }

		$aCSS = array(
			$iNum	=> array(
				'location'	=> $cLocation,
				'file'		=> $cCSS
			)
		);

		if (is_array($this->aAddedCSS)) {
			$aAdded = array_merge($this->aAddedCSS, $aCSS);
		} else {
			$aAdded	= $aCSS;
		}

		$this->aAddedCSS = $aAdded;
	}


}
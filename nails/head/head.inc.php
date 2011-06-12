<?php
/**
 * Head
 *
 * @package
 * @author Max
 * @copyright Copyright (c) 2008
 * @version $Id: head.inc.php 455 2009-12-23 13:55:35Z keloran $
 * @access public
 */
class Head {
	public $aHead       	= false;
	public $cJS		= false;
	public $bHTML		= false;
	public $cCSS		= false;
	public $cDocType	= "xhtml";
	public $cError		= false;
	public $bRTL		= false;

	//Custom settings for templates
	public $cPageTitle		= false;
	public $cPageDescription	= false;
	public $cPageKeywords		= false;
	public $bTitleLower		= false;

	//jQuery
	public $bJSFramework		= false;
	public $cJSFrameworkVersion	= "1.5.1";
	public $cJSFrameworkSubVersion	= "1.5";
	public $cJSFrameworkName	= "jquery";
	public $cJSFrameworkUIVersion	= "1.8.9";
	public $bJSFrameworkUI		= false;
	public $bJS			= true;
	public $bWarning		= true;

	//Nails
	static $oHead;

	//un-coupling
	private $oDB		= false;
	private $oNails		= false;

	private $cPage;
	private $cAction;
	private $cChoice;
	private $iItem;
	private $cStyled;
	private $cAddress;
	private $aAddedCSS;
	private $bTitleMixed;
	public $iCache;
	private $cExtraConds;

    /**
     * Head::__construct()
     *
     */
	private function __construct(Nails $oNails, $cStyle = false, $bNoInstall = null) {
		$this->oNails	= $oNails;
		$this->oDB	= $this->oNails->oDB;

		$this->aHead	= $this->makeHead();

		//set them since de-coupled
		$this->cPage	= $this->oNails->cPage;
		$this->cAction	= $this->oNails->cAction;
		$this->cChoice	= $this->oNails->cChoice;
		$this->iItem	= $this->oNails->iItem;
		$this->cAddress	= $this->oNails->cAddress;

		if (!$bNoInstall) {
			//do the install
    		if ($this->oNails->checkInstalled("keywords") == false) {
    			$this->install();
	    	}

	    	//do the upgrade of keywords table
    		if ($this->oNails->checkVersion("keywords", "1.0") == false) {
	    		//1.0
    			$this->oNails->updateVersion("keywords", "1.0");
	    	}

	    	//do the upgrade of the head library
    		if ($this->oNails->checkVersion("head", "1.3") == false) {
			//1.3
			$this->oNails->updateVersion("head", "1.3", false, "Update to version 1.5 of jQuery, and version 1.8.9 of UI");

    			//1.2
    			$this->oNails->updateVersion("head", "1.2", false, "Tester of XML");

    			//1.1
    			$cSQL	= "CREATE TABLE IF NOT EXISTS `head_titles` (`iTitleID` INT NOT NULL AUTO_INCREMENT, `cPage` VARCHAR(50), `cAction` VARCHAR(50), `cChoice` VARCHAR(50), `iItem` INT, `cTitle` TEXT, PRIMARY KEY(`iTitleID`))";
    			$this->oNails->updateVersion("head", "1.1", $cSQL, "Added the title table");

				//1.0
				$this->oNails->addVersion("head", "1.0");
	    	}
		}

	    //Do the style
	    if ($cStyle) {
	    	$this->cStyled = $cStyle;
	    }

		if (!$bNoInstall) {
			//this is mainly for emails
	    	if (!defined("SITEADDRESS")) {
				$cAddress	= $this->oNails->getConfig("address", $this->oNails->getConfigKey());

	    		define("SITEADDRESS", $cAddress);
	    	}
		}
	}

    /**
     * Head::getInstance()
     *
     * @return
     */
    static function getInstance($oNails, $cStyle = false, $bNoInstall = null) {
    	if (is_null(self::$oHead)) {
    		self::$oHead = new Head($oNails, $cStyle, $bNoInstall);
    	}

    	return self::$oHead;
    }

    /**
     * Head::setRTL()
     *
     * @return null
     */
    public function setRTL() {
    	$this->bRTL = true;
	}

	/**
	 * Head::install()
	 *
	 * @return
	 */
	private function install() {
		// Create the keywords table
		$this->oNails->addTable("
			CREATE TABLE IF NOT EXISTS `keywords` (
				`iKeywordID` INT NOT NULL AUTO_INCREMENT,
				`cPage` VARCHAR(50) NOT NULL,
				`iItem` INT NOT NULL DEFAULT 0,
				`cKeywords` TEXT NOT NULL,
				PRIMARY KEY(`iKeywordID`))
			ENGINE = MyISAM");

		$this->oNails->addVersion("keywords", "1.0");

		$this->oNails->sendLocation("install");
	}

	/**
	 * Head::setTitle()
	 *
	 * @param string $cTitle
	 * @param bool $bLowerCase do you want the title to always be lower case
	 * @return
	 */
	public function setTitle($cTitle, $bLowerCase = false, $bMixed = false) {
		$this->cPageTitle 	= $cTitle;
		$this->bTitleLower	= $bLowerCase;
		$this->bTitleMixed	= $bMixed;
	}

    /**
     * Head::getTitle()
     *
     * @return
     */
    public function getTitle() {
    	//seperator, most people will be happy with ..::..
		$cSep	= $this->oNails->getConfig("seperator", $this->oNails->getConfigKey());
		$aBrand	= $this->oNails->getConfig("title", $this->oNails->getConfigKey());

		if (is_array($aBrand)) {
			$cTitle = $aBrand[0];
			$bLower = $aBrand[1]['case'];
		} else {
			$cTitle = $aBrand['title'];
			$bLower		= true;
		}

    	//theres some attributes
		if (isset($aBrand['attrs'])) {
			if (isset($aBrand['attrs']['case'])) {
				switch ($aBrand['attrs']['case']) {
					case "lower":
						$cBrand	= strtolower($aBrand['title']);
						break;

					case "upper":
						$cBrand = strtoupper($aBrand['title']);
						break;

					case "words":
						$cBrand = ucwords($aBrand['title']);
						break;
				}
			} else {
				$cBrand = $aBrand;
			}
		} else {
			$cBrand	= $cTitle ? $cTitle : $aBrand;
		}

		if (is_array($cSep)) { $cSep = false; }
    	$cBrand		= $cBrand				? $cBrand 						: "Hammer";

		//lower or not the title
		if (!$this->bTitleMixed) {
			$cTitle		= $this->bTitleLower	? strtolower($this->cPageTitle) : ucwords($this->cPageTitle);
		} else {
			$cTitle	= $this->cPageTitle;
		}

    	$cSeperator	= $cSep					? $cSep	 						: " ..::.. ";

		//page title is for SEO purposes
    	if ($this->cPageTitle) {
    		$cTitle = "<title>" . $cTitle . $cSeperator . $cBrand . "</title>\n";
    	} else {
	    	if ($this->cPage) {
	    		$cPageTitle 	= ucwords(unSEO($this->cPage));

	    		//Action
    			if ($this->cAction) {
    				$cActionTitle	= ucwords(unSEO($this->cAction));
    				$cTitle			= "<title>" . $cActionTitle . $cSeperator . $cPageTitle . $cSeperator . $cBrand . "</title>\n";

    				//Choice
    				if ($this->cChoice) {
    					$cChoiceTitle = ucwords(unSEO($this->cChoice));
    					$cTitle		= "<title>" . $cChoiceTitle . $cSeperator . $cActionTitle . $cSeperator . $cPageTitle . $cSeperator . $cBrand . "</title>\n";
    				}
				} else {
					$cTitle = "<title>" . $cPageTitle . $cSeperator . $cBrand . "</title>\n";
				}
			} else {
				$cTitle = "<title>" . $cBrand . "</title>\n";
			}
		}

        return $cTitle;
    }

    /**
     * Head::setKeywords()
     *
     * @param string $cKeywods
     * @return
     */
    public function setKeywords($cKeywods) {
    	$this->cPageKeywords = $cKeywords;
    }

    /**
     * Head::getKeywords()
     *
     * @return
     */
    public function getKeywords() {
    	$cKeyword	= "";

		if ($this->cPageKeywords) {
			$cKeyword = $this->cPageKeywords;
		} else {
	    	if ($this->cPage) {
    			if ($this->iItem) {
    				$aSelect = array($this->cPage, $this->iItem);
    				$this->oDB->read("SELECT cKeywords FROM keywords WHERE cPage = ? AND iItem = ? LIMIT 1", $aSelect);
    				if ($this->oDB->nextRecord()) {
    					$cKeyword .= $this->oDB->f('cKeywords') . ",";
    				}
				} else {
					$this->oDB->read("SELECT cKeywords FROM keywords WHERE cPage = ? AND iItem = 0 LIMIT 1", $this->cPage);
					if ($this->oDB->nextRecord()) {
						$cKeyword .= $this->oDB->f('cKeywords') . ",";
					}
				}
			}

			$cKeyword .= $this->oNails->getConfig("keywords", $this->oNails->getConfigKey());
		}

        $cKeywords = "<meta name=\"keywords\" content=\"" . $cKeyword . "\" />\n";
        return $cKeywords;
    }

    /**
     * Head::setDescription()
     *
     * @param string $cDescription
     * @return
     */
    public function setDescription($cDescription) {
    	$this->cPageDescription = $cDescription;
    }

    /**
     * Head::getDescription()
     *
     * @return
     */
    public function getDescription() {
    	if ($this->cPageDescription) {
    		$cDescriptions = $this->cPageDescription;
    	} else {
		$cDescriptions	= $this->oNails->getConfig("description", $this->oNails->getConfigKey());
	    }

	    $cDescription = "<meta name=\"description\" content=\"" . $cDescriptions . "\" />\n";
        return $cDescription;
    }

	/**
	 * Head::mobileBrowser()
	 *
	 * @param mixed $mBrowser
	 * @return bool
	 */
	private function mobileBrowser($mBrowser) {
		$bReturn	= false;

		if (is_array($mBrowser)) {
			if (in_array("android", $mBrowser)) {
				$bReturn	= true;
			} else if (in_array("iphone", $mBrowser)) {
				$bReturn	= true;
			}
		} else {
			switch($mBrowser) {
				case "android":
				case "iphone":
					$bReturn = true;
					break;
			} // switch
		}

		return $bReturn;
	}

    /**
     * Head::getMetaTags()
     *
     * @return
     */
    public function getMetaTags() {
		$aTags		= $this->oNails->getConfig("metaData", $this->oNails->getConfigKey());
    	$cCan		= false;
    	$bViewPort	= false;

    	//get the browser, since there might be a viewport tag for iphone
    	$mBrowser	= getBrowser();
    	$bViewPort	= $this->mobileBrowser($mBrowser);

		//The initial tag to denote it as made with Hammer
		$cReturn = "<meta name=\"Generator\" content=\"Hammer Framework\" />\n";

		if (count($aTags) > 0) {
			foreach($aTags as $cTag => $cValue) {
				if (($cTag == "viewport") && ($bViewPort == false)) { continue; } //Skip the viewport on non iphones
				if ($cTag == "metaData") { continue; } //since this is invalid

				if (strstr($cTag, "|")) {
					$iSplitPos = strpos($cTag, "|");
					$cTag_a = substr($cTag, 0, $iSplitPos);
					$cTag_b = substr($cTag, $iSplitPos + 1, strlen($cTag));

					if ($this->cDocType == "html5") {
						$cReturn .= "<meta name=\"" . $cTag_b . "\" content=\"" . $cValue . "\" />\n";
					} else {
						$cReturn .= "<meta name=\"" . $cTag_b . "\" http-equiv=\"" . $cTag_b . "\" content=\"" . $cValue . "\" />\n";
					}
				} else {
					if ($this->cDocType == "html5") {
						$cReturn .= "<meta name=\"" . $cTag . "\" content=\"" . $cValue . "\" />\n";
					} else {
						$cReturn .= "<meta name=\"" . $cTag . "\" http-equiv=\"" . $cTag . "\" content=\"" . $cValue . "\" />\n";
					}
				}
			}
		}

    	//Canoical
    	if ($this->cPage) {
    		if ($this->cAddress) {
    			$cCan = $this->cAddress . "/";
    		} else {
    			if (isset($_SERVER['SERVER_NAME'])) {
    					$cCan = $_SERVER['SERVER_NAME'];
    			}
    		}

			if (isset($_SERVER['HTTPS'])) {
				$cHTTP = "https";
			} else {
				$cHTTP = "http";
			}
    	}

    	return $cReturn;
    }

    /**
     * Head::getCSS()
     *
     * @param string $cFile Filename of the css file
     * @param string $cStyle Dictate the style that should be used, so that it can be easily changed, without overwriting files
     * @return
     */
    public function getCSS($cFile = false, $cStyle = false) {
    	$cCSS_a = false;
		if (isset($this->cCSS)) { if ($this->cCSS) { $cCSS_a = $this->cCSS; }} //this is to avoid a memory loss when apache segfaults

		//set the css
		$mConfigCSS	= $this->oNails->getConfig("css", $this->oNails->getConfigKey());
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
        $mBrowser = getBrowser();
        $cCSS .= $this->getBrowserCSS($mBrowser, $cStyled);

    	//a page structure overrides the css
    	$cCSS .= $this->getAddedCSS();

        return $cCSS;
    }

	/**
	 * Head::getAddedCSS()
	 *
	 * @desc This adds the css file to the head
	 * @return string
	 */
	private function getAddedCSS() {
		$cReturn	= "";

		if ($this->aAddedCSS) {
			foreach ($this->aAddedCSS as $aCSS) {
				if (strstr($aCSS['location'], "http")) {
					$cReturn .= "<link type=\"text/css\" rel=\"stylesheet\" href=\"" . $aCSS['location'] . $aCSS['file'] . "\" />\n";
				} else {
					$cReturn .= "<link type=\"text/css\" rel=\"stylesheet\" href=\"/css/" . $aCSS['location'] . $aCSS['file'] . ".css\" />\n";
				}
			}
		}

		return $cReturn;
	}

	/**
	 * Head::addCSS()
	 *
	 * @desc This is for usage in structure files, to add a specific set of rules
	 * @param string $cCSS
	 * @return null
	 */
	public function addCSS($cCSS, $cLocation = null) {
		if (strstr($cCSS, ".css")) {
			$cCSS = substr($cCSS, -4);
		}

		if ($cLocation) {
			if (!strstr($cLocation, "/")) {
				$cLocation .= "/";
			}
		}

		$iNum	= count($this->aAddedCSS);
		if ($iNum) { $iNum++; }

		$this->aAddedCSS[$iNum]['location']	= $cLocation;
		$this->aAddedCSS[$iNum]['file']		= $cCSS;
	}

    /**
    * Head::getBrowserCSS()
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
	 * Head::addCache()
	 *
	 * @param int $iLength
	 * @return
	 */
	public function addCache($iLength = false) {
		if ($this->iCache) { $iLength = $this->iCache; }
		$iLength = $iLength ? $iLength : 0;

		//have the headers already been sent
		if (!headers_sent()) {
			if ($iLength) {
				$tsDay	= 86400;
				$tsWeek = time() + ($tsDay * $iLength);
				$cCache	= "max-age=7200, must-revalidate";
			} else {
				$tsWeek	= time() - 1000;
				$cCache = "no-store, no-cache, must-revalidate";
				header('Pragma: no-cache');
			}
			$dWeek	= date("r", $tsWeek);

			header('Expires: ' . $dWeek);
			header('Cache-Control: ' . $cCache);
			header('Cache-Control: post-check=0, pre-check=0', FALSE);
		}
	}

    /**
     * Head::getJS()
     *
     * @return
     */
    private function getJS() {
		$aHead	= $this->aHead;
    	$aJS	= false;

		$aJSReturn	= $this->oNails->getConfig("javascript", $this->oNails->getConfigKey());
		if (is_array($aJSReturn)) {
			foreach ($aJSReturn as $iJSNum => $cJSName) {
				$aJS[]	= $cJSName;
			}
		}

    	//Folder could be js, or javascript
    	$cFolder = is_dir(SITEPATH . "/javascript/") ? "/javascript/" : "/js/";

    	//get the returned js
		$cJS 	= $this->cJS;

		//theres quite a few of them
		if ($aJS) {
		        for($i = 0; $i < count($aJS); $i++) {
    		       	if ($this->bJSFramework) {
    		       		$cJSPath	= $cFolder . $this->cJSFrameworkName . "." . $aJS[$i] . ".js";

    		       		//append the jquery[framework] part to the name, since all js files should start with the framework,
    		       		//e.g. jquery.menu.js, in loader all you put is menu if your turning on google framework loader
    		       		if (file_exists(SITEPATH . $cJSPath)) {
        					$cJS .= "<script type=\"text/javascript\" src=\"" . $cJSPath . "\"></script>\n";
    		       		}
		           	} else {
						$cJS .= "<script type=\"text/javascript\" src=\"" . $cFolder . $aJS[$i] . ".js\"></script>\n";
	        	   	}
        		}
	        }

        return $cJS;
    }

	/**
	* Head::addJS()
	*
	* @desc add extra javascript that might not be needed in the config file
	* @param string $cJS
	* @param bool $bExternal This is so external ones can be used
	*/
	public function addJS($cJS, $bExternal = false) {
		if ($bExternal) {
			$this->cJS .= "<script type=\"text/javascript\" src=\"" . $cJS . "\"</script>\n";
		} else {
			$this->cJS .= "<script type=\"text/javascript\" src=\"/js/" . $cJS . ".js\"></script>\n";
		}
	}

	/**
	 * Head::addJSExtras()
	 *
	 * @param string $cJS
	 * @return null
	 */
	public function addJSExtras($cJS) {
		$this->cJS .= "<script type=\"text/javascript\">" . $cJS . "</script>\n";
	}

	/**
	* Head::addFrameworkJS()
	*
	* @desc This is to add a framework specific js, e.g. jquery.ui.min.js
	* @param string $cJS eg: ui.min
	* @return null
	*/
	public function addFrameworkJS($cJS) {
		if ($this->cJSFrameworkName) {
			$cJS	= $this->cJSFrameworkName . "." . $cJS;

			//Path could be js, or javascript
			$cFolder	= is_dir(SITEPATH . "/javascript/") ? "/javascript/" : "/js/";

			$this->cJS .= "<script type=\"text/javascript\" src=\"" . $cFolder . $cJS . ".js\"></script>\n";
		}

		return false;
	}

    /**
     * Head::loadJSFramework()
     *
     * @return
     */
    private function loadJSFramework() {
    	//if its not hosted locally load the google version
		if (file_exists(SITEPATH . "/js/"  . $this->cJSFrameworkName . ".js")) {
			$cReturn = "<script type=\"text/javascript\" src=\"/js/" . $this->cJSFrameworkName . ".js\"></script>\n";
		} else if (file_exists(SITEPATH . "/js/" . $this->cJSFrameworkName . ".min.js")) {
			$cReturn = "<script type=\"text/javascript\" src=\"/js/" . $this->cJSFrameworkName . ".min.js\"></script>\n";
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

			//sometimes google doesnt have it but MS does
			/**
			if (!file_exists($cPath)) {
				$cPath  = $cHTTP . "://ajax.aspnetcdn.com/ajax/";
				$cPath .= $this->cJSFrameworkName . "/";
				$cPath .= $this->cJSFrameworkName . "-";
				$cPath .= $this->cJSFrameworkSubVersion . ".min.js";
			}
			*/

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
		}

    	return $cReturn;
    }

	/**
	 * Head::addFrameworkCSS()
	 *
	 * @param string $cName
	 * @return string
	 */
	public function addFrameworkCSS($cName) {
		$cReturn = false;
		if ($this->bJSFrameworkUI) {
			$iNum = count($this->aAddedCSS);
			if ($iNum) { $iNum++; }

			$cLocation  = "http://ajax.googleapis.com/ajax/libs/";
			$cLocation .= $this->cJSFrameworkName . "ui/";
			$cLocation .= $this->cJSFrameworkUIVersion .= "/";
			$cLocation .= "themes/" . $cName . "/";

			$cNamed		= $this->cJSFrameworkName . "-ui.css";

			$this->aAddedCSS[$iNum]['location'] = $cLocation;
			$this->aAddedCSS[$iNum]['file']		= $cNamed;
		}

		return $cReturn;
	}

    /**
     * Head::setJSFramework()
     *
     * @param string $cName
     * @param string $cVersion
     * @return
     */
    public function setJSFramework($cName = false, $cVersion = false, $bUI = false) {
    	$cJSName	= $cName 	? $cName 	: "jquery";
    	$cJSVersion	= $cVersion	? $cVersion	: "1.5.1";
	$cJSSubVersion	= $cVersion	? $cVersion	: "1.5";

    	$this->cJSFrameworkName		= $cJSName;
    	$this->cJSFrameworkVersion	= $cJSVersion;
    	$this->bJSFramework		= true;
	$this->cJSFrameworkSubVersion	= $cJSSubVersion;

		//is the ui set
		if ($bUI) { $this->bJSFrameworkUI = true; }
    }

    /**
     * returnJS function.
     *
     * @desc this is mainly so the js can be loaded at the bottom
     * @access public
     * @return void
     */
    public function returnJS() {
    	$cReturn	= "";

    	if ($this->bJSFramework) {
    		$cReturn .= $this->loadJSFramework();
    	}

    	$cReturn .= $this->getJS();

    	return $cReturn;
    }

    /**
     * Head::getDocType()
     *
     * @return
     */
    public function getDocType() {
	$cDirection = $this->bRTL ? " dir=\"rtl\"" : "";

    	if ($this->cDocType == "html") {
		$cReturn  = "<?xml version=\"1.0\" encoding=\"ISO-8859-1\"?>\n";
		$cReturn .= "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Strict//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd\">\n";
    		$cReturn .= "<html xmlns=\"http://www.w3.org/1999/xhtml\" xml:lang=\"en\" lang=\"en\">\n";
    	} else if ($this->cDocType == "html5") {
    		$cReturn = "<!DOCTYPE html>\n";
    		$cReturn .= "<html>\n";
    	} else {
		$cReturn  = "<?xml version=\"1.0\" encoding=\"ISO-8859-1\"?>\n";
		if (isset($this->cCSS)) {
	   		$cReturn .= "<?xml-stylesheet type=\"text/css\" href=\"" . $this->cCSS . "\" ?>\n";
	   	}

		$cReturn .= "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.1//EN\" \"http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd\">\n";
		$cReturn .= "<html xmlns=\"http://www.w3.org/1999/xhtml\" xml:lang=\"en\">\n";
	}

    	return $cReturn;
    }

    /**
    * Head::getWarnings()
    *
    * @return string
    */
    public function getWarnings() {
    	$cReturn	= false;
		$aReturn	= false;

		if (ini_get("register_globals")) { 		$aReturn[]	=  "You have register_globals turned on, this is a bad idea, turn it off<br />\n"; }
		if (ini_get("short_tags")) {			$aReturn[]	=  "You don't have short tags turned on, it is recommended you turn it on, for no other reason that it makes writing templates easier<br />\n"; }
		if (ini_get("memory_limit") <= 31) {		$aReturn[]	= "Your memory_limit is set to less than 32M it is recommended to have this higher<br />\n"; }
		if (ini_get("post_max_size") <= 8) { 		$aReturn[]	= "Your post_max_size is set to less than 9M it is recommeded you increase this if you plan on creating a file area<br />\n"; }
		if (ini_get("magic_quotes_gpc")) {		$aReturn[]	= "You have magic_quotes_gpc turned on, this is a bad idea, turn it off<br />\n"; }
		if (ini_get("upload_max_filesize") <= 31) {	$aReturn[]	= "Your upload_max_filesize is set to less than 32M it is recommneded you increase this if you plan on creating a file area<br />\n"; }
		if (ini_get("allow_url_include")) {		$aReturn[]	= "You have allow_url_include turned on, it is recommended that you turn this off<br />\n"; }

		if ($aReturn) {
			$cReturn	= "<div style=\"width: 100%; background-color: red; color: white; font-size: 1.3em;\">\n";
			$cReturn	.= "<h1>Warnings</h1>";

			for ($i = 0; $i < count($aReturn); $i++) {
				$cReturn .= $aReturn[$i];
			}

			$cReturn	.= "</div>\n";
		}

		return $cReturn;
    }

    /**
     * Head::getRSS()
     *
     * @return string
     */
	private function getRSS() {
    	$cReturn	= false;

    	$bRSS	= $this->oNails->getConfig("rss", $this->oNails->getConfigKey());

    	if ($bRSS) {
    		$cReturn	= $this->oNails->getRSS();
    	}

    	return $cReturn;
    }

	/**
	 * Head::addExtraConds()
	 *
	 * @param string $cString
	 * @return null
	 */
	public function addExtraConds($cString) {
		$this->cExtraConds .= $cString . "\n";
	}

	/**
	 * Head::getExtraConds()
	 *
	 * @return string
	 */
	private function getExtraConds() {
		return $this->cExtraConds;
	}

    /**
     * Head::getFullHead()
     *
     * @param string $cFile The CSS file
     * @param bool $bBody This is if you want it to return the body opener too
     * @return
     */
	public function getFullHead($cFile = false, $bBody = false) {
		$this->addCache();

		//Get the HTML header info, e.g. if its html5/4 or xhtml 1.1
		$cReturn = $this->getDocType();
		$cReturn .= "<head>\n";

		//get the css first to speed up page load
		$cReturn .= $this->getCSS($cFile);

		//always add the charset
		$cReturn .= "<meta charset=\"utf-8\" />\n";

		//Base
		if (isset($_SERVER['SERVER_NAME'])) {
			if (isset($_SERVER['SERVER_PORT']) && ($_SERVER['SERVER_PORT'] == 443)) {
				$cReturn .= "<base href=\"https://" . $_SERVER['SERVER_NAME'] . "/\" />\n";
			} else {
				$cReturn .= "<base href=\"http://" . $_SERVER['SERVER_NAME'] . "/\" />\n";
			}
		}

		//get the meta data
		$cReturn .= $this->getTitle();
		$cReturn .= $this->getKeywords();
		$cReturn .= $this->getDescription();
		$cReturn .= $this->getMetaTags();
		//$cReturn .= $this->getRSS();

		if ($this->bJS) {
			//jQuery
			if ($this->bJSFramework) {
				$cReturn .= $this->loadJSFramework();
			}

			$cReturn .= $this->getJS();
		}

		//browser cap
		$mBrowser = getBrowserCap();

		//Favicon
		$cReturn .= "<link rel=\"icon\" href=\"/favicon.ico\" type=\"image/x-icon\" />\n";
		$cReturn .= "<link rel=\"shortcut icon\" href=\"/favicon.ico\" type=\"image/x-icon\" />\n";
		$cReturn .= "<link rel=\"apple-touch-icon\" href=\"/favicon.ico\" />\n"; //iphone image

		//show the ie6 warning
		if ($this->bWarning) {
			$cReturn .= "<!--[if lte IE 8]>\n";
			$cReturn .= "<div style=\"clear: both; height: 59px; padding:0 0 0 15px; position: relative;\">\n";
			$cReturn .= "<a href=\"http://www.microsoft.com/windows/internet-explorer/default.aspx?ocid=ie6_countdown_bannercode\">\n";
			$cReturn .= "<img src=\"http://www.theie6countdown.com/images/upgrade.jpg\" border=\"0\" height=\"42\" width=\"820\" alt=\"IE Upgrade\" />\n";
			$cReturn .= "</a>\n";
			$cReturn .= "</div>\n";
			$cReturn .= "<![endif]-->\n";
		}

		//since most of the time you will be using HTML5 add the shiv for less than IE9 which likes HTML5
		//its not a good idea to have stuff in the head thats remote, but google should be fast
		if ($this->cDocType == "html5") {
			$cReturn .= "<!--[if lt IE 9]>\n";
			$cReturn .= "<script src=\"http://html5shim.googlecode.com/svn/trunk/html5.js\"></script>\n";
			$cReturn .= "<![endif]-->\n";
		}

		//Get the extra conditions, usually just IE stuffs
		$cReturn .= $this->getExtraConds();

		//close the head
		$cReturn .= "</head>\n";

		//get the body
		if ($bBody) { $cReturn .= "<body>\n"; }

		//Display any warnings till i get the setup script made
		if ($this->getWarnings()) {
			$cReturn .= $this->getWarnings();
		}

		return $cReturn;
	}

	/**
	 * Head::makeHead()
	 *
	 * @return array
	 */
	private function makeHead() {
		$aReturn	= false;

		$aReturn['title']	= $this->oNails->getConfig("title", $this->oNails->getConfigKey());
		$aReturn['keywords']	= $this->oNails->getConfig("keywords", $this->oNails->getConfigKey());
		$aReturn['description']	= $this->oNails->getConfig("description", $this->oNails->getConfigKey());
		$aReturn['css']		= $this->oNails->getConfig("css", $this->oNails->getConfigKey());

		//Because Javascript can have multiple sub elements
		$aJS					= $this->oNails->getConfig("javascript", $this->oNails->getConfigKey());
		if (isset($aJS[0])) {
			if ($aJS) {
				foreach ($aJS as $aJSPart) {
					$aReturn['javascript'][]	= $aJSPart;
				}
			}
		}

		return $aReturn;
	}

	/**
	 * Head::debugHead()
	 *
	 * @param string $cType
	 * @return string
	 */
	public function debugHead($cType = "css") {
		$cType		= strtoupper($cType);
		$cReturn	= false;
		$cType		= "get" . $cType;

		$cReturn	= $this->$cType();
		$cReturn	= htmlentities($cReturn);
		$cReturn	= nl2br($cReturn);

		return $cReturn;
	}

	/**
	 * Head::getBrowser()
	 *
	 * @desc for back compat
	 * @return mixed
	 */
	public function getBrowser() {
		return getBrowser();
	}
}

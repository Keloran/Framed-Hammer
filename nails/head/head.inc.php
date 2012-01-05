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
	//Traits
	use Browser, Warnings;

	public $cDocType	= "xhtml";


	public $bWarning	= true;
	public $bMobile		= false;

	//Nails
	static $oHead;

	//un-coupling
	private $oDB		= false;
	private $oNails		= false;
	private $aData		= false;

	//children
	private $oCSS;
	private $oJS;

	public $bJSFrameworkUI = false;

    /**
     * Head::__construct()
     *
     */
	private function __construct(Nails $oNails, $cStyle = false, $bNoInstall = null) {
		$this->oNails	= $oNails;
		$this->oDB		= $this->oNails->oDB;
		$this->oCSS		= $this->oNails->getNails("Head_CSS");
		$this->oJS		= $this->oNails->getNails("Head_JS", $this->oCSS);

		$this->aHead	= $this->makeHead();

		//set them since de-coupled
		$this->cPage	= $this->oNails->cPage;
		$this->cAction	= $this->oNails->cAction;
		$this->cChoice	= $this->oNails->cChoice;
		$this->iItem	= $this->oNails->iItem;
		$this->cAddress	= $this->oNails->cAddress;

	    //Do the style
	    if ($cStyle) { $this->cStyled = $cStyle; }

		if (!$bNoInstall) {
			//this is mainly for emails
	    	if (!defined("SITEADDRESS")) {
				$cAddress	= $this->oNails->getConfig("address", "head")['address'];
	    		define("SITEADDRESS", $cAddress);
	    	}
		}

		$mBrowser		= $this->getBrowser();
		$this->bMobile	= $this->mobileBrowser($mBrowser);
		$this->bWarning = $this->IEBrowser($mBrowser);
	}

	/**
	 * Head::__set()
	 *
	 * @param string $cName
	 * @param mixed $mValue
	 * @return null
	 */
	public function __set($cName, $mValue) {
		$this->aData[$cName] = $mValue;
	}

	/**
	 * Head::__isset()
	 *
	 * @param string $cName
	 * @return bool
	 */
	public function __isset($cName) {
		$bReturn = false;

		if (isset($this->aData[$cName])) {
			$bReturn = true;
		} else if (isset($this->$cName)) {
			$bReturn = true;
		}

		return false;
	}

	/**
	 * Head::__get()
	 *
	 * @param string $cName
	 * @return mixed
	 */
	public function __get($cName) {
		$mReturn = false;

		if (isset($this->aData[$cName])) { $mReturn = $this->aData[$cName]; }

		return $mReturn;
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
    	$mKey 	= $this->oNails->getConfigKey();
    	$bLower	= true;

	   	//seperator, most people will be happy with ..::..
		$cSep	= $this->oNails->getConfig("seperator", $mKey);
		$aTitle	= $this->oNails->getConfig("title", $mKey);
		$aBrand = $this->oNails->getConfig("brand", $mKey);
    	$aCase	= $this->oNails->getConfig("case", $mKey);

    	//set them depending on which is alive
    	if (isset($aBrand['brand'])) { $aBrand = $aBrand['brand']; }
    	if (isset($aTitle['title'])) { $aBrand = $aTitle['brand']; }
    	if (isset($cSep['seperator'])) { $cSep = $cSep['seperator']; }
    	if (isset($aCase['case'])) { $bLower = $aCase['case']; }

    	//page title
		if (!$this->cPageTitle) { $this->cPageTitle = $aTitle['title']; }

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
	    $cBrand		= $cBrand ? $cBrand : "Hammer";

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
    	$oMeta					= $this->oNails->getNails("Head_Meta");
    	$oMeta->cPage			= $this->cPage;
    	$oMeta->cPageKeywords	= $this->cPageKeywords;
    	return $oMeta->getKeywords();
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
    	$oMeta						= $this->oNails->getNails("Head_Meta");
    	$oMeta->cPageDescription	= $this->cPageDescription;
    	return $oMeta->getDescription();
    }

    /**
     * Head::getMetaTags()
     *
     * @return
     */
    public function getMetaTags() {
    	$oMeta	= $this->oNails->getNails("Head_Meta");
    	return $oMeta->getMetaTags();
    }

	/**
	 * Head::addCSS()
	 *
	 * @desc This is for usage in structure files, to add a specific set of rules
	 * @param string $cCSS
	 * @return null
	 */
	public function addCSS($cCSS, $cLocation = null) {
		$oCSS	= $this->oCSS;
		$oCSS->addCSS($cCSS, $cLocation);
	}

	/**
	 * Head::getCSS()
	 *
	 * @param string $cFile
	 * @return string
	 */
	public function getCSS($cFile = false) {
		$oCSS	= $this->oCSS;
		return $oCSS->getCSS($cFile);
	}

    /**
     * Head::getJS()
     *
     * @return
     */
    private function getJS() {
    	$oJS	= $this->oJS;
    	return $oJS->getJS();
    }

	/**
	* Head::addJS()
	*
	* @desc add extra javascript that might not be needed in the config file
	* @param string $cJS
	* @param bool $bExternal This is so external ones can be used
	*/
	public function addJS($cJS, $bExternal = false) {
		$oJS	= $this->oJS;
		$oJS->addJS($cJS, $bExternal);
	}

	/**
	 * Head::addJSExtras()
	 *
	 * @param string $cJS
	 * @return null
	 */
	public function addJSExtras($cJS) {
		$oJS	= $this->oJS;
		$oJS->addJSExtras($cJS);
	}

	/**
	* Head::addFrameworkJS()
	*
	* @desc This is to add a framework specific js, e.g. jquery.ui.min.js
	* @param string $cJS eg: ui.min
	* @return null
	*/
	public function addFrameworkJS($cJS) {
		$oJS	= $this->oJS;
		$oJS->addFrameworkJS($cJS);
	}

    /**
     * Head::loadJSFramework()
     *
     * @return
     */
    private function loadJSFramework() {
    	$oJS	= $this->oJS;
    	return $oJS->loadJSFramework();
    }

	/**
	 * Head::addFrameworkCSS()
	 *
	 * @param string $cName
	 * @return string
	 */
	public function addFrameworkCSS($cName) {
		$oJS					= $this->oJS;
		$oJS->bJSFrameworkUI	= $this->bJSFrameworkUI;
		return $oJS->addFrameworkCSS($cName);
	}

    /**
     * Head::setJSFramework()
     *
     * @param string $cName
     * @param string $cVersion
     * @return
     */
    public function setJSFramework($cName = false, $cVersion = false, $bUI = false) {
    	$oJS	= $this->oJS;
    	$oJS->setJSFramework($cName, $cVersion, $bUI);
    }

    /**
     * returnJS function.
     *
     * @desc this is mainly so the js can be loaded at the bottom
     * @access public
     * @return void
     */
    public function returnJS() {
    	$oJS					= $this->oJS;
    	$oJS->bJSFrameworkUI	= $this->bJSFrameworkUI;

    	return $oJS->fullLoad();
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
		//Get the HTML header info, e.g. if its html5/4 or xhtml 1.1
		$cReturn = $this->getDocType();
		$cReturn .= "<head>\n";

		//get the css first to speed up page load
		$cReturn .= $this->getCSS($cFile);

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

		$cReturn .= $this->returnJS();

		//Favicon
		$cFavi	= false;
		if (file_exists(SITEPATH . "/favicon.ico")) {
			$cFavi	= "/favicon.ico";
		} else if (file_exists(SITEPATH . "/images/favicon.ico")) {
			$cFavi	= "/images/favicon.ico";
		}
		if ($cFavi) {
			$cReturn .= "<link rel=\"icon\" href=\"" . $cFavi . "\" type=\"image/x-icon\" />\n";
			$cReturn .= "<link rel=\"shortcut icon\" href=\"" . $cFavi . "\" type=\"image/x-icon\" />\n";
			$cReturn .= "<link rel=\"apple-touch-icon\" href=\"" . $cFavi . "\" />\n"; //iphone image
		}

		//Get the extra conditions, usually just IE stuffs
		$cReturn .= $this->getExtraConds();

		//close the head
		$cReturn .= "</head>\n";

		//get the body
		if ($bBody) { $cReturn .= "<body>\n"; }

		//mobile browser
		if ($this->bMobile) { $cReturn .= "<div data-role=\"page\" id=\"jqm-home\" class=\"type-home\">\n"; }

		//show the ie6 warning
		if ($this->bWarning) {
			$cBanner  = $this->getBanner();
			$cReturn .= $this->getShiv();
			$cReturn .= $this->getWarnings($cBanner);
		} else {
			$cReturn .= $this->getWarnings();
		}

		//do the header call for caching
		$this->doHeader();

		return $cReturn;
	}

	/**
	 * Head::makeHead()
	 *
	 * @return array
	 */
	private function makeHead() {
		$aReturn	= false;

		$aReturn['title']		= $this->oNails->getConfig("title", "head")['title'];
		$aReturn['keywords']	= $this->oNails->getConfig("keywords", "head")['keywords'];
		$aReturn['description']	= $this->oNails->getConfig("description", "head")['description'];
		$aReturn['css']			= $this->oNails->getConfig("css", "head")['css'];
		$aReturn['address']		= $this->oNails->getConfig("address", "head")['address'];

		//get the resource domains if they exist
		$cCSS	= $this->oNails->getConfig("css", "resourceDomains")['css'];
		$cJS	= $this->oNails->getConfig("js", "resourceDomains")['js'];

		//now mash them with the address
		if ($cCSS) { $aReturn['resource']['css']	= ($cCSS . $aReturn['address']); }
		if ($cJS) { $aReturn['resource']['js']		= ($cJS . $aReturn['address']); }

		//Because Javascript can have multiple sub elements
		$aJS					= $this->oNails->getConfig("javascript", "head");
		if (isset($aJS[0])) {
			if (strlen($aJS[0]) >= 2) {
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
}

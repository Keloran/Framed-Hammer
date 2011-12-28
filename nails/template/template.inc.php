<?php
/**
 * Template
 *
 * @package
 * @author Max
 * @copyright Copyright (c) 2008
 * @version $Id: templates.inc.php 498 2010-02-20 13:09:57Z keloran $
 * @access public
 */
class Template extends Template_Abstract {
	static $oTemplate; //the static (never called in this version)

	public $cTemplate		= false;
	public $aVars			= array();
	public $cJS				= false;
	public $cExtraJS		= false;
	public $cPagination		= false;
	public $bDeleted		= false;
	public $cFormTemplate	= false;

	//override and get a different structure for hte homepage
	public $bStructOverride	= false;
	public $cOverride		= false;

    //Sub templates so that templates themselves dont need todo buisness
    private $aSubTemplates	= false;
	protected $cTemp		= false;
    protected $cFolder		= false;
	private $cOrigFolder	= false;

	//is it called
    public $cSiteCalled		= false; //a check to see if it was called
	public $bCalled			= false; //a check to see if it was called
	private $bSetCalled		= false;
	private $bFormAdded		= false;
	private $bThere			= false; //if there is a return from setTemplate then this is set to true

	//is there a form object
    public $oForms			= false;

	//decouple
	public $oNails			= false;

	public $cPage		= false;
	public $cAction		= false;
	public $cChoice		= false;
	public $cLang		= false;
	public $cBrand		= false;
	public $cError		= false;
	public $iPage		= false;
	public $iItem		= false;
	public $cSkin		= false;
	public $bChecked	= false;

	private $oType		= false;

	//Skin Settings
	public $cSkinSetting	= false;

	/**
	 * Template::__construct()
	 *
	 */
	public function __construct($aParams, $cSite = false, $cSkinSetting = false) {
		//skin
		$this->cSkinSetting	= isset($cSkinSetting) ? $cSkinSetting : "brand";

		//site
		if ($cSite) { $this->cSiteCalled 	= $cSite; }

		$this->aParams		= $aParams;
		$this->setParams($aParams);

		//Add the error from hammer (others) into the vars
		$this->setVars("error", $this->cError);

		//Now we have a setting
		$cSkin			= "c" . ucfirst($this->cSkinSetting);
		if (isset($this->$cSkin)) {
			$this->cSkin	= $this->$cSkin;
		}

		$this->cJS			= false;
		$this->cExtraJS		= false;

		//do the setTemplate on the standard
		$this->setTemplate();
	}

	/**
	 * Template::getInstance()
	 *
	 * @return
	 */
	static function getInstance($aParams, $cSite = false, $cSkinSetting = false) {
		if (is_null(self::$oTemplate)) {
			self::$oTemplate	= new Template($aParams, $cSite, $cSkinSetting);
		}
		return self::$oTemplate;
	}

	/**
	 * Template::setVars()
	 *
	 * @param mixed $cName
	 * @param mixed $mVars
	 * @return null
	 */
	public function setVars($cName, $mVars) {
		if ($this->oType) { return $this->oType->setVars($cName, $mVars); }

		return false;
	}

	/**
	 * Template::getMainPageNew()
	 *
	 * @desc this replaces getMainPage so that it is more effecient
	 * @param string $cDefault
	 * @param bool $bEcho
	 * @param array $aDefaultLayotu
	 * @return string
	 */
	private function getMainPageNew($cDefault = null, $bEcho = null, $aDefaultLayout = null) {
		$oMain	= new Template_Content($cDefault);
		$oMain->setDefaultLayout($aDefaultLayout);
		$oMain->setTemplate($cDefault);

		$this->oType	= $oMain;

		$cReturn	 = $oMain->cStarter;
		$cReturn	.= $this->renderTemplate();
		$cReturn	.= $oMain0->cEnder;


		//Echo it rather htan returning
		if ($bEcho) {
			echo $cReturn;
		} else {
			return $cReturn;
		}
	}

	/**
	 * Template::getMainPage()
	 *
	 * @desc This thows the main page to the renderer
	 * @param string $cDefault The Default page, e.g. News
	 * @param bool $bEcho Echo it rather than asking to be returned
	 * @param array $aDefaultLayout This is to set the default layout so it can be customized easier
	 * @return
	 */
	public function getMainPage($cDefault = false, $bEcho = false, $aDefaultLayout = null) {
		return $this->getMainPageNew($cDefault, $bEcho, $aDefaultLayout);
	}

	/**
	* Template::getCore()
	*
	* @param string $cPage
	* @param bool $bEcho this is incase we dont want to use echo inside the struct
	* @return string
	*/
	public function getCore($cPage, $bEcho = null) {
		$oPage				= new Template_Core($this->aParams);
		$cReturn			= $oPage->setTemplate($cPage);

		$this->cTemplate	= $cReturn;

		$this->oType		= $oPage;

		//We want to echo it, to make structure.struct pages nicer
		if ($bEcho) {
			echo $this->renderTemplate();
			return false;
		} else {
			return $this->renderTemplate();
		}
	}

	/**
	 * Template::getLayout()
	 *
	 * @param string $cPage
	 * @param bool $bEcho
	 * @return string
	 */
	public function getLayout($cPage, $bEcho = null) {
		return $this->getCore($cPage, $bEcho);
	}

	/**
	* Template::indexTemplate()
	*
	* @param string $cTemplate
	* @desc This is for old sites, dont use for new ones
	*/
	public function indexTemplate($cTemplate) {
		$this->cTemplate = SITEPATH . "/templates/" . $cTemplate . ".tpl";

		return $this->renderTemplate();
	}

	/**
	 * Template::getStructure()
	 *
	 * @param string $cStructure
	 * @return string
	 */
	public function getStructure($cStructure = null) {
		$oStruct = Template_Structure::getInstance($this->aParams);
		$oStruct->setTemplate($cStructure);
		$oStruct->createTemplate();
		$this->bCalled	= true;

		$this->oType	= $oStruct;

		return $oStruct->renderTemplate(); //render the actual site/layout
	}

	/**
	* Template::setCoreTemplate();
	*
	* @param string $cTemplate
	* @return null
	*/
	public function setCoreTemplate($cTemplate = false, $bDebug = false) {
		$oTemplate	= new Template_Layout($this->aParams);
		$cTemplate	= $oTemplate->setTemplate($cTemplate);

		//debug will kill it
		if ($bDebug) { $oTemplate->debugTemplates(); }

		$this->cTemplate	= $cTemplate;
		$this->bChecked		= true;

		$this->oType	= $oTemplate;

		return $oTemplate;
	}

	/**
	* Template::getCoreTemplate()
	*
	* @param strng $cTemplate
	* @desc This is the old method, you should use setCoreTemplate since your setting not getting
	* @return
	*/
	private function getCoreTemplate($cTemplate = false) {
		return $this->setCoreTemplate($cTemplate);
	}

	/**
	 * Template::setLayout()
	 *
	 * @param string $cTemplate
	 * @return string
	 */
	public function setLayout($cTemplate = false, $bDebug = false) {
		return $this->setCoreTemplate($cTemplate, $bDebug);
	}

	/**
	 * Template::getTemplate()
	 *
	 * @param string $cTemplate
	 * @param string $cAltPage
	 * @return null
	 */
	public function getTemplate($cTemplate = null, $cAltPage = null) {
		return $this->setTemplate($cTemplate, $cAltPage);
	}

	/**
	 * Template::setTemplate()
	 *
	 * @param string $cTemplate The actual template file
	 * @param string $cAltPage The location of the page itself, e.g. news instead of /news
	 * @return null
	 */
	public function setTemplate($cTemplate = null, $cAltPage = null) {
		$oPage				= new Template_Page($this->aParams);
		$oPage->setDefault($this->cDefault);
		$this->bCalled		= true;
		$this->bSetCalled	= true;

		$cReturn			= $oPage->setTemplate($cTemplate, $cAltPage);
		$this->cTemplate	= $cReturn;

		//is there anything returned
		if ($cReturn) { $this->bThere	= true; }

		$this->oType	= $oPage;

		return $cReturn;
	}

	/**
	 * Template::setEmailTemplate()
	 *
	 * @param string $cTemplate
	 * @return string
	 */
	public function setEmailTemplate($cTemplate) {
		$oRender			= new Template_Email($this->aParams);
		$oRender->setDefault($this->cDefault);
		$this->bCalled		= true;

		$cReturn			= $oRender->setTemplate($cTemplate);
		$this->cTemplate	= $cReturn;

		$this->oType	= $oRender;

		return $cReturn;
	}

	/**
	* Template::setLanguage()
	*
	* @param string $cLanguage
	* @return
	*/
	public function setLanguage($cLanguage) {
		$this->cLanguage = $cLanguage;
	}

	/**
	 * Template::addJS()
	 *
	 * @param string $cFile
	 * @param array $aVariables
	 * @return
	 */
	public function addJS($cFile, $aVariables = false) {
		$cReturn = "<script type='text/javascript' src='/js/" . $cFile . ".js'>\n";

		if ($aVariables) {
			foreach ($aVariables as $cName => $cValue) {
				if ($cName) {
					$cReturn .= "var " . $cName . " = " . $cValue . ";\n";
				} else {
					$cReturn .= $cValue . ";\n";
				}
			}
		}

		$cReturn .= "</script>\n";
		$this->cJS .= $cReturn;
	}

	/**
	 * Template::addJSProperties()
	 *
	 * @param string $cJS
	 * @return
	 */
	public function addJSProperties($cJS) {
        $cReturn  = "<script type='text/javascript'>\n";
		$cReturn .= $cJS . "\n";
		$cReturn .= "</script>\n";

		$this->cExtraJS .= $cReturn;
	}

    /**
     * Template::addForm()
     *
     * @desc Add a form to the template
     * @var mixed $mObject do you want the form to return objects, or is it an object itself
     * @return object
     */
	public function addForm($mObject = null) {
		if (is_object($mObject)) {
			$this->oForms			= $mObject;
			$this->bFormAdded		= true;
		} else {
			$this->oForms			= new Form($this);
			$this->bFormAdded		= true;
			$this->oForms->bObject	= $mObject;
		}

		return $this->oForms;
	}

    /**
     * Template::getForm()
     * @desc This is really only for ease of use
     * @return object
     */
    private function getForm() {
    	return $this->addForm();
    }

    /**
     * Template::addFormed()
     *
     * @return
     */
    private function addFormed() {
    	$oForm	= $this->addForm();

    	$this->oForms = $oForm;

    	return $this->oForms;
    }

	/**
	 * Template::getValidate()
	 *
	 * @desc Get the validator
	 * @todo Change to addValidator
	 * @return object
	 */
	public function getValidate() {
		$oHammer	= Hammer::getHammer();
		$oValidate	= $oHammer->getValidator();

		return $oValidate;
	}

	/**
	* Template::addValidator()
	*
	* @return
	*/
	public function addValidator() {
		return $this->getValidator();
	}

	/**
	* Template:addPagination()
	*
	* @desc Adds pagination to a page
	* @param mixed $mList the list of items so that pagination can be created
	* @param int $iLimit the limit that can be shown in order to create the pagination
	* @param string $cAddress the address of the page that the pagination will be filtered to
	* @param bool $bStated if there needs to be an = at end instead of /
	* @return string
	*/
	public function addPagination($mList, $cAddress, $iLimit, $bStated = false) {
		$cReturn 	= false;

		$iPaged		= 0;
		$iItem		= $this->iItem;
		$iPage		= $this->iPage;

		if ($iPage) { $iPaged = $iPage ? $iPage : 0; }
		if ($iItem && !$iPaged) { $iPaged = $iItem ? $iItem : 0; }

		$iCurrentPage 	= $iPaged;

		$cNewAddress 	= $bStated 		? $cAddress . "&amp;num=" 	: $cAddress . "page/";
		$iAmount	= is_array($mList)	? count($mList) 		: $mList;

		if ($iAmount > $iLimit) {
			$j = 2;
			$cReturn  = "<div class=\"pages\">\n";

			//image
			$cReturn .= "<img src=\"/images/layout/pages.png\" alt=\"Pages\" />&nbsp; ";

			//First
			$cReturn .= "<a href=\"" . $cAddress . "\">First</a> || ";

			//if the currentpage and its less than the amount
			if ($iCurrentPage && ($iCurrentPage <= $iAmount)) { $cReturn .= " <a href=\"" . $cNewAddress . ($iCurrentPage - 1) . "\">Back</a> || "; }

			if ($iCurrentPage) {
				$cReturn .= "Page <a href=\"" . $cAddress . "\">1</a>";
			} else {
				$cReturn .= "Page <a class=\"selectedPage\" href=\"" . $cAddress . "\">1</a>";
			}

			for ($i = 0; $i < $iAmount; $i++) {
				if ($i > 0) {
					if ($i % $iLimit == 0) {
						if ($iCurrentPage == $j) {
							$cReturn .= ", <a class=\"selectedPage\" href=\"" . $cNewAddress . $j . "\">" . $j . "</a>";
						} else {
							$cReturn .= ", <a href=\"" . $cNewAddress . $j . "\">" . $j . "</a>";
						}
						$j++;
					}
				}
			}

			//next
			if ($iCurrentPage < $iAmount) {
				if (($iCurrentPage + 1) <= $iAmount) {
					if (($iCurrentPage + 1) == 1) { $iCurrentPage = 1; }
					$cReturn .= " || <a href=\"" . $cNewAddress . ($iCurrentPage + 1) . "\">Next</a> || ";
				}
			}

			//last
			$cReturn .= " || <a href=\"" . $cNewAddress . ($j - 1) . "\">Last</a>";

			//close
			$cReturn .= "</div>";
		}

		$this->cPagination = $cReturn;
		return $this->cPagination;
	}

	/**
	 * Template::renderTemplate()
	 *
	 * @param bool $bEcho Echo the template rather than returning it
	 * @return mixed
	 */
	public function renderTemplate($bEcho = null, $bDebug = null) {
		$cReturn	= false;
		$cTemplate	= false;

		//remove the option of pages for templates
		if (strstr($this->oType->cTemplate, "http:")) { return false; }


		//since the template doesnt exist, yet somehow it has been checked
		if (!$this->oType->cTemplate) {
			$this->oType->setTemplate(); //this sometimes doesnt get called

			printRead($this->oType);

			if (!file_exists($this->oType->cTemplate)) { return $this->errorTemplate($this->oType->cTemplate); }
		}

		//its been checked
		if ($this->bFormAdded) {
			if ($this->oForms) {
				$this->oType->setVars('cForm', $this->oForms->fullForm($this->cTemplate));
				$this->oType->setVars("oForm", $this->oForms);
			} else { //this is incase you didnt add the form but your trying to call it
				$this->oType->setVars("cForm", false);
				$this->oType->setVars("oForm", false);
			}
		} else {
			$this->oType->setVars("cForm", false);
			$this->oType->setVars("oForm", false);
		}

		//layout folder better name than core
		if (($this->cSkin) && is_dir(SITEPATH . "/layout/" . $this->cSkin)) {
			$cLayoutPath	= "/layout/" . $this->cSkin . "/";
		} else if (is_dir(SITEPATH . "/layout")) {
			$cLayoutPath = "/layout/";
		} else {
			$cLayoutPath = "/core/";
		}

		//check for old style
		if (!file_exists(SITEPATH . "templates/structure.struct")) { //really old method
			if (!file_exists(SITEPATH . "layout/structure.struct")) { //newer method
				if (!isset($oHammer)) { $oHammer = Hammer::getHammer(); }
				$this->oType->setVars("oHammer", $oHammer);
			}
		}

		$this->oType->setVars("cExtraJS", $this->cExtraJS);
		$this->oType->setVars("cPagination", $this->cPagination);
		$this->oType->setVars('cJS', $this->cJS);

		//debug
		if ($bDebug) { $this->debugTemplates(); }

		//so that it does it before the others
		if ($this->oType) { return $this->oType->renderTemplate($bEcho); }

		//indented to show that stuff inside happens inside and then is cleaned after
		$cTemplate	= false;
		if (file_exists($this->cTemplate)) {
			if (!checkHeaders()) { ob_start(); }
				extract($this->aVars, EXTR_SKIP);
				include $this->cTemplate;
				$cTemplate	.= ob_get_contents();
				$cTemplate	.= $this->cJS;
				$cTemplate	.= $this->cExtraJS;

			//if there is a started ob
			if (!checkHeaders()) { if (ob_get_level()) { ob_end_clean(); }}
		} else {
			$cTemplate	= $this->errorTemplate($this->cTemplate);
		}
		$cReturn	= $cTemplate;

		//do we echo or return it
		if ($bEcho) {
			echo $cReturn;
			return false;
		} else {
			return $cReturn;
		}
	}

	/**
	 * Template::setSubTemplate()
	 *
	 * @param string $cTemplate
	 * @param bool $bCore
	 * @return
	 */
	public function setSubTemplate($cTemplate, $bCore = false) {
		//Layout
		if (($this->cSkin) && is_dir(SITEPATH . "/layout/" . $this->cSkin)) {
			$cLayoutFolder	= "/layout/" . $this->cSkin . "/";
		} else if (is_dir(SITEPATH . "/layout")) {
			$cLayoutFolder = "/layout/";
		} else {
			$cLayoutFolder = "/core/";
		}

		if ($bCore) {
			if ($this->cSkin) {
				$cSubTemplate	= SITEPATH . $cLayoutFolder . "templates/" . $this->cSkin . "/" . $cTemplate . ".tpl";
			} else {
				$cSubTemplate	= SITEPATH . $cLayoutFolder . "templates/" . $cTemplate . ".tpl";
			}
		} else {
			if ($this->cSkin) {
				$cSubTemplate	= PAGES . $this->cPage . "/templates/" . $this->cSkin . "/" . $cTemplate . ".tpl";
			} else {
				$cSubTemplate	= PAGES . $this->cPage . "/templates/" . $cTemplate . ".tpl";
			}
		}

		$this->aSubTemplates[] = $cSubTemplate;
	}

	/**
	 * Template::errorTemplate()
	 *
	 * @return
	 */
	private function errorTemplate($cCalled = false) {
		//Layout
		if (is_dir(SITEPATH . "/layout")) {
			$cLayoutFolder = "/layout/";
		} else {
			$cLayoutFolder = "/core/";
		}

		//this was making validator fail for some reason
		#header("HTTP/1.0 404 Not Found");
		http_response_code(404);
		if ($this->cError) {
			if (file_exists(SITEPATH . $cLayoutFolder . "templates/error.tpl")) {
				//open the buffer
				if (!checkHeaders()) { ob_start("ob_process"); }

				//Custom error page
				include SITEPATH . $cLayoutFolder . "templates/error.tpl";
				$cTemplate = ob_get_contents();

				//clean the buffer
				if (!checkHeaders()) { ob_end_clean(); }
			} else {
				$cTemplate	 = "<section id=\"error\">\n<header>\n";
				$cTemplate	.= "<h1>Error</h1>\n";
				$cTemplate	.= "</header>\n<article>\n";
				$cTemplate	.= $this->cError . "<br />";

				//this is incase the page doesnt exist
				switch($this->cPage){
					case "register": //Shall be changing register to be an actual page
						$cTemplate .= "This page might actually be <a href=\"/login/register/\">Here</a>";
						break;

					case "user": //Change this once the user module does actually exist
						$cTemplate .= "The user module hasn't been finished yet sorry";
						break;

					case "manager":
					default:
						$cTemplate .= "I have no idea where your trying to get to, but most likelly your trying to use a bug that doesn't exist";
						break;
				} // switch

				if (isset($_SERVER['HTTP_REFERER'])) {
					$cTemplate .= "<hr /><a href=\"" . $_SERVER['HTTP_REFERER'] . "\">Back</a>\n";
				} else {
					$cTemplate .= "<hr /><a href=\"/\">Back</a>\n";
				}

				$cTemplate	.= "</article>\n</section>\n";
			}

			return $cTemplate;
		} else {
			$cMessage = "Template Doesnt exist:";
			//all the extras to diagnose
			if ($this->cTemplate) {		$cMessage .= " Called: "			. $this->cTemplate; }
			if ($cCalled) { 			$cMessage .= "<br /> Template: "	. $cCalled; }
			if ($this->cPage) {			$cMessage .= "<br /> Page: "		. $this->cPage; }
			if ($this->cAction) {		$cMessage .= "<br /> Action: "		. $this->cAction; }
			if ($this->cChoice) {		$cMessage .= "<br /> Choice: "		. $this->cChoice; }
			if ($this->cError) { 		$cMessage .= "<br /> Error: " 		. $this->cError; }
			if ($this->cSiteCalled) { 	$cMessage .= "<br /> SiteCalled: " 	. $this->cSiteCalled; }

			//Params
			$cMessage .= "<br /> Params: " . print_r($this->aParams, true);

			throw new Spanner($cMessage, 2);
		}
	}

	/**
	 * Template::__destruct()
	 *
	 */
	public function __destruct() {
		$this->oHammer		= null;
		$this->oNails		= null;
		$this->oForms		= null;
		$this->cForm		= null;

		unset($this->oNails);
		unset($this->oForms);
		unset($this->cForm);
	}

	/**
	 * Template::getTemp()
	 *
	 * @return string
	 */
	public function getTemp() {
		$aReturn = array(
			$this->cTemplate,
			$this->cDefault
		);

		return $aReturn;
	}
}

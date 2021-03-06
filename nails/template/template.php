<?php
/**
 * Template
 *
 * @package
 * @author Keloran
 * @copyright Copyright (c) 2011
 * @version $Id$
 * @access public
 */
class Template extends Template_Abstract {
	static $oTemplate;

	private $oNails;
	private $oForms;
	protected $bDebug;
	private $oType;

	/**
	 * Template::__construct()
	 *
	 * @param array $aParams
	 * @param string $cSite
	 * @param string $cSkinSetting
	 */
	public function __construct($aParams, $cSite = false, $cSkinSetting = false, $bDebug = false) {
		//skin
		$this->cSkinSetting	= $cSkinSetting ?: "brand";

		//site
		if ($cSite) { $this->cSiteCalled	= $cSite; }

		//debug
		if ($bDebug) {
			$this->doDebug();
			$this->setDebug();
		}

		//params
		$this->aParams	= $aParams;
		$this->setParams($aParams);
		$this->createHammer();
	}

	/**
	 * Template::getInstance()
	 *
	 * @param array $aParams
	 * @param string $cSite
	 * @param string $cSkinSetting
	 * @return
	 */
	static function getInstance($aParams, $cSite = false, $cSkinSetting = false) {
		if (is_null(self::$oTemplate)) { self::$oTemplate = new Template($aParams, $cSite, $cSkinSetting); }

		return self::$oTemplate;
	}

	/**
	 * Template::setVars()
	 *
	 * @param string $cName
	 * @param mixed $mVar
	 * @return null
	 */
	public function setVars($cName, $mVar)	{
		if ($this->oType) { return $this->oType->setVars($cName, $mVar); }

		return false;
	}

	/**
	 * Template::setDebug()
	 *
	 * @return null
	 */
	public function setDebug() {
		$this->bDebug	= true;
	}

	/**
	 * Template::createHammer()
	 *
	 * @return null
	 */
	private function createHammer() {
		if (!$this->oHammer) { $this->oHammer = Hammer::getHammer(); }
	}

	/**
	 * Template::giveHammer()
	 *
	 * @return null
	 */
	private function giveHammer($oType = null) {
		if ($oType) {
			$oType->setVars("oHammer", $this->oHammer);
			$oType->setVars("Hammer", $this->oHammer);
		} else {
			if ($this->oType) {
				$this->oType->setVars("oHammer", $this->oHammer);
				$this->oType->setVars("Hammer", $this->oHammer);
			}
		}
	}

	/**
	 * Template::getMainPage()
	 *
	 * @param string $cDefault
	 * @param bool $bEcho
	 * @param array $aLayout
	 * @return string
	 */
	public function getMainPage($cDefault = null, $bEcho = null, $aLayout = null) {
		//open the object
		$oMain	= new Template_Content($this->aParams);

		//set the type for calling of render afterwards
		$this->oType	= $oMain;

		//set the debug
		if ($this->bDebug) { $oMain->doDebug(); }

		//no layout do the default
		if (!$aLayout) { $aLayout = array("<div id=\"mainArea\">","</div>\n"); }

		//set the default
		$oMain->setDefault($cDefault);
		$oMain->setTemplate($cDefault);
		$oMain->setLayout($aLayout);

		//set the vars
		$oMain->setVars("error", $this->cError);
		$this->giveHammer();

		//do the render
		$cReturn	 = $oMain->cStarter;
		$cReturn	.= $oMain->renderTemplate();
		$cReturn	.= $oMain->cEnder;

		//are we in echo
		if ($bEcho) { echo $cReturn; }

		//return the string
		return $cReturn;
	}

	/**
	 * Template::getContent()
	 *
	 * @param string $cTemplate
	 * @param bool $bEcho
	 * @return string
	 */
	public function getContent($cTemplate = null, $bEcho = null) {
		if ($this->oType instanceof Template_Content) {
			$oContent	= new Template_Content_Template($this->oType);

			//turn on debug
			if ($this->bDebug) { $oContent->doDebug(); }

			//fix the vars in case layout was called outside of the set of type
			$this->fixVars($this->oType->aVars, $oContent);
			$this->fixVars($this->aVars, $oContent);

			//set the type
			$this->oType	= $oContent;

			//set the params
			$oContent->setParams($this->aParams);

			//set the template
			$oContent->setTemplate($cTemplate);

			//add the form if it was added afterwards
			if ($this->oForm) { $this->oType->oForm = $this->oForm; }

			return $oContent;
		}
	}

	/**
	 * Template::getStructure()
	 *
	 * @param string $cStructure
	 * @param bool $bEcho
	 * @return string
	 */
	public function getStructure($cStructure = null, $bEcho = null) {
		$oStruct	= Template_Structure::getInstance($this->aParams);

		//set the type
		$this->oType	= $oStruct;

		//set debug
		if ($this->bDebug) { $oStruct->doDebug(); }

		//set the template
		$oStruct->setTemplate($cStructure);
		$this->giveHammer();

		return $oStruct->renderTemplate($bEcho);
	}

	/**
	 * Template::getLayout()
	 *
	 * @param string $cLayout
	 * @param bool $bEcho
	 * @return string
	 */
	public function getLayout($cLayout = null, $bEcho = null) {
		$oLayout	= new Template_Layout($this->aParams);

		//set the type
		$this->oParentType	= $oLayout;

		//set debug
		if ($this->bDebug) { $oLayout->doDebug(); }

		//set the template
		$oLayout->setTemplate($cLayout);
		$this->giveHammer($oLayout);

		//echo or not
		return $oLayout->renderTemplate($bEcho);
	}

	/**
	 * Template::setLayout()
	 *
	 * @param string $cTemplate
	 * @param bool $bEcho
	 * @return string
	 */
	public function setLayout($cTemplate = null, $bEcho = null) {
		if ($this->oParentType instanceof Template_Layout) {
			$oLayout	= new Template_Layout_Template($this->oType);

			//turn on debug
			if ($this->bDebug) { $oLayout->doDebug(); }

			//fix the vars in case layout was called outside of the set of type
			$this->fixVars($this->oParentType->aVars, $oLayout);
			$this->fixVars($this->aVars, $oLayout);

			//set the type
			$this->oType	= $oLayout;

			//set the params just incase
			$oLayout->setParams($this->aParams);

			//set the template
			$oLayout->setTemplate($cTemplate);

			//see if i can strip the parents
			$oLayout->stripHammer();

			return $oLayout;
		} else {
			throw new Spanner("Template parent isnt a layout, " . $cTemplate, 300);
		}

		return false;
	}

	/**
	 * Template::setEmailTemplate()
	 *
	 * @param string $cTemplate
	 * @return string
	 */
	public function setEmailTemplate($cTemplate = null)  {
		$oTemplate	= new Template_Email($this->aParams);

		//set the type
		$this->oType	= $oTemplate;

		//set debug
		if ($this->bDebug) { $oTemplate->doDebug(); }

		//set the template
		$oTemplate->setTemplate($cTemplate);
		$this->giveHammer();

		return $oTemplate->renderTemplate();
	}

	/**
	 * Template::addForm()
	 *
	 * @param mixed $mObject
	 * @return object
	 */
	public function addForm($mObject = null) {
		if (is_object($mObject)) {
			$this->oForm	= $mObject;
		} else {
			$this->oForm	= $this->oType->addForm($mObject);
		}

		return $this->oForm;
	}

	/**
	 * Template::renderTemplate()
	 *
	 * @param bool $bEcho
	 * @return string
	 */
	public function renderTemplate($bEcho = null) {
		if ($this->oType) { return $this->oType->renderTemplate($bEcho); }

		return false;
	}

	/**
	 * Template::__destruct()
	 *
	 */
	public function __destruct() {
		$this->oHammer	= null;
		$this->oNails	= null;
		$this->oForms	= null;
		$this->cForm	= null;
		$this->oType	= null;

		unset($this->oNails);
		unset($this->oForms);
		unset($this->oType);
	}

	#LEGACY
	/**
	 * Template::setCoreTemplate()
	 *
	 * @param string $cTemplate
	 * @return object
	 */
	public function setCoreTemplate($cTemplate) {
		return $this->setLayout($cTemplate);
	}

	/**
	 * Template::setTemplate()
	 *
	 * @param string $cTemplate
	 * @return object
	 */
	public function setTemplate($cTemplate = null) {
		if ($this->oType instanceof Template_Content) { return $this->getContent($cTemplate); }

		return false;
	}

	/**
	 * Template::getValidate()
	 *
	 * @return object
	 */
	public function getValidate() {
		return $this->oType->addValidate();
	}

	/**
	 * Template::getCore()
	 *
	 * @param string $cTemplate
	 * @param bool $bEcho
	 * @return object
	 */
	public function getCore($cTemplate = null, $bEcho = null) {
		return $this->getLayout($cTemplate, $bEcho);
	}
}
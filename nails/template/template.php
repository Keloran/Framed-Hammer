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
	public function __construct($aParams, $cSite = false, $cSkinSetting = false) {
		//skin
		$this->cSkinSetting	= $cSkinSetting ?: "brand";

		//site
		if ($cSite) { $this->cSiteCalled	= $cSite; }

		//params
		$this->aParams	= $aParams;
		$this->setParams($aParams);
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

		//set the debug
		if ($this->bDebug) { $oMain->doDebug(); }

		//set the default
		$oMain->setDefault($cDefault);
		$oMain->setTemplate($cDefault);
		$oMain->setLayout($aLayout);

		$oMain->setVars("error", $this->cError);

		//set the type for calling of render afterwards
		$this->oType	= $oMain;

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
	 * Template::getStructure()
	 *
	 * @param string $cStructure
	 * @param bool $bEcho
	 * @return string
	 */
	public function getStructure($cStructure = null, $bEcho = null) {
		$oStruct	= new Template_Structure($this->aParams);

		//set debug
		if ($this->bDebug) { $oStruct->doDebug(); }

		//set the template
		$oStruct->setTemplate($cStructure);
		$oStruct->createHammer();

		$this->oType	= $oStruct;

		if ($bEcho) { echo $oStruct->renderTemplate(); }

		return $oStruct->renderTemplate();
	}


}
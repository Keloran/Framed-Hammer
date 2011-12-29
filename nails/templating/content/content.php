<?php
class Template_Content extends Template_Abstract {
	//Traits
	use Browser;

	public $aParams;
	public $cTemplate;

	protected $aVars;

	//split stuff, e.g. page/action
	protected $cPage;
	protected $cAction;
	protected $cChoice;
	protected $aOthers;
	private $cSkin;

	//instance
	static $oContent;

	/**
 	* Template_Layout::__construct()
 	*
 	* @param mixed $mParams
 	* @param string $cTemplate
 	*/
	public function __construct($mParams, $cTemplate = null) {
		$this->setParams($mParams);

		//since we have a template set
		if ($cTemplate) {
			$this->setTemplate($cTemplate);
		}
	}

	public function setDefaultLayout($aDefault = false) {
		if ($aDefault) {
			$this->cStarter	= $aDefault[0];
			$this->cEnder	= $aDefault[1];
		} else {
			$this->cStarter = "<div id=\"mainArea\">\n";
			$this->cEnder	= "</div>\n";
		}
	}

	/**
 	* Template_Layout::setTemplate()
 	*
 	* @param string $cTemplate
 	* @return string
 	*/
	public function setTemplate($cDefault = null) {
		$cReturn	= $this->cStarter;
		$cPage		= false;

		//page
		$cPage = $this->getPage($cDefault);
		$this->cDefault	= $cDefault;

		//action
		if ($this->cAction) {
			$cAction	= $this->getAction();
			$cPage		= $cAction ? $cAction : $cPage;
		}

		//choice
		if ($this->cChoice) {
			$cChoice	= $this->getChoice();
			$cPage		= $cChoice ? $cChoice : $cPage;
		}

		//others
		if (isset($this->extraParams) && ($this->extraParams)) {
			$cOther		= $this->getOther();
			$cPage		= $cOther ? $cOther : $cPage;
		}

		$this->setVars("defaultPage", $cDefault);

		if ($cPage) {
			$this->cTemplate	= $cPage;
		} else{
			 $this->cTemplate	= $this->cError;
		}

		return $this->cReturn;
	}
}
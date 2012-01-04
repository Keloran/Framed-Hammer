<?php
/**
 * Template_Content
 *
 * @package
 * @author Keloran
 * @copyright Copyright (c) 2011
 * @version $Id$
 * @access public
 */
class Template_Content extends Template_Abstract {
	public $bTraverse = true;

	/**
	 * Template_Content::__construct()
	 *
	 * @param mixed $mParms
	 * @param string $cTemplate
	 */
	public function __construct($mParams, $cTemplate = null) {
		$this->setParams($mParams);

		if ($cTemplate) { $this->setTemplate($cTemplate); }
	}

	/**
	 * Template_Content::setLayout()
	 *
	 * @param array $aLayout
	 * @return null
	 */
	public function setLayout($aLayout = null) {
		if ($aLayout) {
			$this->cStarter	= $aLayout[0];
			$this->cEnder	= $aLayout[1];
		}
	}

	/**
	 * Template_Content::setTemplate()
	 *
	 * @param string $cDefault
	 * @return string
	 */
	public function setTemplate($cDefault = null) {
		if ($this->cDefault) { $cDefault = $this->cDefault; }

		if (!$this->bTraverse) {
			printRead(array(
				"Template"	=> $cDefault,
				"Caller"	=> $this->getCaller()
			));
			die();
		}

		//so that i can debug
		$cPage		= false;
		$cAction	= false;
		$cChoice	= false;
		$cOther		= false;

		$cPage			= $this->getPage($cDefault);
		$this->cDefault = $cDefault;
		$this->addDebug("Page", $cPage);
		$this->addDebug("Default", $cDefault);

		//is there an action
		if ($this->cAction) {
			$cAction	= $this->getAction();
			$cPage		= $cAction ?: $cPage;
		}
		$this->addDebug("Action", $cAction);
		$this->addDebug("Action Page", $cPage);

		//is there a choice
		if ($this->cChoice) {
			$cChoice	= $this->getChoice();
			$cPage		= $cChoice ?: $cPage;
		}
		$this->addDebug("Choice", $cChoice);
		$this->addDebug("Choice Page", $cPage);

		//if there are others
		if (isset($this->extraParams) && $this->extraParams) {
			$cOther	= $this->getOther();
			$cPage	= $cOther ?: $cPage;
		}
		$this->addDebug("Other", $cOther);
		$this->addDebug("Other Page", $cPage);

		$this->setVars("defaultPage", $cDefault);

		if ($cPage) {
			$this->cTemplate	= $cPage;
		} else {
			$this->cTemplate	= $this->cError;
		}

		$this->cCaller	= "content";

		return $this->cTemplate;
	}

	/**
	 * Template_Content::setDefault()
	 *
	 * @param string $cDefault
	 * @return null
	 */
	public function setDefault($cDefault = null) {
		$this->cDefault	= $cDefault;
	}
}
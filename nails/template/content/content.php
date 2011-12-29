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

	public function setTemplate($cDefault = null) {
		$cPage			= $this->getPage($cDefault);
		$this->cDefault = $cDefault;

		//is there an action
		if ($this->cAction) {
			$cAction	= $this->getAction();
			$cPage		= $cAction ?: $cPage;
		}

		//is there a choice
		if ($this->cChoice) {
			$cChoice	= $this->getChoice();
			$cPage		= $cChoice ?: $cPage;
		}

		//if there are others
		if (isset($this->extraParams) && $this->extraParams) {
			$cOther	= $this->getOther();
			$cPage	= $cOther ?: $cPage;
		}

		$this->setVars("defaultPage", $cDefault);

		if ($cPage) {
			$this->cTemplate	= $cPage;
		} else {
			$this->cTemplate	= $this->cError;
		}

		return $this->cTemplate;
	}
}
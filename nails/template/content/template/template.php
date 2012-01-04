<?php
/**
 * Template_Content_Template
 *
 * @package
 * @author keloran
 * @copyright Copyright (c) 2012
 * @version $Id$
 * @access public
 */
class Template_Content_Template extends Template_Abstract {
	private $oContent;

	/**
	 * Template_Content_Template::__construct()
	 *
	 * @param object $oTemplate
	 * @param string $cTemplate
	 */
	public function __construct($oTemplate, $cTemplate = false) {
		$this->oContent	= $oTemplate;

		$this->setParams($oTemplate->aParams);

		if ($cTemplate) { $this->setTemplate($cTemplate); }
	}

	/**
	 * Template_Content_Template::setTemplate()
	 *
	 * @param string $cTemplate
	 * @return string
	 */
	public function setTemplate($cTemplate = null) {
		$cReturn	= false;
		$cCaller	= $this->getCaller();
		$this->addDebug("Caller", $cCaller);

		$this->cTemplate	= $cTemplate;

		return $cTemplate;
	}
}
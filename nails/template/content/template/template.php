<?php
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

	public function setTemplate($cTemplate) {

	}
}
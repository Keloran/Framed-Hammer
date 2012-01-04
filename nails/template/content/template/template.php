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

		//no template used, so use caller
		if (!$cTemplate) { $cTemplate	= $cCaller; }

		//Original
		$cLayout	= PAGES . $cCaller . "/templates/" . $cTemplate . ".tpl";
		$this->addDebug("Original", $cLayout);

		//page
		if ($this->cPage) {
			$bFound	= false;
			if (file_exists(PAGES . $this->cPage . "/templates/" . $cCaller . ".tpl")) {
				$cLayout	= PAGES . $this->cPage . "/templates/" . $cCaller . ".tpl";
				$bFound		= true;
			}
			if (!$bFound && file_exists(PAGES . $this->cPage . "/templates/" . $cTemplate . ".tpl")) {
				$cLayout	= PAGES . $this->cPage . "/templates/" . $cTemplate . ".tpl";
				$bFound		= true;
			}
			$this->addDebug("Page", $cLayout);
		}

		//action
		if ($this->cAction) {
			$bFound	= false;
			if (file_exists(PAGES . $this->cPage . "/" . $this->cAction . "/templates/" . $cCaller . ".tpl")) {
				$cLayout	= PAGES . $this->cPage . "/" . $this->cAction . "/templates/" . $cCaller . ".tpl";
				$bFound		= true;
			}
			if (!$bFound && file_exists(PAGES . $this->cPage . "/" . $this->cAction . "/templates/" . $cTemplate . ".tpl")) {
				$cLayout	= PAGES . $this->cPage . "/" . $this->cAction . "/templates/" . $cTemplate . ".tpl";
				$bFound		= true;
			}
			$this->addDebug("Action", $cLayout);
		}

		//choice
		if ($this->cChoice) {
			$bFound	= false;
			if (file_exists(PAGES . $this->cPage . "/" . $this->cAction . "/" . $this->cChoice . "/templates/" . $cCaller . ".tpl")) {
				$cLayout	= PAGES . $this->cPage . "/" . $this->cAction . "/" . $this->cChoice . "/templates/" . $cCaller . ".tpl";
				$bFound		= true;
			}
			if (!$bFound && file_exists(PAGES . $this->cPage . "/" . $this->cAction . "/" . $this->cChoice . "/templates/" . $cTemplate . ".tpl")) {
				$cLayout	= PAGES . $this->cPage . "/" . $this->cAction . "/" . $this->cChoice . "/templates/" . $cTemplate . ".tpl";
				$bFound		= true;
			}
			$this->addDebug("Choice", $cLayout);
		}

		//last check to make sure
		if (!file_exists($cLayout)) { $this->cError = "No Template for " . $cCaller . " found, template requested was " . $cTemplate; }

		//error found
		if ($this->cError && $this->bDebug) { $this->debugTemplates(); }

		$this->cTemplate	= $cLayout;
		$this->cCaller		= "content_template";

		return $cLayout;
	}
}
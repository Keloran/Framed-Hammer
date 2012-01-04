<?php
/**
 * Template_Layout
 *
 * @package
 * @author Keloran
 * @copyright Copyright (c) 2011
 * @version $Id$
 * @access public
 */
class Template_Layout_Template extends Template_Abstract {
	private $oLayout;

	/**
	 * Template_Layout_Template::__construct()
	 *
	 * @param object $oTemplate
	 * @param string $cTemplate
	 */
	public function __construct($oTemplate, $cTemplate = false) {
		$this->oLayout	= $oTemplate;

		$this->setParams($oTemplate->aParams);

		if ($cTemplate) { $this->setTemplate($cTemplate); }
	}

	/**
	 * Template_Layout_Template::setTemplate()
	 *
	 * @param string $cTemplate
	 * @return string
	 */
	public function setTemplate($cTemplate = null) {
		$cReturn	= false;
		$cCaller	= $this->getCaller();
		$this->addDebug("Caller", $cCaller);

		//no template given but we know its parent
		if (!$cTemplate) {
			if ($this->oLayout->cLayout) { 			$cTemplate = $this->oLayout->cLayout; }
			if ($this->oLayout->cLayoutTemplate) {	$cTemplate = $this->oLayout->cLayoutTemplate; }
		}

		//dont need the layout object now so kill it
		$this->oLayout	= null;

		$cLayout	= SITEPATH . "/layout/" . $cCaller . "/templates/" . $cCaller . ".tpl";
		$this->addDebug("Original Layout Template", $cLayout);

		//page
		if ($this->cPage) {
			if (file_exists(PAGES . $this->cPage . "/layout/templates/" . $cCaller . ".tpl")) {
				$cLayout = PAGES . $this->cPage . "/layout/templates/" . $cCaller . ".tpl";
			}
			if (file_exists(PAGES . $this->cPage . "/layout/templates/" . $cTemplate . ".tpl")) {
				$cLayout = PAGES . $this->cPage . "/layout/templates/" . $cTemplate . ".tpl";
			}
			if (file_exists(PAGES . $this->cPage . "/layout/" . $cCaller . "/templates/" . $cCaller . ".tpl")) {
				$cLayout = PAGES . $this->cPage . "/layout/" . $cCaller . "/templates/" . $cCaller . ".tpl";
			}
			if (file_exists(PAGES . $this->cPage . "/layout/" . $cCaller . "/templates/" . $cTemplate . ".tpl")) {
				$cLayout = PAGES . $this->cPage . "/layout/" . $cCaller . "/templates/" . $cTemplate . ".tpl";
			}
			$this->addDebug("Page Layout Template", $cLayout);
		}

		//action
		if ($this->cAction) {
			if (file_exists(PAGES . $this->cPage . "/" . $this->cAction . "/layout/templates/" . $cCaller . ".tpl")) {
				$cLayout = PAGES . $this->cPage . "/" . $this->cAction . "/layout/templates/" . $cCaller . ".tpl";
			}
			if (file_exists(PAGES . $this->cPage . "/" . $this->cAction . "/layout/templates/" . $cTemplate . ".tpl")) {
				$cLayout = PAGES . $this->cPage . "/" . $this->cAction . "/layout/templates/" . $cTemplate . ".tpl";
			}
			if (file_exists(PAGES . $this->cPage . "/" . $this->cAction . "/layout/" . $cCaller . "/templates/" . $cCaller . ".tpl")) {
				$cLayout = PAGES . $this->cPage . "/" . $this->cAction . "/layout/" . $cCaller . "/templates/" . $cCaller . ".tpl";
			}
			if (file_exists(PAGES . $this->cPage . "/" . $this->cAction . "/layout/" . $cCaller . "/templates/" . $cTemplate . ".tpl")) {
				$cLayout = PAGES . $this->cPage . "/" . $this->cAction . "/layout/" . $cCaller . "/templates/" . $cTemplate . ".tpl";
			}

			$this->addDebug("Action Layout Template", $cLayout);
		}

		//choice
		if ($this->cChoice) {
			if (file_exists(PAGES . $this->cPage . "/" . $this->cAction . "/layout/templates/" . $cCaller . ".tpl")) {
				$cLayout = PAGES . $this->cPage . "/" . $this->cAction . "/layout/templates/" . $cCaller . ".tpl";
			}
			if (file_exists(PAGES . $this->cPage . "/" . $this->cAction . "/layout/templates/" . $cTemplate . ".tpl")) {
				$cLayout = PAGES . $this->cPage . "/" . $this->cAction . "/layout/templates/" . $cTemplate . ".tpl";
			}
			if (file_exists(PAGES . $this->cPage . "/" . $this->cAction . "/layout/" . $cCaller . "/templates/" . $cCaller . ".tpl")) {
				$cLayout = PAGES . $this->cPage . "/" . $this->cAction . "/layout/" . $cCaller . "/templates/" . $cCaller . ".tpl";
			}
			if (file_exists(PAGES . $this->cPage . "/" . $this->cAction . "/layout/" . $cCaller . "/templates/" . $cTemplate . ".tpl")) {
				$cLayout = PAGES . $this->cPage . "/" . $this->cAction . "/layout/" . $cCaller . "/templates/" . $cTemplate . ".tpl";
			}

			$this->addDebug("Choice Layout Template", $cLayout);
		}

		//last check just incase
		if (!file_exists($cLayout)) { $this->cError = "No Template for " . $cCaller . " found, template requested was " . $cTemplate; }

		//is tehre an error and is debug turned on
		if ($this->cError && $this->bDebug) { $this->debugTemplates(); }

		$this->cTemplate	= $cLayout;
		$this->cCaller		= "layout_template";

		return $cLayout;
	}
}
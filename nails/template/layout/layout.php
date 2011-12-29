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
class Template_Layout extends Template_Abstract {
	/**
	 * Template_Layout::__construct()
	 *
	 * @param mixed $mParams
	 * @param string $cTemplate
	 */
	public function __construct($mParams, $cTemplate = false) {
		$this->setParams($mParams);

		if ($cTemplate) { $this->setTemplate($cTemplate); }
	}

	/**
	 * Template_Layout::setTemplate()
	 *
	 * @param string $cTemplate
	 * @return string
	 */
	public function setTemplate($cTemplate = null) {
		$cReturn	= false;
		$cCaller	= $this->getCaller();
		$this->addDebug("Caller", $cCaller);

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

		$this->cTemplate = $cLayout;

		return $cLayout;
	}
}
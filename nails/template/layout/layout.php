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
	public function __construct($mParams, $cTemplate = null) {
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
		$cCaller				= $this->getCaller();
		$this->cLayout			= $cCaller;
		$this->cLayoutTemplate	= $cTemplate;

		if ($cTemplate) {
			$cLayout	= SITEPATH . "/layout/" . $cTemplate . "/" . $cTemplate . ".php";
		} else {
			$cLayout	= SITEPATH . "/layout/" . $cCaller . "/" . $cCaller . ".php";
		}
		$this->addDebug("Original Layout", $cLayout);

		//page
		if ($this->cPage) {
			if (file_exists(PAGES . $this->cPage . "/layout/" . $cCaller . "/" . $cCaller . ".php")) {
				$cLayout = PAGES . $this->cPage . "/layout/" . $cCaller . "/" . $cCaller . ".php";
			}
			if (file_exists(PAGES . $this->cPage . "/layout/" . $cTemplate . "/" . $cTemplate . ".php")) {
				$cLayout = PAGES . $this->cPage . "/layout/" . $cTemplate . "/" . $cTemplate . ".php";
			}

			$this->addDebug("Page Layout", $cLayout);
		}

		//action
		if ($this->cAction) {
			if (file_exists(PAGES . $this->cPage . "/" . $this->cAction . "/layout/" . $cCaller . "/" . $cCaller . ".php")) {
				$cLayout = PAGES . $this->cPage . "/" . $this->cAction . "/layout/" . $cCaller . "/" . $cCaller . ".php";
			}
			if (file_exists(PAGES . $this->cPage . "/" . $this->cAction . "/layout/" . $cTemplate . "/" . $cTemplate . ".php")) {
				$cLayout = PAGES . $this->cPage . "/" . $this->cAction . "/layout/" . $cTemplate . "/" . $cTemplate . ".php";
			}

			$this->addDebug("Action Layout", $cLayout);
		}

		//choice
		if ($this->cChoice) {
			if (file_exists(PAGES . $this->cPage . "/" . $this->cAction . "/" . $this->cChoice . "/layout/" . $cCaller . "/" . $cCaller . ".php")) {
				$cLayout = PAGES . $this->cPage . "/" . $this->cAction . "/" . $this->cChoice . "/layout/" . $cCaller . "/" . $cCaller . ".php";
			}
			if (file_exists(PAGES . $this->cPage . "/" . $this->cAction . "/" . $this->cChoice . "/layout/" . $cTemplate . "/" . $cTemplate . ".php")) {
				$cLayout = PAGES . $this->cPage . "/" . $this->cAction . "/" . $this->cChoice . "/layout/" . $cTemplate . "/" . $cTemplate . ".php";
			}

			$this->addDebug("Choice Layout", $cLayout);
		}

		//last check
		if (!file_exists($cLayout)) { $this->cError = "Layout doesn't seem to exist at all for " . $cCaller . " looking for " . $cTemplate; }

		//is debug turned on
		if ($this->cError && $this->bDebug) { $this->debugTemplates(); }

		$this->cTemplate 	= $cLayout;
		$this->cCaller		= "layout";

		return $cLayout;
	}
}
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
		$bFound		= false;

		//new style caller
		$cCallerLayout	= SITEPATH . "/layout/" . $cCaller . "/templates/" . $cCaller . ".tpl";
		$this->addDebug("Original Layout Template with Caller", $cCallerLayout);

		//new style dictated template
		$cTemplateLayout	= SITEPATH . "/layout/" . $cCaller . "/templates/" . $cTemplate . ".tpl";
		$this->addDebug("Original Layout Template", $cTemplateLayout);

		//old style caller
		$cOldStyleCaller	= SITEPATH . "/layout/templates/" . $cCaller . ".tpl";
		$this->addDebug("Old Style Original Layout Template with Caller", $cOldStyleCaller);

		//old style with dictated template
		$cOldStyleLayout	= SITEPATH . "/layout/templates/" . $cTemplate . ".tpl";
		$this->addDebug("Old Style Original Layout Template", $cOldStyleLayout);

		//set teh default layout
		if (!$cTemplate) {
			$cLayout 	= $cCallerLayout;
			$cLayoutO	= $cOldStyleLayout;
		} else {
			$cLayout 	= $cTemplateLayout;
			$cLayoutO	= $cOldStyleCaller;
		}
		$this->addDebug("Default Layout Template", $cLayout);

		//no template given but we know its parent
		if (!$cTemplate) {
			if ($this->oLayout->cLayout) { 			$cTemplate = $this->oLayout->cLayout; }
			if ($this->oLayout->cLayoutTemplate) {	$cTemplate = $this->oLayout->cLayoutTemplate; }
		}

		//dont need the layout object now so kill it
		$this->oLayout	= null;

		//page
		if ($this->cPage) {
			$bFound	= false;
			if (file_exists(PAGES . $this->cPage . "/layout/templates/" . $cCaller . ".tpl")) {
				$cLayout	= PAGES . $this->cPage . "/layout/templates/" . $cCaller . ".tpl";
				$bFound		= true;
			}
			if (!$bFound && file_exists(PAGES . $this->cPage . "/layout/templates/" . $cTemplate . ".tpl")) {
				$cLayout	= PAGES . $this->cPage . "/layout/templates/" . $cTemplate . ".tpl";
				$bFound		= true;
			}
			if (!$bFound && file_exists(PAGES . $this->cPage . "/layout/" . $cCaller . "/templates/" . $cCaller . ".tpl")) {
				$cLayout 	= PAGES . $this->cPage . "/layout/" . $cCaller . "/templates/" . $cCaller . ".tpl";
				$bFound		= true;
			}
			if (!$bFound && file_exists(PAGES . $this->cPage . "/layout/" . $cCaller . "/templates/" . $cTemplate . ".tpl")) {
				$cLayout 	= PAGES . $this->cPage . "/layout/" . $cCaller . "/templates/" . $cTemplate . ".tpl";
				$bFound		= true;
			}
			$this->addDebug("Page Layout Template", $cLayout);
		}

		//action
		if ($this->cAction) {
			$bFound	= false;
			if (file_exists(PAGES . $this->cPage . "/" . $this->cAction . "/layout/templates/" . $cCaller . ".tpl")) {
				$cLayout	= PAGES . $this->cPage . "/" . $this->cAction . "/layout/templates/" . $cCaller . ".tpl";
				$bFound		= true;
			}
			if (!$bFound && file_exists(PAGES . $this->cPage . "/" . $this->cAction . "/layout/templates/" . $cTemplate . ".tpl")) {
				$cLayout 	= PAGES . $this->cPage . "/" . $this->cAction . "/layout/templates/" . $cTemplate . ".tpl";
				$bFound		= true;
			}
			if (!$bFound && file_exists(PAGES . $this->cPage . "/" . $this->cAction . "/layout/" . $cCaller . "/templates/" . $cCaller . ".tpl")) {
				$cLayout 	= PAGES . $this->cPage . "/" . $this->cAction . "/layout/" . $cCaller . "/templates/" . $cCaller . ".tpl";
				$bFound		= true;
			}
			if (!$bFound && file_exists(PAGES . $this->cPage . "/" . $this->cAction . "/layout/" . $cCaller . "/templates/" . $cTemplate . ".tpl")) {
				$cLayout 	= PAGES . $this->cPage . "/" . $this->cAction . "/layout/" . $cCaller . "/templates/" . $cTemplate . ".tpl";
				$bFound		= true;
			}

			$this->addDebug("Action Layout Template", $cLayout);
		}

		//choice
		if ($this->cChoice) {
			$bFound	= false;
			if (file_exists(PAGES . $this->cPage . "/" . $this->cAction . "/layout/templates/" . $cCaller . ".tpl")) {
				$cLayout 	= PAGES . $this->cPage . "/" . $this->cAction . "/layout/templates/" . $cCaller . ".tpl";
				$bFound		= true;
			}
			if (!$bFound && file_exists(PAGES . $this->cPage . "/" . $this->cAction . "/layout/templates/" . $cTemplate . ".tpl")) {
				$cLayout 	= PAGES . $this->cPage . "/" . $this->cAction . "/layout/templates/" . $cTemplate . ".tpl";
				$bFound		= true;
			}
			if (!$bFound && file_exists(PAGES . $this->cPage . "/" . $this->cAction . "/layout/" . $cCaller . "/templates/" . $cCaller . ".tpl")) {
				$cLayout 	= PAGES . $this->cPage . "/" . $this->cAction . "/layout/" . $cCaller . "/templates/" . $cCaller . ".tpl";
				$bFound		= true;
			}
			if (!$bFound && file_exists(PAGES . $this->cPage . "/" . $this->cAction . "/layout/" . $cCaller . "/templates/" . $cTemplate . ".tpl")) {
				$cLayout 	= PAGES . $this->cPage . "/" . $this->cAction . "/layout/" . $cCaller . "/templates/" . $cTemplate . ".tpl";
				$bFound		= true;
			}

			$this->addDebug("Choice Layout Template", $cLayout);
		}

		//Now do the others
		$cPath1 = false;
		$cPath2 = false;
		if ($this->iExtras) {
			$bFound	= false;
			for ($i = $this->iExtras; $i > 0; $i--) {
				$cPath = PAGES . $this->cPage . "/" . $this->cAction . "/" . $this->cChoice;
				for ($j = 1; $j < ($i + 1); $j++) {
					$cParam = "cParam" . $j;
					$cPath .= "/" . $this->$cParam;
					$aExtras[] = $cPath;
				}
				$cPath = $cPath . "/layout/templates/" . $cTemplate . ".tpl";

				if (file_exists($cPath)) {
					$cLayout	= $cPath;
					$bFound		= true;
				}

				//since we dont need to go further down the chain
				if ($bFound == true) { break; }
			}
		}

		//last check just incase
		if (!file_exists($cLayout)) {
			if (!file_exists($cLayoutO)) {
				$this->cError = "No Template for " . $cCaller . " found, template requested was " . $cTemplate;
			} else {
				$cLayout = $cLayoutO;
			}
		}

		//is tehre an error and is debug turned on
		if ($this->cError && $this->bDebug) { $this->debugTemplates(); }

		$this->cTemplate	= $cLayout;
		$this->cCaller		= "layout_template";

		return $cLayout;
	}
}
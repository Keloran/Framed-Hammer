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
		$cLayout	= PAGES . $cTemplate . "/templates/" . $cTemplate . ".tpl";
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
				}

				$cPath1 = $cPath . "/templates/" . $cCaller . ".tpl";
				$cPath2 = $cPath . "/templates/" . $cTemplate . ".tpl";

				if (file_exists($cPath1))				 {
					$cLayout	= $cPath1;
					$bFound		= true;
				}
				if (!$bFound && file_exists($cPath2)) {
					$cLayout	= $cPath2;
					$bFound		= true;
				}

				//since we dont need to go further down the chain
				if ($bFound == true) { break; }
			}
		}

		printRead($this->iExtras);
		printRead($aExtras);
		printRead($cLayout);
		printRead($cCaller);
		printRead($cPath1);
		printRead($cPath2);
		die();

		//last check to make sure
		if (!file_exists($cLayout)) { $this->cError = "No Template for " . $cCaller . " found, template requested was " . $cTemplate; }

		//error found
		if ($this->cError && $this->bDebug) { $this->debugTemplates(); }

		$this->cTemplate	= $cLayout;
		$this->cCaller		= "content_template";

		return $cLayout;
	}
}
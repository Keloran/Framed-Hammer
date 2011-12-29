<?php
/**
 * Template_Structure
 *
 * @package
 * @author Keloran
 * @copyright Copyright (c) 2011
 * @version $Id$
 * @access public
 */
class Template_Structure extends Template_Abstract {
	use Address, Cookie;

	/**
	 * Template_Structure::__construct()
	 *
	 * @param array $aParams
	 */
	function __construct($aParams) {
		$this->setParams($aParams);
		$this->aSetParams	= $aParams;
	}

	/**
	 * Template_Structure::setTemplate()
	 *
	 * @param string $cTemplate
	 * @return string
	 */
	public function setTemplate($cTemplate = null) {
		$this->cNamedTemplate	= $cTemplate;
		$this->cSetTemplate		= $cTemplate;
		$cLayout				= false;

		$bMobile	= $this->mobileBrowser(false, true);
		$bUseNormal	= $this->getCookie("useNormal") ?: false;

		//do we use the normal layout or the mobile one
		$this->bNormal	= true;
		if ($bMobile) {
			if ($bUseNormal) {
				$this->bNormal	= true;
			} else {
				$this->bNormal	= false;
			}
		}

		//original structure name
		$cOriginal			= "structure.struct";
		$this->addDebug("Default Structure", $cOriginal);

		$cOriginalMobile	= "mobile.struct";
		$this->addDebug("Default Mobile Structure", $cOriginalMobile);

		if ($cTemplate) {
			$cStruct	= $cTemplate . ".struct";
			$cMobile	= $cTemplate . "." . $cOriginalMobile;
		} else {
			$cStruct	= $cOriginal;
			$cMobile	= $cOriginalMobile;
		}
		$this->cStruct			= $cStruct;
		$this->cMobileStruct	= $cMobile;
		$this->addDebug("Structure", $cStruct);
		$this->addDebug("Mobile Structure", $cMobile);


		//do we have a brand specific layout
		$bBrand	= false;
		if (isset($this->cBrand)) { $bBrand = true; }
		$this->bBrand	= $bBrand;
		$this->addDebug("Branded", $bBrand);
		$this->addDebug("Brand", $this->cBrand);

		//do we have a language specific layout
		$bLanguage	= false;
		if (isset($this->cLanguage)) { $bLanguage = true; }
		$this->bLanguage = $bLanguage;
		$this->addDebug("Languaged", $bLanguage);
		$this->addDebug("Language", $this->cLanguage);

		//do we have a filter specific layout
		$bFilter	= false;
		if (isset($this->cFilter)) { $bFilter = true; }
		$this->addDebug("Filtered", $bFilter);
		$this->addDebug("Filter", $this->cFilter);

		$cLayout	= $this->getLayout($bFilter);

		$this->cTemplate = $cLayout;

		return $this->cTemplate;
	}

	/**
	 * Template_Structure::getBrand()
	 *
	 * @param bool $bFilter
	 * @return string
	 */
	private function getBrand($bFilter = false) {
		$cReturn	= SITEPATH . "/layout/" . $this->cBrand . "/";
		if ($bFilter) { $cReturn .= $this->cFilter . "/"; }

		//is there a page
		if ($this->cPage) {
			$cReturn = PAGES . $this->cPage . "/layout/" . $this->cBrand . "/";
			if ($bFilter) { $cReturn .= $cReturn .= $this->cFilter . "/"; }
		}

		//action
		if ($this->cAction) {
			$cReturn = PAGES . $this->cPage . "/" . $this->cAction . "/layout/" . $this->cBrand . "/";
			if ($bFilter) { $cReturn .= $this->cFilter . "/"; }
		}

		//choice
		if ($this->cChoice) {
			$cReturn = PAGES . $this->cPage . "/" . $this->cAction . "/" . $this->cChoice .  "/layout/" . $this->cBrand . "/";
			if ($bFilter) { $cReturn .= $this->cFilter . "/"; }
		}

		return $cReturn;
	}

	/**
	 * Template_Structure::getLanguage()
	 *
	 * @param bool $bFilter
	 * @return string
	 */
	private function getLanguage($bFilter = false) {
		$cReturn	= SITEPATH . "/layout/" . $this->cLanguage . "/";
		if ($bFilter) { $cReturn .= $this->cFilter . "/"; }

		//page
		if ($this->cPage) {
			$cReturn = PAGES . $this->cPage . "/layout/" . $this->cLanguage . "/";
			if ($bFilter) { $cReturn .= $this->cFilter . "/"; }
		}

		//action
		if ($this->cAction) {
			$cReturn = PAGES . $this->cPage . "/" . $this->cAction . "/layout/" . $this->cLanguage . "/";
			if ($bFilter) { $cReturn .= $this->cFilter . "/"; }
		}

		//choice
		if ($this->cChoice) {
			$cReturn = PAGES . $this->cPage . "/" . $this->cAction . "/" . $this->cChoice . "/layout/" . $this->cLanguage . "/";
			if ($bFilter) { $cReturn .= $this->cFilter . "/"; }
		}

		return $cReturn;
	}

	/**
	 * Template_Structure::getLayout()
	 *
	 * @param bool $bFilter
	 * @return string
	 */
	private function getLayout($bFilter = false) {
		$bSpecial 	= false;

		$cBrand	= $this->bBrand ? $this->getBrand($bFilter) : false;
		$this->addDebug("Layout 1", $cBrand);

		$cLanguage	= $this->bLanguage ? $this->getLanguage($bFilter) : false;
		$this->addDebug("Layout 2", $cLanguage);

		if (substr($cBrand, -2, 2) == "//") { $cBrand = substr($cBrand, 0, (strlen($cBrand) - 1)); }
		if (substr($cLanguage, -2, 2) == "//") { $cLanguage = substr($cLayout2, 0, (strlen($cLanguage) - 1)); }

		//set the default structure
		if ($this->bNormal) {
			$cStructure	= SITEPATH . "/layout/" . $this->cStruct;
		} else {
			$cStructure	= SITEPATH . "/layout/" . $this->cMobileStruct;
		}
		$this->addDebug("Default Structure", $cStructure);

		//is there a brand
		if ($cBrand) {
			if ($this->bNormal) {
				if (file_exists($cBrand . $this->cStruct)) {
					$cStructure = $cBrand . $this->cStruct;
					$bSpecial	= true;
				}
			} else {
				if (file_exists($cBrand . $this->cMobileStruct)) {
					$cStructure = $cBrand . $this->cMobileStruct;
					$bSpecial	= true;
				}
			}

			$this->addDebug("Brand Structure", $cStructure);
		}

		//is tehre a language
		if ($cLanguage) {
			if ($this->bNormal) {
				if (file_exists($cLanguage . $this->cStruct)) {
					$cStructure = $cLanguage . $this->cStruct;
					$bSpecial	= true;
				}
			} else {
				if (file_exists($cLanguage . $this->cMobileStruct)) {
					$cStructure	= $cLanguage . $this->cStruct;
					$bSpecial	= true;
				}
			}

			$this->addDebug("Language Structure", $cStructure);
		}

		//if there isnt a special set we need to go through the normal way
		if (!$bSpecial) {
			//is there a page
			if ($this->cPage) {
				if ($this->bNormal) {
					if (file_exists(PAGES . $this->cPage . "/layout/" . $this->cStruct)) { $cStructure = PAGES . "/" . $this->cPage . "/layout/" . $this->cStruct; }
				} else {
					if (file_exists(PAGES . $this->cPage . "/layout/" . $this->cMobileStruct)) { $cStructure = PAGES . $this->cPage . "/layout/" . $this->cMobileStruct; }
				}

				$this->addDebug("Page Structure", $cStructure);
			}

			//action
			if ($this->cAction) {
				if ($this->bNormal) {
					if (file_exists(PAGES . $this->cPage . "/" . $this->cAction . "/layout/" . $this->cStruct)) { $cStructure = PAGES . $this->cPage . "/" . $this->cAction . "/layout/" . $this->cStruct; }
				} else {
					if (file_exists(PAGES . $this->cPage . "/" . $this->cAction . "/layout/" . $this->cMobileStruct)) {
						$cStructure = PAGES . $this->cPage . "/" . $this->cAction . "/layout/" . $this->cMobileStruct;
					}
				}

				$this->addDebug("Action Structure", $cStructure);
			}

			//choice
			if ($this->cChoice) {
				if ($this->bNormal) {
					if (file_exists(PAGES . $this->cPage . "/" . $this->cAction . "/" . $this->cChoice . "/layout/" . $this->cStruct)) {
						$cStructure	= PAGES . $this->cPage . "/" . $this->cAction . "/" . $this->cChoice . "/layout/" . $this->cStruct;
					}
				} else {
					if (file_exists(PAGES . $this->cPage . "/" . $this->cAction . "/" . $this->cChoice . "/layout/" . $this->cMobileStruct)) {
						$cStructure = PAGES . $this->cPage . "/" . $this->cAction . "/" . $this->cChoice. "/layout/" . $this->cMobileStruct;
					}
				}

				$this->addDebug("Choice Structure", $cStructure);
			}

			//one last check since it might be default only
			if (!file_exists($cStructure)) { $this->cError = "Structure seems to be completlly missing"; }

			//debug if it fails
			if ($this->bDebug && $this->cError) { $this->debugTemplates(); }

			$this->cTemplate = $cStructure;

			return $cStructure;
		}
	}
}
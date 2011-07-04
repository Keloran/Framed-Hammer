<?php
/**
 * Template_Structure
 *
 * @package
 * @author Keloran
 * @copyright Copyright (c) 2010
 * @version $Id$
 * @access public
 */
class Template_Structure extends Template_Abstract {
	public $aParams;
	public $cTemplate;

	//Variables that are sent to the rendered page
	protected $aVars;

	//Variables that are only used in the class
	public $cLang;
	public $cBrand;
	public $cFilter;
	public $aSetParams;
	public $cSetTemplate;

	//instance stuff
	static $oStructure;

	/**
	 * Template_Structure::__construct()
	 *
	 * @return null
	 */
	private function __construct($aParams) {
		$this->setParams($aParams);
		$this->aSetParams	= $aParams;
	}

	/**
	 * Template_Structure::getInstance()
	 *
	 * @param array $aParams
	 * @return object
	 */
	public static function getInstance($aParams) {
		if (is_null(self::$oStructure)) {
			self::$oStructure = new Template_Structure($aParams);
		}

		return self::$oStructure;
	}

	/**
	 * Template_Structure::setTemplate()
	 *
	 * @param string $cTemplate
	 * @return null
	 */
	protected function setTemplate($cTemplate = null) {
		$this->cNamedTemplate	= $cTemplate;
		$this->cSetTemplate		= $cTemplate;

		//This is so that they can be destroyed at the end of the function
		$cPage			= $this->cPage;
		$cAction		= $this->cAction;
		$cChoice		= $this->cChoice;
		$cLang			= $this->cLang;
		$cBrand			= $this->cBrand;
		$cFilter		= $this->cFilter;

		//Folder stuff
		$cSep			= "/";
		$cTempFolder	= "templates";
		$cOldFolder		= $cTempFolder;
		$cNewFolder		= "";

		//If these are set we need to follw a different path
		$bStructure	= false;
		$bBranded	= false;
		$bLanguage	= false;
		$bFiltered	= false;

		//is it a mobile device, and
		$bMobile 	= mobileBrowser();
		$bUseNormal	= $this->getParam("useNormal") ?: $this->getCookie("useNormal") ?: false;

		//The structure name
		if ($cTemplate) {
			$cStruct	= $cTemplate . ".struct";
			$cStruct1	= "structure.struct";
			$cMobile	= "mobile.struct";
		} else {
			$cStruct	= "structure.struct";
			$cStruct1	= $cStruct;
			$cMobile	= "mobile.struct";
		}

		//now check to see what the core folder is called
		if (is_dir(SITEPATH . "/layout")) {
			$cLayout	= SITEPATH . "/layout";
		} else {
			$cLayout	= SITEPATH . "/core";
		}

		//if theres a language, brand or filter, set them
		if (isset($cBrand)) { 	$bBranded	= true; }
		if (isset($cLang)) {	$bLanguage	= true; }
		if (isset($cFilter)) {	$bFiltered	= true; }

		//There is a language ora brand, so we need to change the ends
		if ($bBranded) {
			if ($bFiltered) { //it also has a filter
				$cEnd1	= $cSep . $cBrand . $cSep . $cFilter . $cSep . $cNewFolder . $cSep;
				$cEnd2	= $cSep . $cBrand . $cSep . $cFilter . $cSep . $cOldFolder . $cSep;
			} else {
				$cEnd1	= $cSep . $cBrand . $cSep . $cNewFolder . $cSep;
				$cEnd2	= $cSep . $cBrand . $cSep . $cOldFolder . $cSep;
			}
		} else if ($bLanguage) {
			if ($bFiltered) { //it also has a filter
				$cEnd1	= $cSep . $cLang . $cSep . $cFilter . $cSep . $cNewFolder . $cSep;
				$cEnd2	= $cSep . $cLang . $cSep . $cFilter . $cSep . $cOldFolder . $cSep;
			} else {
				$cEnd1	= $cSep . $cLang . $cSep . $cNewFolder . $cSep;
				$cEnd2	= $cSep . $cLang . $cSep . $cOldFolder . $cSep;
			}
		} else {
			if ($bFiltered) {
				$cEnd1	= $cSep . $cFilter . $cNewFolder . $cSep;
				$cEnd2	= $cSep . $cFilter . $cOldFolder . $cSep;
			} else {
				$cEnd1	= $cSep . $cNewFolder . $cSep;
				$cEnd2	= $cSep . $cOldFolder . $cSep;
			}
		}

		//remove the double slashes
		if ($cEnd1 == "//") { $cEnd1 = "/"; }
		if ($cEnd2 == "//") { $cEnd2 = "/"; }

		//is there a page in place
		if ($cPage) {
			if (file_exists(PAGES . $cSep . $cPage . $cEnd1 . $cStruct)) { // news --end1-named.struct
				$this->cTemplate	= PAGES . $cSep . $cPage . $cEnd1 . $cStruct; //normal browser
			} else if (file_exists(PAGES . $cSep . $cPage . $cEnd2 . $cStruct)) { // news --end2-named.struct
				$this->cTemplate	= PAGES . $cSep . $cPage . $cEnd2 . $cStruct;
			} else if (file_exists(PAGES . $cSep . $cPage . $cEnd1 . $cStruct1)) { //news --end1-structure.struct
				$this->cTemplate	= PAGES . $cSep . $cPage . $cEnd1 . $cStruct1;

				if ($bMobile && !$bUseNormal) { //use mobile
					if (file_exists(PAGES . $cSep . $cPage . $cEnd1 . $cMobile)) { $this->cTemplate = PAGES . $cSep . $cPage . $cEnd1 . $cMobile; }
				}
			} else if (file_exists(PAGES . $cSep . $cPage . $cEnd2 . $cStruct1)) { //news --end2-structure.struct
				$this->cTemplate	= PAGES . $cSep . $cPage . $cEnd2 . $cStruct1;

				if ($bMobile && !$bUseNormal) { //use mobile
					if (file_exists(PAGES . $cSep . $cPage . $cEnd2 . $cMobile)) { $this->cTemplate = PAGES . $cSep . $cPage . $cEnd2 . $cMobile; }
				}
			}
		}


		//is the an action in place
		if ($cAction) {
			if (file_exists(PAGES . $cSep . $cPage . $cSep . $cAction . $cEnd1 . $cStruct)) { // news/comments --end1-named.struct
				$this->cTemplate	= PAGES . $cSep . $cPage . $cSep . $cAction . $cEnd1 . $cStruct;
			} else if (file_exists(PAGES . $cSep . $cPage . $cSep . $cAction . $cEnd2 . $cStruct)) { // news/comments --end1-named.struct
				$this->cTemplate	= PAGES . $cSep . $cPage . $cSep . $cAction . $cEnd2 . $cStruct;
			} else if (file_exists(PAGES . $cSep . $cPage . $cSep . $cAction . $cEnd1 . $cStruct1)) { // news/comments --end2-structure.struct
				$this->cTemplate	= PAGES . $cSep . $cPage . $cSep . $cAction . $cEnd1 . $cStruct1;

				if ($bMobile && !$bUseNormal) { //use mobile
					if (file_exists(PAGES . $cSep . $cPage . $cSep . $cAction . $cEnd1 . $cMobile)) { $this->cTemplate = PAGES . $cSep . $cPage . $cSep . $cAction . $cEnd1 . $cMobile; }
				}
			} else if (file_exists(PAGES . $cSep . $cPage . $cSep . $cAction . $cEnd2 . $cStruct1)) { // news/comments --end2-structure.struct
				$this->cTemplate	= PAGES . $cSep . $cPage . $cSep . $cAction . $cEnd2 . $cStruct1;

				if ($bMobile && !$bUseNormal) { //use mobile
					if (file_exists(PAGES . $cSep . $cPage . $cSep . $cAction . $cEnd2 . $cMobile)) { $this->cTemplate = PAGES . $cSep . $cPage . $cSep . $cAction . $cEnd2 . $cMobile; }
				}
			}
		}

		//is there a choice used
		if ($cChoice) {
			if (file_exists(PAGES . $cSep . $cPage . $cSep . $cAction . $cSep . $cChoice . $cEnd1 . $cStruct)) { // news/comments/jimmy --named.struct
				$this->cTemplate	= PAGES . $cSep . $cPage . $cSep . $cAction . $cSep . $cChoice . $cEnd1 . $cStruct;
			} else if (file_exists(PAGES . $cSep . $cPage . $cSep . $cAction . $cSep . $cChoice . $cEnd2 . $cStruct)) { // news/comments/jimmy --named.struct
				$this->cTemplate	= PAGES . $cSep . $cPage . $cSep . $cAction . $cSep . $cChoice . $cEnd2 . $cStruct;
			} else if (file_exists(PAGES . $cSep . $cPage . $cSep . $cAction . $cSep . $cChoice . $cEnd1 . $cStruct1)) { // news/comments/jimmy --structure.struct
				$this->cTemplate	= PAGES . $cSep . $cPage . $cSep . $cAction . $cSep . $cChoice . $cEnd1 . $cStruct1;

				if ($bMobile && !$bUseNormal) { //use mobile
					if (file_exists(PAGES . $cSep . $cPage . $cSep . $cAction . $cSep . $cChoice . $cEnd1 . $cMobile)) {
						$this->cTemplate = PAGES . $cSep . $cPage . $cSep . $cAction . $cSep . $cChoice . $cEnd1 . $cMobile;
					}
				}
			} else if (file_exists(PAGES . $cSep . $cPage . $cSep . $cAction . $cSep . $cChoice . $cEnd2 . $cStruct1)) { // news/comments/jimmy --structure.struct
				$this->cTemplate	= PAGES . $cSep . $cPage . $cSep . $cAction . $cSep . $cChoice . $cEnd2 . $cStruct1;

				if ($bMobile && !$bUseNormal) { //use mobile
					if (file_exists(PAGES . $cSep . $cPage . $cSep . $cAction . $cSep . $cChoice . $cEnd2 . $cMobile)) {
						$this->cTemplate = PAGES . $cSep . $cPage . $cSep . $cAction . $cSep . $cChoice . $cEnd2 . $cMobile;
					}
				}
			}
		}

		//now override all of that if one is specified, unless one actually exists at this point
		//this is incase we dont want to write struct files for every page, but do want a different one for the home page
		if ($this->cNamedTemplate) {
			if (!$cPage) {
				if (!$this->cTemplate) {
					if (file_exists(PAGES . $cSep . $this->cNamedTemplate . $cEnd1 . $cStruct)) { //end1-name.struct
						$this->cTemplate = PAGES . $cSep . $this->cNamedTemplate . $cEnd1 . $cStruct;

					} else if (file_exists(PAGES . $cSep . $this->cNamedTemplate . $cEnd2 . $cStruct)) { //end2-name.struct
						$this->cTemplate = PAGES . $cSep . $this->cNamedTemplate . $cEnd2 . $cStruct;

					} else if (file_exists(PAGES . $cSep . $this->cNamedTemplate . $cEnd1 . $cStruct1)) { //end1-structure.struct
						$this->cTemplate = PAGES . $cSep . $this->cNamedTemplate . $cEnd1 . $cStruct1;

						if ($bMobile && !$bUseNormal) { //use mobile
							if (file_exists(PAGES . $cSep . $this->cNamedTemplate . $cEnd1 . $cMobile)) {
								$this->cTemplate = PAGES . $cSep . $this->cNamedTemplate . $cEnd1 . $cMobile;
							}
						}

					} else if (file_exists(PAGES . $cSep . $this->cNamedTemplate . $cEnd2 . $cStruct1)) { //end2-structure.struct
						$this->cTemplate = PAGES . $cSep . $this->cNamedTemplate . $cEnd2 . $cStruct1;

						if ($bMobile && !$bUseNormal) { //use mobile
							if (file_exists(PAGES . $cSep . $this->cNamedTemplate . $cEnd2 . $cMobile)) {
								$this->cTemplate = PAGES . $cSep . $this->cNamedTemplate . $cEnd2 . $cMobile;
							}
						}
					}
				}
			}
		}

		//layout structure
		if (!$this->cTemplate) {
			if (file_exists($cLayout . $cEnd1 . $cStruct)) { //layout-end1-named.struct
				$this->cTemplate	= $cLayout . $cEnd1 . $cStruct;
			} else if (file_exists($cLayout . $cEnd2 . $cStruct)) { //layout-end2-named.struct
				$this->cTemplate	= $cLayout . $cEnd2 . $cStruct;

			} else if (file_exists($cLayout . $cEnd1 . $cStruct1)) { //layout-end1-structure.struct
				$this->cTemplate	= $cLayout . $cEnd1 . $cStruct1;

				if ($bMobile && !$bUseNormal) { //use mobile
					if (file_exists($cLayout . $cEnd1 . $cMobile)) {
						$this->cTemplate = $cLayout . $cEnd1 . $cMobile;
					}
				}
			} else if (file_exists($cLayout . $cEnd2 . $cStruct1)) { //layout-end2-structure.struct
				$this->cTemplate	= $cLayout . $cEnd2 . $cStruct1;

				if ($bMobile && !$bUseNormal) { //use mobile
					if (file_exists($cLayout . $cEnd2 . $cMobile)) {
						$this->cTemplate = $cLayout . $cEnd2 . $cMobile;
					}
				}
			}
		}

		//debug
		$aDebug = array(
					"P1-End1" 	=> PAGES . $cSep . $this->cNamedTemplate . $cEnd1 . $cStruct,
					"P1-End2" 	=> PAGES . $cSep . $this->cNamedTemplate . $cEnd2 . $cStruct,
					"Struct"	=> $cStruct,
					"P2-End1" 	=> PAGES . $cSep . $this->cNamedTemplate . $cEnd1 . $cStruct1,
					"P2-End2" 	=> PAGES . $cSep . $this->cNamedTemplate . $cEnd2 . $cStruct1,
					"Struct1"	=> $cStruct1,
					"End1"		=> $cEnd1,
					"End2"		=> $cEnd2,
					"Template"	=> $this->cTemplate,
					"Page"		=> $cPage,
					"Params"	=> $this->aSetParams,
					"setTemplate"	=> $this->cSetTemplate,
					"namedTemplate"	=> $this->cNamedTemplate,
					"Layout"	=> $cLayout,
					"this"		=> $this
				);
		//printRead($aDebug);die();

		//There still hasnt been a structure set so the page/action/choice dont have a specific strucutre
		if (!$this->cTemplate) {
			throw new Spanner("There is no layout at all, come on how many checks do I have todo", 500);
		}
	}
}

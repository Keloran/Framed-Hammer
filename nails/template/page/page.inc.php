<?php
/**
 * Template_Page
 *
 * @package
 * @author keloran
 * @copyright Copyright (c) 2010
 * @version $Id$
 * @access public
 */
class Template_Page extends Template_Abstract {
	//Traits
	use Browser;

	public $aParams;
	public $cTemplate;

	protected $aVars;

	//split stuff, e.g. page/action
	protected $cPage;
	protected $cAction;
	protected $cChoice;
	protected $aOthers;
	private $cSkin;

	//instance
	static $oPage;

	//specific
	private $cFolder;
	private $cAltPage;
	protected $cDefault;

	/**
	 * Template_Page::__construct()
	 *
	 * @param mixed $mParams
	 * @param string $cTemplate
	 */
	public function __construct($mParams, $cTemplate = null) {
		$this->setParams($mParams);

		//since we have a template set
		if ($cTemplate) {
			$this->setTemplate($cTemplate);
		}
	}

	/**
	 * Template_Page::setDefault()
	 *
	 * @param string $cDefault
	 * @return null
	 */
	public function setDefault($cDefault) {
		$this->cDefault = $cDefault;
	}

	/**
	 * Template_Page::getDefaultInfo()
	 *
	 * @param string $cTemplate0
	 * @param string $cTemplate1
	 * @param string $cTemplate2
	 * @return array
	 */
	private function getDefaultInfo($cTemplate0 = null, $cExtra = null) {
		$cPath		= false;
		$cTemplate	= false;
		$aReturn	= false;

		if ($this->cDefault) { //default page, e.g. forums
			$cPage		= PAGES . $this->cDefault . $this->cFolder;
			$cTemplate	= $cTemplate0;
			$cTemplate1	= ($cTemplate ? $cTemplate : $this->cDefault) . $cExtra . ".tpl"; //extras
			$cTemplate2	= ($cTemplate ? $cTemplate : $this->cDefault) . ".tpl"; //normal

			//does it exist
			if (file_exists($cPage . $cTemplate1)) { //extras
				$cPath 		= $cPage . $cTemplate1;
				$cTemplate	= $cTemplate1;
			} else if (file_exists($cPage . $cTemplate2)) { //normal
				$cPath		= $cPage . $cTemplate2;
				$cTemplate	= $cTemplate2;
			}
		} else {
			$cPage 		= PAGES . "news" . $this->cFolder;
			$cTemplate	= $cTemplate0;
			$cTemplate1	= ($cTemplate ? $cTemplate : "news") . $cExtra . ".tpl"; //extras
			$cTemplate2	= ($cTemplate ? $cTemplate : "news") . ".tpl"; //normal

			//does it exist
			if (file_exists($cPage . $cTemplate1)) {
				$cPath		= $cPage . $cTemplate1;
				$cTemplate	= $cTemplate1;
			} else if (file_exists($cPage . $cTemplate2)) {
				$cPath		= $cPage . $cTemplate2;
				$cTemplate	= $cTemplate2;
			}
		}

		//now what do we return
		if ($cPath) { 		$aReturn['path'] 		= $cPath; }
		if ($cTemplate) { 	$aReturn['template']	= $cTemplate; }

		return $aReturn;
	}

	/**
	 * Template_Page::setTemplate()
	 *
	 * @param string $cTemplate
	 * @param string $cAltPage
	 * @return string
	 */
	public function setTemplate($cTemplate = null, $cAltPage = null) {
		$cPath		= false;
		$cReturn	= false;
		$cSep		= "/";
		$cExtra		= "_";
		$cPath1		= false;
		$cPath2		= false;
		$cPath3		= false;
		$cPath4		= false;
		$cTemplate0	= $cTemplate;
		$cTemplate1	= false;
		$cTemplate2	= false;

		//is there folderset
		if (!$this->cFolder) { $this->cFolder = "/templates/"; }

		//get the mobile pages
		if ($this->getBrowser("iphone")) {
			$cExtra = "_iphone";
		} else if ($this->getBrowser("android")) {
			$cExtra = "_android";
		}

		//now is there page
		if ($this->cPage) {
			$cPage		= PAGES . $this->cPage . $this->cFolder;
			$cTemplate	= $cTemplate0;
			$cTemplate1	= ($cTemplate ? $cTemplate : $this->cPage) . $cExtra . ".tpl"; //extras
			$cTemplate2 = ($cTemplate ? $cTemplate : $this->cPage) . ".tpl"; //normal

			//does it exist
			if (file_exists($cPage . $cTemplate1)) { //extras
				$cPath		= $cPage . $cTemplate1;
				$cTemplate	= $cTemplate1;
			} else if (file_exists($cPage . $cTemplate2)) { //normal
				$cPath 		= $cPage . $cTemplate2;
				$cTemplate	= $cTemplate2;
			}
		} else {
			$aDefault	= $this->getDefaultInfo($cTemplate0, $cExtra);
			if (isset($aDefault['path'])) {
				$cPath 		= $aDefault['path'];
				$cTemplate	= $aDefault['template'];
			}
		}
		$this->addDebug("Path 1", $cPath);
		$this->addDebug("Template 1", $cTemplate);

		//this is incase it doesnt exist at a higher level
		if ($cPath) { $cPath1 = $cPath; }

		//is there an action
		if ($this->cAction) {
			$cTemplate	= $cTemplate0;
			$cTemplate1	= ($cTemplate ? $cTemplate : $this->cAction) . $cExtra . ".tpl"; //extras
			$cTemplate2	= ($cTemplate ? $cTemplate : $this->cAction) . ".tpl"; //normal

			//old nad new method of doing pages
			$cAction1	= PAGES . $this->cPage . ucfirst($this->cAction) . $this->cFolder;
			$cAction2	= PAGES . $this->cPage . $cSep . $this->cAction . $this->cFolder;

			//now does it exist
			if (file_exists($cAction1 . $cTemplate1)) { //iphone
				$cPath 		= $cAction1 . $cTemplate1;
				$cTemplate	= $cTemplate1;
			} else if (file_exists($cAction1 . $cTemplate2)) { //normal
				$cPath 		= $cAction1 . $cTemplate2;
				$cTemplate	= $cTemplate2;
			} else if (file_exists($cAction2 . $cTemplate1)) { //iphone
				$cPath 		= $cAction2 . $cTemplate1;
				$cTemplate	= $cTemplate1;
			} else if (file_exists($cAction2 . $cTemplate2)) { //normal
				$cPath 		= $cAction2 . $cTemplate2;
				$cTemplate	= $cTemplate2;
			}

			//just in case
			if ($cPath) { $cPath2 = $cPath; }
		}
		$this->addDebug("Path 2", $cPath);
		$this->addDebug("Template 2", $cTemplate);

		//choice
		if ($this->cChoice) {
			$cTemplate	= $cTemplate0;
			$cTemplate1	= ($cTemplate ? $cTemplate : $this->cChoice) . $cExtra . ".tpl"; //extras
			$cTemplate2 = ($cTemplate ? $cTemplate : $this->cChoice) . ".tpl"; //normal

			//old way
			$cChoice1	= PAGES . $this->cPage . $cSep . $this->cAction . ucfirst($this->cChoice) . $this->cFolder;
			$cChoice2	= PAGES . $this->cPage . $cSep . $this->cAction . $cSep . $this->cAction . ucfirst($this->cChoice) . $this->cFolder;
			$cChoice3	= PAGES . $this->cPage . $cSep . $this->cAction . $cSep . $this->cChoice . $this->cFolder;

			//does it exist
			if (file_exists($cChoice1 . $cTemplate1)) { //iphone
				$cPath		= $cChoice1 . $cTemplate1;
				$cTemplate	= $cTemplate1;
			} else if (file_exists($cChoice1 . $cTemplate2)) { //normal
				$cPath		= $cChoice1 . $cTemplate2;
				$cTemplate	= $cTemplate2;

			} else if (file_exists($cChoice2 . $cTemplate1)) { //iphone
				$cPath		= $cChoice2	. $cTemplate1;
				$cTemplate	= $cTemplate1;
			} else if (file_exists($cChoice1 . $cTemplate2)) { //normal
				$cPath		= $cChoice2 . $cTemplate2;
				$cTemplate	= $cTemplate2;

			} else if (file_exists($cChoice3 . $cTemplate1)) { //iphone
				$cPath		= $cChoice3 . $cTemplate1;
				$cTemplate	= $cTemplate1;
			} else if (file_exists($cChoice3 . $cTemplate2)) { //normal
				$cPath		= $cChoice3 . $cTemplate2;
				$cTemplate	= $cTemplate2;
			}

			//just incase
			if ($cPath) { $cPath3 = $cPath; }
		}
		$this->addDebug("Path 3", $cPath);
		$this->addDebug("Template 3", $cTemplate);

		//extra params, e.g. page/action/choice/asda/beep
		if (isset($this->extraParams) && ($this->extraParams)) {
			$bForward	= false;

			//since we need it greater than 1 to get anywhere
			if (isset($this->cParam1)) {
				$cParamOuter	= false;
				$iParam			= 2;

				//get the last one in the chain
				for ($i = 2; $i < $this->extraParams; $i++) {
					$cParam1 = "cParam" . $i;
					$cParam2 = "cParam" . ($i - 1);

					if (isset($this->$cParam)) {
						$cParamOuter = $this->$cParam1;
					} else {
						$cParamOuter = $this->$cParam2;
					}
					$iParam++;
				}

				//got the most outer one
				for ($i = 2; $i < $iParam; $i++) {
					$cParam 		 = "cParam" . $i;
					$cParamPath		.= $cSep . $this->$cParam;

					$cTemplate	= $cTemplate0;
					$cTemplate1	= ($cTemplate ? $cTemplate : $this->$cParam) . $cExtra . ".tpl"; //extras
					$cTemplate2	= ($cTemplate ? $cTemplate : $this->$cParam) . ".tpl"; //normal
					$cParam1	= PAGES . $this->cPage . $cSep . $this->cAction . $cSep . $this->cChoice . $cParamPath . $this->cFolder;

					//now check that the file exists
					if (file_exists($cParam1 . $cTemplate1)) {
						$cPath		= $cParam1 . $cTemplate1;
						$cTemplate	= $cTemplate1;
						break;
					} else if (file_exists($cParam1 . $cTemplate2)) {
						$cPath		= $cParam1 . $cTemplate2;
						$cTemplate	= $cTemplate2;
						break;
					}
				}
			}
		}
		$this->addDebug("Path 4", $cPath);
		$this->addDebug("Template 4", $cTemplate);


		//if there really isnt one yet
		if (!$cPath) {
			if (file_exists(SITEPATH . "/pages/" . $cTemplate . "/templates/" . $cTemplate . ".tpl")) {
				$cPath4	= SITEPATH . "/pages/" . $cTemplate . "/templates/" . $cTemplate . ".tpl";
			}
		}
		$this->addDebug("Path 5", $cPath4);

		//since the page isnt actually real
		if (strstr($cPath, "http:")) { return false; }

		//now try the paths
		$cFinalPath	= false;
		if ($cPath1 && !$cFinalPath) { $cFinalPath = $cPath4; }
		if ($cPath3 && !$cFinalPath) { $cFinalPath = $cPath3; }
		if ($cPath2 && !$cFinalPath) { $cFinalPath = $cPath2; }
		if ($cPath1 && !$cFinalPath) { $cFinalPath = $cPath1; }

		$this->cTemplate	= $cFinalPath;

		//there is no path at all
		if ($cFinalPath) {
			return $cFinalPath;
		} else if ($cPath) {
			return $cPath;
		} else {
			if (!$cTemplate) {
				return false;
			} else {
				$this->debugTemplates();

				throw new Spanner($cTemplate . " template doesnt exist at " . $cFinalPath, 500);
			}
		}
	}
}

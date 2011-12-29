<?php
/**
 * Template_Layout
 *
 * @package
 * @author keloran
 * @copyright Copyright (c) 2010
 * @version $Id$
 * @access public
 */
class Template_Layout extends Template_Abstract {
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
	static $oLayout;

	/**
	 * Template_Layout::__construct()
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
	 * Template_Layout::setTemplate()
	 *
	 * @param string $cTemplate
	 * @return string
	 */
	public function setTemplate($cTemplate = null) {
		$cReturn		= false;
		$cSep			= "/"; //make it cleaner code
		$cExtra			= "_"; //just so it checks something
		$cLayout		= "/core/templates/"; //old way
		$cFinal			= false;

		$cCaller	= $this->getCaller();

		//if template is blank, get the file and its possible template
		if (!$cTemplate) {
			$cTemplate	= substr($cCaller, 0, -4);
		}
		//SubFolder Layouts
		$cTemplate2		= $cTemplate . $cSep;
		$cNewLayout		= "/layout/";
		$cNewLayout2	= "templates/";

		//levels
		$cLayout1		= false;
		$cLayout2		= false;
		$cLayout3		= false;
		$cLayout4		= false;
		$cLayout5		= false;

		//is it the old way or new
		if (is_dir(SITEPATH . "/layout/")) { $cLayout = "/layout/templates/"; }
		$this->addDebug("Layout", $cLayout);

		//browser checker
		if ($this->getBrowser("iphone")) {
			$cExtra = "_iphone";
		} else if ($this->getBrowser("android")) {
			$cExtra = "_android";
		}

		//does the page have a layout folder
		if ($this->cPage) {
			if (is_dir(PAGES . $this->cPage . $cLayout)) { $cLayout1 	= PAGES . $this->cPage . $cLayout; }
			if (is_dir(PAGES . $this->cPage . $cNewLayout)) { $cLayout1	= PAGES . $this->cPage . $cNewLayout; }
		}
		$this->addDebug("Layout 1", $cLayout1);

		//is it an action layout
		if ($this->cAction) {
			if (is_dir(PAGES . $this->cPage . $cSep . $this->cAction . $cLayout)) { $cLayout2 		= PAGES . $this->cPage . $cSep . $this->cAction . $cLayout; }
			if (is_dir(PAGES . $this->cPage . $cSep . $this->cAction . $cNewLayout)) { $cLayout2	= PAGES . $this->cPage . $cSep . $this->cAction . $cLayout; }
		}
		$this->addDebug("Layout 2", $cLayout2);

		//is it a choice layout
		if ($this->cChoice) {
			if (is_dir(PAGES . $this->cPage . $cSep . $this->cAction . $cSep . $this->cChoice . $cLayout)) { $cLayout3		= PAGES . $this->cPage . $cSep . $this->cAction . $cSep . $this->cChoice . $cLayout; }
			if (is_dir(PAGES . $this->cPage . $cSep . $this->cAction . $cSep . $this->cChoice . $cNewLayout)) { $cLayout3	= PAGES . $this->cPage . $cSep . $this->cAction . $cSep . $this->cChoice . $cLayout; }
		}
		$this->addDebug("Layout 3", $cLayout3);

		//now extra params
		if (isset($this->extraParams) && ($this->extraParams)) {
			for ($i = $this->extraParams; $i > 0; $i--) {
				$cParam	= "cParam" . $i;

				//does it have a layout folder
				if (is_dir(PAGES . $this->cPage . $cSep . $this->cAction . $cSep . $this->cChoice . $cSep . $cParam . $cLayout)) {
					$cLayout4 = PAGES . $this->cPage . $cSep . $this->cAction . $cSep . $this->cChoice . $cSep . $cParam . $cLayout;
					break;
				}

				if (is_dir(PAGES . $this->cPage . $cSep . $this->cAction . $cSep . $this->cChoice . $cSep . $cParam . $cNewLayout)) {
					$cLayout4 = PAGES . $this->cPage . $cSep . $this->cAction . $cSep . $this->cChoice . $cSep . $cParam . $cLayout;
					break;
				}
			}
		}
		$this->addDebug("Layout 4", $cLayout4);

		//is there a param layout, and does it have the template in there
		if ($cLayout4) {
			if (file_exists($cLayout4 . $cTemplate . $cExtra . ".tpl")) { //extras
				$cFinal = $cLayout4 . $cTemplate . $cExtra . ".tpl";
			} else if (file_exists($cLayout4 . $cTemplate . ".tpl")) { //normal
				$cFinal = $cLayout4 . $cTemplate . ".tpl";
			} else if (file_exists($cLayout4 . $cTemplate2 . $cNewLayout2 . $cTemplate . ".tpl")) { //in a folder
				$cFinal = $cLayout4 . $cTemplate2 . $cTemplate . ".tpl";
			} else if (file_exists($cLayout4 . $cNewLayout2 . $cTemplate . ".tpl")) {
				$cFinal = $cLayout4 . $cNewLayout2 . $cTemplate . ".tpl";
			}
		}

		//now check if final has been set and that we are on choice
		if ($cLayout3 && !$cFinal) {
			if (file_exists($cLayout3 . $cTemplate . $cExtra . ".tpl")) { //extras
				$cFinal = $cLayout3 . $cTemplate . $cExtra . ".tpl";
			} else if (file_exists($cLayout3 . $cTemplate . ".tpl")) { //normal
				$cFinal = $cLayout3 . $cTemplate . ".tpl";
			} else if (file_exists($cLayout3 . $cTemplate2 . $cNewLayout2 . $cTemplate . ".tpl")) { //in a folder
				$cFinal = $cLayout3 . $cTemplate2 . $cTemplate . ".tpl";
			} else if (file_exists($cLayout3 . $cNewLayout2 . $cTemplate . ".tpl")) {
				$cFinal = $cLayout3 . $cNewLayout2 . $cTemplate . ".tpl";
			}
		}

		//now check if final has been set and we are on action
		if ($cLayout2 && !$cFinal) {
			if (file_exists($cLayout2 . $cTemplate . $cExtra . ".tpl")) { //extras
				$cFinal = $cLayout2 . $cTemplate . $cExtra . ".tpl";
			} else if (file_exists($cLayout2 . $cTemplate . ".tpl")) { //normal
				$cFinal = $cLayout2 . $cTemplate . ".tpl";
			} else if (file_exists($cLayout2 . $cTemplate2 . $cNewLayout2 . $cTemplate . ".tpl")) { //in a folder
				$cFinal = $cLayout2 . $cTemplate2 . $cNewLayout2 . $cTemplate . ".tpl";
			} else if (file_exists($cLayout2 . $cNewLayout2 . $cTemplate . ".tpl")) {
				$cFinal = $cLayout2 . $cNewLayout2 . $cTemplate . ".tpl";
			}
		}

		//now check if final has been set and we are on page
		if ($cLayout1 && !$cFinal) {
			if (file_exists($cLayout1 . $cTemplate . $cExtra . ".tpl")) { //extras
				$cFinal = $cLayout1 . $cTemplate . $cExtra . ".tpl";
			} else if (file_exists($cLayout1 . $cTemplate . ".tpl")) { //normal
				$cFinal = $cLayout1 . $cTemplate . ".tpl";
			} else if (file_exists($cLayout1 . $cTemplate2 . $cNewLayout2 . $cTemplate . ".tpl")) { //in a folder
				$cFinal = $cLayout1 . $cTemplate2 . $cNewLayout2 . $cTemplate . ".tpl";
			} else if (file_exists($cLayout1 . $cNewLayout2 . $cTemplate . ".tpl")) {
				$cFinal = $cLayout1 . $cNewLayout2 . $cTemplate . ".tpl";
			}
		}
		$this->addDebug("Final 1", $cFinal);

		//now if no final set it must be at the very bottom layer
		if (!$cFinal) {
			if (file_exists(SITEPATH . $cLayout . $cTemplate . $cExtra . ".tpl")) { //extras
				$cFinal = SITEPATH . $cLayout . $cTemplate . $cExtra . ".tpl";
			} else if (file_exists(SITEPATH . $cLayout . $cTemplate . ".tpl")) { //normal
				$cFinal = SITEPATH . $cLayout . $cTemplate . ".tpl";
			} else if (file_exists(SITEPATH . $cNewLayout . $cTemplate2 . $cNewLayout2 . $cTemplate . ".tpl")) { //in a folder
				$cFinal = SITEPATH . $cNewLayout . $cTemplate2 . $cNewLayout2 . $cTemplate . ".tpl";
			} else if (file_exists(SITEPATH . $cNewLayout . $cTemplate . ".tpl")) {
				$cFinal = SITEPATH . $cNewLayout . $cTemplate . ".tpl";
			} else if (file_exists(SITEPATH . $cNewLayout . $cCaller . "/templates/" . $cTemplate . ".tpl")) {
				$cFinal = SITEPATH . $cNewLayout . $cCaller . "/templates/" . $cTemplate . ".tpl";
			}
		}
		$this->addDebug("Final 2", $cFinal);

		//debug
		$this->addDebug("Template 1", $cTemplate);
		$this->addDebug("Template 2", $cTemplate2);
		$this->addDebug("Full Path", (SITEPATH . $cLayout . $cTemplate2 . $cTemplate . ".tpl"));
		$this->addDebug("Full Path 1", ($cLayout1 . $cTemplate2 . $cTemplate . ".tpl"));
		$this->addDebug("Full Path 2", ($cLayout2 . $cTemplate2 . $cTemplate . ".tpl"));
		$this->addDebug("Full Path 3", ($cLayout3 . $cTemplate2 . $cTemplate . ".tpl"));
		$this->addDebug("Full Path 4", ($cLayout4 . $cTemplate2 . $cTemplate . ".tpl"));
		$this->addDebug("New Layout", $cNewLayout);
		$this->addDebug("Caller", $cCaller);
		$this->addDebug("Debugged", SITEPATH . $cNewLayout . $cCaller . "/templates/" . $cTemplate . ".tpl");

		if (!$cFinal) {
			$this->debugTemplates();
			throw new Spanner("Layout template: " . $cTemplate . " doesnt exist", 550);
		}

		$this->cTemplate = $cFinal;

		return $cFinal;
	}
}

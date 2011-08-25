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
		if (!function_exists("getBrowser")) { include HAMMERPATH . "/functions/browser.php"; }

		$cReturn		= false;
		$cSep			= "/"; //make it cleaner code
		$cExtra			= "_"; //just so it checks something
		$cLayout		= "/core/templates/"; //old way
		$cFinal			= false;

		//if template is blank, get the file and its possible template
		if (!$cTemplate) {
			$aDebug 	= debug_backtrace();
			$cFile		= $aDebug[1]['file'];
			$aFile		= explode("/", $cFile);
			$iFile		= (count($aFile) - 1);
			$cTemplate	= substr($aFile[$iFile], 0, -4);
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

		//is it the old way or new
		if (is_dir(SITEPATH . "/layout/")) { $cLayout = "/layout/templates/"; }

		//browser checker
		if (getBrowser("iphone")) {
			$cExtra = "_iphone";
		} else if (getBrowser("android")) {
			$cExtra = "_android";
		}

		//does the page have a layout folder
		if ($this->cPage) {
			if (is_dir(PAGES . $this->cPage . $cLayout)) {
				$cLayout1	= PAGES . $this->cPage . $cLayout;
			} else if (is_dir(PAGES . $this->cPage . $cNewLayout)) {
				$cLayout1	= PAGES . $this->cPage . $cNewLayout;
			}
		}

		//is it an action layout
		if ($this->cAction) {
			if (is_dir(PAGES . $this->cPage . $cSep . $this->cAction . $cLayout)) {
				$cLayout2	= PAGES . $this->cPage . $cSep . $this->cAction . $cLayout;
			} else if (is_dir(PAGES . $this->cPage . $cSep . $this->cAction . $cNewLayout)) {
				$cLayout2	= PAGES . $this->cPage . $cSep . $this->cAction . $cLayout;
			}
		}

		//is it a choice layout
		if ($this->cChoice) {
			if (is_dir(PAGES . $this->cPage . $cSep . $this->cAction . $cSep . $this->cChoice . $cLayout)) {
				$cLayout3	= PAGES . $this->cPage . $cSep . $this->cAction . $cSep . $this->cChoice . $cLayout;
			} else if (is_dir(PAGES . $this->cPage . $cSep . $this->cAction . $cSep . $this->cChoice . $cNewLayout)) {
				$cLayout3	= PAGES . $this->cPage . $cSep . $this->cAction . $cSep . $this->cChoice . $cLayout;
			}
		}

		//now extra params
		if (isset($this->extraParams) && ($this->extraParams)) {
			for ($i = $this->extraParams; $i > 0; $i--) {
				$cParam	= "cParam" . $i;

				//does it have a layout folder
				if (is_dir(PAGES . $this->cPage . $cSep . $this->cAction . $cSep . $this->cChoice . $cSep . $cParam . $cLayout)) {
					$cLayout4 = PAGES . $this->cPage . $cSep . $this->cAction . $cSep . $this->cChoice . $cSep . $cParam . $cLayout;
					break;
				} else if (is_dir(PAGES . $this->cPage . $cSep . $this->cAction . $cSep . $this->cChoice . $cSep . $cParam . $cNewLayout)) {
					$cLayout4 = PAGES . $this->cPage . $cSep . $this->cAction . $cSep . $this->cChoice . $cSep . $cParam . $cLayout;
					break;
				}
			}
		}

		//is there a param layout, and does it have the template in there
		if ($cLayout4) {
			if (file_exists($cLayout4 . $cTemplate . $cExtra . ".tpl")) { //extras
				$cFinal = $cLayout4 . $cTemplate . $cExtra . ".tpl";
			} else if (file_exists($cLayout4 . $cTemplate . ".tpl")) { //normal
				$cFinal = $cLayout4 . $cTemplate . ".tpl";
			} else if (file_exists($cLayout4 . $cTemplate2 . $cNewLayout2 . $cTemplate . ".tpl")) { //in a folder
				$cFinal = $cLayout4 . $cTemplate2 . $cTemplate . ".tpl";
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
			}
		}

		//now if no final set it must be at the very bottom layer
		if (!$cFinal) {
			if (file_exists(SITEPATH . $cLayout . $cTemplate . $cExtra . ".tpl")) { //extras
				$cFinal = SITEPATH . $cLayout . $cTemplate . $cExtra . ".tpl";
			} else if (file_exists(SITEPATH . $cLayout . $cTemplate . ".tpl")) { //normal
				$cFinal = SITEPATH . $cLayout . $cTemplate . ".tpl";
			} else if (file_exists(SITEPATH . $cNewLayout . $cTemplate2 . $cNewLayout2 . $cTemplate . ".tpl")) { //in a folder
				$cFinal = SITEPATH . $cNewLayout . $cTemplate2 . $cNewLayout2 . $cTemplate . ".tpl";
			}
		}

		if (!$cFinal) {
			$a = array(
				"Layout"	=> $cLayout,
				"Layout1"	=> $cLayout1,
				"Layout2"	=> $cLayout2,
				"Layout3"	=> $cLayout3,
				"Layout4"	=> $cLayout4,
				"Final"		=> $cFinal,
				"Template1"	=> $cTemplate,
				"Template2"	=> $cTemplate2,
				"FullPath"	=> (SITEPATH . $cLayout . $cTemplate2 . $cTemplate . ".tpl"),
				"FullPath1"	=> ($cLayout1 . $cTemplate2 . $cTemplate . ".tpl"),
				"FullPath2"	=> ($cLayout2 . $cTemplate2 . $cTemplate . ".tpl"),
				"FullPath3"	=> ($cLayout3 . $cTemplate2 . $cTemplate . ".tpl"),
				"FullPath4"	=> ($cLayout4 . $cTemplate2 . $cTemplate . ".tpl"),
			);
			printRead($a);
			die();

			throw new Spanner("Layout template: " . $cTemplate . " doesnt exist", 550);
		}

		return $cFinal;
	}
}

<?php
/**
 * Template_Core
 *
 * @package
 * @author keloran
 * @copyright Copyright (c) 2010
 * @version $Id$
 * @access public
 */
class Template_Core extends Template_Abstract {
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
	static $oCore;

	/**
	 * Template_Core::__construct()
	 *
	 * @param mixed $mParams
	 * @param string $cTemplate
	 */
	public function __construct($mParams, $cTemplate = null) {
		$this->setParams($mParams);
	}

	/**
	 * Template_Core::setTemplate()
	 *
	 * @param string $cTemplate
	 * @return string
	 */
	public function setTemplate($cTemplate = null) {
		if (!function_exists("getBrowser")) { include HAMMERPATH . "/functions/browser.php"; }

		$cReturn		= false;
		$cSep			= "/"; //make it cleaner code
		$cExtra			= "_"; //just so it checks something
		$cLayout		= "/core/"; //old way
		$cFinal			= false;
		$cTemplate2		= $cTemplate . $cSep;

		//levels
		$cLayout1		= false;
		$cLayout2		= false;
		$cLayout3		= false;
		$cLayout4		= false;

		//is it the old way or new
		if (is_dir(SITEPATH . "/layout/")) { $cLayout = "/layout/"; }

		//browser checker
		if (getBrowser("iphone")) {
			$cExtra = "_iphone";
		} else if (getBrowser("android")) {
			$cExtra = "_android";
		}

		//does the page have a layout folder
		if ($this->cPage) {
			if (is_dir(PAGES . $this->cPage . $cLayout)) {
				$cLayout1		= PAGES . $this->cPage . $cLayout;
			}
		}

		//is it an action layout
		if ($this->cAction) {
			if (is_dir(PAGES . $this->cPage . $cSep . $this->cAction . $cLayout)) {
				$cLayout2		= PAGES . $this->cPage . $cSep . $this->cAction . $cLayout;
			}
		}

		//is it a choice layout
		if ($this->cChoice) {
			if (is_dir(PAGES . $this->cPage . $cSep . $this->cAction . $cSep . $this->cChoice . $cLayout)) {
				$cLayout3		= PAGES . $this->cPage . $cSep . $this->cAction . $cSep . $this->cChoice . $cLayout;
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
				}
			}
		}

		//is there a param layout, and does it have the template in there
		if ($cLayout4) {
			if (file_exists($cLayout4 . $cTemplate . $cExtra . ".php")) { //extras
				$cFinal = $cLayout4 . $cTemplate . $cExtra . ".php";
			} else if (file_exists($cLayout4 . $cTemplate . ".php")) { //normal
				$cFinal = $cLayout4 . $cTemplate . ".php";
			} else if (file_exists($cLayout4 . $cTemplate2 . $cTemplate . ".php")) { //inside a folder
				$cFinal = $cLayout4 . $cTemplate2 . $cTemplate . ".php";
			}
		}

		//now check if final has been set and that we are on choice
		if ($cLayout3 && !$cFinal) {
			if (file_exists($cLayout3 . $cTemplate . $cExtra . ".php")) { //extras
				$cFinal = $cLayout3 . $cTemplate . $cExtra . ".php";
			} else if (file_exists($cLayout3 . $cTemplate . ".php")) { //normal
				$cFinal = $cLayout3 . $cTemplate . ".php";
			} else if (file_exists($cLayout3 . $cTemplate2 . $cTemplate . ".php")) { //inside a folder
				$cFinal = $cLayout3 . $cTemplate2 . $cTemplate . ".php";
			}
		}

		//now check if final has been set and we are on action
		if ($cLayout2 && !$cFinal) {
			if (file_exists($cLayout2 . $cTemplate . $cExtra . ".php")) { //extras
				$cFinal = $cLayout2 . $cTemplate . $cExtra . ".php";
			} else if (file_exists($cLayout2 . $cTemplate . ".php")) { //normal
				$cFinal = $cLayout2 . $cTemplate . ".php";
			} else if (file_exists($cLayout2 . $cTemplate2 . $cTemplate . ".php")) { //inside a folder
				$cFinal = $cLayout2 . $cTemplate2 . $cTemplate . ".php";
			}
		}

		//now check if final has been set and we are on page
		if ($cLayout1 && !$cFinal) {
			if (file_exists($cLayout1 . $cTemplate . $cExtra . ".php")) { //extras
				$cFinal = $cLayout1 . $cTemplate . $cExtra . ".php";
			} else if (file_exists($cLayout1 . $cTemplate . ".php")) { //normal
				$cFinal = $cLayout1 . $cTemplate . ".php";
			} else if (file_exists($cLayout1 . $cTemplate2 . $cTemplate . ".php")) { //inside a folder
				$cFinal = $cLayout1 . $cTemplate2 . $cTemplate . ".php";
			}
		}

		//now if no final set it must be at the very bottom layer
		if (!$cFinal) {
			if (file_exists(SITEPATH . $cLayout . $cTemplate . $cExtra . ".php")) { //extras
				$cFinal = SITEPATH . $cLayout . $cTemplate . $cExtra . ".php";
			} else if (file_exists(SITEPATH . $cLayout . $cTemplate . ".php")) { //normal
				$cFinal = SITEPATH . $cLayout . $cTemplate . ".php";
			} else if (file_exists(SITEPATH . $cLayout . $cTemplate2 . $cTemplate . ".php")) { //inside a folder
				$cFinal = SITEPATH . $cLayout . $cTemplate2 . $cTemplate . ".php";
			}
		}

		//k now if no final throw a spanner
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
				"FullPath"	=> (SITEPATH . $cLayout . $cTemplate2 . $cTemplate . ".tpl")
			);
			printRead($a);
			die();

			throw new Spanner("Layout Controller: " . $cTemplate . " doesnt exist", 550);
		}

		return $cFinal;
	}
}

<?php
/**
 * Template_Abstract
 *
 * @package
 * @author Keloran
 * @copyright Copyright (c) 2010
 * @version $Id$
 * @access public
 */
abstract class Template_Abstract implements Template_Interface {
	protected $aVars;
	protected $cTemplate;
	protected $mParams;
	protected $cDefault;
	protected $aData;

	/**
	* Template_Abstract::setTemplate
	*
	* @desc This sets the template to something
	* @return null
	*/
	protected function setTemplate($cTemplate = null) {
		$this->cTemplate = $cTemplate;
	}

	/**
	* Template_Abstract::setVars()
	*
	* @desc This sets the variable for the template
	* @param string $cName This is the name of the variable
	* @param mixed $mVar This is the property of the variable
	* @return null
	*/
	public final function setVars($cName, $mVars) {
		if (is_array($mVars)) {
			foreach ($mVars as $cVarName => $cVar) {                
                //remove the previous entry
                if ($this->aVars[$cName][$cVarName] == $cVarName) { 
                    $cName = $cName . "_preset";
                }

				$this->aVars[$cName][$cVarName] = $cVar;
			}
		} else {
			$this->aVars[$cName] = $mVars;
		}
	}

	/**
	* Template_Abstract::setParams()
	*
	* @desc This turns the array of params into individual properties
	* @param array $aParams
	* @return null
	*/
	public final function setParams($mParams) {
		$this->mParams	= $mParams;

		if (is_array($mParams)) {
			foreach ($mParams as $cName	=> $cValue) {
				$this->$cName	= $cValue;
			}
		}
	}

	/**
	 * Template_Abstract::__set()
	 *
	 * @param string $cName
	 * @param mixed $mValue
	 * @return null
	 */
	public function __set($cName, $mValue) {
		$this->aData[$cName]	= $mValue;
	}

	/**
	 * Template_Abstract::__get()
	 *
	 * @param string $cName
	 * @return mixed
	 */
	public function __get($cName) {
		$mReturn = false;

		if (isset($this->aData[$cName])) { $mReturn = $this->aData[$cName]; }

		return $mReturn;
	}

	/**
	* Template_Abstract::createTemplate()
	*
	* @desc This adds the Hammer variable
	* @return null
	*/
	public function createTemplate() {
		if (!isset($oHammer)) {
			$oHammer	= Hammer::getHammer();

			$this->setVars("oHammer", $oHammer);
		}
	}

	/**
	* Template_Abstract::renderTemplate()
	*
	* @desc Since this does the same thing everytime might aswell make it part of the abstract
	* @return string
	*/
	public function renderTemplate() {
		//open the buffer
		if (!checkHeaders()) { ob_start(); }

		extract($this->aVars, EXTR_SKIP);

		include $this->cTemplate;
		$cTemplate = ob_get_contents();

		//make sure we are in an ob before cleaning
		if (!checkHeaders()) { if (ob_get_level()) { ob_end_clean(); }}

		return $cTemplate;
	}

	/**
	* Template_Abstract::getPage()
	*
	* @desc This is so that it can be easier to debug, no other reason
	* @param string $cDefault This is so that i can set the default page, e.g. forums
	* @return string
	*/
	protected function getPage($cDefault = null) {
		$cPage	= false;

		if (!$this->cPage) {
			if ($cDefault) {
				$cPage 			= PAGES . $cDefault . "/" . $cDefault . ".php";
				$this->cPage	= $cDefault;
				$this->cDefault	= $cDefault;
			} else {
				$cPage 			= PAGES . "news/news.php";
				$this->cPage	= "news";
				$this->cDefault	= "news";
			}
		} else {
			$cPaged	= PAGES . $this->cPage . "/" . $this->cPage . ".php";

			if (file_exists($cPaged)) {
				$cPage = $cPaged;
			} else {
				$this->cError = "Sorry " . $this->cPage . " doesn't exist";
			}
		}

		if (!$cPage) {
			$this->cError = "Erm there is no default page it seems (" . $cDefault . ")";
		}

		return $cPage;
	}

	/**
	* Template_Abstract::getAction()
	*
	* @desc This is for easier debug
	* @return string
	*/
	protected function getAction() {
		$cPage	= false;

		$cPaged_a	= PAGES . $this->cPage . "/" . $this->cPage . ucfirst($this->cAction) . ".php";
		$cPaged_b	= PAGES . $this->cPage . "/" . $this->cAction . "/" . $this->cAction . ".php";
		$cPaged_c	= PAGES . $this->cPage . "/" . $this->cAction . ".php";

		if (file_exists($cPaged_a)) {
			$cPage = $cPaged_a;
		} else if (file_exists($cPaged_b)) {
			$cPage = $cPaged_b;
		} else if (file_exists($cPaged_c)) {
			$cPage = $cPaged_c;
		} else {
			$this->cError = "Sorry " . $this->cAction . " doesn't exist";
		}

		return $cPage;
	}

	/**
	* Template_Abstract::getChoice()
	*
	* @desc this is for easier debug
	* @return string
	*/
	protected function getChoice() {
		$cPage	 = false;

		$cPaged_a	= PAGES . $this->cPage . "/" . $this->cAction . "/" . $this->cAction . ucfirst($this->cChoice) . ".php";
		$cPaged_b	= PAGES . $this->cPage . "/" . $this->cAction . "/" . $this->cChoice . ".php";
		$cPaged_c	= PAGES . $this->cPage . "/" . $this->cAction . "/" . $this->cAction . "/" . $this->cChoice . ".php";
		$cPaged_d	= PAGES . $this->cPage . "/" . $this->cAction . "/" . $this->cChoice . "/" . $this->cChoice . ".php";

		if (file_exists($cPaged_a)) {
			$cPage = $cPaged_a;
		} else if (file_exists($cPaged_b)) {
			$cPage = $cPaged_b;
		} else if (file_exists($cPaged_c)) {
			$cPage = $cPaged_c;
		} else if (file_exists($cPaged_d)) {
			$cPage = $cPaged_d;
		} else {
			$this->cError = "Sorry " . $this->cChoice . " doesn't exist";
		}

		return $cPage;
	}

	/**
	* Template_Abstract::getOther()
	*
	* @desc this is for easier debug
	* @return string
	*/
	protected function getOther() {
		$cPage		= false;
		$bForward	= false;
		$iStart		= 0;

		//is there at least 1 deep
		if (isset($this->cParam1)) {
			$cParamPath	= "/" . $this->cParam1;

			//forwards
			for ($i = 2; $i < $this->extraParams; $i++) {
				$cParam		 = "cParam" . $i;
				if (isset($this->$cParam)) {
					$cParamPath	.= "/" . $this->$cParam;

					$cParam1	= PAGES . $this->cPage . "/" . $this->cAction . "/" . $this->cChoice . $cParamPath . ".php";
					$cParam2	= PAGES . $this->cPage . "/" . $this->cAction . "/" . $this->cChoice . $cParamPath . "/" . $this->$cParam . ".php";

					if (file_exists($cParam_a)) {
						$cPage		= $cParam_a;
						$bForward	= true;
						break;
					} else if (file_exists($cParam_b)) {
						$cPage		= $cParam_b;
						$bForward	= true;
						break;
					}
				}
			}
		}

		//backwards
		if (!$bForward) {
			$cFinalParam	 = PAGES;
			$cFinalParam	.= $this->cPage 	. "/";
			$cFinalParam	.= $this->cAction	. "/";
			$cFinalParam	.= $this->cChoice	. "/";

			$iFinalParam	= $this->extraParams;
			$cInterPram		= false;

			//make the tree
			for ($i = $iFinalParam; $i > 0; $i--) {
				//last param
				$cThisParam		= "cParam" . $i;
				$cNextParam		= false;
				$iLastParam		= $i;
				$cFinalParam_b	= false;

				//top param exists
				if (isset($this->$cThisParam)) {
					//make the list for the final
					for ($j = 0; $j < $iLastParam; $j++) {
						$cNextParam		 = "cParam" . $j;

						if ($j == 1) { $cInterParam .= "/"; }
						$cInterParam	.= $cNextParam . "/";

						$cFinalParam_b	.= $cInterParam . $cNextParam;
					}
					$cFinalParam_b	.= ".php";
					$cFinalParam_c	= $cFinalParam . $cFinalParam_b;

					//if it exists
					if (file_exists($cFinalParam_c)) {
						$cPage	= $cFinalParam_c;
						break;
					}
				}
			}

			/**
			for ($i = $this->extraParams; $i > 0; $i--) {
				$cParam = "cParam" . $i;

				//get the last one
				if (isset($this->$cParam)) {

				}

				if (isset($this->$cParam)) {
					$cParam1	= PAGES . $this->cPage . "/" . $this->cAction . "/" . $this->cChoice . "/" . $this->$cParam . ".php";
					$cParam2	= PAGES . $this->cPage . "/" . $this->cAction . "/" . $this->cChoice . "/" . $this->$cParam . "/" . $this->$cParam . ".php";

					if (file_exists($cParam_a)) {
						$cPage = $cParam_a;
						break;
					} else if (file_exists($cParam_b)) {
						$cPage = $cParam_b;
						break;
					}
				}
			}
			*/
		}

		return $cPage;
	}

	/**
	 * Template_Abstract::getCaller()
	 *
	 * @return string
	 */
	public function getCaller() {
		$aDebug 	= debug_backtrace(false, 7);
		$cFile		= false;

        foreach ($aDebug AS $debug) {
            if ($debug['function'] == "getCore") {
                if (isset($debug['args'])) {
    				if (isset($debug['args'][0])) {
    					$cFile	= $debug['args'][0];
                    }
				}
			}
		}

		return $cFile;
	}
}

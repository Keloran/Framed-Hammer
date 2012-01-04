<?php
/**
 * Template_Abstract
 *
 * @package
 * @author Keloran
 * @copyright Copyright (c) 2011
 * @version $Id$
 * @access public
 */
abstract class Template_Abstract {
	use Browser;

	protected $aVars;
	protected $cTemplate;
	protected $mParams;
	protected $cDefault;
	protected $aData;
	protected $aDebug;
	protected $bDebug;
	protected $bFormed;

	public $cError;
	public $cCaller;

	/**
	 * Template_Abstract::doDebug()
	 *
	 * @return null
	 */
	public function doDebug() {
		$this->bDebug	= true;
	}

	/**
	 * Template_Abstract::addDebug()
	 *
	 * @param string $cName
	 * @param mixed $mValue
	 * @return null
	 */
	public function addDebug($cName, $mValue) {
		$this->aDebug[$cName] = $mValue;
	}

	/**
	 * Template_Abstract::debugTemplates()
	 *
	 * @return null
	 */
	public function debugTemplates() {
		printRead($this->aDebug, "Debug");
		printRead($this, "Template Object");
		die();
	}

	/**
	 * Template_Abstract::setTemplate()
	 *
	 * @param string $cTemplate
	 * @return null
	 */
	public function setTemplate($cTemplate = null) {
		$this->cTemplate	= $cTemplate;
	}

	/**
	 * Template_Abstract::setVars()
	 *
	 * @param string $cName
	 * @param mixed $mVars
	 * @return null
	 */
	public function setVars($cName, $mVars) {
		//unset the previous to stop scalar conflict
		unset($this->aVars[$cName]);

		if (is_array($mVars)) {
			foreach ($mVars as $cVar => $mValue) {
				$this->aVars[$cName][$cVar]	= $mValue;
			}
		} else {
			$this->aVars[$cName] = $mVars;
		}
	}

	/**
	 * Template_Abstract::setParams()
	 *
	 * @param mixed $mParams
	 * @return null
	 */
	public final function setParams($mParams) {
		$this->mParams	= $mParams;

		if (is_array($mParams)) {
			foreach ($mParams as $cName => $mParam) {
				$this->$cName	= $mParam;
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
		$this->aData[$cName] = $mValue;
	}

	/**
	 * Template_Abstract::__get()
	 *
	 * @param string $cName
	 * @return mixed
	 */
	public function __get($cName) {
		if (isset($this->aData[$cName])) { return $this->aData[$cName]; }

		return false;
	}

	/**
	 * Template_Abstract::__isset()
	 *
	 * @param string $cName
	 * @return bool
	 */
	public function __isset($cName) {
		if (isset($this->aData[$cName])) { return true; }

		return false;
	}

	/**
	 * Template_Abstract::getForm()
	 *
	 * @return null
	 */
	private function getForm() {
		$cForm	= false;
		$oForm	= false;

		//it exists
		if ($this->oForm) {
			$cForm	= $this->oForm->fullForm();
			$oForm	= $this->oForm;
		}

		$this->setVars("cForm", $cForm);
		$this->setVars("oForm", $oForm);
		$this->bFormed	= true;

		return false;
	}

	/**
	 * Template_Abstract::renderTemplate()
	 *
	 * @param bool $bEcho
	 * @return string
	 */
	public function renderTemplate($bEcho = null) {
		$cReturn	= false;

		//now make sure we have a template otherwise just do nothing
		if (!$this->cTemplate) { return false; }

		//get the form if there is one
		if (!$this->bFormed) { $this->getForm(); }

		if (strstr($this->cTemplate, "latester")) {
			printRead($this->getCaller(true);
			die();
		}

		//start the buffer so that we can process the request
		ob_start();
			extract($this->aVars, EXTR_OVERWRITE); //skip on override
			include $this->cTemplate;
		$cReturn	= ob_get_clean();

		//are we echoing the results or retuning
		if ($bEcho) { echo $cReturn; }

		//still return just incase
		return $cReturn;
	}

	/**
	 * Template_Abstract::removeParents()
	 *
	 * @return null
	 */
	private function removeParents() {
		unset($this->aVars["this"]);
		unset($this->aVars["Hammer"]);
		unset($this->aVars["oHammer"]);

		//make absolute certain
		$this->aVars["this"]	= false;
		$this->aVars["Hammer"]	= false;
		$this->aVars["oHammer"]	= false;
	}

	/**
	 * Template_Abstract::getPage()
	 *
	 * @param string $cDefault
	 * @return string
	 */
	protected function getPage($cDefault = null) {
		$cPage	= false;

		//there is no page so it must be the default
		if (!$this->cPage) {
			if ($cDefault) { //a default has been given so use this
				$this->cPage	= $cDefault;
				$this->cDefault	= $cDefault;
			} else {
				$this->cPage	= "news";
				$this->cDefault	= "news";
			}
			$cPage	= PAGES . $this->cDefault . "/" . $this->cDefault . ".php";
		} else { //we are in a page
			$cPage	= PAGES . $this->cPage . "/" . $this->cPage . ".php";

			//now does the file actually exist
			if (!file_exists($cPage)) {
				$this->cError = "Sorry " . $this->cPage . " doesn't seem to exist";
				$this->addDebug("Error", $this->cError);
			}
		}

		//now check the default exists
		if (!file_exists($cPage)) { $this->cError = "Sorry the default page doesnt seem to exist either ( " . $this->cDefault . " )"; }

		//now if there is no error return
		if ($this->cError && $this->bDebug) { $this->debugTemplates(); }

		return $cPage;
	}

	/**
	 * Template_Abstract::getAction()
	 *
	 * @return string
	 */
	protected function getAction() {
		$cReturn	= false;

		$cLegacy1	= PAGES . $this->cPage . "/" . $this->cPage . ucfirst($this->cAction) . ".php";
		$this->addDebug("Legacy 1", $cLegacy1);

		$cLegacy2	= PAGES . $this->cPage . "/" . $this->cAction . ".php";
		$this->addDebug("Legacy 2", $cLegacy2);

		$cModern	= PAGES . $this->cPage . "/" . $this->cAction . "/" . $this->cAction . ".php";
		$this->addDebug("Modern", $cModern);

		if (file_exists($cLegacy1)) {
			$cReturn		= $cLegacy1;
		} else if (file_exists($cLegacy2)) {
			$cReturn		= $cLegacy2;
		} else if (file_exists($cModern)) {
			$cReturn		= $cModern;
		} else {
			$this->cError	= "Sorry " . $this->cAction . " doesn't seem to exist";
		}

		//error
		if ($this->cError && $this->bDebug) { $this->debugTemplates(); }

		return $cReturn;
	}

	/**
	 * Template_Abstract::getChoice()
	 *
	 * @return string
	 */
	protected function getChoice() {
		$cReturn	= false;

		$cLegacy1	= PAGES . $this->cPage . "/" . $this->cAction . "/" . $this->cAction . ucfirst($this->cChoice) . ".php";
		$this->addDebug("Legacy 1", $cLegacy1);

		$cLegacy2	= PAGES . $this->cPage . "/" . $this->cAction . "/" . $this->cChoice . ".php";
		$this->addDebug("Legacy 2", $cLegacy2);

		$cLegacy3	= PAGES . $this->cPage . "/" . $this->cAction . "/" . $this->cAction . "/" . $this->cChoice . ".php";
		$this->addDebug("Legacy 3", $cLegacy3);

		$cModern	= PAGES . $this->cPage . "/" . $this->cAction . "/" . $this->cChoice . "/" . $this->cChoice . ".php";
		$this->addDebug("Modern", $cModern);

		//now does any of that actually exist
		if (file_exists($cLegacy1)) {
			$cReturn		= $cLegacy1;
		} else if (file_exists($cLegacy2)) {
			$cReturn		= $cLegacy2;
		} else if (file_exists($cLegacy3)) {
			$cReturn		= $cLegacy3;
		} else if (file_exists($cModern)) {
			$cReturn		= $cModern;
		} else {
			$this->cError	= "Sorry " . $this->cChoice . " doesnt't seem to exist";
		}

		if ($this->cError && $this->bDebug) { $this->debugTemplates(); }

		return $cReturn;
	}

	/**
	 * Template_Abstract::getOther()
	 *
	 * @return string
	 */
	protected function getOther() {
		$cReturn		= false;
		$bForward		= false;
		$iStart			= 0;
		$cFinalParam	= PAGES . $this->cPage . "/" . $this->cAction . "/" . $this->cChoice;

		//is there one forwards
		if (isset($this->cParam1)) {
			$cParamPath	= "/" . $this->cParam1;

			//try it forward
			for ($i = 2; $i < $this->extraParams; $i++) {
				$cParam	= "cParam" . $i;
				if ($this->$cParam) { //does the param actually exist
					$cParam1	= $cFinalParam . $cParamPath . ".php";
					$this->addDebug("Param 1", $cParam1);

					$cParam2	= $cFinalParam . $cParamPath . "/" . $this->$cParam . ".php";
					$this->addDebug("Param 2", $cParam2);

					//does any of them exist
					if (file_exists($cParam1)) {
						$cPage		= $cParam1;
						$bForward	= true;
					} else if (file_exists($cParam2)) {
						$cPage		= $cParam2;
						$bForward	= true;
					}

					//has it been found
					if ($bForward) {
						break;
					} else {
						$cParamPath .= "/" . $this->$cParam;
					}
				}
			}

			//try it backwards
			if (!$bFoward) {
				$iFinalParam	= $this->extraParams;
				$cInterParam	= false;

				//get the last one
				for ($i = $iFinalParam; $i > 0; $i--) {
					$cThisParam		= $cParam . $i;
					$cNextParam		= false;
					$iLastParam		= $i;
					$cFinalParam1	= false;

					//make sure that the top param actually exists
					if (isset($this->$cThisParam)) {
						//now we need to add up the path from the beginning
						for ($j = 0; $j < $iLastParam; $j++) {
							$cNextParam	= "cParam" . $j;

							//add a slash to end
							if ($j == 1) { $cInterParam .= "/"; }
							$cInterParam	.= $cNextParam . "/";
							$this->addDebug("Inter Param", $cInterParam);

							$cFinalParam1	= $cInterParam . $cNextParam;
							$this->addDebug("Final Param 1", $cFinalParam1);
						}

						$cFinalParam1 	.= ".php";
						$cFinalParam2	 = $cFinalParam . $cFinalParam1;
						$this->addDebug("Final Param 2", $cFinalParam2);

						//check if it exists
						if (file_exists($cFinalParam2)) {
							$cPage	= $cFinalParam2;
							break;
						}
					}
				}
			}

			//debug
			if (!$cPage) {
				$this->cError	= "Nothing seems to exist";
				if ($this->bDebug) { $this->debugTemplates(); }
			}

			return $cPage;
		}
	}

	/**
	 * Template_Abstract::getCaller()
	 *
	 * @return string
	 */
	public function getCaller($bDebug = false) {
		$aDebug		= debug_backtrace(false, 7);
		$cFile		= false;
		$aDebugger	= false;

		//go through the debug
		foreach ($aDebug AS $debug) {
			switch($debug['function']) {
				case "getCore":
				case "setLayout":
				case "getMainPage":
					if (isset($debug['args']) && isset($debug['args'][0])) {
						$cFile = $debug['args'][0];
						$this->addDebug("Back Trace", $debug);
					}
					break;
			}

			$aDebugger['function'][] 	= $debug['function'];
			$aDebugger['args'][]		= $debug['args'];
		}

		//debug caller function
		if ($bDebug) { return $aDebugger; }

		//no file, and debug is turned on
		if (!$cFile && $this->bDebug) { $this->debugTemplates(); }

		return $cFile;
	}

	/**
	 * Template_Abstract::addForm()
	 *
	 * @param bool $bObject
	 * @return object
	 */
	public function addForm($bObject = null) {
		$oForm			= new Form($this);
		$oForm->bObject	= $bObject;
		$this->oForm	= $oForm;

		return $oForm;
	}
}
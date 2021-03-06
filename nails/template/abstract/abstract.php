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
abstract class Template_Abstract extends Template_Abstract_Extend {
	protected $mParams;
	protected $cDefault;
	protected $aDebug;
	protected $bDebug;
	protected $bFormed;

	public $cCaller;

	public $bStatic;

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
		$bDebug	= false;
		if (isset($this->aDebug)) { $bDebug = true; }
		if (isset($this->oType) && isset($this->oType->aDebug)) { $bDebug = true; }

		if ($bDebug) {
			if (isset($this->oType)) { printRead($this->oType->aDebug, "Type Debug"); }
			printRead($this->aDebug, "Top Level Debug");

			//get rid of hammer for debug
			unset($this->aVars['oHammer']);
			unset($this->aVars['hammer']);
			unset($this->aVars['Hammer']);

			//now return the object

			if (isset($this->oType)) { printRead($this->oType, "Type Object"); }
			printRead($this, "Template Object");
			die();
		}
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

		//static page
		if ($this->cTemplate && $this->bStatic) { return $this->cTemplate; }

		//final check just incase somehow it got here and it still dindt exist
		if (!file_exists($this->cTemplate)) { return $this->errorTemplate(); }

		//remove the parents
		$this->removeParents();

		//get the form if there is one
		if (!$this->bFormed) { $this->getForm(); }

		//add defaults
		$this->addDefaults();

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
		switch($this->cCaller) {
			case "content_template":
			case "layout_template":
				unset($this->aVars["this"]);
				unset($this->aVars["Hammer"]);
				unset($this->aVars["oHammer"]);

				//make absolute certain
				$this->aVars["this"]	= false;
				$this->aVars["Hammer"]	= false;
				$this->aVars["oHammer"]	= false;
				break;
		}
	}

	/**
	 * Template_Abstract::stripHammer()
	 *
	 * @desc This is so I can manually strip hammer
	 * @return null
	 */
	public function stripHammer() {
		unset($this->aVars["this"]);
		unset($this->aVars["Hammer"]);
		unset($this->aVars["oHammer"]);

		//make absolute certain
		$this->aVars["this"]	= false;
		$this->aVars["Hammer"]	= false;
		$this->aVars["oHammer"]	= false;

		$this->oHammer	= null;

		//strip hammer from a higher level
		$this->stripHammerUpper();
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
				$this->cError = "Sorry " . $this->cPage . " Doesn't seem to exist";
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
			$this->cError	= "Sorry " . $this->cAction . " Doesn't seem to exist";
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
		$aExtras		= array();
		$iStart			= 0;
		$cFinalParam	= PAGES . $this->cPage . "/" . $this->cAction . "/" . $this->cChoice;
		$cPage			= false;

		if ($this->iExtras) {
			$bFound	= false;
			for ($i = $this->iExtras; $i > 0; $i--) {
				$cPath = PAGES . $this->cPage . "/" . $this->cAction . "/" . $this->cChoice;
				for ($j = 1; $j < ($i + 1); $j++) {
					$cParam = "cParam" . $j;
					$cPath .= "/" . $this->$cParam;
					$aExtras[] = $cPath;
				}


				$cPath1 = $cPath . "/" . $this->$cParam . ".php";
				$cPath2 = $cPath . "/" . $this->$cParam . "/" . $this->$cParam . ".php";

				if (file_exists($cPath1)) {
					$cPage		= $cPath1;
					$bFound		= true;
				}
				if (!$bFound && file_exists($cPath2)) {
					$cPage		= $cPath2;
					$bFound		= true;
				}

				//since we dont need to go further down the chain
				if ($bFound == true) {
					break;
				} else {
					$this->cError = "Sorry " . $this->$cParam . " Doesn't Seem To Exist";
				}
			}
		}

		//debug
		if (!$cPage) {
			$oStatic	= new Template_Static($this->mParams);
			$cStatic	= $oStatic->getStatic();

			if ($cStatic) {
				$this->bStatic	= true;
				$this->cError 	= false;
				return $cStatic;
			} else {
				if ($this->bDebug) { $this->debugTemplates(); }
			}
		}

		return $cPage;
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
		$i	= 0;
		foreach ($aDebug AS $debug) {
			switch($debug['function']) {
				case "getCore":
				case "getLayout":
				case "getMainPage":
					if (isset($debug['args']) && isset($debug['args'][0])) {
						$cFile = $debug['args'][0];
						$this->addDebug("Back Trace " . $i, $debug);
						$i++;
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

	/**
	 * Template_Abstract::addValidate()
	 *
	 * @return object
	 */
	public function addValidate() {
		$oHammer			= Hammer::getHammer();
		$oValidate			= $oHammer->getValidator();
		$this->oValidate	= $oValidate;

		return $oValidate;
	}
}
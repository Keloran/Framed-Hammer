<?php
abstract class Template_Abstract_Extend {
	use Browser, Mailer, Layout;

	private $aData;

	protected $aVars;
	protected $cTemplate;

	public $cError;

	/**
	 * Template_Abstract_Extend::__isset()
	 *
	 * @param string $cName
	 * @return bool
	 */
	public function __isset($cName) {
		if (isset($this->aData[$cName])) { return true; }

		return false;
	}

	/**
	 * Template_Abstract_Extend::__get()
	 *
	 * @param string $cName
	 * @return mixed
	 */
	public function __get($cName) {
		if (isset($this->aData[$cName])) { return $this->aData[$cName]; }

		return false;
	}

	/**
	 * Template_Abstract_Extend::__set()
	 *
	 * @param string $cName
	 * @param mixed $mValue
	 * @return null
	 */
	public function __set($cName, $mValue) {
		$this->aData[$cName] = $mValue;
	}

	/**
	 * Template_Abstract_Extend::setVars()
	 *
	 * @param string $cName
	 * @param mixed $mVars
	 * @return null
	 */
	public function setVars($cName, $mVars) {
		//unset the previous to stop scalar conflict
		unset($this->aVars[$cName]);

		//now add back to the array
		if (is_array($mVars)) {
			foreach ($mVars as $cVar => $mValue) {
				$this->aVars[$cName][$cVar]	= $mValue;
			}
		} else {
			$this->aVars[$cName] = $mVars;
		}
	}

	/**
	 * Template_Abstract_Extend::addDefaults()
	 *
	 * @return null
	 */
	public function addDefaults() {
		//set cJS to blank
		if (!isset($this->aVars['cJS'])) { $this->setVars("cJS", false); }
	}

	/**
	 * Template_Abstract_Extend::stripHammerUpper()
	 *
	 * @return null
	 */
	public function stripHammerUpper() {
		unset($this->aVars["this"]);
		unset($this->aVars["Hammer"]);
		unset($this->aVars["oHammer"]);

		//make absolute certain
		$this->aVars["this"]	= false;
		$this->aVars["Hammer"]	= false;
		$this->aVars["oHammer"]	= false;

		$this->oHammer	= null;
	}


	public function errorTemplate($cCalled = false) {
		http_response_code(404); //set page to not found
		$cTemplate	= false;

		if ($this->cError) {
			//is there a site error template
			if (file_exists(SITEPATH . "/layout/error.tpl")) {
				ob_start();
					include SITEPATH . "/layout/error.tpl";
					$cTemplate	= ob_get_contents();
				ob_end_clean();
			} else {
				$cTemplate	= "<section id=\"error\">\n";
				$cTemplate .= "<header>\n";
				$cTemplate .= "<h1>Error</h1>\n";
				$cTemplate .= "</header>\n";
				$cTemplate .= "<article>\n";
				$cTemplate .= $this->cError . "\n";

				//is there a referer page
				if (isset($_SERVER['HTTP_REFERER'])) {
					$cTemplate .= "<hr />\n";
					$cTemplate .= "<a href=\"" . $_SERVER['HTTP_REFERER'] . "\" title=\"Back\">Back</a>\n";
				} else {
					$cTemplate .= "<hr />\n";
					$cTemplate .= "<a href=\"/\" title=\"Back\">Back</a>\n";
				}

				$cTemplate .= "</article>\n";
				$cTemplate .= "</section>\n";
			}

			return $cTemplate;
		} else {
			$this->cError = "Page Not Found";
			return $this->errorTemplate();
		}
	}
}
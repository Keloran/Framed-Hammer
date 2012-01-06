<?php
abstract class Template_Abstract_Extend {
	use Browser, Mailer, Layout;

	private $aData;
	protected $aVars;

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

	public function addDefaults() {
		//set cJS to blank
		if (!isset($this->aVars['cJS']) { $this->setVars("cJS", false); }
	}
}
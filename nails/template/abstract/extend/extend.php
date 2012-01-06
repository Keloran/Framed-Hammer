<?php
class Template_Abstract_Extend {
	#use Browser, Mailer, Layout;

	private $aData;

	/**
	 * Template_Abstract_Extend::__construct()
	 *
	 */
	public function __construct() {

	}

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


}
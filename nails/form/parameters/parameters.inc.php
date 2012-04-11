<?php
/**
 * Form_Parameters
 *
 * @package Form
 * @author keloran
 * @copyright Copyright (c) 2010
 * @version $Id$
 * @access public
 */
class Form_Parameters {
	private $aElement = array();

	/**
	 * Form_Parameters::__construct()
	 *
	 */
	public function __construct() {}

	/**
	 * Form_Parameters::__set()
	 *
	 * @param string $cName
	 * @param mixed $mValue
	 * @return null
	 */
	public function __set($cName, $mValue) {
		$this->aElement[$cName]	= $mValue;
	}

	/**
	 * Form_Parameters::__get()
	 *
	 * @param string $cName
	 * @return mixed
	 */
	public function __get($cName) {
		$mReturn	= false;

		if (isset($this->aElement[$cName])) {
			$mReturn = $this->aElement[$cName];
		}

		return $mReturn;
	}
}
<?php
/**
 * Form_Value
 *
 * @package
 * @author keloran
 * @copyright Copyright (c) 2010
 * @version $Id$
 * @access public
 */
class Form_Value {
	private $cName;
	private $bFile;
	private $iNum;
	private $cType;
	private $bObject;

	public $mValue;

	/**
	 * Form_Value::__construct()
	 *
	 * @param string $cName
	 * @param bool $bFile
	 * @param int $iNum
	 */
	public function __construct($bObject = false) {
		$this->bObject = $bObject;
	}

	/**
	 * Form_Value::setName()
	 *
	 * @param string $cName
	 * @return object
	 */
	public function setName($cName) {
		$this->cName = $cName;

		return $this;
	}

	/**
	 * Form_Value::setType()
	 *
	 * @param string $cType
	 * @return object
	 */
	public function setTyped($cType) {
		$this->cType = $cType;

		return $this;
	}

	public function __call($cFunction, $mValue) {
		$cType		= "Form_" . $this->cType;
		$oType		= new $cType();
		$oType->mValue 	= $this->mValue;
		return $oType->$cFunction($mValue);
	}

	/**
	 * Form_Value::getValue()
	 *
	 * @return mixed
	 */
	public function getValue() {
		$bObject	= $this->bObject;

		//special handlings
		switch ($this->cType) {
			case "file":
				if ($bObject) {
					$this->mValue = $this->getFile();
					return $this;
				} else {
					return $this->getFile();
				}
				break;

			case "checkbox":
				if ($bObject) {
					$this->mValue = $this->getCheckbox();
					return $this;
				} else {
					return $this->getCheckbox();
				}
				break;

			default:
				if ($bObject) {
					$this->mValue = $this->getDefault();
					return $this;
				} else {
					return $this->getDefault();
				}
				break;
		}
	}

	/**
	 * Form_Value::getDefault()
	 *
	 * @return string
	 */
	private function getDefault() {
		$cName	= $this->cName;

		if (isset($_POST[$cName])) {
			return $_POST[$cName];
		}

		return false;
	}

	/**
	 * Form_Value::getCheckbox()
	 *
	 * @return bool
	 */
	private function getCheckbox() {
		$cName		= $this->cName;
		$bReturn	= 0;

		if (isset($_POST[$cName])) {
			if ($_POST[$cName] == "on") {
				$bReturn = 1;
			}
		}

		return $bReturn;
	}

	/**
	 * Form_Value::getFile()
	 *
	 * @return mixed
	 */
	private function getFile() {
		$cName		= $this->cName;
		$iName		= $this->iNum;
		$mReturn	= false;

		if (isset($_FILES[$cName])) {
			if (is_int($iName)) {
				$mReturn['name']	= $_FILES[$cName]['name'][$iName];
				$mReturn['type']	= $_FILES[$cName]['type'][$iName];
				$mReturn['size']	= $_FILES[$cName]['size'][$iName];
				$mReturn['tmpName']	= $_FILES[$cName]['tmp_name'][$iName];
			} else {
				$mReturn['name']	= $_FILES[$cName]['name'];
				$mReturn['type']	= $_FILES[$cName]['type'];
				$mReturn['size']	= $_FILES[$cName]['size'];
				$mReturn['tmpName']	= $_FILES[$cName]['tmp_name'];
			}

			//if it doesnt have a name, pointless returning anything else
			if (!$mReturn['name']) { return false; }

			$mReturn['tmp']		= $mReturn['tmpName'];

			//get the extension on if your inside a single one
			if (!is_array($mReturn['name'])) {
				$iPoint				= (stripos($mReturn['name'], ".") + 1);
				$cExt				= substr($mReturn['name'], $iPoint);
				$mReturn['ext']		= strtolower($cExt);
			} else {
				$mReturn['ext']		= "unknown";
			}

			//There has been an error in the file upload
			switch ($_FILES[$cName]['error']) {
				case 1:
				case 2:
					$mReturn['error']	= "File too big";
					break;

				case 3:
					$mReturn['error']	= "File only partially uploaded";
					break;

				case 4:
					$mReturn['error']	= "File didn't exist, usually this is caused by trying to upload a symlink";
					break;

				case 5:
					$mReturn['error']	= "File couldn't be saved, a server side error";
					break;

				case 6:
					$mReturn['error']	= "File type isnt allowed";
					break;

				default:
					$mReturn['error']	= "";
					break;
			}
		}

		return $mReturn;
	}
}

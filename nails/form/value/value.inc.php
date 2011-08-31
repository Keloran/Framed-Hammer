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
	 * @var bool $bObject
	 */
	public function __construct($bObject = false) {
		$this->bObject = $bObject;
	}

	/**
	 * Form_Value::__call()
	 *
	 * @param string $cFunction
	 * @param mixed $mValue
	 * @return mixed
	 */
	public function __call($cFunction, $mValue) {
		$cType		= "Form_" . $this->cType;
		$oType		= new $cType();
		$oType->mValue 	= $this->mValue;
		return $oType->$cFunction($mValue);
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

	/**
	 * Form_Value::getValue()
	 *
	 * @return mixed
	 */
	public function getValue() {
		//special handlings
		switch ($this->cType) {
			case "file":
				return $this->getFile();
				break;

			case "checkbox":
				return $this->getCheckbox();
				break;

			default:
				return $this->getDefault();
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
			if ($this->bObject) {
				$this->mValue	= $_POST[$cName];
				return $this;
			} else {
				return $_POST[$cName];
			}
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

	/**
	 * Form_Value::validate()
	 *
	 * @param string $cType
	 * @return string
	 */
	public function validate($cType = "text") {
		$mReturn	= false;

		switch($cType) {
			case "email":
				$mReturn = $this->validateEmail();
				break;

			case "number":
				$mReturn = $this->validateNumber();
				break;

			default:
				$mReturn = $this->validateText();
				break;
		}

		return $mReturn;
	}

	/**
	 * Form_Value::addValidate()
	 *
	 * @desc this is a link to validate
	 * @param string $cType
	 * @return mixed
	 */
	public function addValidate($cType = "text") {
		return $this->validate($cType);
	}

	/**
	 * Form_Value::validateEmail()
	 *
	 * @return string
	 */
	private function validateEmail() {
		$cReturn	= false;

		if ($this->bObject) {
			$cInput	= $this->mValue;
		} else {
			$cInput	= $this->getValue();
		}

		//Might aswell use filter var if its avalible, less resource-hungry
		if (function_exists("filter_var")) {
			$cReturn	= filter_var($cInput, FILTER_VALIDATE_EMAIL);
		} else {
			$cPattern = "([\\w-+]+(?:\\.[\\w-+]+)*@(?:[\\w-]+\\.)+[a-zA-Z]{2,7})";
			if (preg_match($cPattern, $cInput)) {
				$cReturn = $cInput;
			}
		}

		return $cReturn;
	}

	/**
	 * Form_Value::validateText()
	 *
	 * @return string
	 */
	private function validateText() {
		$cReturn	= false;

		if ($this->bObject) {
			$cInput	= $this->mValue;
		} else {
			$cInput	= $this->getValue();
		}

		//It doesnt have anything
		if (!isset($cInput[0])) { return false; }

		//Use filter var
		if (function_exists("filter_var")) {
			$aFilters	= array(FILTER_FLAG_ENCODE_HIGH, FILTER_FLAG_ENCODE_LOW, FILTER_FLAG_ENCODE_AMP);
			$cReturn	= filter_var($cInput, FILTER_SANITIZE_STRING, $aFilters);
		} else {
			if (preg_match("`([a-zA-Z0-9\-_]+)`is", $cInput)) {
				$cReturn = addslashes($cInput);
			}
		}

		return $cReturn;
	}

	/**
	 * Form_Value::validateNumber()
	 *
	 * @return string
	 */
	private function validateNumber() {
		$cReturn	= false;

		if ($this->bObject) {
			$cInput	= $this->mValue;
		} else {
			$cInput	= $this->getValue();
		}

		//its not actually got any chars
		if (!isset($cInput[0])) { return false; }

		if (function_exists("filter_var")) {
			if (filter_var($cInput, FILTER_VALIDATE_INT)) {
				$cReturn	= filter_var($cInput, FILTER_SANITIZE_NUMBER_INT);
			}
		} else {
			if (preg_match("`([0-9\s]+)`is", $cInput)) {
				$cReturn = addslashes($cInput);
			}
		}

		return $cReturn;
	}
}
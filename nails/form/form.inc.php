<?php
/**
 * Template_Formed
 *
 * @package
 * @author Keloran
 * @copyright Copyright (c) 2009
 * @version $Id: formed.inc.php 462 2009-12-28 23:15:16Z keloran $
 * @access public
 */
class Form {
	private $aReturn;
	private $aForm;
	private $aFormed;
	private $aData	= array();

	//Vars for the end form, all private you have to use setters
	private $cMethod 		= "post";
	private $cEncType		= "application/x-www-form-urlencoded";

	//errors are public
	public $aErrors			= array();

	//Reference to itself for singleton purposes
	static $oForm;

	//get elements
	private $oTextArea;
	private $oInput;
	private $oButton;

	//this so a user can grab the rendered object
	public $oFullForm;

	//This is for seperation
	public $oNails			= false;
	private $oTemplate		= false;

	/**
	 * Form::__construct()
	 *
	 */
	public function __construct($oTemplate = false) {
		$this->oTemplate	= null;
		$this->oTemplate	= $oTemplate;

		if ($oTemplate instanceof Template) {
			$this->oNails		= null;
			$this->oNails		= $this->oTemplate->oNails;

			$this->cFile		= $this->oTemplate->cFormTemplate;

			//see if this can remove the extra stuff
			$this->oTemplate->oForms	= null;


		}

		//Clear the form, so that singleton can be used
		$this->aForm	= null;
		$this->aFormed	= null;
		$this->bSearch	= null;

		$this->aForm	= array();
		$this->aFormed	= array();
	}

	/**
	 * Form::__set()
	 *
	 * @param string $cName
	 * @param mixed $mValue
	 * @return
	 */
	public function __set($cName, $mValue) {
		$this->aData[$cName]	= $mValue;
		return $this;
	}

	/**
	 * Form::__get()
	 *
	 * @param string $cName
	 * @return mixed
	 */
	public function __get($cName) {
		$mReturn	= false;

		if (isset($this->aData[$cName])) { $mReturn	= $this->aData[$cName]; }

		return $mReturn;
	}

	/**
	 * Form::__isset()
	 *
	 * @param string $cName
	 * @return bool
	 */
	public function __isset($cName) {
		$bReturn	= false;

		if (isset($this->aData[$cName])) { $bReturn = true; }
		if (isset($this->$cName)) { $bReturn = true; }

		return $bReturn;
	}

	/**
	 * Form::setOrder()
	 *
	 * @desc this is for old sites and is no-longer in use, order is now determined organic
	 */
	public function setOrder() {
		return $this;
	}

	/**
	 * Form::clearAll()
	 *
	 * @return null
	 */
	public function clearAll() {
		$this->aFormed	= null;
		$this->aForm	= null;
	}

	/**
	 * Form::setMethod()
	 *
	 * @param bool $bMethod
	 * @return
	 */
	public function setMethod($bMethod = false) {
		if ($bMethod) {
			$this->cMethod = "get";
		} else {
			$this->cMethod = "post";
		}
	}

	/**
	 * Form::setID()
	 *
	 * @param string $cID
	 * @return
	 */
	public function setID($cID) {
		$this->cFormID = $cID;
	}

	/**
	 * Form::setClass()
	 *
	 * @param string $cClass
	 * @return
	 */
	public function setClass($cClass) {
		$this->cFormClass = $cClass;
	}

	/**
	 * Form::setLabelClass()
	 *
	 * @param string $cClass
	 * @return
	 */
	public function setLabelClass($cClass) {
		$this->cLabelClass = $cClass;
	}

	/**
	 * Form::setAction()
	 *
	 * @param string $cAction
	 * @return
	 */
	public function setAction($cAction) {
		$this->cFormAction = $cAction;
	}

	/**
	 * Form::setFormName()
	 *
	 * @param string $cName
	 * @return
	 */
	public function setFormName($cName) {
		$this->cFormName = $cName;
	}

	/**
	 * Form::setFormID()
	 *
	 * @desc this is for back compat, use setID (since it uses that internally)
	 * @param string $cID
	 * @return
	 */
	public function setFormID($cID) {
		$this->setID($cID);
	}

	/**
	 * Form::setTitle()
	 *
	 * @param string $cTitle
	 * @param string $cClass
	 * @return
	 */
	public function setTitle($cTitle, $cClass = false) {
		if ($cClass) { $this->cTitleClass = $cClass; }

		$this->cFormTitle = $cTitle;
	}

	/**
	 * Form::addCheckbox()
	 *
	 * @param mixed $mName
	 * @param string $cLabel
	 * @param bool $bChecked
	 * @return null
	 */
	public function addCheckbox($mName, $cLabel = false, $bChecked = false, $cClass = false) {
		$cElement	= false;

		if (is_array($mName)) { //better way
			foreach ($mName as $cKey => $cValue) {
				switch ($cKey) {
					case "name":
						$cName = $cValue;
						break;

					case "label":
					case "title":
						$cLabel = $cValue;
						break;

					case "value":
					case "checked":
					case "selected":
						$bChecked = true;
						break;

					case "class":
						$cClass = $cValue;
						break;

					case "addElement":
						$cElement = $cValue;
						break;
				}
			}
		} else { //old way
			$cName = $mName;
		}

		if ($bChecked) {
			$cChecked = "checked";
		} else {
			$cChecked = false;
		}

		$aInput = array(
			"name" 		=> $cName,
			"label"		=> $cLabel,
			"class"		=> $cClass,
			"type"		=> "checkbox",
			"checked"	=> $cChecked
		);
		$this->addInput($aInput);
	}

	/**
	 * Form::addInput()
	 *
	 * @param mixed $mData
	 * @param string $cLabel
	 * @param string $cClass
	 * @param string $cValue
	 * @param string $cType
	 * @param string $mError
	 * @param string $cJS Javascript to use (this is for old sites only, new sites should really use jQuery)
	 * @param mixed $mParams
	 * @return null
	 */
	public function addInput($mData, $cLabel = false, $cClass = false, $cValue = false, $cType = false, $mError = false, $cJS = false, $mParams = false) {
		$aData			= false;
		$cPlaceHolder	= false;
		$cElement		= false;
		$cElementClass	= false;
		$cElementID		= false;
		$cName			= false;
		$cTitle			= false;
		$bLabel			= false;

		if (is_array($mData)) {
			foreach ($mData as $cKey => $mValue) {
				switch($cKey) {
					case "label":
						$cLabel = $mValue;
						$cTitle	= $mValue;
						break;

					case "title":
						$cTitle	= $mValue;
						$cLabel	= $mValue;
						break;

					case "class":
						$cClass = $mValue;
						break;

					case "value":
						$cValue = $mValue;
						break;

					case "type":
						$cType = $mValue;
						break;

					case "error":
						$mError = $mValue;
						break;

					case "javascript":
						$cJS = $mValue;
						break;

					case "params":
						$mParams = $mValue;
						break;

					case "name":
						$cName = $mValue;
						break;

					case "placeholder":
						$cPlaceHolder = $mValue;
						break;

					case "nolabel":
						$bLabel		= true;
						break;

					case "addElement":
						if (is_array($mValue)) {
							$cElement 		= $mValue['element'];

							if (isset($mValue['class'])) {	$cElementClass	= $mValue['class']; }
							if (isset($mValue['id'])) { 	$cElementID		= $mValue['id']; }
						} else {
							$cElement = $mValue;
						}
						break;

					default:
						$aData[] = array($cKey => $mValue);
						break;
				} // switch
			}
		} else {
			$cName	= $mData;
			$cTitle	= $cLabel;
			$bLabel	= false;
		}

		//set this to text by default
		if (!$cType) { $cType = "text"; }
		//$aType[] = array("extraType" => $cType);

		//placeholder
		if (!$cPlaceHolder) { $cPlaceHolder = $cLabel; }

		$oInput = new Form_Input($cName);
		$oInput->setValue($cValue)
			->setClass($cClass)
			->setID($cName)
			->setError($mError)
			->addExtras($aData)
			->addTitle($cTitle)
			->setLabel($cLabel, $this->cLabelClass)
			->setSearch($bLabel)
			->setPlaceHolder($cPlaceHolder)
			->addSurrowned($cElement, $cElementClass, $cElementID)
			->setTyped($cType);

		//JS
		if ($cJS) { $oInput->addExtras(array("js" => $cJS)); }

		//Params
		if ($mParams) {
			switch ($mParams) {
				case "readonly":
					$aParams[] = array("readonly" => true);
					break;

				case "checked":
					$aParams[] = array("checked" => true);
					break;

				case "focus":
					$aParams[] = array("autofocus" => true);
					break;

				default:
					$aParams[] = array("list" => $mParams);
					break;
			}

			$oInput->addExtras($aParams);
		}

		$this->aFormed[] = $oInput;

		return $oInput;
	}

	/**
	 * Form::addPassword()
	 *
	 * @param mixed $mName
	 * @param string $cLabel
	 * @param string $cClass
	 * @param mixed $mError
	 * @return
	 */
	public function addPassword($mName, $cLabel = false, $cClass = false, $cValue = false, $mError = false) {
		if (is_array($mName)) { //better way
			foreach ($mName as $cKey => $cValued) {
				switch ($cKey) {
					case "name":
						$cName = $cValued;
						break;

					case "label":
					case "title":
						$cLabel = $cValued;
						$cTitle	= $cValued;
						break;

					case "class":
						$cClass = $cValued;
						break;

					case "value":
						$cValue = $cValued;
						break;

					case "error":
						$mError = $cValued;
						break;
				}
			}
		} else { //old way
			$cName	= $mName;
			$cTitle	= $cLabel;
		}

		$aInput = array(
			"name"		=> $cName,
			"label"		=> $cLabel,
			"class"		=> $cClass,
			"value"		=> $cValue,
			"type"		=> "password",
			"error"		=> $mError,
			"title"		=> $cTitle
		);
		$this->addInput($aInput);
	}

	/**
	 * Form::addElement()
	 *
	 * @param mixed $mParams
	 * @return null
	 */
	public function addElement($mParams) {
		$cClass	= false;
		$cID	= false;
		$cType	= false;
		$cValue = false;

		if (is_array($mParams)) { //a better way, an even better one would be object usage
			foreach ($mParams as $cKey => $cValued) {
				switch ($cKey) {
					case "class":
						$cClass = $cValued;
						break;

					case "id":
						$cID	= $cValued;
						break;

					case "type":
						$cType = $cValued;
						break;

					case "value":
						$cValue = $cValued;
						break;
				}
			}
		} else {
			$cType = $mParams;
		}

		//since this can be anything unless an id is given, make it a rand
		if ($cID) {
			$cName = $cID;
		} else {
			$cName = "element" . rand();
		}

		$oElement	= new Form_Element($cName);
		$oElement->setElement($cType)
			->addExtras(array("id" => $cID))
			->addExtras(array("class" => $cClass))
			->setValue($cValue);

		$this->aFormed[] = $oElement;
	}

	/**
	 * Form::addSearch()
	 *
	 * @desc This is to create a HTML5 searchbox (no real native support yet)
	 * @param string $cName
	 * @param string $cLabel
	 * @param string $cClass
	 * @param string $cValue
	 * @param string $cList
	 * @return null
	 */
	public function addSearch($cName, $cLabel = false, $cClass = false, $cValue = false, $cList = false) {
		$this->addInput($cName, $cLabel, $cClass, $cValue, "search", false, false, $cList);
		$this->makeSearch();
	}

	/**
	 * Form::addDatePicker()
	 *
	 * @desc This is to create a HTML5 datepicker (only does it native in Opera and MobileSafari)
	 * @param string $cName
	 * @param string $cLabel
	 * @param string $cClass
	 * @param string $cValue
	 * @return null
	 */
	public function addDatePicker($cName, $cLabel = false, $cClass = false, $cValue = false) {
		$this->addInput($cName, $cLabel, $cClass, $cValue, "date");
	}

	/**
	 * Form::addEmail()
	 *
	 * @desc This creates an HTML5 email element (only works nativly in MobileSafari {proberlly only place it will nativly work})
	 * @param string $cName
	 * @param string $cLabel
	 * @param string $cClass
	 * @param string $cValue
	 * @return
	 */
	public function addEmail($cName, $cLabel = false, $cClass = false, $cValue = false) {
		$this->addInput($cName, $cLabel, $cClass, $cValue, "email");
	}

	/**
	 * Form::addText()
	 *
	 * @param mixed $mName
	 * @param string $cLabel
	 * @param string $cClass
	 * @param string $cValue
	 * @param mixed $mError
	 * @return null
	 */
	public function addText($mName, $cLabel = false, $cClass = false, $cValue = false, $bbCode = false, $mError = false) {
		$cElement 		= false;
		$cElementClass	= false;
		$cElementID		= false;
		$cPlaceHolder	= false;
		$cTitle			= false;

		if (is_array($mName)) { //better way
			foreach ($mName as $cKey => $mValued) {
				switch ($cKey) {
					case "name":
						$cName = $mValued;
						break;

					case "class":
						$cClass = $mValued;
						break;

					case "label":
					case "title":
						$cLabel = $mValued;
						$cTitle	= $mValued;
						break;

					case "bbcode":
					case "bbCode":
						$bbCode = true;
						break;

					case "value":
						$cValue = $mValued;
						break;

					case "error":
						$mError = $mValued;
						break;

					case "placeholder":
						$cPlaceHolder = $mValued;
						break;


					case "addElement":
						if (is_array($mValued)) {
							$cElement 		= $mValued['element'];

							if (isset($mValued['class'])) { $cElementClass	= $mValued['class']; }
							if (isset($mValued['id'])) {	$cElementID		= $mValued['id']; }
						} else {
							$cElement = $mValued;
						}
						break;
				}
			}
		} else { //old way
			$cName 	= $mName;
			$cTitle	= $cLabel;
		}

		$oTextArea = new Form_TextArea($cName);

		//if no placeholder but a label
		if (!$cPlaceHolder) { $cPlaceHolder = $cLabel; }

		$oTextArea->setLabel($cLabel, $this->cLabelClass)
			->setValue($cValue)
			->addExtras($bbCode)
			->addTitle($cTitle)
			->setError($mError)
			->setID($cName)
			->setClass($cClass)
			->addSurrowned($cElement, $cElementClass, $cElementID)
			->setPlaceHolder($cPlaceHolder);

		$this->aFormed[]	= $oTextArea;

		//since bbcode added make sure its set
		if ($bbCode) {
			$this->bBBCode = true;
		}
	}

	/**
	 * Form::addButton()
	 *
	 * @param mixed $mType
	 * @param string $cLabel
	 * @param string $cID
	 * @return
	 */
	public function addButton($mType, $cLabel = false, $cID = false, $cName = false) {
		$cClass	= false;

		if (is_array($mType)) { //better way
			foreach ($mType as $cKey => $cValue) {
				switch ($cKey) {
					case "type":
						$cType = $cValue;
						break;

					case "label":
					case "title":
						$cLabel = $cValue;
						break;

					case "id":
						$cID = $cValue;
						break;

					case "class":
						$cClass = $cValue;

					case "name":
						$cName = $cValue;
						break;
				}
			}
		} else { //old way
			$cType = $mType;
		}

		if (!$this->bSearch) {
			//The type of button, and make a name for it
			switch($cType) {
			case "submit":
				$cName	= "submitButton";
				break;

			case "reset":
				$cName	= "resetButton";
				break;

			case "button":
				if ($cName) {
					$cName = $cName . "Button";
				} else {
					$cName = "buttonButton";
				}
				break;
		}

			if (!$cID) { $cID = $cName . "ID"; }

			$oButton = new Form_Button($cName);
			$oButton->setLabel($cLabel, $this->cLabelClass)
				->addExtras($cType)
				->setID($cID)
				->setClass($cClass);

			$this->aFormed[] = $oButton;
		}
	}

	/**
	 * Form::addOption()
	 *
	 * @param string $cValue
	 * @param string $cDescription
	 * @param bool $bSelected
	 * @param string $cSelect
	 * @return
	 */
	public function addOption($mValue, $cDescription = false, $cSelect = false, $bSelected = false) {
		if (is_array($mValue)) { //its an array, the better way
			foreach ($mValue as $cKey => $cValued) {
				switch($cKey){
					case "value":
						$cValue			= $cValued;
						break;

					case "description":
					case "label":
					case "title":
						$cDescription	= $cValued;
						break;

					case "select":
						$cSelect		= $cValued;
						break;

					case "selected":
						$bSelected		= $cValued;
						break;
				}
			}
		} else {
			$cValue = $mValue;
		}

		if (!$cSelect) { return false; }

		foreach ($this->aFormed as $oFormed) {
			if ($oFormed->getName() == $cSelect) {
				if ($bSelected) { $cValue .= "||"; }
				$aOption	= array($cDescription => $cValue);

				$oFormed->addExtras($aOption);
			}
		}
	}

	/**
	 * Form::addSelect()
	 *
	 * @param mixed $mName
	 * @param string $cLabel
	 * @param array $aOptions
	 * @param string $cClass
	 * @return
	 */
	public function addSelect($mName, $cLabel = false, $aOptions = false, $cClass = false) {
		if (is_array($mName)) { //the better way
			foreach ($mName as $cKey => $mValue) {
				switch($cKey) {
					case "name":
						$cName = $mValue;
						break;

					case "label":
					case "title":
						$cLabel = $mValue;
						$cTitle	= $mValue;
						break;

					case "options":
						$aOptions = $mValue;
						break;

					case "class":
						$cClass = $mValue;
						break;
				} // switch
			}
		} else { //the old way
			$cName	= $mName;
			$cTitle	= $cLabel;
		}


		$oSelect	= new Form_Select($cName);
		$oSelect->setClass($cClass)
			->setID($cName)
			->addTitle($cTitle)
			->setLabel($cLabel, $this->cLabelClass);

		//options
		if ($aOptions) {
			$oSelect->addExtras($aOptions);
		}

		$this->aFormed[] = $oSelect;
	}

	/**
	 * Form::addFile()
	 *
	 * @param mixed $mName
	 * @param string $cLabel
	 * @param string $cClass
	 * @return
	 */
	public function addFile($mName, $cLabel = false, $cClass = false, $cID = null) {
		$this->cEncType = "multipart/form-data";

		//the better way
		if (is_array($mName)) {
			foreach ($mName as $cKey => $cValue) {
				switch ($cKey) {
					case "name":
						$cName = $cValue;
						break;

					case "label":
					case "title":
						$cLabel = $cValue;
						$cTitle	= $cValue;
						break;

					case "class":
						$cClass = $cValue;
						break;

					case "id":
						$cID = $cValue;
						break;
				} // switch
			}
		} else { //the old way
			$cName	= $mName;
			$cTitle	= $cLabel;
		}

		$oFile	= new Form_File($cName);
		$oFile->setClass($cClass)
			->setLabel($cLabel, $this->cLabelClass)
			->addTitle($cTitle)
			->setID($cID);

		$this->aFormed[] = $oFile;
	}

	/**
	 * Form::addProgress()
	 *
	 * @param string $cName
	 * @param int $iProgress
	 * @return null
	 */
	public function addProgress($cName, $iProgress) {
		$oProgress	= new Form_Progress($cName);

		$aExtras	= array(
			array("max", "100"),
			array("min", "1"),
		);

		$oProgress->addExtras($aExtras)
			->setValue($iProgress);

		$this->aFormed[] = $oProgress;
	}

	/**
	 * Form::addMeter()
	 *
	 * @param mixed $mName
	 * @param int $iValue
	 * @param int $iMin
	 * @param int $iMax
	 * @param int $iOptimum
	 * @param string $cType
	 * @return null
	 */
	public function addMeter($mName, $iValue = null, $iMin = null, $iMax = null, $iOptimum = null, $cType = null) {
		if (is_array($mName)) { //better way
			 foreach ($mName as $cKey => $cValue) {
			 	switch ($cKey) {
			 		case "name":
			 			$cName = $cValue;
			 			break;

			 		case "value":
			 			$iValue = $cValue;
			 			break;

			 		case "min":
			 			$iMin = $cValue;
			 			break;

			 		case "max":
			 			$iMax = $cValue;
			 			break;

			 		case "optimum":
			 			$iOptimum = $cValue;
			 			break;

			 		case "type":
			 			$cType = $cValue;
			 			break;
			 	}
			 }
		} else { //old way
			$cName = $mName;
		}


		$oMeter = new Form_Meter($cName);

		//min
		if (!$iMin) { $iMin = 1; }

		//max
		if (!$iMax) { $iMax = 100; }

		//optimum
		if (!$iOptimum) { $iOptimum = 10; }

		//type
		if (!$cType) { $cType = "cm"; }

		$aExtras = array(
			array("min", $iMin),
			array("max", $iMax),
			array("optimum", $iOptimum),
			array("meterType", $cType),
		);

		$oMeter->addExtras($aExtras)
			->setValue($iValue);

		$this->aFormed[] = $oMeter;
	}

	/**
	 * Form::setDivID()
	 *
	 * @param string $cID
	 * @return
	 */
	public function setDivID($cID) {
		$this->cDivID = $cID;
	}

	/**
	 * Form::setDivClass()
	 *
	 * @param string $cClass
	 * @return
	 */
	public function setDivClass($cClass) {
		$this->cDivClass = $cClass;
	}

	/**
	 * Form::getValue()
	 *
	 * @param string $cName
	 * @param bool $bFile
	 * @return mixed
	 */
	public function getValue($mName, $bFile = false, $iName = false) {
		$cType		= false;
		$bObject	= $this->bObject;

		if (is_array($mName)) {
			foreach ($mName as $cKey => $cValue) {
				switch ($cKey) {
					case "name":
						$cName = $cValue;
						break;

					case "type":
						$cType = $cValue;
						break;

					case "object":
						$bObject = $cValue;
						break;

				}
			}
		} else {
			$cName = $mName;
			if ($bFile) { $cType = "file"; }
		}

		//Set the default type to input
		if (!$cType) { $cType = "input"; }

		$oValue = new Form_Value($bObject);
		$oValue->setName($cName)
			->setTyped($cType);

		return $oValue->getValue();
	}

	/**
	 * Form::getParam()
	 *
	 * @param mixed $mName
	 * @param bool $bFile
	 * @param int $iName
	 * @return mixed
	 */
	public function getParam($mName, $bFile = false, $iName = false) {
		return $this->getValue($mName, $bFile, $iName);
	}

	/**
	 * Form::getError()
	 *
	 * @param string $cName
	 * @return
	 */
	protected function getError($cName) {
		$cReturn = false;

		if (array_key_exists($cName, $this->aErrors)) {
			$cReturn = "<span class=\"formError\">\n";
			$cReturn .= $this->aErrors[$cName];
			$cReturn .= "</span>\n";
		}

		return $cReturn;
	}

	/**
	 * Form::setError()
	 *
	 * @param string $cName
	 * @param string $cError
	 * @return
	 */
	public function setError($cName, $cError) {
		$this->aErrors[$cName] = $cError;
	}

	/**
	 * Form::createFile()
	 *
	 * @param array $aElement
	 * @return
	 */
	private function createFile($aElement) {
		$cReturn = $this->createLabel($aElement['name'], $aElement['label']);

		$cReturn .= "<input type=\"file\" ";

		//has an id
		if ($aElement['id']) { $cReturn .= "id=\"" . $aElement['id'] . "\" "; }

		//has a class
		if ($aElement['class']) { $cReturn .= "class=\"" . $aElement['class'] . "\" "; }

		//close the element
		$cReturn .= "name=\"" . $aElement['name'] . "\" />\n";

		//has it got an error
		$cReturn .= $this->getError($aElement['name']);

		return $cReturn;
	}

	/**
	 * Form::makeSearch()
	 *
	 * @desc this is to make the form a search form and not need buttons
	 * @return
	 */
	public function makeSearch() {
		$this->bSearch = true;
	}

	/**
	 * Form::checkForButtons()
	 *
	 * @desc This is to make sure there is a button in the form, since its pointless returning a form that cant be submitted
	 * @return
	 */
	private function checkForButtons() {
		$bButton	= false;

		//return true if its a search form, since its pointless going through the array
		if ($this->bSearch) { return true; }

		//new method
		if (is_array($this->aFormed)) {
			foreach ($this->aFormed as $oFormed) {
				if ($oFormed->getTyped() == "button") {
					$bButton = true;
				}
			}
		}

		return $bButton;
	}

	/**
	 * Form::setStatus()
	 *
	 * @param string $cStatus
	 * @return null
	 */
	public function setStatus($cStatus) {
		$this->cStatus = $cStatus;
	}

	/**
	* Form::setSectionClass()
	*
	* @param string $cClass
	* @return null
	*/
	public function setSectionClass($cClass) {
		$this->cSectionClass = $cClass;
	}

	/**
	 * Form::fullForm()
	 *
	 * @return string
	 */
	public function fullForm($cTemplate = null) {
		$cReturn	= false;
		$bOpened	= false;

		//this is to stop forms without buttons, searchforms can force this true
		if (!$this->checkForButtons()) {
			//reset the form
			$this->aForm = null;
			$this->aForm = array();

			return $cReturn;
		}

		//open the form
		if ($this->cSectionClass || $this->cSectionID) {
			$cReturn	.= "<section ";

			if ($this->cSectionClass) { $cReturn .= " class=\"" . $this->cSectionClass . "\" "; }
			if ($this->cSectionID) { $cReturn .= " id=\"" . $this->cSectionID . "\" "; }

			$cReturn .= ">\n";
		} else {
			$cReturn .= "<section class=\"formSection\">\n";
		}

		//form has a title
		if ($this->cFormTitle) {
			$cReturn .= "<header>\n";
			$cReturn .= "<h1";
			if ($this->cTitleClass) { $cReturn .= " class=\"" . $this->cTitleClass . "\" "; }
			$cReturn .= ">\n";

			$cReturn .= $this->cFormTitle . "</h1>\n";
			$cReturn .= "</header>\n";
		}

		//status
		if ($this->cStatus) {
			$cReturn	.= "<article class=\formStatus\" ";
			if ($this->cFormID) { $cReturn .= "id=\"" . $this->cFormID . "Status\" "; }
			$cReturn	.= ">" . $this->cStatus . "</article>\n";
		}

		//div/section properties set
		if ($this->cDivID || $this->cArticleID) {
			$bOpened	= true;
		} else if ($this->cDivClass || $this->cArticleClass) {
			$bOpened	= true;
		}

		//div has an id
		if ($bOpened) {
			$cClassed	= false;
			$cIDed		= false;

			//has an id been set
			if ($this->cDivID) { $cIDed = " id=\"" . $this->cDivID . "\" "; }
			if ($this->cArticleID) { $cIDed = " id=\"" . $this->cArticleID . "\" "; }

			//has a class been set
			if ($this->cDivClass) { $cClassed = " class=\"" . $this->cDivClass . "\" "; }
			if ($this->cArticleClass) { $cClassed = " class=\"" . $this->cArticleClass . "\" "; }

			$cReturn .= "<article ";
			$cReturn .= $cIDed;
			$cReturn .= $cClassed;
			$cReturn .= ">\n";
		} else {
			$cReturn .= "<article>\n";
		}

		//finish the start of the div
		if ($bOpened) { $cReturn .= ">\n"; }

		//form already opened
		$cReturn .= "<form ";

		//the method e.g. post
		if (!$this->cMethod) { $this->cMethod = "post"; }
		$cReturn .= " method=\"" . $this->cMethod . "\" ";

		//if there is a formname
		if ($this->cFormName) { $cReturn .= " name=\"" . $this->cFormName . "\" "; }

		//form has a class
		if ($this->cFormClass) { $cReturn .= " class=\"" . $this->cFormClass . "\" "; }

		//form has an id
		if ($this->cFormID) { $cReturn .= " id=\"" . $this->cFormID . "\" "; }

		//do the enctype
		$cReturn .= " enctype=\"" . $this->cEncType . "\" ";

		//get the action
		$cReturn .= "action=\"" . $this->cFormAction . "\" ";

		//charset set to utf8
		$cReturn .= "accept-charset=\"UTF-8\" ";

		//close the form opener
		$cReturn .= ">\n";

		$bDoneButton 	= false;
		$bDoneSurrowned	= false;
		$aBBCodes		= array();

		//this creates teh elements
		if (isset($this->aFormed)) {
			$bi = 0;

			foreach ($this->aFormed as $oFormed) {
				if ($oFormed->cFormElementType == "button") {
					if (!$bDoneButton) { //since we dont want it multiple times
						$bDoneButton 	= true;
						$bDoneSurrowned = $this->buttonSurrownedStart();
						$cReturn .= $bDoneSurrowned;
					}
				} else if ($oFormed->cFormElementType == "textarea") {
					$aBBCodes[$bi]['name']		= $oFormed->cFormElementName;
					$aBBCodes[$bi]['options']	= $oFormed->cBBCodeOptions;
					$aBBCodes[$bi]['bBBCode']	= $oFormed->bBBCode;
					$bi++;
				}

				$cReturn .= $oFormed->createElement();
			}
		}

		//now close the surrowned
		if ($bDoneSurrowned) { $cReturn .= $this->buttonSurrownedEnd(); }

		//close the form
		$cReturn .= "</form>\n";

		//article is always opened
		$cReturn .= "</article>\n";

		//close the section
		$cReturn .= "</section>\n";

		//if bbcode make sure the bbcode is added
		if ($this->bBBCode) {
			if (file_exists(SITEPATH . "/js/jquery/bbcode.js")) {
				$cCode	= "/js/jquery/bbcode.js";
			} else if (file_exists(SITEPATH . "/js/jquery.bbcode.js")) {
				$cCode = "/js/jquery.bbcode.js";
			} else {
				$cCode = false;
			}

			$cReturn .= "<script type=\"text/javascript\" src=\"" . $cCode . "\"></script>\n";
			$cReturn .= "<link href=\"/css/bbcode.css\" type=\"text/css\" rel=\"stylesheet\" />\n";

			//add bbcode to the elements now we have it loaded
			$cReturn .= "<script type=\"text/javascript\">\n";
			for ($i = 0; $i < count($aBBCodes); $i++) {
				if ($aBBCodes[$i]['bBBCode']) {
					$cReturn .= "$(\"#" . $aBBCodes[$i]['name'] . "\").bbCode(" . $aBBCodes[$i]['options'] . ");\n";
				}
			}
			$cReturn .= "</script>\n";
		}

		//now that the form is allowed to proced
		/*
		ob_start();
			print($cReturn);
			$cObReturn = ob_get_contents();
		ob_end_clean();

		return $cObReturn;
		*/
		return $cReturn;
	}

	/**
	 * Form::buttonSurrowned()
	 *
	 * @param string $cElement
	 * @param string $cClass
	 * @param string $cID
	 * @return null
	 */
	public function buttonSurrowned($cElement, $cClass = false, $cID = false) {
		$this->cSurrowned 		= $cElement;
		$this->cSurrownedClass	= $cClass;
		$this->cSurrownedID		= $cID;
	}

	/**
	 * Form::buttonSurrownedStart()
	 *
	 * @return string
	 */
	private function buttonSurrownedStart() {
		$cReturn	= false;

		if ($this->cSurrowned) {
			$cReturn = "<" . $this->cSurrowned;

			if ($this->cSurrownedClass) {	$cReturn .= " class=\"" . $this->cSurrownedClass . "\" "; }
			if ($this->cSurrownedID) {		$cReturn .= " id=\"" . $this->cSurrownedID . "\" "; }

			$cReturn .= ">\n";
		}

		return $cReturn;
	}

	/**
	 * Form::buttonSurrownedEnd()
	 *
	 * @return string
	 */
	private function buttonSurrownedEnd() {
		$cReturn = false;

		if ($this->cSurrowned) { $cReturn = "</" . $this->cSurrowned . ">\n"; }

		return $cReturn;
	}

	/**
	 * Form::__destruct()
	 *
	 */
	public function __destruct() {
		$this->oDB	= false;
		$this->oNails	= false;
		$this->aForm	= false;
		$this->aFormed	= false;

		unset($this->oDB);
		unset($this->oNails);
		unset($this->aForm);
		unset($this->aFormed);
	}
}

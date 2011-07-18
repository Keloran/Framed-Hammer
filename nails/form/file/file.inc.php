<?php
/**
 * Form_File
 *
 * @package
 * @author keloran
 * @copyright Copyright (c) 2010
 * @version $Id: file.inc.php 3382 2011-03-02 11:04:47Z keloran $
 * @access public
 */
class Form_File extends Form_Abstract {
	public $cFormElementType = "file";

	/**
	 * Form_File::__construct()
	 *
	 * @param string $cName
	 */
	public function __construct($cName) {
		if ($this->cName !== $cName) {
			$this->cName = $cName;
			$this->addElement("input");
		}
	}

	/**
	 * Form_File::addExtras()
	 *
	 * @desc This sets the type, e.g. password
	 * @return null
	 */
	public function addExtras($mExtras) {
		$cName = $this->cName;

		return $this;
	}

	/**
	 * Form_File::createElement()
	 *
	 * @return string
	 */
	public function createElement() {
		$cName	= $this->cName;

		$cReturn = $this->createLabel($cName);

		$cReturn .= "<input type=\"file\"";

		//has a class
		if ($this->aElement[$cName]['class']) { $cReturn .= "class=\"" . $this->aElement[$cName]['class'] . "\" "; }

		//has an id
		if ($this->aElement[$cName]['id']) { $cReturn .= "id=\"" . $this->aElement[$cName]['id'] . "\" "; }

		//set its name and close the opener
		$cReturn .= "name=\"" . $cName . "\"";

		//HTML5
		$cReturn .= $this->addHTML5();

		//close the element
		$cReturn .= " />\n";

		//Errors
		$cReturn .= $this->addError();

		return $cReturn;
	}

	/**
	 * Form_File::validate()
	 *
	 * @param string $cFileName
	 * @param bool $bType
	 * @return bool
	 */
	public function validate($cFileName, $bType = false) {
			$bReturn		= false;
			$aConfImages	= false;
			$aConfFiles		= false;

			//Get the position, if there isnt one, then its not going to be valid
			$iDotPos = strrpos($cFileName, '.');
			if (!$iDotPos) { return false; }

			$aFilters		= $this->oNails->getConfig("fiters");
			if ($aFilters) {
				$aConfImages	= $aFilters['images'];
				$aConfFiles		= $aFilters['files'];
			}

			//Since they want to specify the image filters
			if ($aConfImages) {
				$aImages	= $aConfImages;
			} else {
				$aImages    = array("jpg", "png", "gif", "psd", "tiff");
			}

			//Since they want to specify the file filters
			if ($aConfFiles) {
				$aFiles	= $aConfFiles;
			} else {
				$aFiles     = array("txt", "zip", "rar", "doc", "docx", "pdf");
			}

			//What is its extension, true this isnt a very good check, becasue you could just name anything this
			$cExt = strtolower(substr($cFileName, ($iDotPost + 1)));

			switch($bType){
				case 1: //Files
					if (in_array($cExt, $aFiles)) {
						$bReturn = true;
					}
					break;
				case 2: //Images
					if (in_array($cExt, $aImages)) {
						$bReturn = true;
					}
					break;
				default:
					$aNewArray = array_merge($aImages, $aFiles);
					if (in_array($cExt, $aNewArray)) {
						$bReturn = true;
					}
					break;
			} // switch

			return $bReturn;
		}
	}
}
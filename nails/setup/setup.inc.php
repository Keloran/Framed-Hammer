<?php
class Setup {
	//This is only needed for the inital start
	private static $oSetup;

	private $oXML;
	private $oRoot;
	private $aElements;

	function __construct() {
		if (!class_exists("DOMDocument")) {
			echo "You need to use the old method todo the <a href=\"http://www.framedhammer.com/docs/oldsetup\">Old Setup Method</a><br />";
			echo "Or Enable DOMElement";
			die();
		}

		$this->oXML	= new DOMDocument("1.0", "ISO-8859-1");
	}

	static function getSetup() {
		if (is_null(self::$oSetup)) {
			self::$oSetup	= new Setup();
		}

		return self::$oSetup;
	}

	public function addElement($cParent, $cName, $cValue) {
		$this->aElements[$cParent][$cName]	= $cValue;
	}

	public function addParentElement($cName) {
	}

	public function writeConfig() {
		$aConfig	= false;
		$oRoot		= $this->oXML->createElement("config");

		if (isset($this->aElements)) {
			$aElements	= $this->aElements;
			foreach ($aElements as $mKey => $mValue) {
				$oParent = $this->oXML->createElement($mKey);

				if (is_array($mValue)) {
					foreach ($mValue as $cKey => $cValue) {
						$oElement	= $this->oXML->createElement($cKey, $cValue);
						$oParent->appendChild($oElement);
					}	
				}

				$oRoot->appendChild($oParent);
			}

		}

		$this->oXML->appendChild($oRoot);
		return $this->oXML->saveXML();
	}
}

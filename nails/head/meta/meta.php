<?php
/**
 * Head_Meta
 *
 * @package Head
 * @author keloran
 * @copyright Copyright (c) 2011
 * @version $Id$
 * @access public
 */
class Head_Meta {
	use browser;

	private $aData;
	private $oDB;
	private $oNails;

	/**
	 * Constructor
	 */
	function __construct(Nails $oNails) {
		$this->oNails	= $oNails;
		$this->oDB		= $oNails->getDatabase();

		$aGet			= $this->oNails->getConfig("address", "head");
		$cAddress		= $aGet['address'];
		$this->cAddress	= $cAddress;

		$aGet				= $this->oNails->getConfig("keywords", "head");
		$cKeywords			= $aGet['keywords'];
		$this->cKeywords	= $cKeywords;

		$aMeta			= false;
		$aGet			= $this->oNails->getConfig("metaData", $this->oNails->getConfigKey());
		if (isset($aGet['metaData'])) { $aMeta	= $aGet['metaData']; }
		$this->aMeta	= $aMeta;

		$aGet				= $this->oNails->getConfig("description", $this->oNails->getConfigKey());
		$cDescription		= $aGet['description'];
		$this->cDescription	= $cDescription;
	}

	/**
	 * __set()
	 *
	 * @param mixed $cName
	 * @param mixed $mValue
	 * @return
	 */
	public function __set($cName, $mValue) {
		$this->aData[$cName] = $mValue;
	}

	/**
	 * __get()
	 *
	 * @param mixed $cName
	 * @return
	 */
	public function __get($cName) {
		$mReturn	= false;
		if (isset($this->aData[$cName])) { $mReturn = $this->aData[$cName]; }
		return $mReturn;
	}

	public function getKeywords() {
		$cKeyword	= "";
		if ($this->cPageKeywords) {
			$cKeyword = $this->cPageKeywords;
		} else {
			if ($this->cPage) {
				if ($this->iItem) {
					$aSelect = array($this->cPage, $this->iItem);
					$this->oDB->read("SELECT cKeywords FROM keywords WHERE cPage = ? AND iItem = ? LIMIT 1", $aSelect);
					if ($this->oDB->nextRecord()) {
						$cKeyword .= $this->oDB->f('cKeywords') . ",";
					}
				} else {
					$this->oDB->read("SELECT cKeywords FROM keywords WHERE cPage = ? AND iItem = 0 LIMIT 1", $this->cPage);
					if ($this->oDB->nextRecord()) {
						$cKeyword .= $this->oDB->f('cKeywords') . ",";
					}
				}
			}
		}

		$cKeyword	.= $this->cKeywords;
		return "<meta name=\"keywords\" content=\"" . $cKeyword . "\" />\n";
	}

	/**
	 * getDescription()
	 *
	 * @return
	 */
	public function getDescription() {
		if ($this->cPageDescription) {
			$cDescriptions = $this->cPageDescription;
		} else {
			$cDescriptions	= $this->cDescription;
		}

		$cDescription = "<meta name=\"description\" content=\"" . $cDescriptions . "\" />\n";
		return $cDescription;
	}

	/**
	 * getMetaTags()
	 *
	 * @return string
	 */
	public function getMetaTags() {
		$aTags		= $this->aMeta;
		$cCan		= false;
		$bViewPort	= false;

		//get the browser, since there might be a viewport tag for iphone
		#$mBrowser	= getBrowser();
		$bViewPort	= $this->bMobile;

		//The initial tag to denote it as made with Hammer
		$cReturn = "<meta name=\"Generator\" content=\"Hammer Framework\" />\n";

		if ($aTags) {
			foreach($aTags as $cTag => $cValue) {
				if (($cTag == "viewport") && ($bViewPort == false)) { continue; } //Skip the viewport on non iphones
				if ($cTag == "metaData") { continue; } //since this is invalid

				if (strstr($cTag, "|")) {
					$iSplitPos = strpos($cTag, "|");
					$cTag_a = substr($cTag, 0, $iSplitPos);
					$cTag_b = substr($cTag, $iSplitPos + 1, strlen($cTag));

					if ($this->cDocType == "html5") {
						$cReturn .= "<meta name=\"" . $cTag_b . "\" content=\"" . $cValue . "\" />\n";
					} else {
						$cReturn .= "<meta name=\"" . $cTag_b . "\" http-equiv=\"" . $cTag_b . "\" content=\"" . $cValue . "\" />\n";
					}
				} else {
					if ($this->cDocType == "html5") {
						$cReturn .= "<meta name=\"" . $cTag . "\" content=\"" . $cValue . "\" />\n";
					} else {
						$cReturn .= "<meta name=\"" . $cTag . "\" http-equiv=\"" . $cTag . "\" content=\"" . $cValue . "\" />\n";
					}
				}
			}
		}

		//Canoical
		if ($this->cPage) {
			if ($this->cAddress) {
				$cCan = $this->cAddress . "/";
			} else {
				if (isset($_SERVER['SERVER_NAME'])) {
					$cCan = $_SERVER['SERVER_NAME'];
				}
			}

			if (isset($_SERVER['HTTPS'])) {
				$cHTTP = "https";
			} else {
				$cHTTP = "http";
			}
		}


		//always add the charset
		$cReturn .= "<meta charset=\"utf-8\" />\n";

		return $cReturn;
	}
}

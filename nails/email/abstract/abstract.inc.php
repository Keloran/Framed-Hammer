<?php
/**
 * Email_Abstract
 *
 * @package
 * @author keloran
 * @copyright Copyright (c) 2011
 * @version $Id$
 * @access public
 */
abstract class Email_Abstract implements Email_Interface {
	public $iMID;
	public $pIMAP;

	private $aData;

	/**
	 * Email_Abstract::__set()
	 *
	 * @param string $cName
	 * @param mixed $mValue
	 * @return null
	 */
	public function __set($cName, $mValue) {
		$this->aData[$cName] = $mValue;
	}

	/**
	 * Email_Abstract::__get()
	 *
	 * @param string $cName
	 * @return mixed
	 */
	public function __get($cName) {
		return $this->aData[$cName];
	}

	/**
	 * Email_Abstract::__isset()
	 *
	 * @param string $cName
	 * @return bool
	 */
	public function __isset($cName) {
		$bReturn	= false;
		if (isset($this->aData[$cName])) {
			$bReturn = true;
		}

		return $bReturn;
	}

	/**
	 * Email_Abstract::checkConnection()
	 *
	 * @return bool
	 */
	public function checkConnection() {
		$bReturn	= false;

		if ($this->pIMAP) { $bReturn = true; }

		return $bReturn;
	}

	/**
	 * Email_Abstract::decode()
	 *
	 * @param array $aPart
	 * @return string
	 */
	public function decode($aPart) {
		$cCharset	= "UTF-8";
		$cReturn	= false;

		//if there is a charset set
		if ($aPart['struct']->ifparameters) {
			if ($aPart['struct']->parameters[0]->attribute == "charset") {
				$cCharset = $aPart['struct']->parameters[0]->value;
			}
		}

		//get the body
		$cBody	= $aPart['body'];

		//depending on the encoding
		switch ($aPart['struct']->encoding) {
			case 2:
				$cReturn = imap_binary($cBody);
				break;

			case 3:
				$cReturn = imap_base64($cBody);
				break;

			case 4:
				$cReturn = imap_qprint($cBody);
				$cReturn = urldecode($cReturn);
				$cReturn = utf8_encode($cReturn);
				break;

			default:
				$cReturn = imap_8bit($cBody);
				$aReturn = imap_mime_header_decode($cReturn);
				$iReturn = count($aReturn);

				//go through and get the text
				if ($iReturn >= 2) {
					for ($i = 0; $i < $iReturn; $i++) {
						$cReturn	.= $aReturn[$i]->text;
					}
				} else {
					if (isset($aReturn[0])) {
						$cReturn = $aReturn[0]->text;
					}
				}
				break;

		}

		//decode the part
		switch ($cCharset) {
			case "UTF-8":
				$cReturn = utf8_decode($aPart['body']);
				break;

			default:
				$cReturn = $aPart['body'];
				break;
		}

		//strip the =\n
		$cReturn = $this->decode_qprint($cReturn);

		return $cReturn;
	}

	/**
	 * Email_Abstract::decode_qprint()
	 *
	 * @param string $cString
	 * @return string
	 */
	private function decode_qprint($cString) {
		$cReturn = preg_replace("/\=([A-F][A-F0-9])/", "%$1", $cString);
		$cReturn = urldecode($cReturn);
		$cReturn = utf8_encode($cReturn);

		//strip the extras
		$cReturn = str_replace('3D"', '"', $cReturn);
		$cReturn = str_replace("3DISO", "ISO", $cReturn);

		return $cReturn;
	}

	/**
	 * Email_Abstract::simpleDecode()
	 *
	 * @param string $cText
	 * @param int $iType
	 * @return string
	 */
	public function simpleDecode($cText, $iType) {
		$cReturn = $cText;

		switch($iType) {
			//base64
			case 3:
				$cReturn = base64_decode($cText);
				break;

			//qprint
			case 4:
				$cReturn = quoted_printable_decode($cText);
				break;
		}

		return $cReturn;
	}

	/**
	 * Email_Abstract::getBody()
	 *
	 * @return null
	 */
	public function getBody() {}

	/**
	 * Email_Abstract::getStruct()
	 *
	 * @return object
	 */
	public function getStruct() {
		$oStruct	= imap_fetchstructure($this->pIMAP, $this->iMID);

		return $oStruct;
	}

	/**
	 * Email_Abstract::getPartStruct()
	 *
	 * @param string $cPart
	 * @return object
	 */
	public function getPartStruct($cPart) {
		$oStruct	= imap_bodystruct($this->pIMAP, $this->iMID, $cPart);

		return $oStruct;
	}

	/**
	 * Email_Abstract::imap_utf8_fix()
	 *
	 * @param string $string
	 * @return string
	 */
	public function imap_utf8_fix($string) {
		return iconv_mime_decode($string,0,"UTF-8");
	}
}
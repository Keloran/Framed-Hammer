<<<<<<< HEAD
<?php
/**
 * Email_Text
 *
 * @package
 * @author keloran
 * @copyright Copyright (c) 2011
 * @version $Id$
 * @access public
 */
class Email_HTML extends Email_Abstract {
	//message parts
	private $aParts;
	private $cBoundry;

	/**
	 * Email_Text::__construct()
	 *
	 * @param pointer $pIMAP
	 * @param int $iMID
	 */
	public function __construct($pIMAP, $iMID) {
		$this->pIMAP	= $pIMAP;
		$this->iMID		= $iMID;
	}

	/**
	 * Email_Text::getBody()
	 *
	 * @return string
	 */
	public function getBody() {
		$oStruct	= $this->getStruct();
		$cReturn	= false;
		$iEnc		= 1;

		//now go throug the struct, cause there might not be one that is text
		if ($oStruct->type === 1) { //its multipart
			$aParts = $oStruct->parts;

			//now make sure this part isnt also multipart
			if ($aParts[0]->type === 0) {
				//make sure its html aswell, now it shouldnt be, it should be part1 if anything
				if ($aParts[0]->subtype == "HTML") {
					$cReturn	= imap_fetchbody($this->pIMAP, $this->iMID, "1");
					$iEnc		= $aParts[0]->encoding;

				//this should be it really
				} else if ($aParts[1]->subtype == "HTML") {
					$cReturn	= imap_fetchbody($this->pIMAP, $this->iMID, "2");
					$iEnc		= $aParts[1]->encoding;
				}

			//has attachments
			} else if ($aParts[0]->type === 1) {
				$aParts_a	= false;

				//check if there is actually multiparts
				if (isset($aParts[0]->parts)) {
					$aParts_a	= $aParts[0]->parts;

					//now it should really be 1.2 for this one
					if ($aParts_a[1]->subtype == "HTML") {
						$cReturn	= imap_fetchbody($this->pIMAP, $this->iMID, "1.2");
						$iEnc		= $aParts_a[1]->encoding;
					}
				}

			//no idea whats going on here
			} else {
				for ($i = 1; $i < count($aParts); $i++) {
					//now find the first HTML one
					if ($aParts[$i]->subtype == "HTML") {
						$cReturn 	= imap_fetchbody($this->pIMAP, $this->iMID, $i);
						$iEnc		= $aParts[$i]->encoding;
						break;
					}
				}
			}
		}

		//now we have this we really should decode it
		$cReturn = $this->simpleDecode($cReturn, $iEnc);
		$cReturn = $this->stripContainer($cReturn);
		$cReturn = $this->fixLinks($cReturn);

		return $cReturn;
	}

	/**
	 * Email_HTML::fixLinks()
	 *
	 * @param string $cString
	 * @return string
	 */
	private function fixLinks($cString) {
		$cReturn	= $cString;
		//$cReturn	= preg_replace('`((http|https)://)([a-zA-Z0-9\#\-_\&\%\./\?=@:\*]+)`is', '<a href="\1\3" target="_blank">\1\3</a>', $cReturn);

		return $cReturn;
	}

	/**
	 * Email_HTML::stripContainer()
	 *
	 * @param string $cText
	 * @return string
	 */
	private function stripContainer($cText) {
		$cReturn = $cText;

		//now strip the body and html tags to make it valid
		$aSearch = array(
			'`(<html>)`is',
			'`(</html>)`is',
			'`(<body(.+?)>)`is',
			'`(<body>)`is',
			'`(</body>)`is',
			'`(<head>)`is',
			'`(</head>)`is',
			'`(<title>(.+?)</title>)`is',
			'`(<base(.+?)/>)`is',
			'`(<bgsound(.+?)/>)`is',
			'`(<!DOCTYPE(.+?)>)`is',
			'`(body{(.+?)})`is',
			'`(<style type="text/css">(.+?)</style>)`is',
		);

		$aReplace 	= array("");
		$cReturn	= preg_replace($aSearch, $aReplace, $cReturn);

		return $cReturn;
	}
}
=======
<?php
/**
 * Email_Text
 *
 * @package
 * @author keloran
 * @copyright Copyright (c) 2011
 * @version $Id$
 * @access public
 */
class Email_HTML extends Email_Abstract {
	//message parts
	private $aParts;
	private $cBoundry;

	/**
	 * Email_Text::__construct()
	 *
	 * @param pointer $pIMAP
	 * @param int $iMID
	 */
	public function __construct($pIMAP, $iMID) {
		$this->pIMAP	= $pIMAP;
		$this->iMID		= $iMID;
	}

	/**
	 * Email_Text::getBody()
	 *
	 * @return string
	 */
	public function getBody() {
		$oStruct	= $this->getStruct();
		$cReturn	= false;
		$iEnc		= 1;

		//now go throug the struct, cause there might not be one that is text
		if ($oStruct->type === 1) { //its multipart
			$aParts = $oStruct->parts;

			//now make sure this part isnt also multipart
			if ($aParts[0]->type === 0) {
				//make sure its html aswell, now it shouldnt be, it should be part1 if anything
				if ($aParts[0]->subtype == "HTML") {
					$cReturn	= imap_fetchbody($this->pIMAP, $this->iMID, "1");
					$iEnc		= $aParts[0]->encoding;

				//this should be it really
				} else if ($aParts[1]->subtype == "HTML") {
					$cReturn	= imap_fetchbody($this->pIMAP, $this->iMID, "2");
					$iEnc		= $aParts[1]->encoding;
				}

			//has attachments
			} else if ($aParts[0]->type === 1) {
				$aParts1	= false;

				//check if there is actually multiparts
				if (isset($aParts[0]->parts)) {
					$aParts1	= $aParts[0]->parts;

					//now it should really be 1.2 for this one
					if ($aParts1[1]->subtype == "HTML") {
						$cReturn	= imap_fetchbody($this->pIMAP, $this->iMID, "1.2");
						$iEnc		= $aParts1[1]->encoding;
					}
				}

			//no idea whats going on here
			} else {
				for ($i = 1; $i < count($aParts); $i++) {
					//now find the first HTML one
					if ($aParts[$i]->subtype == "HTML") {
						$cReturn 	= imap_fetchbody($this->pIMAP, $this->iMID, $i);
						$iEnc		= $aParts[$i]->encoding;
						break;
					}
				}
			}
		}

		//now we have this we really should decode it
		$cReturn = $this->simpleDecode($cReturn, $iEnc);
		$cReturn = $this->stripContainer($cReturn);
		$cReturn = $this->fixLinks($cReturn);

		return $cReturn;
	}

	/**
	 * Email_HTML::fixLinks()
	 *
	 * @param string $cString
	 * @return string
	 */
	private function fixLinks($cString) {
		$cReturn	= $cString;

		#$cReturn	= preg_replace('`((http|https)://)([a-zA-Z0-9\#\-_\&\%\./\?=@:\*]+)`is', '<a href="\1\3" target="_blank">\1\3</a>', $cReturn);

		return $cReturn;
	}

	/**
	 * Email_HTML::stripContainer()
	 *
	 * @param string $cText
	 * @return string
	 */
	private function stripContainer($cText) {
		$cReturn = $cText;

		//now strip the body and html tags to make it valid
		$aSearch = array(
			'`(<html>)`is',
			'`(</html>)`is',
			'`(<body(.+?)>)`is',
			'`(<body>)`is',
			'`(</body>)`is',
			'`(<head>)`is',
			'`(</head>)`is',
			'`(<title>(.+?)</title>)`is',
			'`(<base(.+?)/>)`is',
			'`(<bgsound(.+?)/>)`is',
			'`(<!DOCTYPE(.+?)>)`is',
			'`(body{(.+?)})`is',
			'`(<style type="text/css">(.+?)</style>)`is',
		);

		$aReplace 	= array("");
		$cReturn	= preg_replace($aSearch, $aReplace, $cReturn);

		return $cReturn;
	}
}
>>>>>>> c0c66965fad63221c98f14c695de9a95e55161f3

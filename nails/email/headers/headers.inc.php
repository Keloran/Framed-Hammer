<?php
/**
 * Email_Headers
 *
 * @package Email
 * @author keloran
 * @copyright Copyright (c) 2011
 * @version $Id$
 * @access public
 */
class Email_Headers extends Email_Abstract {
	private $oHeaders;
	private $iPages;


	/**
	 * Email_Headers::__construct()
	 *
	 * @param pointer $pIMAP
	 * @param int $iMID
	 */
	public function __construct($pIMAP, $iMID = false) {
		$this->pIMAP 	= $pIMAP;
		$this->iMID		= $iMID;

		if ($iMID) { $this->getHeaders(); }
	}

	/**
	 * Email_Headers::getBody()
	 *
	 * @desc not needed on headers, needed for interface
	 * @return
	 */
	public function getBody() {}

	/**
	 * Email_Headers::getHeaders()
	 *
	 * @return
	 */
	public function getHeaders() {
		$this->oHeaders	= imap_headerinfo($this->pIMAP, $this->iMID);

		return $this->oHeaders;
	}

	/**
	 * Email_Headers::getHeader()
	 *
	 * @param string $cHeader
	 * @return mixed
	 */
	public function getHeader($cHeader) {
		if (!$this->oHeaders) { return false; }

		$mReturn	= false;

		if (isset($this->oHeaders->$cHeader)) {
			$mHeader = $this->oHeaders->$cHeader;

			//now is that header an array, or jsut a string
			if (is_array($mHeader)) {
				foreach ($mHeader as $cName => $cValue) {
					$mReturn[$cName] = $cValue;
				}
			} else {
				$mReturn = $mHeader;
			}
		}

		return $mReturn;
	}

	/**
	 * Email_Headers::makeNice()
	 *
	 * @desc this goes through the array and makes it abit more sensible
	 * @param array $aHeader
	 * @return array
	 */
	public function makeNice($mHeader) {
		$aReturn	= false;

		if (is_object($mHeader)) {
			if (isset($mHeader->personal)) { $aReturn['name'] = $mHeader->personal; }

			if (isset($mHeader->mailbox) && isset($mHeader->host)) {
				$aReturn['email']	 = $mHeader->mailbox;
				$aReturn['email']	.= "@";
				$aReturn['email']	.= $mHeader->host;

				//add this for parsers
				$aReturn['host']	= $mHeader['host'];
				$aReturn['box']		= $mHeader['mailbox'];
			}
		} else {
			//name
			if (isset($mHeader['personal'])) { $aReturn['name']	= $mHeader['personal']; }

			//email
			if (isset($mHeader['mailbox']) && isset($mHeader['host'])) {
				$aReturn['email']	 = $mHeader['mailbox'];
				$aReturn['email']	.= "@";
				$aReturn['email']	.= $mHeader['host'];

				//add this for parsers
				$aReturn['host']	= $mHeader['host'];
				$aReturn['box']		= $mHeader['mailbox'];
			}

			if (!isset($mHeader['host'])) { foreach ($mHeader as $header) { $aReturn[] = $this->makeNice($header); }}
		}

		//now reduce it since there might only be 1
		if (count($aReturn) <= 1) {
			$aRet		= $aReturn;
			$aReturn	= false;

			foreach ($aRet[0] as $cKey => $cValue) { $aReturn[$cKey] = $cValue; }
		}

		return $aReturn;
	}

	/**
	 * Email_Headers::getAllHeaders()
	 *
	 * @return array
	 */
	public function getAllHeaders($iLimit = false, $iPage = false) {
		$iHeaders	= imap_num_msg($this->pIMAP);

		//userlimit
		$iLimit		= $iLimit ?: 20;
		$iPage		= $iPage ?: 1;

		$aDebug["Headers"] = $iHeaders;
		$aDebug["Limit"] = $iLimit;
		$aDebug["Page"] = $iPage;

		//set the defaults
		$iStart		= 1;
		$iFullLimit	= 1;

		$aDebug["Start"] = $iStart;
		$aDebug["Full"] = $iFullLimit;

		//limit the amount of entities
		if ($iHeaders >= $iLimit) {
			$iFullLimit = $iLimit;
		} else {
			$iFullLimit = $iHeaders;
		}
		$aDebug["Full After Cond"] = $iFullLimit;

		//now do the calc on pages
		if ($iPage >= 2) {
			$iStart 	= ($iFullLimit * $iPage);
			$iFullLimit	= ($iStart + $iFullLimit);
		}
		$aDebug["Final Start"] = $iStart;
		$aDebug["Final Full"] = $iFullLimit;

		//last calc
		if ($iFullLimit >= $iHeaders) { $iFullLimit = $iHeaders; }

		//go through and get the stuff
		$j	= 0;
		for ($i = $iStart; $i < $iFullLimit; $i++) {
			$this->iMID = $i;
			$this->getHeaders();

			//as given by the system, but it might not have a subject, so make one
			$aHeaders[$j]['subject']	= $this->cleanHeader($this->getHeader("subject"));
			if (!$aHeaders[$j]['subject']) { $aHeaders[$j]['subject'] = "** No Subject **"; }

			$aHeaders[$j]['title']		= $aHeaders[$j]['subject'];
			$aHeaders[$j]['address']	= urlencode(trim($aHeaders[$j]['title']));
			$aHeaders[$j]['uid']		= imap_uid($this->pIMAP, $this->iMID);
			$aHeaders[$j]['size']		= $this->getHeader("Size");
			$aHeaders[$j]['to']			= $this->getHeader("to");
			$aHeaders[$j]['debug']		= $this->oHeaders;
			$j++;
		}

		//now send the amount of pages
		$iPages 	= round(($iHeaders / $iLimit));
		$this->iPages	= $iPages;

		//$aHeaders	= imap_headers($this->pIMAP);
		return $aHeaders;
	}

	/**
	* Clean Headers
	*/
	public function cleanHeader($cText) {
		$cReturn = $cText;

		$aMatch = array(
			'`(=\?iso-8859-1\?q\?(.+?)\?=)`is',
			'`(_)`is',
			'`(=\?UTF-8\?Q\?i(.+?)\?=)`is',
			'`(=\?UTF-8\?Q\?s(.+?)\?=)`is',
			'`(=\?utf-8\?q\?(.+?)\?=)`is',
			'`(=C2=A(\d))`is',
			'`(=A3(\d))`is',
		);

		$aReplace = array(
			'\2',
			' ',
			'\2',
			'\2',
			'\2',
			'&pound;\2',
			'&pound;\2',
		);

		$cReturn = preg_replace($aMatch, $aReplace, $cReturn);

		//proberlly utf8
		if (strstr($cReturn, "=?UTF-8")) {
			$cReturn = $this->imap_utf8_fix($cReturn);
		}

		return $cReturn;
	}

	/**
	 * Email_Headers::getPages()
	 *
	 * @param int $iLimit
	 * @return int
	 */
	public function getPages($iLimit = false) {
		if (!$this->iPages) {
			$iHeaders	= imap_num_msg($this->pIMAP);
			$iLimit		= $iLimit ?: 20;
			$iPages		= round(($iHeaders / $iLimit));
			$this->iPages	= $iPages;
		}

		return $this->iPages;
	}
}

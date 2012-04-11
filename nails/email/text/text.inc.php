<?php
/**
 * Email_Text
 *
 * @package Email
 * @author keloran
 * @copyright Copyright (c) 2011
 * @version $Id$
 * @access public
 */
class Email_Text extends Email_Abstract {
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

			//now make sure this part isnt also multipart (cause this could get annoying
			if ($aParts[0]->type === 0) {
				//make sure its plain aswell, since text should be
				if ($aParts[0]->subtype == "PLAIN") {

					$cReturn 	= imap_fetchbody($this->pIMAP, $this->iMID, "1.1");
					$iEnc		= $aParts[0]->encoding;
				}
			} else if ($aParts[0]->type === 1) {
				$aParts_a = false;

				//its a multipart
				if (isset($aParts[0]->parts)) {
					//now it should be here
					$aParts_a = $aParts[0]->parts;

					if ($aParts_a[0]->type === 0) {
						//now this should always be text
						if ($aParts_a[0]->subtype == "PLAIN") {
							$cReturn	= imap_fetchbody($this->pIMAP, $this->iMID, "1.1");
							$iEnc		= $aParts_a[0]->encoding;
						}
					}
				}
			}

			//now nothing returned so really need to do it again, this time fixed at 1
			if (!$cReturn) {
				if ($aParts[0]->type === 0) {
					$cReturn 	= imap_fetchbody($this->pIMAP, $this->iMID, "1");
					$iEnc		= $aParts[0]->encoding;
				}
			}
		} else { //is it a text email
			if ($oStruct->type === 0) {
				//nothing returned from the multi
				$cReturn 	= imap_fetchbody($this->pIMAP, $this->iMID, "1");
				$iEnc		= $oStruct->encoding;
			}
		}

		//now decode it
		$cReturn	= $this->simpleDecode($cReturn, $iEnc);
		$cReturn	= nl2br($cReturn);

		return $cReturn;
	}
}

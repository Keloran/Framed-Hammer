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
class Email_Attachments extends Email_Abstract {
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
	public function getBody() {}

	private function getPart($oPart, $iPart) {
		$aReturn	= false;
		$iSubPart	= ($iPart - 1);
		$iUpPart	= ($iPart + 1);

		//its an object
		if (is_object($oPart)) {
				//standard params
			$aParams	= $oPart->parameters;

			//if its not an array, get dparams
			if (!is_array($aParams)) { $aParams = $oPart->dparameters; }

			//now we have params, get the filename
			if (isset($aParams[$iSubPart])) { $cFilename = $aParams[$iSubPart]->value; }

			//header
			$cHeader	= $this->getFileHeader($oPart->type);
			if ($cHeader) {
				$cHeader .= strtolower($oPart->subtype);
			} else if ($oPart->type === 0) {
				$cHeader = "text/" . strtolower($aParts->subtype);
			} else {
				$cHeader = "application/octet-stream";
			}

			//now depending on the encoding, hopefully it will only be 3 or less
			$cFile 	= imap_fetchbody($this->pIMAP, $this->iMID, $iUpPart);
			$iEnc	= $oPart->encoding;

			//add to array
			$aReturn['type']		= $cHeader;
			$aReturn['name']		= $cFilename;
			$aReturn['partnum']		= $iUpPart;
			$aReturn['length']		= $oPart->bytes;
			$aReturn['filename']		= $cFilename;
			$aReturn['seoname']		= str_replace(" ", "_", $cFilename);
			$aReturn['enc']			= $iEnc;
			$aReturn['subtype']		= strtolower($oPart->subtype);
			$aReturn['realtype']		= $oPart->type;
			$aReturn['subnum']		= ($iUpPart - 1);
		}

		return $aReturn;
	}

	/**
	 * Email_Attachments::getAttachments()
	 *
	 * @return array
	 */
	public function getAttachments() {
		$oStruct	= $this->getStruct();
		$aReturn	= false;
		$iEnc		= 1;

		//multiple
		$bSingle	= false;
		$bAttachment	= false;
		$j		= 0;

		//now we should see if there are any attachments
		if ($oStruct->type === 1) { //good its a multipart
			$oParts = $oStruct->parts;
			foreach ($oParts as $oPart) {
				$iType = $oPart->type;

				//find the type of the attachment
				switch ($iType) {
					//its an attachment
					case 4:
					case 5:
					case 6:
					case 1:
						$bAttachment	= true;
						break;

					//Its text/html
					default:
						continue;
						break;
				}
			}
		}

		//no attachments at all
		if (!$bAttachment) { return false; }

		//now its attachmnets, go through and get them
		foreach ($oParts as $oPart) {
			$iType 	= $oPart->type;
			$aParts = false;

			//is there parts
			if (isset($oPart->parts)) { $aParts = $oPart->parts; }

			//its a multipart
			if ($aParts) {
				for ($i = 1; $i < (count($aParts) + 1); $i++) {
					if (!isset($aParts[$i]->type)) { continue; }

					$iSubType = $aParts[$i]->type;
					switch($iSubType) {
						case 4:
						case 5:
						case 6:
							$aReturn[] = $this->getPart($aParts[$i], $i);
							break;
						default:
							continue;
							break;
					} // switch
				}
			} else {
				//go through and get the types
				switch($iType){
					case 4:
					case 5:
					case 6:
						$aReturn[] = $this->getPart($oPart, 1);
						break;
				} // switch
			}
		}

		return $aReturn;
	}

	/**
	 * Email_Attachments::getAttachment()
	 *
	 * @param int $iPart
	 * @return string
	 */
	public function getAttachment($iPart) {
		//since need to clean the buffer before doing this
		while (ob_get_level()) { ob_end_clean(); }

		//get the structure
		$oStruct	= $this->getPartStruct($iPart);
		$cFilename	= str_replace(" ", "_", $oStruct->description);
		$iLengthO	= $oStruct->bytes;
		$iEnc		= $oStruct->encoding;

		//filetype
		$cType		 = $this->getFileHeader($oStruct->type);
		$cType		.= strtolower($oStruct->subtype);

		//start a new buffer so that download works
		ob_start();
			$cFile		= imap_fetchbody($this->pIMAP, $this->iMID, $iPart);
			$cDecode 	= $this->simpleDecode($cFile, $iEnc);
			$iLength	= strlen($cDecode);

			//printRead(array($cFile, $cDecode, $iLength, $iLengthO));die();
			header('Content-Description: File Transfer');
			header("Content-Type: " . $cType);
			header("Content-Disposition: attachment; filename=\"" . $cFilename . "\"");
			header("Content-Length: " . $iLength);
			header('Content-Transfer-Encoding: binary');
			echo $cDecode;

			$cReturn = ob_get_contents();
		ob_end_clean();

		return $cReturn;
	}

	/**
	 * Email_Attachments::saveAttachment()
	 *
	 * @param int $iPart
	 * @return string
	 */
	public function saveAttachment($iPart) {
		$oStruct	= $this->getPartStruct($iPart);

		$cFilename	= str_replace(" ", "_", $oStruct->description);
		$iEnc		= $oStruct->encoding;

		//filetype
		$cType		 = $this->getFileHeader($oStruct->type);
		$cType		.= strtolower($oStruct->subtype);

		$cDecode	= $this->simpleDecode(imap_fetchbody($this->pIMAP, $this->iMID, $iPart), $iEnc);
		$cFile		= $this->iMID . "_" . time() . "_" . $cFilename;
		$cReturn	= SITEPATH . "/files/" . $cFile;

		//write the file
		$fp 	= fopen($cFile, 'w');
			fwrite($fp, $cDecode);
		$bReturn =	fclose($fp);

		if ($bReturn) { return $cFile; }

		return false;
	}

	/**
	 * Email_Attachments::getFileHeader()
	 *
	 * @param int $iSubType
	 * @return string
	 */
	private function getFileHeader($iSubType) {
		$cHeader = false;

		//get the maintype
		switch($iSubType) {
			case 3:
				$cHeader	= "application/";
				break;

			case 4:
				$cHeader	= "audio/";
				break;

			case 5:
				$cHeader	= "image/";
				break;

			case 6:
				$cHeader	= "video/";
				break;

			case 7:
				$cHeader	= "other";
				break;
		}

		return $cHeader;
	}

	/**
	 * Email_Attachments::previewAttachment()
	 *
	 * @param int $iPart
	 * @return mixed
	 */
	public function previewAttachment($iPart) {
		$oStruct	= $this->getPartStruct($iPart);
		$cFilename	= false;

		//there is a description to get the filename
		if ($oStruct->ifdescription) {
			$cFilename	= $oStruct->description;
		} else {
			//dparameters
			if ($oStruct->ifdparameters) {
				$aDParams = $oStruct->dparameters;
				for ($i = 0; $i < count($aDParams); $i++) {
					if ($aDParams[$i]->attribute == "filename") {
						$cFilename = $aDParams[$i]->value;
						break;
					}
				}
			} else if ($oStruct->ifparameters) {
				$aDParams = $oStruct->parameters;
                                for ($i = 0; $i < count($aDParams); $i++) {
                                        if ($aDParams[$i]->attribute == "name") {
                                                $cFilename = $aDParams[$i]->value;
                                                break;
                                        }
                                }
			}
		}

		//there is a filename
		if ($cFilename) {
			$cFilename = str_replace(" ", "_", $cFilename);
		} else {
			return false;
		}

		//Encoding
		$iEnc		= $oStruct->encoding;

		//type
		$iType		= $oStruct->type;

		//make sure its an image
		if ($iType !== 5) { return false; }

		//now its an image throw the value to a resource
		$cContent	= $this->simpleDecode(imap_fetchbody($this->pIMAP, $this->iMID, $iPart), $iEnc);
		$cReturn	= false;

		//start the buffer
		$pImage = imagecreatefromstring($cContent);
		if ($pImage !== false) {
			ob_start();
				imagepng($pImage);
				$cImage = ob_get_contents();
			ob_end_clean();

			//clean up
			imagedestroy($pImage);

			if ($cImage) {
				$cFile	= tempnam(SITEPATH . "/images/temp", "tempimg");
				$bWrite	= file_put_contents($cFile, $cImage);

				$iRetFile = strlen(SITEPATH . "/images/temp/");
				$cRetFile =  substr($cFile, $iRetFile);

				//if we have written the file
				if ($bWrite) {
					//resize if neeeded
					list($iWidth, $iHeight) = getimagesize($cFile);
					$iNewWidth	= false;
					$iNewHeight	= false;
					$bResizeW	= false;
					$bResizeH	= false;

					//get the width
					if ($iWidth >= 800) {
						$iNewWidth	= 750;
						$bResizeW	= true;
					} else {
						$iNewWidth = $iWidth;
					}

					//get the hieght
					if ($iHeight >= 700) {
						$iNewHeight	= 650;
						$bResizeH	= false;
					} else {
						$iNewHeight = $iHeight;
					}

					//now lets resize
					if ($bResizeH || $bResizeW) {
						$pNew		= imagecreatetruecolor($iNewWidth, $iNewHeight);
						$pSource	= imagecreatefromstring($cContent);

						// Resize
						imagecopyresampled($pNew, $pSource, 0, 0, 0, 0, $iNewWidth, $iNewHeight, $iWidth, $iHeight);

						//now put that into a buffer
						ob_start();
							imagepng($pNew);
							$cImage = ob_get_contents();
						ob_end_clean();

						//clean up
						imagedestroy($pSource);
						imagedestroy($pNew);

						//now write that buffer to file
						$bWrite = file_put_contents($cFile, $cImage);
					}
				}

				if ($bWrite) { $cReturn = $cRetFile; }
			}
		}

		return $cReturn;
	}
}

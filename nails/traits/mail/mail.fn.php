<?php
/**
 * sendMail()
 *
 * @param mixed $mInfo
 * @param string $cSubject
 * @param string $cContent
 * @param string $cContentText
 * @param string $cFrom
 * @param string $cFromName
 * @param string $cReturn
 * @return bool
 */
function sendMail($mInfo, $cSubject = false, $cContent = false, $cContentText = false, $cFrom = false, $cFromName = false, $cReturn = false) {
	$oSend 		= new Email_Send();
	$bReturn	= false;

	if (is_array($mInfo)) {
		$bReturn = $oSend->compose($mInfo);
	} else {
		$aSend = array(
			"to"		=> $mInfo,
			"from"		=> $cFrom,
			"fromName"	=> $cFromName,
			"return"	=> $cReturn,
			"text"		=> $cContentText,
			"html"		=> $cContent,
			"subject"	=> $cSubject,
		);
		$bReturn = $oSend->compose($aSend);
	}

	return $bReturn;
}

/**
 * sendEmail()
 *
 * @param mixed $mInfo
 * @param string $cSubject
 * @param string $cContent
 * @param string $cContentText
 * @param string $cFrom
 * @param string $cFromName
 * @param string $cReturn
 * @return bool
 */
function sendEmail($mInfo, $cSubject = false, $cContent = false, $cContentText = false, $cFrom = false, $cFromName = false, $cReturn = false) {
	$oSend 		= new Email_Send();
	$bReturn	= false;

	if (is_array($mInfo)) {
		$bReturn = $oSend->compose($mInfo);
	} else {
		$aSend = array(
			"to"		=> $mInfo,
			"from"		=> $cFrom,
			"fromName"	=> $cFromName,
			"return"	=> $cReturn,
			"text"		=> $cContentText,
			"html"		=> $cContent,
			"subject"	=> $cSubject,
		);
		$bReturn = $oSend->compose($aSend);
	}

	return $bReturn;
}

/**
 * sendEmail()
 *
 * @desc This is a better method of sending mail instead of using mail() it sends in html and text
 * @param string $cTo
 * @param string $cSubject
 * @param string $cContent
 * @param string $cFrom
 * @return bool
 */
function oldsendEmail($mInfo, $cSubject = false, $cContent = false, $cContentText = false, $cFrom = false, $cFromName = false, $cReturn = false, $bTextOnly = false) {
	$bTemplate		= false; //if your using a template this will get set to true
	$cTemplate		= false; //the template name
	$aParams		= false;
	$mAttachments	= false;

	$cBoundry	= "----Hammer_Mailer----" . md5(time());

	//is there an array for the first element, pass it through and get elements
	if (is_array($mInfo)) {
		//set this to true since your using the new method,
		//if you want html you will have added it in the array
		$bTextOnly	= true;
		foreach ($mInfo as $cKey => $mValue) {
			switch ($cKey) {
				case "to":
					$cTo = $mValue;
					break;

				case "subject":
					$cSubject = $mValue;
					break;

					//html part of the email
				case "content":
				case "html":
					$cContent	= $mValue;
					$bTextOnly	= false;
					break;

					//text of the email
				case "contentText":
				case "text":
					$cContentText = $mValue;
					break;

					//template thats to be used instead of written html
				case "template":
					$cTemplate	= $mValue;
					$bTemplate	= true;
					$bTextOnly	= false;
					break;

					//template params
				case "templateParams":
				case "params":
					$aParams = $mValue;
					break;

					//From address
				case "from":
					$cFrom	= $mValue;
					break;

					//From name e.g. site.com
				case "fromName":
				case "fromname":
					$cFromName	= $mValue;
					break;

					//is there a return address
				case "return":
				case "returnPath":
				case "returnAddress":
					$cReturn = $mValue;
					break;

				case "attachments":
					$mAttachments = $mValue;
					break;
			}
		}
	} else { //its not an array, its still the old way
		$cTo = $mInfo;
	}

	//From
	if ($cFrom) {
		if ($cFromName) { //there is a name set so has to be put in gt/lt tags
			$cHeaders       = "From: " . $cFromName . " <" . $cFrom . ">\n";
		} else { //no name set
			$cHeaders       = "From: " . $cFrom . "\n";
		}
	}

	//set the some of the headers
	$cHeaders .= "X-Mailer: Hammer\n";
	$cHeaders .= "User-Agent: Hammer\n";
	$cHeaders .= "MIME-Version: 1.0\n";

	//Is it text only
	if ($bTextOnly) {
		$cHeaders .= "Content-type: text/plain; charset=UTF-8\n";
	} else { //Headers to say its multipart
		$cHeaders .= 'Content-Type: multipart/mixed; boundary="' . $cBoundry . '"' . "\n";
	}

	//return
	if (!$cReturn) {
		if ($cFrom) {
			$cHeaders .= "Return-Path: " . $cFrom . "\n";
			$cHeaders .= "Return-path: <" . $cFrom . ">\n";
		}
	} else {
		$cHeaders .= "Return-Path: " . $cReturn . "\n";
		$cHeaders .= "Return-path: <" . $cReturn . ">\n";
	}

	if ($bTextOnly) {
		$cBody = $cContentText;
	} else {
		//Text
		$cBody  = "--" . $cBoundry . "\n";
		$cBody .= "Content-Type: text/plain; charset=UTF-8\n";
		$cBody .= "Content-Transfer-Encoding: 8bit\n\n";
		$cBody .= $cContentText . "\n";

		//HTML part
		$cBody .= "--" . $cBoundry . "\n";
		$cBody .= "Content-Type: text/html; charset=UTF-8\n";
		$cBody .= "Content-Transfer-Encoding: 8bit\n\n";

		//theres a template in place
		if ($bTemplate) {
			if ($cTemplate) {
				$oHammer	= Hammer::getHammer();
				$oTemplate	= $oHammer->getTemplate();
				$oTemplate->setTemplate($cTemplate);

				//theres some params to set
				if ($aParams) {
					foreach ($aParams as $cKey => $cValue) {
						$oTemplate->setParams($cKey, $cValue);
					}
				}

				$cBody .= $oTemplate->renderTemplate();
			} else {
				$cBody .= $cContent;
			}
		} else {
			$cBody .= "<html>\n";
			$cBody .= "<body style=\"font-family:Verdana, Verdana, Geneva, sans-serif; font-size:12px; color:#666666;\">\n";
			$cBody .= $cContent;
			$cBody .= "</body>\n";
			$cBody .= "</html>\n";
		}

		//Close
		$cBody .= "--" . $cBoundry . "--\n";

		//now go through the attachments
		if ($mAttachments) {
			//single attachment
			if (isset($mAttachments['name'])) {
				$cBody .= "Content-Type: ";

				//multiple attachments
			} else {
				for ($k = 0; $k < count($mAttachments); $k++) {

				}
			}
		}
	}

	//see if -f should be used or not
	$bLogin = false;
	if (defined("emailed")) {
		if (strstr($cFrom, emailed)) {
			$bLogin = true;
		}
	}

	if ($bLogin) {
		return mail($cTo, $cSubject, $cBody, $cHeaders, "-f " . $cFrom);
	} else {
		return mail($cTo, $cSubject, $cBody, $cHeaders);
	}
}


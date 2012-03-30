<?php
/**
 * Mailer
 *
 * @package Traits
 * @author Keloran
 * @copyright Copyright (c) 2011
 * @version $Id$
 * @access public
 */
trait Mailer {
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
}

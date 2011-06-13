<?php
/**
 * stribet()
 *
 * @desc This finds the bits between the start and end inside a string
 * @param string $cString The whole string
 * @param string $cStart The starter part to find
 * @param string $cEnd The ender
 * @example stribet(tester@beep>, @, >) returns beep
 * return string
 */
function stribet($cString, $cStart, $cEnd) {
	$cString	= strtolower($cString);
	$cStart		= strtolower($cStart);
	$cEnd		= strtolower($cEnd);

	$iStart		= strpos($cString, $cStart) + 1;
	$cReturn	= substr($cString, $iStart);

	$iEnd		= strpos($cReturn, $cEnd);
	$cReturn	= substr($cReturn, 0, $iEnd);

	return $cReturn;
}

/**
 * shrinkThis()
 *
 * @desc This shrinks the content, and trys not to break the syntax so that it still passes validator
 * @param string $cString
 * @param int $iLength
 * @return string
 */
function shrinkThis($cString, $iLength) {
	$iPosBR		= stripos($cString, "<br />");

	//Start the tags
	$iPosStart_a	= stripos($cString, "<");
	$iPosEnd_a		= stripos($cString, ">");
	$iLength_a		= (($iPosEnd_a + 1) + $iPosStart_a);
	$cStrStart		= substr($cString, $iPosStart_a, $iLength_a);

	//Get the length before the tag
	if (isset($iPosStart_a) && ($iPosStart_a > 0)) {
		$cStart_b	= substr($cString, 0, $iPosStart_a);
		$iLength_b	= strlen($cStart_b);
	}

	//The return string if the tag is after the point wanted to cut anyway
	if (isset($iLength_b) && ($iLength_b > 0)) {
		if ($iLength_b > $iLength) {
			$cReturn = substr($cString, 0, $iLength);
		} else {
			$cReturn = substr($cString, 0, $iLength_b);
		}
	}

	//End the tag
	if (isset($cStart_b)) {
		$iPosEnd_c	= stripos($cString, "</" . $cStart_b . ">");
		$iPosEnd_d	= (3 + $iLength_a);
		$iPosEnd_e	= ($iPosEnd_c + $iPosEnd_d);
	}

	//Get the length after the end of the tags
	if (isset($iPosEnd_e) && ($iPosEnd_e > 0)) {
		$cStart_c	= substr($cString, 0, $iPosEnd_e);
		$iLength_e	= strlen($cStart_c);
	}

	//If the length is less than the end of the tags, then return after tag end
	if (isset($iLength_e) && ($iLength_e > 0)) {
		if ($iLength_e > $iLength) {
			$cReturn	= substr($cString, 0, $iLength);
		}
	}

	if (isset($cReturn)) {
		$cReturn = wordwrap($cReturn, $iLength);
	} else {
		$cReturn = substr($cString, 0, $iLength);
		$cReturn = wordwrap($cReturn, $iLength);
	}

	return $cReturn . "...";
}


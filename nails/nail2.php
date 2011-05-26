<?php
/**
 * getNail_Version()
 *
 * @param string $cNail
 * @param object $oNail
 * @param mixed $mParams
 * @return object
 */
function getNail_Version($cNail, $oNail, $mParams = null) {
	//There are params set
	if ($mParams) {
		$aParams = array($oNail, $mParams);
	} else { //By default they all need the nails object
		$aParams = array($oNail);
	}

	$aNail		= array($cNail, "getInstance");
	$oNail		= call_user_func_array($aNail, $aParams);

	return $oNail;
}
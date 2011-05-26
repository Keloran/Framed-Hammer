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
	return $cNail::getInstance($oNail, $mParams);
}
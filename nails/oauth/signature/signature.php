<?php
/**
 * oauth_signature
 *
 * @package
 * @author keloran
 * @copyright Copyright (c) 2011
 * @version $Id$
 * @access public
 */
abstract class oauth_signature {
	/**
	 * oauth_signature::get_name()
	 *
	 * @return
	 */
	abstract public function get_name();

	/**
	 * oauth_signature::build_signature()
	 *
	 * @param string $cRequest
	 * @param string $cConsumer
	 * @param string                                         $cToken
	 * @return
	 */
	abstract public function build_signature($oRequest, $oConsumer, $cToken);

	public function check_signature($oRequest, $oConsumer, $cToken, $cSignature) {
		$cBuilt	= $this->build_signature($oRequest, $oConsumer, $cToken);

		if (strlen($cBuilt) == 0 || strlen($cSignature) == 0) { return false; }
		if (strlen($cBuilt) != strlen($cSignature)) { return false; }

		//time leak
		$mResult	= 0;
		for ($i = 0; $i < strlen($cSignature); $i++) {
			$mResult |= ord($cBuilt{$i}) ^ ord($cSignature{$i});
		}

		return $mResult == 0;
	}
}
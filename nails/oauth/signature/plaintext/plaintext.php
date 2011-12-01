<?php
/**
 *
 *
 */
class oauth_signature_plaintext extends oauth_signature {
	public function get_name() {
		return "PLAINTEXT";
	}

	public function build_signature($oRequest, $oConsumer, $oToken) {
		$aKey	= array(
			$oConsumer->cSecret,
			($oToken) ? $oToken->cSecret : ""
		);

		$aKey	= oauth_util::urlencode_rfc3986($aKey);
		$cKey	= implode("&", $aKey);

		$oRequest->cBase	= $cKey;

		return $cKey;
	}
}
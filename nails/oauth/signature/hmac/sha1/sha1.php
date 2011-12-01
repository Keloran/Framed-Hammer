<?php
/**
 *
 *
 */
class oauth_signature_hmac_sha1 extends oauth_signature {
	function get_name() {
		return "HMAC-SHA1";
	}

	public function build_signature($oRequest, $oConsumer, $oToken) {
		$cBase	= $oRequest->get_signature_base_string();
		$oRequest->cBase	= $cBase;

		$aKey	= array(
			$oConsumer->cSecret,
			($oToken) ? $oToken->cSecret : ""
		);

		$aKey	= oauth_util::urlencode_rfc3986($aKey);
		$cKey	= implode("&", $aKey);

		return base64_encode(hash_hmac("sha1", $cBase, $cKey, true));
	}
}
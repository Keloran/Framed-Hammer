<?php
/**
 *
 *
 */
abstract class oauth_signature_rsa_sha1 extends oauth_signature {
	public function get_name() {
		return "RSA-SHA1";
	}

	protected abstract function fetch_public_cert($request);
}
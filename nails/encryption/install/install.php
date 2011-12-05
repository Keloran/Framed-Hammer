<?php
class Encryption_Install {
	private $oNails;
	private $oDB;

	/**
 	* Twitter_Install::__construct()
 	*
 	* @param Nails $oNails
	 */
	public function __construct(Nails $oNails) {
		$this->oNails	= $oNails;
		$this->oDB		= $oNails->getDatabase();

		$bInstalled	= $oNails->checkInstalled("encryption");
		if ($bInstalled) {
			$this->upgrade();
		} else {
			$this->install();
		}
	}

	private function install() {
		$this->addVersion("encryption", "1.0");
	}

	private function upgrade() {

	}
}
<?php
class Forum_Install {
	private $oNails;
	private $oDB;

	/**
	 * Forum_Install::__construct()
	 *
	 * @param Nails $oNails
	 */
	function __construct(Nails $oNails) {
		$this->oNails	= $oNails;
		$this->oDB		= $oNails->getDatabase();

		$bInstalled	= $oNails->checkInstalled("forums");
		if ($bInstalled) {
			$this->upgrade();
		} else {
			$this->install();
		}
	}

	/**
	 * Forum_Install::install()
	 *
	 * @return
	 */
	private function install() {
		$this->oNails->addVersion("forums", "1.0");

		$this->oNails->sendLocation("install");
	}

	private function upgrade() {

	}
}

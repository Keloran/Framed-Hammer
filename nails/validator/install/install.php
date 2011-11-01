<?php
/**
 * Validator_Install
 *
 * @package
 * @author keloran
 * @copyright Copyright (c) 2011
 * @version $Id$
 * @access public
 */
class Validator_Install {
	private $oNails;
	private $oDB;

	/**
	 * Validator_Install::__construct()
	 *
	 * @param Nails $oNails
	 */
	function __construct(Nails $oNails) {
		$this->oNails	= $oNails;
		$this->oDB		= $oNails->getDatabase();

		$bInstalled	= $oNails->checkInstalled("validator");
		if ($bInstalled) {
			$this->upgrade();
		} else {
			$this->install();
		}
	}

	function install() {
		$this->oNails->addVersion("validator", "1.0");
		$this->oNails->sendLocation("install");
	}

	/**
	 * Validator_Install::upgrade()
	 *
	 * @return
	 */
	function upgrade() {
		if ($this->oNails->checkVersion("validator", "1.2") == false) {
			//1.1
			$this->oNails->updateVersion("validator", "1.1", false, "De-Coupled");

			//1.2
			$this->oNails->updateVersion("validator", "1.2", false, "Moved to a seperate folder to make replacing easier");
		}
	}
}
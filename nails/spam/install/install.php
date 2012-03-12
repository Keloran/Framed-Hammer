<?php
/**
 * Spam_Install
 *
 * @package
 * @author keloran
 * @copyright Copyright (c) 2011
 * @version $Id$
 * @access public
 */
class Spam_Install{
	private $oNails;
	private $oDB;

	function __construct(Nails $oNails) {
		$this->oNails	= $oNails;
		$this->oDB		= $oNails->getDatabase();

		$bInstalled	= $oNails->checkInstalled("admin");
		if ($bInstalled) {
			$this->upgrade();
		} else {
			$this->install();
		}
	}

	private function upgrade() {

	}

	/**
	 * Spam_Install::install()
	 *
	 * @return
	 */
	private function install() {
		$this->oNails->addVersion("spam", "1.0");

		$this->oNails->sendLocation("install");
	}
}
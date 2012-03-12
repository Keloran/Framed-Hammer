<?php
/**
 * Messages_Install
 *
 * @package
 * @author keloran
 * @copyright Copyright (c) 2011
 * @version $Id$
 * @access public
 */
class Messages_Install {
	private $oNails;
	private $oDB;

	/**
	 * Messages_Install::__construct()
	 *
	 * @param Nails $oNails
	 */
	function __construct(Nails $oNails) {
		$this->oNails	= $oNails;
		$this->oDB		= $oNails->getDatabase();

		$bInstalled	= $oNails->checkInstalled("messages");
		if ($bInstalled) {
			$this->upgrade();
		} else {
			$this->install();
		}
	}

	/**
	 * Messages_Install::upgrade()
	 *
	 * @return
	 */
	private function upgrade() {
		if ($this->oNails->checkVersion("messages", "1.2") == false) {
			//1.1
			$this->oNails->updateVersion("messages", "1.1", false, "Added Groups Notifications");

			//1.2
			$this->oNails->updateVersion("messages", "1.2", false, "Added Users Notifications");
		}
	}

	/**
	 * Messages_Install::install()
	 *
	 * @return
	 */
	private function install() {
		$this->oNails->addVersion("messages", "1.0");

		$this->oNails->sendLocation("install");
	}
}
<?php
/**
 * Email_Send_Install
 *
 * @package
 * @author keloran
 * @copyright Copyright (c) 2011
 * @version $Id$
 * @access public
 */
class Email_Send_Install {
	private $oNails;
	private $oDB;

	/**
	 * Email_Send_Install::__construct()
	 *
	 * @param Nails $oNails
	 */
	function __construct(Nails $oNails) {
		$this->oNails	= $oNails;
		$this->oDB		= $oNails->getDatabase();

		$bInstalled	= $oNails->checkInstalled("email_send");
		if ($bInstalled) {
			$this->upgrade();
		} else {
			$this->install();
		}
	}

	/**
	 * Email_Send_Install::install()
	 *
	 * @return
	 */
	private function install() {
		$this->oNails->addVersion("email_send", "1.0");
		$this->oNails->sendLocation("install");
	}

	/**
	 * Email_Send_Install::upgrade()
	 *
	 * @return
	 */
	private function upgrade() {

	}
}